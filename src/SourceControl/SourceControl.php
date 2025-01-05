<?php

    namespace DO\Main\SourceControl;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use function DO\Main\SourceControl\app;

/**
 * Class SourceControl
 *
 * Represents a mechanism for controlling data sources in a more efficient and reusable way.
 */
class SourceControl extends Model {
    protected $table;

    /**
     * Constructor for SourceControl
     *
     * @param array $attributes Contains the attributes for the initial table setup
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        if (isset($attributes['table'])) {
            $this->setTable($attributes['table']);
        }
    }

    /**
     * Method to set up a query based on given configuration.
     * This involves interpreting and resolving model strings to retrieve sourceControl instances.
     *
     * @param array $config Contains details about the data source control setup.
     *
     * @return self|Builder|null
     * @throws Exception If no model or table name is provided in the configuration.
     */
    public static function setQuery(array $config): self|Builder|null {
        if (!isset($config['model']) && !isset($config['table']) && !isset($config['sourceType'])) {
            return null;
        }
        // Interpretation und Auflösung des Model-Strings
        $sourceControl = isset($config['model']) ? self::resolveModel($config['model']) : new self(['table' => $config['table']]);
        // Anwenden der Konfigurationseinstellungen
        self::applyConfigurations($sourceControl, $config);
        return $sourceControl;
    }

    /**
     * This method resolves dynamic relations in the path to the base model instance.
     * Here, path is in the form of 'segment->relation_name'.
     *
     * @param string $modelString The string path to the model.
     *
     * @return Builder
     * @throws Exception If the path does not start with 'Auth::user()' or if a non-existent method or relationship is
     *                   accessed.
     */
    protected static function resolveModel(string $modelString): Builder {
        if (str_contains($modelString, '->')) {
            return self::resolveDynamicRelation($modelString);
        }
        if (str_contains($modelString, '::')) {
            return self::resolveModelObject($modelString);
        }
        return ModelFactory::make($modelString);
    }

    /**
     * This method applies various configurations (like select, join, where, order, group, having, limit, offset, with)
     * to the given query.
     *
     * @param string $path The query to which configurations have to be applied.
     * @throws Exception
     */
    protected static function resolveDynamicRelation(string $path) {
        $segments = explode('->', $path);
        $baseModel = null;
        foreach ($segments as $index => $segment) {
            $segment = trim($segment);
            try {
                if ($index === 0) {
                    // Überprüfen, ob das Segment ein Klassenname ist
                    if (class_exists($segment)) {
                        $baseModel = app($segment);
                        if (!$baseModel) {
                            throw new Exception("$segment lieferte kein Object.");
                        }
                    } // Überprüfen, ob das Segment 'Auth::user()' ist
                    else if ($segment === 'Auth::user()') {
                        $baseModel = Auth::user() ?? null;
                        if ($baseModel === null) {
                            throw new Exception("Auth::user() lieferte kein Benutzerobjekt.");
                        }
                    } // Wenn weder noch, Fehler werfen
                    else {
                        throw new Exception("Das erste Segment muss ein gültiger Modellname oder 'Auth::user()' sein.");
                    }
                } else {
                    // Methodenaufruf
                    if (str_ends_with($segment, '()')) {
                        $methodName = substr($segment, 0, -2);
                        if (!method_exists($baseModel, $methodName)) {
                            throw new Exception("Methode '$methodName' existiert nicht in der Modellkette: $path");
                        }
                        $baseModel = $baseModel->$methodName();
                    } // Attributzugriff
                    else {
                        if (!isset($baseModel->$segment)) {
                            throw new Exception("Relation '$segment' existiert nicht im aktuellen Modell.");
                        }
                        $baseModel = $baseModel->$segment;
                    }
                }
            } catch (Exception $e) {
                throw new Exception("Fehler in resolveDynamicRelation: " . $e->getMessage() . " Pfad: $path. Beschränkungen verhindern die Nutzung dieser Liste.");
            }
        }
        return $baseModel;
    }

    protected static function resolveModelObject($modelString) {
        $segments = explode('::', $modelString);
        $baseClass = $segments[0];
        // Eine Liste von Methoden, die eine Datenabfrage auslösen
        $dataFetchMethods = ['all', 'get', 'first', 'find', 'pluck', 'count', 'max', 'min', 'avg', 'sum'];
        if (class_exists($baseClass)) {
            $baseModel = $baseClass::query();
        } else {
            return null;
        }
        // Durchlaufe alle Beziehungen, wenn vorhanden
        for ($i = 1; $i < count($segments); $i++) {
            $baseMethod = str_replace("()", "", $segments[$i]);
            if (method_exists($baseModel, $baseMethod) && !in_array($baseMethod, $dataFetchMethods, true)) {
                $baseModel = $baseModel->$baseMethod();
            }
        }
        return $baseModel;
    }

