<?php

    namespace DO\Main;

    use Carbon\Carbon;
    use DO\Main\PropertyElements\DataTableProperties;
    use DO\Main\PropertyElements\ListProperties;
    use DO\Main\PropertyElements\ObjectProperties;
    use Exception;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Database\Eloquent\Relations\Relation;
    use Illuminate\Database\Query\Builder;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    use Illuminate\View\View;
    use LogicException;
    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\NotFoundExceptionInterface;
    use ReflectionClass;
    use UnexpectedValueException;

    /**
     * Class ListObject
     *  Represents a list object with data and configuration properties.
     * The ListObject class is a subclass of DataObjectsCore, designed to handle
     * management and presentation of list-like objects.
     * It houses two key properties: objectProperties and listProperties, which are collections
     * that store data on the ListObject and the list itself respectively.
     */
    final class ListObject extends DataObjectsCore
    {
        /**
         * @var ObjectProperties
         * informs about the ListObject
         * @property string $objectID
         * @property string $objectType
         * @property string $objectName
         * @property string $configRequestURI
         * @accessible with getObjectProperties('propertyName');
         */
        protected ObjectProperties $objectProperties;
        /**
         * @var ListProperties
         * informs about the List Properties
         */
        protected ListProperties $listProperties;
        /**
         * @var DataTableProperties
         * informs about the DataTable Properties.
         */
        protected DataTableProperties $dataTableProperties;
        private string|int|null $dataSetID;

        function __construct() {
            parent::__construct();
        }

        /**
         * Initializes and defines a ListObject based on the given parameters.
         * Makes use of the helper function `defineObject` to perform the object definition.
         * It uses objectID as the unique identifier for the object and the data to be assigned to the object comes
         * from the supplied Collection $array.
         * Additionally, it also logs the method execution into the object's log history
         * by calling the `setLog` method with the method name as the argument.
         *
         * @param string $objectID The unique ID of the object to be loaded.
         * @param Collection<string, mixed> $array The data to be assigned to the object.
         *
         * @return ListObject The initialized ListObject.
         * @throws Exception
         */
        final public static function loadObject(string $objectID, Collection $array): ListObject {
            $object = DataObject::bindObject($objectID, (new ListObject())->defineObject($objectID, $array));
            if (!$object instanceof ListObject) {
                throw new UnexpectedValueException('Expected instance of ListObject, got ' . get_class($object));
            }
            return $object;
        }

        /**
         * @param String $objectID
         * @param Collection<string, array<string, mixed>> $config
         *
         * @return ListObject
         * @throws Exception
         */
        final protected function defineObject(string $objectID, Collection $config): ListObject {
            $this->setLog(__METHOD__);
            if (isset($config['menu'])) {
                $this->setMenuItems($config['menu']);
            }
            $object = match ((new ReflectionClass($this))->getShortName()) {
                'ListObject' => DataObject::bindObject($objectID, $this->defineList($objectID, $config)),
                default => throw new Exception('Unexpected value'),
            };
            if (!$object instanceof ListObject) {
                throw new UnexpectedValueException('Expected instance of ListObject, got ' . get_class($object));
            }
            return $object;
        }

        /**
         * Generates a view for the current DataObjects.table with its associated data
         * and a UI configuration.
         * Creates a view using the Blade template 'DataObjects.table', passes various data and configuration
         * settings to that view, and returns the created view.
         * The included settings manage visibility and functionality of different
         * features of the UI like function buttons and edit button. The `MenuItems`
         * for this ListObject instance are also included for menu construction in
         * the view.
         * no data are passing to the view.
         * the view generates after loading an objects to retrieve the config and the data via ajax
         *
         * @return View The generated table view.
         */
        public function outputObject(): View {
            $this->setLog(__METHOD__);
            return view('DO::table')
                ->with('functionButtons', false)
                ->with('editButton', false)
                ->with('menuItems', $this->getMenuItems())
                ->with('object', $this);
        }

        /**
         * Returns the current object.
         * Response depends on the use of request('processingMethod')
         *
         * @return array<string, array<string, mixed>>|null The current object.
         * - getObject: delivers the configuration of the listObjects
         * - getResults: delivers the results based on the configuration and the associated requests
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         * @throws Exception
         */
        public function getObject(): array|null {
            $request = request();
            $objectResult = [];
            $objectResult['objectID'] = $this->getObjectProperty('objectID');
            if ($request->has('dataSetID')) $this->setDataSetID($request->get('dataSetID'));
            if (request()->get('processingMethod') === 'getObject' || count(request()->all()) === 0) {
                $this->processingProperties();
                $objectResult['objectProperties'] = $this->getObjectProperty();
                $objectResult['listProperties'] = $this->getListProperty();
                $objectResult['dataTableProperties'] = $this->getDataTableProperty();
                $objectResult['menu'] = $this->getMenuItems()->getStacks()->map(function ($item) {
                    return $item['basket']->item();
                });
                $objectResult['fields'] = $this->getFieldProperty();
            } elseif (request()->get('processingMethod') === 'changeValue') {
                echo "send data to change a value in a table\n";
//            var_dump(request('parameters'));
                $objectResult['state'] = $this->changeValueInTable();
            } elseif (request()->get('processingMethod') === 'getResults') {
                $objectResult = $this->getResults();
            }
//        $objectResult['log'] = $this->getLog();
//        var_dump($objectResult);
            return $objectResult;
        }

        /**
         * Sets the data set ID for the object.
         *
         * @param string|int|null $id The ID of the data set.
         *
         * @return self The current object.
         */
        private function setDataSetID(string|int|null $id = null): self {
            $this->setLog(__METHOD__);
            $this->dataSetID = $id;
            return $this;
        }

        /**
         * Processes field properties, including rendering options if they are defined.
         * Iterates over all properties for a table to fetch them and ensure correct table display.
         * If the field has render options defined in the 'fieldRenderOptions' configuration,
         * the associated render function is called, and the resulting rendered options are saved in the field properties.
         *
         * @effects Modifies field properties and adds entries to the operation log. Throws an exception if a render
         *          function does not exist.
         * @throws Exception
         */
        private function processingProperties(): void {
            $this->setLog(__METHOD__);
            // Alle Eigenschaften für die Tabelle müssen gezogen werden, damit die Tabelle korrekt angezeigt wird.
            foreach ($this->getFieldIndex() as $field) {
                if (is_string($field)) $this->getField($field)->processingProperties();
                if ($this->getFieldProperty((string)$field, 'fieldRenderOptions') !== null && array_key_exists($field, $this->fieldConfigs['fieldRenderOptions'])) {
                    $options = [];
                    $className = explode("::", $this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderFunction'])[0];
                    $namespace = '';
                    $fullyQualifiedClassName = $namespace . '\\' . $className;
                    $methodName = explode("::", $this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderFunction'])[1];
                    if (class_exists($fullyQualifiedClassName) && is_callable([$fullyQualifiedClassName, $methodName])) {
                        $funcResult = call_user_func([$fullyQualifiedClassName, $methodName], $this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['identifier']); // `$value` wird als Parameter übergeben
                        foreach ($funcResult as $value) {
                            if (is_string($this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['output'])) {
                                if (isset($value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['value']])) {
                                    $options[$value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['value']]] = [
                                        'value' => $value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['value']],
                                        'identifier' => $value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['identifier']],
                                        'text' => $value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['output']]
                                    ];
                                }
                            } else {
                                $name = '';
                                foreach ($this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['output'] as $data) {
                                    if (isset($data) && $data != '')
                                        $name .= $value[$data];
                                    else $name .= ' ';
                                }
                                $options[$value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['value']]] = ['value' => $value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['value']], 'identifier' => $value[$this->getFieldProperty((string)$field, 'fieldRenderOptions')['renderOutput']['identifier']], 'text' => $name];
                            }
                        }
                        $this->setLog("fieldRenderOptions for field " . $field . " found");
                        $this->setFieldProperty((string)$field, 'fieldOptions', $options);
                    } else throw new Exception("fieldRenderOptions method " . $methodName . " in " . $className . " for field " . $field . " not exists");
                }
            }
        }

        private function changeValueInTable() {
            $success = false;

            // Klonen der Primärquelle
            $source = clone $this->dataSources()->getPrimarySource();
            $model = $source->getModel();

            // Abfrage vor dem Abrufen des Datensatzes
            $query = $model
                ->where($this->getFilterKey(), '=', $this->getFilter())  // z.B. groupid
                ->where($this->getListProperty('primaryKey'), '=', $this->getDataSetID());  // Primärschlüssel

            // Führe die Abfrage aus, um den spezifischen Datensatz abzurufen
            $data = $query->first();
            // Überprüfen, ob ein Datensatz gefunden wurde
            if (!$data) {
                return response()->json(['error' => 'Datensatz nicht gefunden'], 404);
            }

            // Aktualisiere das spezifische Feld mit dem neuen Wert
            $field = request('parameters.field');
            $newValue = request('parameters.newValue');

            // Erzwinge die Aktualisierung mit forceFill(), um sicherzustellen, dass das Modell als geändert erkannt wird
            $data->forceFill([$field => $newValue]);
            // Anstatt das normale save() zu verwenden, fügen wir manuell die variableid zur WHERE-Klausel hinzu
            return (bool)$model->where('groupid', $data->groupid)
                ->where('variableid', $data->variableid)
                ->update([$field => $newValue]);
        }

        private function getDataSetID(): ?int {
            $this->setLog(__METHOD__);
            return $this->dataSetID;
        }

        /**
         * Retrieves the results from a data source query.
         *
         * @return array<string, mixed> The array containing the results. The structure of the array depends on the
         *     conditions of the query:
         *               - If the data source has available sources, the method will return the results of the processing
         *               query, which can be null or an array.
         *               - If there are data results available, the method will return an array with the following
         *               key-value pairs:
         *                 - 'draw': The number of draw requests made.
         *                 - 'data': An array representing the data results.
         *                 - 'recordsTotal': The total number of records in the data results.
         *                 - 'recordsFiltered': The number of records after filtering the data results.
         *               - If there are no data sources or data results available, the method will return the following
         *               array:
         *                 - 'data': An empty array.
         *                 - 'recordsTotal': 0.
         *                 - 'recordsFiltered': 0.
         *                 - 'message': A message indicating that there are no data sources available.
         *                 - 'status': The status of the error, which is always set to 'error'.
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         * @throws Exception
         */
        public function getResults(): array {
            $this->setLog(__METHOD__);
            // is there a source?
            if ($this->dataSources()->hasSources()) {
                return $this
                    ->processingQuery();
            }
            if ($this->hasDataResults()) {
                $recordsTotal = $recordsFiltered = $this->dataResults()->get($this->dataResults()->whichPrimary())->count();
                return [
                    'objectID' => $this->getObjectProperty('objectID'), // 'objectID' => 'listObject
                    'draw' => request('parameters.draw') ?? 1,
                    'data' => $this->dataResults()->get(
                        $this->dataResults()->whichPrimary()
                    )->take(
                        request()->get('length', 25)
                    )->skip(
                        request()->get('start', 0)
                    )->sort()->toArray(),
                    'message' => 'Daten manuell geladen',
                    'state' => 'success',
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                ];
            }
            return [
                'objectID' => $this->getObjectProperty('objectID'),
                'draw' => request('parameters.draw') ?? 1,
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'state' => 'error',
                'error' => "Keine Datenquelle vorhanden",
            ];
        }

        /**
         * Retrieves the count of records from the primary data source's results.
         * Checks if there are data sources defined and whether data results are available.
         * If data results exist and the primary result set is defined, it returns the count of records.
         * If no data sources or results are available, it returns 0.
         * @return int The count of records in the primary data result set, or 0 if no results are available.
         */
        public function getCount(): int {
            if ($this->dataSources()->hasSources()) {
                if (!$this->hasDataResults()) {
                    $this
                        ->processingQuery();
                }

                return $this->dataResults()->get($this->dataResults()->whichPrimary())->count();

            }
            return 0;
        }

        /**
         * Processes the query for fetching data from the database.
         * If the query builder is an instance of \Illuminate\Contracts\Database\Eloquent\Builder,
         * the processingEloquentQueryBuilder method is called and its result is returned.
         * Otherwise, the getBuilderError method is called and its result is returned.
         *
         * @return array<string, mixed> The result of the query processing.
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         * @throws Exception
         */
        private function processingQuery(): array {
            $this->setLog(__METHOD__);
            // get queryBuilder
            $queryBuilder = clone $this->getQueryBuilder();
            // Check if queryBuilder is an instance of Eloquent Builder
            if ($queryBuilder instanceof \Illuminate\Contracts\Database\Eloquent\Builder) {
                return $this->processingEloquentQueryBuilder($queryBuilder);
            }
            // Check if queryBuilder is an instance of generic Builder
            if ($queryBuilder instanceof Builder) {
                return $this->processingQueryBuilder($queryBuilder);
            }
            // This should never happen if the above cases are comprehensive
            throw new LogicException('Unhandled type of query builder.');
        }

        /**
         * Processes the Eloquent query builder.
         * Parses the request parameters to filter, sort, and search the query.
         * Modifies the query builder object.
         *
         * @param \Illuminate\Contracts\Database\Eloquent\Builder $queryBuilder The query builder object to process.
         *
         * @return array<string, mixed> The result of the processed query, including the total number of records and the
         *     filtered result set.
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         * @throws Exception
         */
        private function processingEloquentQueryBuilder(\Illuminate\Contracts\Database\Eloquent\Builder $queryBuilder): array {
            $this->setLog(__METHOD__);
//        Worker::log(json_encode([
//            'start' => request('parameters.start', 1),
//            'length' => request('parameters.length', 25),
//        ]), JSON_PRETTY_PRINT);
            $request = request(); // Zugriff auf die Request-Daten
            // if there is an filter, append it to the query
            $queryBuilder->where($this->getFilterKey(), $this->getFilter());
            // count total records and records filtered
            $dummy = $queryBuilder;
            $recordsTotal = $recordsFiltered = $dummy->count();
            $recordSum = [];
            if ($this->getDataTableProperty('columnSum') !== false) {
                foreach ($this->getDataTableProperty('columnSum') as $column) {
                    $recordSum[$column] = $queryBuilder->sum($column);
                }
            }
            // init filtered flag
            $filtered = false;
            $fieldSources = $this->extractFieldSources();
            $columnFilterArray = [];
            // check request for column search
//        Worker::log(json_encode([
//            request('parameters.columns')
//        ], JSON_PRETTY_PRINT));
            foreach (request('parameters.columns') ?? [] as $column) {
//            Worker::log(json_encode([
//                $column
//            ], JSON_PRETTY_PRINT));
                $cleanedString = null;
                // check, if field has an assigned search field
                $fieldID = (string)$column['data']; // oder (int) $fieldID, je nach Typ der Schlüssel
                // clean search value
                $cleanedString = $column['search']['value'] !== null ? str_replace(['^', '$'], '', $column['search']['value']) : '';
                $cleanedString = str_replace("\\", "", $cleanedString);
                if ($cleanedString === '')
                    $cleanedString = null; // TODO: check, because sometimes using null in some cases
                if ($cleanedString === '_all')
                    $cleanedString = null;
                // check if search value is not empty string
                if (!is_null($cleanedString)) {
                    if ($this->getFieldProperty($fieldID, 'fieldType') === 'select' && str_contains($cleanedString, '|')) {
                        $cleanedString = explode('|', $cleanedString);
                        if (in_array("", $cleanedString, true)) {
                            $cleanedString = array_diff($cleanedString, [""]);
                        }
                    }
                    $columnFilterArray[$fieldID] = $cleanedString;
                    // wir steigen in die Suche ein, müssen vorher prüfen, ob es ein feld oder eine relation gibt
                    if ($fieldSources->has($fieldID)) {

                        $queryBuilder->where(function ($query) use ($fieldSources, $fieldID, $cleanedString) {
                            foreach ((array)$fieldSources->get($fieldID) as $source) {
                                $source = \collect((array)$source);
                                $fieldOrRelation = $source->get('fieldOrRelation');
                                $fieldInRelation = $source->get('fieldInRelation');
                                $model = $query->getModel();
                                if (method_exists($model, $fieldOrRelation) && is_a($model->$fieldOrRelation(), Relation::class)) {
                                    if ($this->getFieldProperty($fieldID, 'fieldType') === 'select') {
                                        $query->orWhereHas($fieldOrRelation, function ($query) use ($fieldInRelation, $cleanedString) {
                                            if (is_string($cleanedString))
                                                if ($fieldInRelation !== null)
                                                    $query->whereIn($fieldInRelation, [$cleanedString]);
                                                else $query->whereIn($fieldInRelation, $cleanedString);
                                        });
                                    } else {
                                        $query->orWhereHas($fieldOrRelation, function ($query) use ($fieldInRelation, $cleanedString) {
                                            if (is_string($cleanedString) && $fieldInRelation !== null)
                                                $query->where($fieldInRelation, "LIKE", "%" . $cleanedString . "%");
                                        });
                                    }
                                } else {
//                                Worker::log(json_encode([
//                                    'fieldID' => $fieldID,
//                                    'cleanedString' => $cleanedString,
//                                    "has" => $fieldSources->has($fieldID),
//                                    'source' => $source->toArray(),
//                                    'relation' => $fieldOrRelation,
//                                    'fieldInRelation' => $fieldInRelation,
//                                    'fieldTpe' => $this->getFieldProperty($fieldID, 'fieldType'),
//                                ], JSON_PRETTY_PRINT));
                                    if ($this->getFieldProperty($fieldID, 'fieldType') === 'select') {
                                        if ($cleanedString === 'null')
                                            $query->whereNull($fieldID);
                                        if (is_array($cleanedString)) {
                                            $query->orWhereIn($fieldID, $cleanedString);
                                        } else {
                                            $query->orWhereIn($fieldID, [$cleanedString]);
                                        }
                                    } else {

                                        if ($fieldInRelation !== null) {
                                            if (is_string($cleanedString) && str_contains($cleanedString, ' ')) {
                                                foreach (explode(' ', $cleanedString) as $string) {
                                                    $query->orWhere($fieldOrRelation, "LIKE", "%" . $string . "%");
                                                }
                                            } else {
                                                if ($cleanedString) $query->orWhere($fieldID, "LIKE", "%" . $cleanedString . "%");
                                            }
                                        } else {
                                            if ($cleanedString) $query->orWhere($fieldID, "LIKE", "%" . $cleanedString . "%");
                                        }
                                    }
                                }
                            }
                        });
                    }
                    $filtered = true;
                }
            }
//        Worker::log(json_encode([
//            'hasSearch' => $request->has('parameters.search')
//        ], JSON_PRETTY_PRINT));
            if (request()->has('parameters.search') && request('parameters.search')['value'] !== "" && request('parameters.search')['value'] !== null) {
                $likeValue = request('parameters.search')['value'] ?? null;
                if ($likeValue !== null) {
                    $likeValue = str_replace(['^', '$'], '', $likeValue);
                }
                $searchValue = $likeValue;
                if ($searchValue !== '') {
                    $queryBuilder->where(function ($query) use ($searchValue, $fieldSources) {
                        foreach ($fieldSources as $fieldID => $sources) {
                            foreach ($sources as $source) {
                                $source = \collect((array)$source);
                                $fieldOrRelation = $source->get('fieldOrRelation');
                                $fieldInRelation = $source->get('fieldInRelation');
                                $model = $query->getModel();
                                if (method_exists($model, $fieldOrRelation) && is_a($model->$fieldOrRelation(), Relation::class)) {
                                    if ($this->getFieldProperty($fieldID, 'fieldType') === 'select') {
                                        if (isset($this->getFieldProperty($fieldID, 'fieldRenderOptions')['renderOutput']['output'])) {
                                            \collect((array)$this->getFieldProperty($fieldID, 'fieldRenderOptions')['renderOutput']['output'])->each(function ($item) use ($model, $fieldOrRelation, $searchValue) {
                                                $model->orWhereHas($fieldOrRelation, function ($query) use ($item, $searchValue) {
                                                    if ($item !== '')
                                                        if (is_string($searchValue)) $query->orWhere($item, "LIKE", "%" . $searchValue . "%");
                                                });
                                            });
                                        } else {
                                            $query->orWhereHas($fieldOrRelation, function ($query) use ($fieldInRelation, $searchValue) {
                                                if (is_string($searchValue))
                                                    if ($fieldInRelation !== null)
                                                        $query->whereIn($fieldInRelation, [$searchValue]);
                                                    else $query->whereIn($fieldInRelation, $searchValue);
                                            });
                                        }
                                    } else {
                                        $query->orWhereHas($fieldOrRelation, function ($query) use ($fieldInRelation, $searchValue) {
                                            if ($fieldInRelation !== null)
                                                if (is_string($searchValue)) $query->where($fieldInRelation, "LIKE", "%" . $searchValue . "%");
                                        });
                                    }
                                } else {
                                    if ($this->getFieldProperty($fieldID, 'fieldType') === 'select') {
                                        if ($searchValue === 'null')
                                            $query->whereNull($fieldOrRelation);
                                        if (is_array($searchValue)) {
                                            $query->orWhereIn($fieldOrRelation, $searchValue);
                                        } else {
                                            $query->orWhereIn($fieldOrRelation, [$searchValue]);
                                        }
                                    } else {
                                        if ($fieldOrRelation !== null) {
                                            if (is_string($searchValue) && str_contains($searchValue, ' ')) {
                                                foreach (explode(' ', $searchValue) as $string) {
                                                    $query->orWhere($fieldOrRelation, "LIKE", "%" . $string . "%");
                                                }
                                            } else if (is_string($searchValue)) $query->orWhere($fieldOrRelation, "LIKE", "%" . $searchValue . "%");
                                        }
                                    }
                                }
                            }
                        }
                    });
                    $filtered = true;
                }
            }
            // check request for orderings
            foreach (request('parameters.order') ?? [] as $order) {
                // get column name
                $dataColumn = request('parameters.columns')[$order['column']]['data'];
                // get assigned field name
                $fieldSearchAssign = $this->getFieldProperty($dataColumn, 'fieldSortAssign');
                // set orderByColumn
                $orderByColumn = $fieldSearchAssign !== null ? $fieldSearchAssign : $dataColumn;
                if (array_key_exists($orderByColumn, $fieldSources->toArray())) {
                    foreach ((array)$fieldSources->get($orderByColumn) as $source) {
                        $source = \collect((array)$source);
                        $fieldOrRelation = $source->get('fieldOrRelation');
                        $fieldInRelation = $source->get('fieldInRelation');
                        $model = $queryBuilder->getModel();
                        if (method_exists($model, $fieldOrRelation) && is_a($model->$fieldOrRelation(), Relation::class)) {
                            $relation = $model->$fieldOrRelation();
                            if ($relation instanceof BelongsToMany) {
                                $pivotTable = $relation->getTable();
                                $relatedTable = $relation->getRelated()->getTable();
                                $queryBuilder->join($pivotTable, function ($join) use ($model, $pivotTable) {
                                    $join->on($model->getQualifiedKeyName(), '=', "$pivotTable." . $model->getForeignKey());
                                });
                                $queryBuilder->join($relatedTable, "$pivotTable." . $relation->getRelatedPivotKeyName(), '=', "$relatedTable." . $relation->getRelatedKeyName());
                                $queryBuilder->orderBy("$pivotTable.$fieldInRelation", $order['dir']);
                            } else if ($relation instanceof BelongsTo) {
                                $relatedTable = $relation->getRelated()->getTable();
                                $foreignKey = $relation->getForeignKeyName();
                                $ownerKey = $relation->getOwnerKeyName();
                                $alias = $relatedTable . '_' . Str::random(8); // Generate a unique alias to avoid conflicts
                                $queryBuilder->leftJoin("$relatedTable as $alias", "$alias.$ownerKey", '=', "{$model->getTable()}.$foreignKey");
                                // Add custom sorting logic to handle mixed numeric and string values and empty values last
                                $queryBuilder->orderByRaw("CASE
                                WHEN $alias.$fieldInRelation IS NULL OR $alias.$fieldInRelation = '' THEN 1
                                WHEN $alias.$fieldInRelation REGEXP '^[0-9]+$' THEN 0
                                ELSE 0
                                END,
                                CASE
                                WHEN $alias.$fieldInRelation REGEXP '^[0-9]+$' THEN LPAD($alias.$fieldInRelation, 10, '0')
                                ELSE $alias.$fieldInRelation
                                END {$order['dir']}");
                            } elseif ($relation instanceof HasOne || $relation instanceof HasMany) {
                                throw new Exception("not implemented yet");
                            }
                        } else {
                            if ($source->has('dataSource') && $source['dataSource'] !== null) {
                                // Add custom sorting logic to handle mixed numeric and string values and empty values last
                                $queryBuilder->orderByRaw("CASE
                                WHEN $orderByColumn IS NULL OR $orderByColumn = '' THEN 1
                                WHEN $orderByColumn REGEXP '^[0-9]+$' THEN 0
                                ELSE 0
                                END,
                                CASE
                                WHEN $orderByColumn REGEXP '^[0-9]+$' THEN LPAD($orderByColumn, 10, '0')
                                ELSE $orderByColumn
                                END {$order['dir']}");
                            }
                        }
                    }
                } else {
                    // Add custom sorting logic to handle mixed numeric and string values and empty values last
                    $queryBuilder->orderByRaw("CASE
                    WHEN $orderByColumn IS NULL OR $orderByColumn = '' THEN 1
                    ELSE 0
                  END,
                  CASE
                    WHEN $orderByColumn REGEXP '^[0-9]+$' THEN LPAD($orderByColumn, 10, '0')
                    ELSE $orderByColumn
                  END {$order['dir']}");
                }
            }
            // count again if filtered
            if ($filtered === true)
                $recordsFiltered = $queryBuilder->count();
            // orderings
            $queryBuilder->skip(request('parameters.start', 0))->take(request('parameters.length', 100) ?? $this->sourceProperties()->whichLimit());
            // fetch results
            $results = $queryBuilder->get();
            // store results in dataResults
            $this->dataResults()->add($results, $this->sourceProperties()->whichPrimary());
            // return the model to the dataSources with current query
            //        $this->dataSources()->create($queryBuilder, $this->sourceProperties()->whichPrimary());
            // init endResults
            $endResults = [];
            foreach ($this->dataResults()->get($this->sourceProperties()->whichPrimary()) as $row) {
                // processing field data
                $endResults[] = $this->processingFieldData($row, $columnFilterArray);
            }
//        Worker::log("Draw . " . json_encode(request('parameters.draw'), JSON_PRETTY_PRINT));
            // return results
            return [
                'objectID' => $this->getObjectProperty('objectID'),
                'draw' => request('parameters.draw') ?? 1,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $endResults,
                'recordSums' => $recordSum,
                'state' => 'success',
            ];
        }

        /**
         * Extracts field sources from the field configuration.
         * This method retrieves the field sources defined in the 'fieldSource' configuration,
         * filtering out any null values. It then maps over the sources and returns an array
         * containing the extracted field source information for each field.
         *
         * @return Collection<string, array<string, mixed>> The extracted field sources.
         */
        private function extractFieldSources(): Collection {
            $fieldSourceConfig = \collect($this->fields()->properties('fieldSource'));
            $fieldSourceConfig = $fieldSourceConfig->filter(function ($value) {
                return $value !== null;
            });
            return $fieldSourceConfig->map(function ($sources) {
                return \collect((array)$sources)->map(function ($source) {
                    // Der erste Schlüssel bezieht sich auf die Datenquelle in ->dataSource()
                    $dataSource = $source[0];
                    // Der zweite Schlüssel bezieht sich auf ein Feld oder eine Relation
                    $fieldOrRelation = $source[1] ?? null;
                    // Der dritte Schlüssel, falls vorhanden, bezieht sich auf ein Feld in der Relation
                    $fieldInRelation = $source[2] ?? null;
                    return [
                        'dataSource' => $dataSource === ' ' ? null : $dataSource,
                        'fieldOrRelation' => $fieldOrRelation,
                        'fieldInRelation' => $fieldInRelation,
                    ];
                });
            });
        }

        /**
         * Processes the sources of defined fields and applies the relevant properties to the passed data row set.
         * This method iterates over the defined field sources and applies them to the passed
         * data row set. The produced result contains a mapping between the field keys and their produced values,
         * as well as the default 'checkbox' and 'tools' fields which are set to empty strings.
         *
         * @param mixed $row A row set of data to be processed.
         * @param array<int|string, array<int, string>|string> $filterArray
         *
         * @return array<string, string> An associative array containing the processed field data.
         * @effects Modifies $row, depending on the processing logic of the function.
         * @throws Exception
         */
        private function processingFieldData(mixed $row, array $filterArray): array {
            $fields = $this->getFieldIndex();
            $result = [];
            $data = $row->toArray();
            $result['DT_RowId'] = $data[$this->getListProperty('primaryKey')];
            $formats = [
                'Y-m-d',        // ISO-Standard
                'd.m.Y',        // Deutsch
                'm/d/Y',        // US-Format
                'd-m-Y',        // Alternativ
                'Y/m/d',        // Alternativ
                'd F Y',        // Langform
                'F d, Y',       // US-Langform
                'd-m-y',        // Kurzform
                'H:i',          // Zeitformat
                'H:i:s',        // Zeit mit Sekunden
                'g:i A',        // AM/PM-Format
                'Y-m-d H:i:s',  // ISO-Datumszeit
                'Y-m-d\TH:i:s.uP', // ISO 8601 mit Mikrosekunden und Zeitzone
                'Y-m-d\TH:i:sP',   // ISO 8601 mit Zeitzone
            ];
            foreach ($fields as $field) {
                $result[$field] = null;
                if (array_key_exists($field, $data)) {
                    if ($this->getFieldProperty($field, 'fieldType') === 'checkbox') {
                        $data[$field] = '';
                    }
                    if ($this->getFieldProperty($field, 'fieldType') === 'tools') {
                        $data[$field] = '';
                    }
                    if (in_array($this->getFieldProperty($field, 'fieldType'), ['date', 'datetime', 'time'])) {
                        $parsedDate = null;

                        foreach ($formats as $format) {
                            try {
                                $parsedDate = Carbon::createFromFormat($format, $data[$field]);

                                if ($parsedDate !== false) {
                                    break; // Erfolgreiches Parsen
                                }
                            } catch (Exception $e) {
                                continue; // Nächstes Format versuchen
                            }
                        }

                        // Fallback für ISO 8601
                        if (!$parsedDate) {
                            try {
                                $parsedDate = Carbon::parse($data[$field]);
                            } catch (Exception $e) {
                                $parsedDate = null;
                            }
                        }

                        if ($parsedDate) {
                            switch ($this->getFieldProperty($field, 'fieldType')) {
                                case 'date':
                                    $data[$field] = $parsedDate->format('d.m.Y');
                                    break;
                                case 'datetime':
                                    $data[$field] = $parsedDate->format('d.m.Y H:i:s');
                                    break;
                                case 'time':
                                    $data[$field] = $parsedDate->format('H:i:s');
                                    break;
                            }
                        } else {
                            $data[$field] = null;
                        }
                    }
                    if ($this->getFieldProperty($field, 'fieldType') === 'boolean') {
                        $data[$field] = isset($data[$field]) && $data[$field] ? 1 : 0;
                    }
                    if ($this->getFieldProperty($field, 'fieldType') === 'filesize') {
                        $data[$field] = formatFileSize($data[$field]);
                    }
                    if (array_key_exists($field, $data)) {
                        $result[$field] = $data[$field];
                    }
                }
                // check fieldSource
                if (is_array($this->getFieldProperty($field, 'fieldSource'))) {
                    $sources = $this->getFieldProperty($field, 'fieldSource');
                    foreach ($sources as $source) {
                        if ($source[0] === ' ') {
                            $result[$field] .= " ";
                        } else if ($source[0] === '<br>') {
                            $result[$field] .= " ";
                        } else {
                            if (count($source) === 1) {
                                if (array_key_exists($source[0], $data)) {
                                    $result[$field] .= $data[$source[0]];
                                }
                            }
                            if (count($source) === 2) {
                                if (array_key_exists($source[1], $data)) {
                                    $value = $data[$source[1]];
                                    if (is_string($value) || is_int($value)) {
                                        $result[$field] = $value;
                                    } else if (is_array($value)) {
                                        // Handle array of values
                                        foreach ($value as $item) {
                                            if (is_array($item) && array_key_exists($this->getListProperty('primaryKey'), $item) && array_key_exists('name', $item)) {
                                                $result[$field] = $value;
                                            }
                                        }
                                    }
                                } else {
                                    $result[$field] = " ";
                                }
                            }
                            if (count($source) === 3) {
                                $nestedData = " ";
                                if (isset($data[$source[1]]) && is_array($data[$source[1]]) && array_key_exists($source[2], $data[$source[1]])) {
                                    $nestedData = $data[$source[1]][$source[2]];
                                    if (count($sources) > 1) {
//                                    $result[$field] .= $nestedData;
                                        $result[$field] .= $nestedData;
                                    } else {
                                        $result[$field] = $nestedData;
                                    }
                                } else {
                                    $case1 = $source[1];
                                    $case2 = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($source[1])));
                                    if (array_key_exists($case1, $data)) {
                                        foreach ($data[$case1] ?? [] as $subData) {
                                            if (is_array($subData) && array_key_exists($source[2], \collect($subData)->last())) {
                                                $result[$field] = \collect($subData)->last()[$source[2]] ?? $source[2];
                                            }
                                        }
                                    } else if (array_key_exists($case2, $data)) {
                                        if (array_key_exists($field, $filterArray) && is_string($filterArray[$field])) {
                                            $result[$field] = \collect($data[$case2])->keyBy($this->getListProperty('primaryKey'))->get($filterArray[$field])[$this->getListProperty('primaryKey')];
                                        } else {
                                            foreach ($data[$case2] ?? [] as $subData) {
                                                if (is_array($subData) && array_key_exists($source[2], \collect($subData)->last())) {
                                                    $result[$field] = \collect($subData)->last()[$source[2]] ?? $source[2];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
//        Worker::log("Result date_of_birth: " . $field . " Data: " . $result["date_of_birth"]);
            return $result;
        }

        /**
         * Processes the query builder object to perform various operations.
         *
         * This method takes a query builder object and performs the following operations:
         * - Counts the total number of records and records filtered.
         * - Summarizes columns if specified in the 'columnSum' configuration.
         * - Checks for column search requests and applies corresponding filters to the query builder.
         * - Checks for order requests and applies corresponding order by clauses to the query builder.
         * - Applies global search value filtering if specified.
         * - Limits the number of results based on pagination settings.
         * - Retrieves the results from the query builder and stores them in the dataResults property.
         * - Converts each row of the results to an array and processes the field data.
         *
         * @param Builder $queryBuilder The query builder object to process.
         *
         * @return array An array containing the processed results, including metadata such as the total number of records,
         *               records filtered, and record sums.
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         * @throws Exception
         */
        private function processingQueryBuilder(Builder $queryBuilder): array {
            // count total records and records filtered
            $recordsTotal = $recordsFiltered = $queryBuilder->count();
            $recordSum = [];
            if ($this->getDataTableProperty('columnSum') !== false) {
                foreach ($this->getDataTableProperty('columnSum') as $column) {
                    $recordSum[$column] = $queryBuilder->sum($column);
                }
            }
            // init filtered flag
            $filtered = false;
            $fieldSources = $this->extractFieldSources();
            $columnFilterArray = [];
            // check request for column search
            foreach (request('params.columns') ?? [] as $column) {
                $cleanedString = null;
                // check, if field has an assigned search field
                $fieldID = $column['data'];
                // clean search value
                $cleanedString = $column['search']['value'] != "" ? str_replace(['^', '$'], '', $column['search']['value']) : '';
                $cleanedString = str_replace("\\", "", $cleanedString);
                if ($cleanedString === '')
                    $cleanedString = null;
                // check if search value is not empty string
//            Worker::log(json_encode([
//                $cleanedString
//            ], JSON_PRETTY_PRINT));
                if ($cleanedString !== null) {
                    if ($this->getFieldProperty($fieldID, 'fieldType') === 'select' && str_contains($cleanedString, '|')) {
                        $cleanedString = explode('|', $cleanedString);
                        if (in_array("", $cleanedString, true)) {
                            $cleanedString = array_diff($cleanedString, [""]);
                        }
                    }
                    $columnFilterArray[$fieldID] = $cleanedString;
                    $filtered = true;
                    // Add where conditions for the column filters
                    if ($fieldSources->has($fieldID)) {
                        $queryBuilder->where(function ($query) use ($fieldSources, $fieldID, $cleanedString) {
                            foreach ($fieldSources->get($fieldID) as $source) {
                                $source = \collect($source);
                                $fieldOrRelation = $source->get('fieldOrRelation');
//                            $fieldInRelation = $source->get('fieldInRelation');
                                if ($this->getFieldProperty($fieldID, 'fieldType') === 'select') {
                                    if ($cleanedString === 'null')
                                        $query->whereNull($fieldOrRelation);
                                    if (is_array($cleanedString)) {
                                        $query->orWhereIn($fieldOrRelation, $cleanedString);
                                    } else {
                                        $query->orWhereIn($fieldOrRelation, [$cleanedString]);
                                    }
                                } else {
                                    if ($fieldOrRelation !== null) {
                                        if (str_contains($cleanedString, ' ')) {
                                            foreach (explode(' ', (string)$cleanedString) as $string) {
                                                $query->orWhere($fieldOrRelation, "LIKE", "%" . $string . "%");
                                            }
                                        } else {
                                            $query->orWhere($fieldOrRelation, "LIKE", "%" . $cleanedString . "%");
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        $queryBuilder->where(function ($query) use ($fieldID, $cleanedString) {
                            if ($this->getFieldProperty($fieldID, 'fieldType') === 'select') {
                                if ($cleanedString === 'null')
                                    $query->whereNull($fieldID);
                                if (is_array($cleanedString)) {
                                    $query->whereIn($fieldID, $cleanedString);
                                } else {
                                    $query->whereIn($fieldID, [$cleanedString]);
                                }
                            } else {
                                if (is_string($cleanedString) && str_contains($cleanedString, ' ')) {
                                    foreach (explode(' ', $cleanedString) as $string) {
                                        $query->orWhere($fieldID, "LIKE", "%" . $string . "%");
                                    }
                                } else {
                                    if (is_string($cleanedString)) $query->orWhere($fieldID, "LIKE", "%" . $cleanedString . "%");
                                }
                            }
                        });
                    }
                }
            }
            // check request for orderings
            foreach (request('parameters.order') ?? [] as $order) {
                // get column name
                $dataColumn = request('parameters.order')[$order['column']]['data'];
                // get assigned field name
                $fieldSearchAssign = $this->getFieldProperty($dataColumn, 'fieldSortAssign');
                // set orderByColumn
                $orderByColumn = $fieldSearchAssign !== null ? $fieldSearchAssign : $dataColumn;
                if (array_key_exists($orderByColumn, $fieldSources->toArray())) {
                    foreach ((array)$fieldSources->get($orderByColumn) as $source) {
                        $source = \collect((array)$source);
                        if ($source['dataSource'] !== null) {
                            // Add custom sorting logic to handle mixed numeric and string values and empty values last
                            $queryBuilder->orderByRaw("CASE
                        WHEN $orderByColumn IS NULL OR $orderByColumn = '' THEN 1
                        WHEN $orderByColumn REGEXP '^[0-9]+$' THEN 0
                        ELSE 0
                        END,
                        CASE
                        WHEN $orderByColumn REGEXP '^[0-9]+$' THEN LPAD($orderByColumn, 10, '0')
                        ELSE $orderByColumn
                        END {$order['dir']}");
                        }
                    }
                } else {
                    // Add custom sorting logic to handle mixed numeric and string values and empty values last
                    $queryBuilder->orderByRaw("CASE
                WHEN $orderByColumn IS NULL OR $orderByColumn = '' THEN 1
                WHEN $orderByColumn REGEXP '^[0-9]+$' THEN 0
                ELSE 0
                END,
                CASE
                WHEN $orderByColumn REGEXP '^[0-9]+$' THEN LPAD($orderByColumn, 10, '0')
                ELSE $orderByColumn
                END {$order['dir']}");
                }
            }
            if (request('parameters.search') && request('parameters.search')['value'] != null) {
                $likeValue = request('parameters.search', null)['value'] ?? null;
                if ($likeValue !== null) {
                    $likeValue = str_replace(['^', '$'], '', $likeValue);
                }
                $searchValue = $likeValue;
                if ($searchValue !== '') {
                    $queryBuilder->where(function ($query) use ($searchValue) {
                        foreach ($this->getFieldIndex() as $field) {
                            if ($this->getFieldProperty($field, 'fieldSearchable') === true) {
                                if ($this->getFieldProperty($field, 'fieldSearchAssign') !== null) {
                                    if (is_string($searchValue)) $query->orWhere($this->getFieldProperty($field, 'fieldSearchAssign'), "LIKE", "%" . $searchValue . "%");
                                } else {
                                    if (is_string($searchValue)) $query->orWhere($field, "LIKE", "%" . $searchValue . "%");
                                }
                            }
                        }
                    });
                    $filtered = true;
                }
            }
            // count again if filtered
            if ($filtered === true)
                $recordsFiltered = $queryBuilder->count();
            if (request("parameters.length") === -1) {
                request("parameters.length", $recordsFiltered);
            }
            // orderings
            $queryBuilder->skip(request('parameters.start', 1))->take(request('parameters.length', 100) ?? $this->sourceProperties()->whichLimit());
            // fetch results
            $results = $queryBuilder->get();
            // store results in dataResults
            $this->dataResults()->add($results, $this->sourceProperties()->whichPrimary());
            // return the model to the dataSources with current query
            $this->dataSources()->create($queryBuilder, $this->sourceProperties()->whichPrimary());
            // init endResults
            $endResults = [];
            foreach ($this->dataResults()->get($this->sourceProperties()->whichPrimary())[0] ?? [] as $row) {
                // Wenn $row nicht als Array oder Collection vorliegt, konvertieren wir es in ein Array
                if (!is_array($row) && !$row instanceof Collection) {
                    $row = (array)$row;
                }
                // processing field data
                $endResults[] = $this->processingFieldData(\collect($row), $columnFilterArray);
            }
            // return results
            return [
                'objectID' => $this->getObjectProperty('objectID'),
                'draw' => request('parameters.draw') ?? 1,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $endResults,
                'recordSums' => $recordSum,
                'state' => 'success'
            ];
        }

        /**
         * Assign the list properties of the current Object based on the supplied properties.
         * The function iterates over the keys from the existing list properties.
         * If a matching key is found in the given properties, it assigns
         * the corresponding value from the given properties to the ListObject's list properties.
         * A specific case it also handles is when a `primaryStack` is defined in the
         * `dataSource` of the supplied properties, it sets the `listLength` property
         * according to its `limit`.
         * The method logs its execution using the setLog method.
         *
         * @param array<string, mixed>|Collection<string, mixed> $config
         * @param string|null $cat
         *
         * @return  self
         */
        protected function defineProperties(array|Collection $config, string $cat = null): self {
            $this->setLog(__METHOD__);
            if ($config instanceof Collection)
                $config = $config->toArray();
            if ($cat == null) {
                foreach ($this->getListProperty() as $key => $value) {
                    if (array_key_exists($key, $config)) {
                        $this->setListProperty($key, $config[$key]);
                    }
                }
                foreach ($this->getDataTableProperty() as $key => $value) {
                    if (array_key_exists($key, $config)) {
                        $this->setDataTableProperty($key, $config[$key]);
                    }
                }
            } else {
                if ($cat === 'listProperties') {
                    foreach ($this->getListProperty() as $key => $value) {
                        if (array_key_exists($key, $config)) {
                            $this->setListProperty($key, $config[$key]);
                        }
                    }
                }
                if ($cat === 'dataTableProperties') {
                    foreach ($this->getDataTableProperty() as $key => $value) {
                        if (array_key_exists($key, $config)) {
                            $this->setDataTableProperty($key, $config[$key]);
                        }
                    }
                }
            }
            if (isset($config['dataSource']['primaryStack'])) {
                $primaryStack = $config['dataSource']['primaryStack'];
                $stack = $config['dataSource']['stacks'][$primaryStack];
                $this->setListProperty('listLength', $stack['limit'] ?? 10);
            }
            return $this;
        }
    }