    /**
     * This method applies various configurations (like select, join, where, order, group, having, limit, offset, with)
     * to the given query.
     *
     * @param Builder $query The query to which configurations have to be applied.
     * @param array|Collection $config The configurations to be applied.
     */
    protected static function applyConfigurations(Builder $query, array|Collection $config): void {
        foreach (['select', 'joins', 'where', 'group', 'having', 'with'] as $feature) {
            if (isset($config[$feature])) {
                $functionName = 'apply' . ucfirst($feature);
                self::$functionName($query, $config[$feature]);
            }
        }
    }

    /**
     * Applies the SELECT SQL statement to this query.
     * This statement is used to select fields from DB tables.
     *
     * @param Builder $query - The Laravel Query Builder instance.
     * @param array $fields - Array of field names to apply in SELECT statement.
     */
    protected static function applySelect(Builder $query, array $fields): void {
        foreach ($fields as $key => $field) {
            $fields[$key] = $query->getModel()->getTable() . "." . $field;
        }
        // Wendet die Auswahl auf die bestehende Abfrage an
        $query->select($fields);
    }

    /**
     * Applies the JOIN SQL statement to this query.
     * This statement is used to join the rows from two or more tables, based on a related column between them.
     *
     * @param QueryBuilder $query - The Laravel Query Builder instance.
     * @param array $joins - Array of join details (table, first, operator, second) to apply in JOIN statement.
     */
    protected static function applyJoins(Builder $query, array $joins): void {
        foreach ($joins as $join) {
            $query->join($join['table'], $join['first'], $join['operator'], $join['second']);
        }
    }

    /**
     * Applies the WHERE SQL conditions to this query.
     * These conditions are used to extract only those records that fulfill the condition provided.
     *
     * @param QueryBuilder $query - The Laravel Query Builder instance.
     * @param array $wheres - Array of where conditions (column, operator, value) to apply in WHERE statement.
     */
    protected static function applyWhere(Builder $query, array $wheres): void {
        foreach ($wheres as $where) {
            $query->where($where['column'], $where['operator'], $where['value']);
        }
    }

    /**
     * Applies the ORDER BY SQL statement to this query.
     * This statement is used to sort the result-set in ascending or descending order.
     *
     * @param QueryBuilder $query - The Laravel Query Builder instance.
     * @param array $orders - Array of order details (column, direction) to apply in ORDER BY statement.
     */
    protected static function applyOrder(Builder $query, array $orders): void {
        foreach ($orders as $order) {
            $query->orderBy($order['column'], $order['direction']);
        }
    }

    /**
     * Applies the GROUP BY statement to this query.
     * This statement is used to group rows that have the same values in specified columns into aggregated data.
     *
     * @param QueryBuilder $query - The Laravel Query Builder instance.
     * @param array $groups - Array of column names to apply in GROUP BY statement.
     */
    protected static function applyGroup(Builder $query, array $groups): void {
        $query->groupBy($groups);
    }

    /**
     * Applies the HAVING SQL statement to this query.
     * This statement is used to filter the results of a GROUP BY statement, acting like a WHERE clause that operates
     * on summary functions (like COUNT(), AVG(), etc.).
     *
     * @param Builder $query - The Laravel Query Builder instance.
     * @param array $havings - Array of having conditions (column, operator, value) to apply in HAVING
     *                              statement.
     */
    protected static function applyHaving(Builder $query, array $havings): void {
        foreach ($havings as $having) {
            $query->having($having['column'], $having['operator'], $having['value']);
        }
    }

    /**
     * Applies the LIMIT SQL statement to this query.
     * This statement is used to specify the number of records to return from the query.
     *
     * @param QueryBuilder $query - The Laravel Query Builder instance.
     * @param int $limit - The limit for the return records.
     */
    protected static function applyLimit(Builder $query, int $limit): void {
        $query->limit($limit);
    }

    /**
     * Applies the OFFSET SQL statement to this query.
     * This statement is used to specify the starting point for the records return.
     *
     * @param QueryBuilder $query - The Laravel Query Builder instance.
     * @param int $offset - The offset from where to start the records.
     */
    protected static function applyOffset(Builder $query, int $offset): void {
        $query->offset($offset);
    }

    /**
     * Applies the WITH SQL statement to this query.
     * This statement is used to provide a sub-query block which can be referenced in several places within the main
     * SQL query.
     *
     * @param QueryBuilder $query - The Laravel Query Builder instance.
     * @param array $withs - Array of relation names with corresponding columns to load in WITH statement.
     */
    protected static function applyWith(Builder $query, array $withs): void {
        foreach ($withs as $relation => $columns) {
            if (is_array($columns)) {
                $query->with([
                    $relation => function ($query) use ($columns) {
                        $relatedTable = $query->getModel()->getTable();
                        $qualifiedColumns = array_map(function ($column) use ($relatedTable) {
                            return $relatedTable . '.' . $column;
                        }, $columns);
                        $query->select($qualifiedColumns);
                    }
                ]);
            } else {
                $query->with($relation);
            }
        }
    }
}
