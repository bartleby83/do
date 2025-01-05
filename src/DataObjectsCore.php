<?php

    namespace DO\Main;

    use App\Helpers\AuthentificationHelper as Auth;
    use DO\Main\Elements\FieldSupport\FieldObject;
    use DO\Main\Elements\FieldSupport\FieldStacks;
    use DO\Main\Elements\FieldSupport\FormFieldObject;
    use DO\Main\Elements\FieldSupport\ListFieldObject;
    use DO\Main\Elements\MenuSupport\MenuStacks;
    use DO\Main\PropertyElements\DataTableProperties;
    use DO\Main\PropertyElements\FormProperties;
    use DO\Main\PropertyElements\ListProperties;
    use DO\Main\PropertyElements\ObjectProperties;
    use DO\Main\SourceControl\DataResultStacks;
    use DO\Main\SourceControl\DataSourceContainer;
    use DO\Main\SourceControl\DataSourceStacks;
    use DO\Main\SourceControl\SourceControl;
    use Exception;
    use Illuminate\Database\Query\Builder;
    use Illuminate\Support\Collection;
    use ReflectionClass;
    use UnexpectedValueException;
    use function formatFileSize;
    
    /**
 * The DataObjectsCore class represents a base class for data objects.
 * It provides properties and methods for handling object properties, list properties, form properties,
 * field configurations, fields, data sources, object data sources, data result stacks, and logging.
 *
 * using method  {@link DataObject::build()}
 *
 */
class DataObjectsCore {
    #protected Collection $objectProperties;
    protected ObjectProperties $objectProperties;
    protected ListProperties $listProperties;
    protected DataTableProperties $dataTableProperties;
    protected FormProperties $formProperties;
    /**
     * @var Collection<string, mixed>
     */
    protected Collection $fieldConfigs;
    protected FieldStacks $fields;
    /**
     * @var DataSourceContainer $dataSourceProperties protected
     * @access via @method $this->sourceProperties()
     */
    protected DataSourceContainer $dataSourceProperties;
    /**
     * @var DataSourceStacks $objectDataSources
     * @access via @method $this->sourceProperties()
     */
    protected DataSourceStacks $objectDataSources;
    /**
     * @var DataResultStacks $dataResultStacks
     * @access via @method $this->dataResults()
     */
    protected DataResultStacks $dataResultStacks;
    protected MenuStacks $menu;
    /** @var array<int, array<string, string>> */
    protected array $debugging;
    /** @var array<int, array<string, string>> */
    protected array $messages = [];
    /** @var array<int, array<string, string>> */
    protected array $errorMessages = [];
    /** @var array<int, array<string, string>> */
    protected array $log = [];

    function __construct() {
        $this->setLog(__METHOD__);
        $this->objectProperties = ObjectProperties::implement();
        if ($this instanceof ListObject) {
            $this->listProperties = ListProperties::implement();
            $this->dataTableProperties = DataTableProperties::implement();
        }
        if ($this instanceof FormObject) {
            $this->formProperties = FormProperties::implement();
        }
        $this->dataSourceProperties = DataSourceContainer::implement();
        $this->objectDataSources = DataSourceStacks::implement();
        $this->dataResultStacks = DataResultStacks::implement();
        $this->menu = MenuStacks::implement();
        $this->fields = FieldStacks::implement();
    }

    public static function createInstance(): self {
        return new self(); // Erzeugt eine Instanz der Parent-Klasse
    }

    /**
     * setDataTableProperty()
     * Sets a specific property for the DataTable object.
     * This method allows for setting individual properties of the DataTable object
     * by providing the property name and corresponding value.
     *
     * @param string $property The name of the property to be set.
     * @param mixed $value The value to be assigned to the property.
     *
     * @return self Returns the object after the property has been set.
     */
    public function setDataTableProperty(string $property, mixed $value): self {
        $this->setLog(__METHOD__);
        $this->dataTableProperties[$property] = $value;
        return $this;
    }

    /**
     * getDataTableProperty()
     * Retrieves the value of a specific data table property, or the entire data table properties if no property is
     * specified. It first checks if a specific property was requested, and if not, returns all data table properties.
     * If a specific property was requested, it retrieves the value of that property by traversing through the nested
     * arrays using dot notation.
     *
     * @param string|null $property The name of the specific data table property to retrieve.
     *
     * @return mixed Returns the value of the specified data table property, or the entire data table properties if no
     *               property is specified. Returns null if the specified property does not exist.
     */
    public function getDataTableProperty(string $property = null): mixed {
        $this->setLog(__METHOD__);
        // Return all list properties if no specific property was requested
        if ($property === null)
            return $this->dataTableProperties;
        $value = $this->dataTableProperties;
        foreach (explode('.', $property) as $key) {
            if (!isset($value[$key]))
                return null;
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Fetches a property of the form.
     * This method either returns all form properties when no specific property is given or a specific property if it
     * exists.
     *
     * @param string|null $property The name of the property whose value is to be returned.
     *                              The default value is null, which means all properties are returned.
     *
     * @return mixed The requested properties or property of the form.
     */
    final public function getFormProperty(string $property = null): mixed {
        $this->setLog(__METHOD__);
        if ($property === 'dataSource')
            return $this->dataSourceProperties;
        if ($property === null)
            return $this->formProperties;
        $value = $this->formProperties;
        foreach (explode('.', $property) as $key) {
            if (!isset($value[$key]))
                return null;
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Sets a filter for the list.
     * This method takes an ID and optionally a key and sets the filter for the list to these values.
     * If a key is provided, it will be stored as 'filterKey' for the list.
     *
     * @param int|string|bool|float|null $value The ID for which the filter is to be set.
     * @param string|null $key The optional key to which the 'filterKey' is to be set.
     *                                          If no key is specified, the 'filterKey' will not be set.
     *
     * @return self Returns the object after the filters have been set.
     */
    public function setFilter(int|string|bool|float|null $value, string $key = null): self {
        $this->setLog(__METHOD__);
        if ($key !== null)
            $this->setListProperty('filterKey', $key);
        $this->setListProperty('filter', $value);
        return $this;
    }

    /**
     * Fetches a property of the list.
     * This method either returns all list properties when no specific property
     * was requested, or a specific property, if it exists. The property can be on a
     * nested level and be accessed via a dot operator.
     *
     * @param string|null $property The name of the property whose value is to be fetched.
     *                              The default value is null, which means all properties are returned.
     * @param mixed $value ;
     *
     * @return self The requested properties or property of the list.
     */
    public function setListProperty(?string $property, mixed $value): self {
        $this->setLog(__METHOD__);
        $this->listProperties[$property] = match ($property) {
            'test' => [],
            default => $value,
        };
        return $this;
    }

    /**
     * Returns the currently set filter for the list.
     *
     * This method returns the value of the 'filter' property which is used to filter the data in the list.
     * The return type could be various types including integer, string, boolean, float, or null.
     *
     * @return int|string|bool|float|null The value of the 'filter' property of the list. It could be of various types.
     */
    public function getFilter(): int|string|bool|float|null {
        $this->setLog(__METHOD__);
        return $this->getListProperty('filter');
    }

    /**
     * @dataObject getter / setter
     * @
     */
    /**
     * Gets a list property that applies to the representation of the list.
     * Supports dot notation for nested properties.
     *
     * @param string|null $property The name of the property to get. If null, all properties are returned.
     *
     * @return mixed The value of the specified property or null if the property does not exist.
     */
    public function getListProperty(string $property = null): mixed {
        $this->setLog(__METHOD__);
        // Return all list properties if no specific property was requested
        if ($property === null)
            return $this->listProperties;
        $value = $this->listProperties;
        foreach (explode('.', $property) as $key) {
            if (!isset($value[$key]))
                return null;
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Returns the 'filterKey' property of the list properties.
     *
     * This method fetches the 'filterKey' property from list properties. The 'filterKey' is used as the key
     * for filtering operations in this data structure. The method will also log the operation
     * (the specific logging method is not visible in this scope).
     *
     * @return mixed Returns the value of the 'filterKey' property from the list properties. It could be of various
     *               types.
     */
    public function getFilterKey(): mixed {
        $this->setLog(__METHOD__);
        return $this->getListProperty('filterKey');
    }

    /**
     * setFilterKey()
     * Sets the filter key for the list object.
     * This method assigns a given filter key to the list's properties and returns the updated list object.
     *
     * @param mixed $filterKey The filter key that needs to be set for the list.
     *
     * @return self Returns the updated list object with the filter key set.
     */
    public function setFilterKey(mixed $filterKey): self {
        $this->setListProperty('filterKey', $filterKey);
        return $this;
    }

    /**
     * Stores a custom message for a specific field.
     * This method takes a field and a message as inputs. It stores the message in a 'fieldMessages' array
     * using the field as the key. This is especially useful in contexts such as form validation where
     * specific error or informational messages need to be associated with respective form fields.
     *
     * @param string $field The field for which the message is meant.
     * @param string $message The message to be associated with the field.
     */
    function setMessage(string $field, string $message): void {
        $this->setLog(__METHOD__);
        $this->messages['fieldMessages'][$field][] = $message;
    }

    /**
     * Retrieves accumulated messages.
     *
     * This method fetches and returns the 'messages' property array.
     * The 'messages' array contains an entry for each field/message pair that has been registered using the
     * 'setMessage' method.
     *
     * @return array<int, array<string, string>> The 'messages' array, with each entry comprising a field/message pair.
     */
    function getMessages(): array {
        $this->setLog(__METHOD__);
        return $this->messages;
    }

    /**
     * Stores an error message.
     * This method takes an error message as input and stores it in the 'errorMessages' array.
     * Each error message is encapsulated in an array detailing file, line, and the error message itself.
     *
     * @param string $message The error message to be stored.
     *
     * @return self Returns the instance of the class itself, allowing for method chaining.
     */
    function setErrorMessage(string $message): self {
        $content = ['file' => "", 'line' => "", 'message' => $message,];
        $this->setLog(__METHOD__);
        $this->errorMessages[] = $content;
        return $this;
    }

    /**
     * Retrieves all accumulated error messages.
     *
     * This method returns all error messages that have been stored within the 'errorMessages' property.
     *
     * @return array<int, array<string, string>> The 'errorMessages' array containing all stored error messages.
     */
    function getErrorMessages(): array {
        $this->setLog(__METHOD__);
        return $this->errorMessages;
    }

    /**
     * getLog()
     * Retrieves the log entries based on the provided filters.
     * The method applies the specified filters to the log and returns the filtered entries.
     *
     * @param mixed ...$filter The filters to apply to the log entries. Multiple filters can be provided.
     *
     * @return array<int, array<string, string>> Returns an array of log entries that match the provided filters.
     */
    public function getLog(...$filter): array {
        $logs = $this->log;
        $logFilter = $this->getLogFilter();
        return array_filter($logs, function ($element) use ($filter, $logFilter) {
            $messageFilter = array_merge($logFilter, $filter);
            foreach ($messageFilter as $f) {
                if (str_contains($element['method'], $f)) {
                    return false; // Das Element enthält den Filter, wird aus der Liste ausgeschlossen
                }
            }
            return true; // Das Element enthält den Filter nicht, wird in der Liste behalten
        });
    }

    /**
     * setLog()
     * Adds a log entry to the object's log array.
     * The method takes a string argument representing the current method being logged.
     * It then checks if the method matches any of the log filters, and if so, returns without adding the log entry.
     * Otherwise, it creates a log entry with the method name, current timestamp, and the last class and function that
     * called the method.
     *
     * @param string $method The name of the current method being logged.
     *
     * @return void
     */
    public function setLog(string $method): void {
        // Get current namespace
        $method = str_replace(__NAMESPACE__ . "\\", "", $method);
        // Check against log filters
        foreach ($this->getLogFilter() as $filter) {
            if (str_contains($method, $filter)) {
                return;
            }
        }
        // Get backtrace data for logging
        $backtrace = debug_backtrace();
        $lastCall = '';
        if (isset($backtrace[2]['class'])) {
            $lastCall .= str_replace(__NAMESPACE__ . "\\", "", $backtrace[2]['class']) . '::';
        }
        if (isset($backtrace[2]['function'])) {
            $lastCall .= $backtrace[2]['function'];
        }
        // Add log entry
        $this->log[] = [
            'method' => $method,
            'time' => date('d.m.Y H:i:s'),
            'last' => $lastCall,
        ];
    }

    /**
     * getLogFilter()
     * Returns an array of methods that can be used for setting or retrieving properties of an object.
     * These methods can be logged in order to track changes or actions performed on the object.
     *
     * @return array<int, string> An array of method names that can be logged.
     */
    private function getLogFilter(): array {
        return ['setFormProperty', 'setFieldProperty', 'setListProperty', 'setDataTableProperty', 'setObjectProperty', 'getObjectProperty', 'getFormProperty', 'getListProperty', 'getFieldIndex', 'getFieldProperty', 'getMenuItems', 'getField', 'field'];
    }

    /**
     * hasDataSources()
     * Checks if the object has any data sources.
     * It does this by calling the dataSources() method of the object
     * and then calling the hasSources() method of the returned data sources object.
     *
     * @return bool Returns true if the object has data sources, false otherwise.
     */
    public function hasDataSources(): bool {
        return $this->dataSources()->hasSources();
    }

    /**
     * Retrieves the DataSourceStacks instance associated with the object.
     *
     * This method is a getter for the 'objectDataSources' property, which holds an instance of the
     * DataSourceStacks class. This class manages a stack (or multiple stacks) of data sources
     * associated with the object. These data sources include various types, such as databases, APIs, or files,
     * from which data can be retrieved or to which data can be persisted. An orderly "stack" management helps
     * to maintain control and strategy over data-centric operations performed on the object.
     *
     * @return DataSourceStacks Returns the 'objectDataSources' object encapsulating the stack(s) of the object's data
     *                          sources.
     */
    public function dataSources(): DataSourceStacks {
        $this->setLog(__METHOD__);
        return $this->objectDataSources;
    }

    /**
     * hasDataResults()
     * Checks whether the object has data results.
     * It calls the dataResults() method to retrieve the data results for the object,
     * then checks if the data results have any results.
     *
     * @return bool Returns true if the object has data results, false otherwise.
     */
    public function hasDataResults(): bool {
        $this->setLog(__METHOD__);
        return $this->dataResults()->hasResults();
    }

    /**
     * Filtering
     */
    /**
     * Retrieves the DataResultStacks instance.
     *
     * This method is a getter for the 'dataResultStacks' property, which holds an instance of the
     * DataResultStacks class. This class manages multiple stacks of data results which have been retrieved
     * from various data sources associated with the object. Each stack can represent a collection of results
     * coming from a different data source, providing an orderly way to handle the results coming from
     * various points of data interaction.
     *
     * @return DataResultStacks The 'dataResultStacks' object which encapsulates the stacks of the data results.
     */
    public function dataResults(): DataResultStacks {
        $this->setLog(__METHOD__);
        return $this->dataResultStacks;
    }

    /**
     * Formats a value based on the provided type.
     *
     * This method takes a value and a type, and formats the value according to the type.
     * Currently supported types are 'datetime' and 'filesize'.
     * - 'datetime': The value is expected to be a valid date string, and it will be formatted to 'd.m.Y H:i'
     * - 'filesize': The value is expected to be an integer representing file size in bytes, it will be formatted
     *    to a human-readable string using the `formatFileSize` function.
     * If an unsupported type is provided, the original value will be returned as is.
     *
     * @param mixed $value The value that needs to be formatted.
     * @param string $type The type that determines how the value should be formatted.
     *
     * @return mixed The formatted value if formatting is applied, otherwise the original value.
     */
    public function format(mixed $value, string $type): mixed {
        $this->setLog(__METHOD__);
        switch ($type) {
            case "datetime":
                if (is_string($value)) {
                    $timestamp = strtotime($value);
                    return $timestamp !== false ? date('d.m.Y H:i', $timestamp) : "-";
                } else {
                    return "-";
                }
            case "filesize":
                if (is_int($value)) {
                    return formatFileSize($value);
                } else {
                    return "-";
                }
            default:
                return $value;
        }
    }

    /**
     * Returns the menu items after checking for permissions.
     *
     * @return MenuStacks Returns an array of menu items.
     */
    public function getMenuItems(): MenuStacks {
        $this->setLog(__METHOD__);
        $user = Auth::user();
        $menu = $this->menu;
        foreach ($menu->getStacks() as $itemID => $item) {
            if (isset($item['permissions'])) {
                foreach ($item['permissions'] as $permission => $value) {
                    if ($user?->can($permission) !== $value) {
                        $menu->removeStack($itemID);
                    }
                }
            }
        }
        return $menu;
    }

    /**
     * Messages and Errors
     */
    /**
     * @throws Exception
     */
    public function getFieldProperties($property = null): array {
        $result = [];
        foreach ($this->getFieldIndex() as $field) {
            if ($property === null) $result[$field] = $this->getFieldProperty($field);
            else $result[$field] = $this->getFieldProperty($field, $property);
        }
        return $result;
    }

    /**
     * Returns the index of the form field.
     * This method either returns the index of the form field, or if the argument $swap is set to true,
     * it returns the swapped index where the field indices are the values and the values are the field indices.
     *
     * @param bool $swap If true, the field indices and values are swapped. Default is false.
     *
     * @return array<int|string, string|int> Returns an array of the index or swapped index.
     */
    public function getFieldIndex(bool $swap = false): array {
        $this->setLog(__METHOD__);
        return $this->fields()->index($swap);
    }

    /**
     * retrieves the @see FieldStacks
     */
    public function fields(): FieldStacks {
        $this->setLog(__METHOD__);
        return $this->fields;
    }

    /**
     * getFieldProperty()
     * Gets the property of a specific field or all field properties of the object.
     * If the field parameter is not provided, it returns an array of all field properties indexed by field ID.
     * If both field and property parameters are not provided, it returns an array of all properties of the specified
     * field. If both field and property parameters are provided, it returns the value of the specified property of the
     * specified field.
     *
     * @param int|string|null $field (optional) The ID/name of the field whose property is to be retrieved.
     * @param string|null $property (optional) The name of the property to retrieve for the specified field.
     *
     * @return mixed Returns an array of field properties indexed by field ID if no field or property is specified,
     *         returns an array of properties of the specified field if no property is specified,
     *         returns the value of the specified property of the specified field if both field and property are
     *         specified, or returns null if the specified field does not exist.
     * @throws Exception
     */
    public function getFieldProperty(int|string $field = null, string $property = null): mixed {
        $this->setLog(__METHOD__);
        $fieldArray = [];
        if ($field === null) {
            foreach ($this->fields()->index() as $fieldId) {
                $fieldArray[$fieldId] = $this->getField($fieldId)->getFieldProperty();
                if ($this->getField($fieldId) instanceof FormFieldObject) $fieldArray[$fieldId]['fieldFormProperties']['ruleSet'] = $this->getField($fieldId)->getRuleSet();
            }
            return $fieldArray;
        } else {
            if ($this->getField($field) === null)
                return null;
            if ($property === null) {
                foreach ($this->getField($field)->getFieldProperty() as $name => $prop) {
                    $fieldArray[$name] = $prop;
                }
                if ($this->getField($field) instanceof FormFieldObject) $fieldArray['fieldFormProperties']['ruleSet'] = $this->getField($field)->getRuleSet();
                return $fieldArray;
            } else {
                return $this->getField($field)->getFieldProperty($property);
            }
        }
    }

    /**
     * Fetches a field object on the basis of field ID.
     * This method returns the corresponding field object if the provided field ID exists in the collection of fields.
     * It is likely to return instances of FormFieldObject or ListFieldObject depending on the specific implementation
     * of your fields. If the requested field ID does not exist within the collection of fields, this method returns
     * null.
     *
     * @param string $fieldId The Identifier of the field intended for retrieval.
     *
     * @return FormFieldObject|ListFieldObject|FieldObject The
     *                                                                                                                                                                                                       requested
     *                                                                                                                                                                                                       field
     *                                                                                                                                                                                                       object
     *                                                                                                                                                                                                       if
     *                                                                                                                                                                                                       exists,
     *                                                                                                                                                                                                       otherwise
     *                                                                                                                                                                                                       null.
     * @throws Exception
     */
    public function getField(string $fieldId): FormFieldObject|ListFieldObject|FieldObject {
        if ($this->fields()->get($fieldId) === null) throw new Exception("Field '$fieldId' not found");
        return $this->fields()->get($fieldId);
    }

    public function createField($type) {
        $newField = $this->fields()->create($type);
        return $this;
    }

    /**
     * @param String $objectID
     * @param Collection<string, array<string, mixed>> $config
     *
     * @return FormObject|ListObject
     * @throws Exception
     */
    protected function defineObject(string $objectID, Collection $config): FormObject|ListObject {
        $this->setLog(__METHOD__);
        if (isset($config['menu']))
            $this->setMenuItems($config['menu']);
        return match ((new ReflectionClass($this))->getShortName()) {
            'ListObject' => DataObject::bindObject($objectID, $this->defineList($objectID, $config)),
            'FormObject' => DataObject::bindObject($objectID, $this->defineForm($objectID, $config)),
            default => throw new Exception('Unexpected value'),
        };
    }

    /**
     * Sets menu items based on a given array of items.
     *
     * @param array|null $items An array of menu items.
     *
     * @return self Returns the instance of the class.
     */
    public function setMenuItems(array|null $items = []): self {
        $this->setLog(__METHOD__);
        $this->menu->addStacks($items);
        return $this;
    }

    /**
     * DataSources and Results
     */

    /**
     * @param string $objectID
     * @param Collection $config
     * @return ListObject
     * @throws Exception
     */
    protected function defineList(string $objectID, Collection $config): ListObject {
        $this->setLog(__METHOD__);
        $config->put('objectID', $objectID);
        $config->put('objectType', "list");
        $object = new ListObject();
        $object->implementProperties($config)
            ->implementFields($config['fields'], 'list')
            ->dataSources()
            ->create(
                SourceControl::setQuery(
                    $object->sourceProperties()
                        ->getPrimarySource()
                ),
                $object->sourceProperties()
                    ->whichPrimary()
            );
        return $object;
    }

    /**
     * Sets the fields for a given object based on the provided field configuration and given conditions.
     * This method sets various properties for each field based on different
     * conditions and config values. It also specially handles different types of field objects,
     * such as ListFieldObject and FormFieldObject.
     *
     * @param array<string, mixed> $fields An array of the fields to be set. Default is an empty array.
     * @param string $type The type of field to be set. Default is an empty string.
     *
     * @return self Returns the object after its fields have been set.
     * @throws Exception
     */
    protected function implementFields(array $fields = [], string $type = ''): self {
        $this->setLog(__METHOD__);
        if (isset($this->fieldConfigs['names'])) {
            if ($type === 'list') {
                $this->fields()->set('checkbox', ListFieldObject::implement());
                $this->setFieldProperty('checkbox', 'systemField', true)
                    ->setFieldProperty('checkbox', 'fieldID', 'checkbox')
                    ->setFieldProperty('checkbox', 'fieldType', 'checkbox')
                    ->setFieldProperty('checkbox', 'fieldName', '#')
                    ->setFieldProperty('checkbox', 'fieldAssocObjectID', $this->getObjectProperty('objectID'))
                    ->setFieldProperty('checkbox', 'fieldHiddenInList', true)
                    ->setFieldProperty('checkbox', 'fieldColumnWidth', '10px');
                $this->fields()->set('tools', ListFieldObject::implement());
                $this->setFieldProperty('tools', 'systemField', true)
                    ->setFieldProperty('tools', 'fieldID', 'tools')
                    ->setFieldProperty('tools', 'fieldType', 'tools')
                    ->setFieldProperty('tools', 'fieldName', '::')
                    ->setFieldProperty('tools', 'fieldAssocObjectID', $this->getObjectProperty('objectID'))
                    ->setFieldProperty('tools', 'fieldColumnWidth', '10px');
            }
            foreach ($this->fieldConfigs['names'] as $field => $name) {
                if ($this->fields()->get($field) === null) {
                    if ($type === 'list')
                        $this->fields()->set($field, ListFieldObject::implement());
                    if ($type === 'form')
                        $this->fields()->set($field, FormFieldObject::implement());
                }
                $this->setFieldProperty($field, 'fieldID', $field);
                $this->setFieldProperty($field, 'fieldName', $name);
                $this->setFieldProperty($field, 'fieldAssocObjectID', $this->getObjectProperty('objectID'));
                if (isset($this->fieldConfigs['fieldSources'][$field])) {
                    $this->setFieldProperty($field, 'fieldSource', $this->fieldConfigs['fieldSources'][$field]);
                }
                if (array_key_exists($field, $this->fieldConfigs['hidden'])) {
                    $this->setFieldProperty($field, 'fieldHiddenInList', $this->fieldConfigs['hidden'][$field]);
                    $this->setFieldProperty($field, 'fieldHiddenInForm', $this->fieldConfigs['hidden'][$field]);
                }
                if (array_key_exists($field, $this->fieldConfigs['hiddenView'])) {
                    $this->setFieldProperty($field, 'fieldHiddenInView', $this->fieldConfigs['hiddenView'][$field]);
                }
                if (array_key_exists($field, $this->fieldConfigs['ignoreFields'])) {
                    $this->setFieldProperty($field, 'ignoreField', $this->fieldConfigs['ignoreFields'][$field]);
                }
                if (array_key_exists($field, $this->fieldConfigs['writeable'])) {
                    $this->setFieldProperty($field, 'fieldReadOnly', true);
                    $this->setFieldProperty($field, 'writeable', true);
                }
                if ($this->fieldConfigs->has('fieldReadOnly') && array_key_exists($field, $this->fieldConfigs['fieldReadOnly'])) {
                    $this->setFieldProperty($field, 'fieldReadOnly', true);
                    $this->setFieldProperty($field, 'writeable', true);
                }
                if (isset($this->fieldConfigs['fieldTypes'][$field]) && array_key_exists($field, $this->fieldConfigs['fieldTypes'])) {
                    $this->setFieldProperty($field, 'fieldType', $this->fieldConfigs['fieldTypes'][$field]);
                }
                if (isset($this->fieldConfigs['fieldContentTypes'][$field]) && array_key_exists($field, $this->fieldConfigs['fieldContentTypes'])) {
                    $this->setFieldProperty($field, 'fieldContentType', $this->fieldConfigs['fieldContentTypes'][$field]);
                }
                if (isset($this->fieldConfigs['fieldOptions'][$field]) && array_key_exists($field, $this->fieldConfigs['fieldOptions'])) {
                    $newOptions = [];
                    foreach ($this->fieldConfigs['fieldOptions'][$field] as $optionKey => $optionValue) {
                        if (!is_array($optionValue)) {
                            $newOptions[$optionKey] = ['value' => $optionKey, 'text' => $optionValue, 'identifier' => $optionKey];
                        } else $newOptions[$optionKey] = $optionValue;
                    }
                    $this->setFieldProperty($field, 'fieldOptions', $newOptions);
                }
                if (isset($this->fieldConfigs['fieldRenderOptions'][$field]) && array_key_exists($field, $this->fieldConfigs['fieldRenderOptions'])) {
                    $this->setFieldProperty($field, 'fieldRenderOptions', $this->fieldConfigs['fieldRenderOptions'][$field]);
                    if ($this->getFieldProperty($field, 'fieldType') !== 'select' && $this->getFieldProperty($field, 'fieldType') !== 'multiselect') $this->setFieldProperty($field, 'fieldType', 'select');
                    $this->setFieldProperty($field, 'fieldContentType', 'options');
                    $this->setFieldProperty($field, 'fieldOptions', []);
                }
                if (isset($this->fieldConfigs['fieldFunctions'][$field]) && array_key_exists($field, $this->fieldConfigs['fieldFunctions'])) {
                    $this->setFieldProperty($field, 'fieldFunctions', $this->fieldConfigs['fieldFunctions'][$field]);
                }
                // required
                if (isset($this->fieldConfigs['required']) && in_array($field, $this->fieldConfigs['required'], true)) {
                    $this->setFieldProperty($field, 'fieldRequired', true);
                }
                if (isset($this->fieldConfigs['fieldSearchable']) && array_key_exists($field, $this->fieldConfigs['fieldSearchable'])) {
                    $this->setFieldProperty($field, 'fieldSearchable', $this->fieldConfigs['fieldSearchable'][$field]);
                }
                if (isset($this->fieldConfigs['fieldFilter']) && array_key_exists($field, $this->fieldConfigs['fieldFilter'])) {
                    $this->setFieldProperty($field, 'fieldFilter', $this->fieldConfigs['fieldFilter'][$field]);
                }
                if (isset($this->fieldConfigs['fieldWidths']) && array_key_exists($field, $this->fieldConfigs['fieldWidths'])) {
                    $this->setFieldProperty($field, 'fieldColumnWidth', $this->fieldConfigs['fieldWidths'][$field]);
                }
                if (isset($this->fieldConfigs['fieldLinks']) && array_key_exists($field, $this->fieldConfigs['fieldLinks'])) {
                    $this->setFieldProperty($field, 'fieldLink', $this->fieldConfigs['fieldLinks'][$field]);
                }
                if (isset($this->fieldConfigs['fieldSortable']) && array_key_exists($field, $this->fieldConfigs['fieldSortable'])) {
                    $this->setFieldProperty($field, 'fieldSortable', $this->fieldConfigs['fieldSortable'][$field]);
                }
                if (isset($this->fieldConfigs['sortAssign']) && array_key_exists($field, $this->fieldConfigs['sortAssign'])) {
                    $this->setFieldProperty($field, 'fieldSortAssign', $this->fieldConfigs['sortAssign'][$field]);
                }
                if (isset($this->fieldConfigs['searchAssign']) && array_key_exists($field, $this->fieldConfigs['searchAssign'])) {
                    $this->setFieldProperty($field, 'fieldSearchAssign', $this->fieldConfigs['searchAssign'][$field]);
                }
                if (isset($this->fieldConfigs['fieldEditable']) && array_key_exists($field, $this->fieldConfigs['fieldEditable'])) {
                    $this->setFieldProperty($field, 'fieldEditable', $this->fieldConfigs['fieldEditable'][$field]);
                }
                if (isset($this->fieldConfigs['fieldDescription']) && array_key_exists($field, $this->fieldConfigs['fieldDescription'])) {
                    $this->setFieldProperty($field, 'fieldDescription', $this->fieldConfigs['fieldDescription'][$field]);
                }
                if (isset($this->fieldConfigs['fieldMinLength']) && array_key_exists($field, $this->fieldConfigs['fieldMinLength'])) {
                    $this->setFieldProperty($field, 'minLength', $this->fieldConfigs['fieldMinLength'][$field]);
                }
                if (isset($this->fieldConfigs['fieldMaxLength']) && array_key_exists($field, $this->fieldConfigs['fieldMaxLength'])) {
                    $this->setFieldProperty($field, 'maxLength', $this->fieldConfigs['fieldMaxLength'][$field]);
                }
                if (isset($this->fieldConfigs['fieldMinValue']) && array_key_exists($field, $this->fieldConfigs['fieldMinValue'])) {
                    $this->setFieldProperty($field, 'minValue', $this->fieldConfigs['fieldMinValue'][$field]);
                }
                if (isset($this->fieldConfigs['fieldMaxValue']) && array_key_exists($field, $this->fieldConfigs['fieldMaxValue'])) {
                    $this->setFieldProperty($field, 'maxValue', $this->fieldConfigs['fieldMaxValue'][$field]);
                }
                if (isset($this->fieldConfigs['fieldMinWidth']) && array_key_exists($field, $this->fieldConfigs['fieldMinWidth'])) {
                    $this->setFieldProperty($field, 'minWidth', $this->fieldConfigs['fieldMinWidth'][$field]);
                }
                if (isset($this->fieldConfigs['fieldMaxWidth']) && array_key_exists($field, $this->fieldConfigs['fieldMaxWidth'])) {
                    $this->setFieldProperty($field, 'maxWidth', $this->fieldConfigs['fieldMaxWidth'][$field]);
                }
                if (isset($this->fieldConfigs['fieldPattern']) && array_key_exists($field, $this->fieldConfigs['fieldPattern'])) {
                    $this->setFieldProperty($field, 'fieldPattern', $this->fieldConfigs['fieldPattern'][$field]);
                }
                if (isset($this->fieldConfigs['fieldRequireSpecialCharacters']) && array_key_exists($field, $this->fieldConfigs['fieldRequireSpecialCharacters'])) {
                    $this->setFieldProperty($field, 'requireSpecialCharacters', $this->fieldConfigs['fieldRequireSpecialCharacters'][$field]);
                }
                if (isset($this->fieldConfigs['fieldDefinedSpecialCharacters']) && array_key_exists($field, $this->fieldConfigs['fieldDefinedSpecialCharacters'])) {
                    $this->setFieldProperty($field, 'definedSpecialCharacters', $this->fieldConfigs['fieldDefinedSpecialCharacters'][$field]);
                }
                if (isset($this->fieldConfigs['fieldRequireNumber']) && array_key_exists($field, $this->fieldConfigs['fieldRequireNumber'])) {
                    $this->setFieldProperty($field, 'requireNumber', $this->fieldConfigs['fieldRequireNumber'][$field]);
                }
                if (isset($this->fieldConfigs['fieldRequireUpperCase']) && array_key_exists($field, $this->fieldConfigs['fieldRequireUpperCase'])) {
                    $this->setFieldProperty($field, 'requireUpperCase', $this->fieldConfigs['fieldRequireUpperCase'][$field]);
                }
                if (isset($this->fieldConfigs['fieldRequireLowerCase']) && array_key_exists($field, $this->fieldConfigs['fieldRequireLowerCase'])) {
                    $this->setFieldProperty($field, 'requireLowerCase', $this->fieldConfigs['fieldRequireLowerCase'][$field]);
                }
                if (isset($this->fieldConfigs['fieldAllowNull']) && array_key_exists($field, $this->fieldConfigs['fieldAllowNull'])) {
                    $this->setFieldProperty($field, 'allowNull', $this->fieldConfigs['fieldAllowNull'][$field]);
                }
            }
        }
        if (is_array($fields) && count($fields) > 0) {
            foreach ($fields as $field => $data) {
                if ($type === 'list' && !isset($this->fields[$field]))
                    $this->fields[$field] = new ListFieldObject();
                if ($type === 'form' && !isset($this->fields[$field]))
                    $this->fields[$field] = new FormFieldObject();
                foreach ($this->getField($field)?->getFieldProperty() as $cat) {
                    foreach ($cat as $property => $no) {
                        if (isset($data[$property]))
                            $this->getField($field)?->setFieldProperty($property, $data[$property]);
                    }
                }
            }
        }
        if (is_array($this->fieldConfigs['grouping']) && count($this->fieldConfigs['grouping']) > 0) {
            if ($this->getObjectProperty('objectType') === 'list')
                $this->setListProperty('grouping', $this->fieldConfigs['grouping']);
            if ($this->getObjectProperty('objectType') === 'form')
                $this->setFormProperty('grouping', $this->fieldConfigs['grouping']);
        }
//        } else $this->setFormProperty('grouping', false);
        return $this;
    }

    /**
     * Sets a property of a form field.
     *
     * This method takes a field identifier, a property and a value and sets the
     * property of the specified field to this value. The behavior of the function varies depending on
     * the given property.
     *
     * @param string $field The name of the field whose property is to be set.
     * @param string $property The name of the property to be set.
     * @param mixed $value The value to which the property is to be set. Depending
     *                         on the property name, this can be a variety of types, including strings, numbers, and
     *                         arrays.
     *
     * @return self Returns the object after the field property has been set.
     * @throws Exception
     */
    public function setFieldProperty(string $field, string $property, mixed $value): self {
        $this->setLog(__METHOD__);
        switch ($property) {
            case "fieldType":
                $this->getField($field)?->setFieldProperty($property, $value);
                if ($value === 'password') {
                    $this->getField($field)?->setFieldProperty($property, 'text');
                    $this->getField($field)?->setFieldProperty('fieldContentType', $value);
                }
                if ($value === 'select' || $value === 'multiselect') { // if type select, the default content are bool selections
                    $this->getField($field)?->setFieldProperty($property, $value);
                    $this->getField($field)?->setFieldProperty('fieldContentType', 'bool');
                    $options = [];
                    $options[0] = ['identifier' => 0, 'value' => 0, 'text' => "Nein"];
                    $options[1] = ['identifier' => 1, 'value' => 1, 'text' => "Ja"];
                    $this->getField($field)?->setFieldProperty('fieldOptions', $options);
                }
                if ($value === 'checkbox') {
                    $this->getField($field)?->setFieldProperty('fieldType', 'checkbox');
                    $this->getField($field)?->setFieldProperty('fieldContentType', 'checkbox');
                    $this->getField($field)?->setFieldProperty('fieldRenderOptions', ['renderFunction' => null, 'renderOutput' => ['value' => 'id', 'identifier' => 'id', 'text' => 'name']]);
                }
                if ($value === 'color') {
                    $this->getField($field)?->setFieldProperty($property, 'color');
                    $this->getField($field)?->setFieldProperty('fieldContentType', 'color');
                }
                break;
            case "fieldSource":
                $data = [];
                if (is_string($value)) {
                    if (str_contains($value, ":"))
                        $value = explode(":", $value);
                    $this->getField($field)?->setFieldProperty($property, $value);
                }
                if (is_array($value)) {
                    foreach ($value as $p) {
                        if (is_string($p)) {
                            // Teilt den String bei jedem Doppelpunkt auf und fügt das resultierende Array hinzu.
                            $data[] = explode(":", $p);
                            $this->getField($field)?->setFieldProperty($property, $data);
                        } elseif (is_array($p)) {
                            // Fügt das Array direkt hinzu.
                            $data[] = $p;
                            $this->getField($field)?->setFieldProperty($property, $data);
                        }
                    }
                }
                break;
            default:
                $this->getField($field)?->setFieldProperty($property, $value);
                break;
        }
        return $this;
    }

    /**
     * Fetches a property of the object.
     * This method either returns all properties of the object when no
     * specific property is given or a specific property if it exists.
     *
     * @param string|null $property The name of the property whose value is to be returned.
     *                              The default value is null, which means all properties are returned.
     *
     * @return mixed The requested properties or property of the object.
     */
    public function getObjectProperty(string $property = null): mixed {
        $this->setLog(__METHOD__);
        if ($property === null)
            return $this->objectProperties;
        $value = $this->objectProperties;
        foreach (explode('.', $property) as $key) {
            if (!isset($value[$key]))
                return null;
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Sets a property of the form.
     *
     * This method takes a property and a value and sets the form's property to this value.
     *
     * @param string $property The name of the property to be set.
     * @param mixed $value The value to which the property should be set.
     *
     * @return self Returns the object after its property has been set.
     */
    final public function setFormProperty(string $property, mixed $value): self {
        $this->setLog(__METHOD__);
        $this->formProperties[$property] = $value;
        return $this;
    }

    /**
     * implementProperties()
     * Sets the properties for a given object based on the provided configuration.
     * The method first transforms the object properties into an array,
     * then sets the field configurations and updates the existing properties of the object
     * based on the provided configuration. It also specially handles whether the object is a list
     * or a form and sets their specific properties accordingly.
     *
     * @param Collection<string, mixed> $config The configuration used to set the properties of the object.
     *
     * @return ListObject|FormObject Returns the object after its properties have been set.
     */
    protected function implementProperties(Collection $config): ListObject|FormObject {
        $this->setLog(__METHOD__);
        $this->fieldConfigs = $config['fieldConfigs'];
        foreach ($this->objectProperties->getProperties() as $property => $value) {
            if (isset($config['objectProperties'][$property])) {
                $this->objectProperties->setProperty($property, $config['objectProperties'][$property]);
            }
        }
        if (count($config['menu']) > 0) {
            $this->menu->addStacks($config['menu']);
        }
        if ($config->has('objectID')) {
            $this->setObjectProperty('objectID', $config->get('objectID'));
        }
        if ($config->has('objectType')) {
            $this->setObjectProperty('objectType', $config->get('objectType'));
        }
        if ($this->getObjectProperty('objectType') === 'list') {
            $this->defineProperties($config['listProperties'], 'listProperties')
                ->defineProperties($config['dataTableProperties'], 'dataTableProperties')
                ->sourceProperties()
                ->readConfig($config['dataSource']);
        }
        if ($this->getObjectProperty('objectType') === 'form') {
            $this->defineProperties($config['formProperties'], 'formProperties')
                ->sourceProperties()
                ->readConfig($config['dataSource']);
        }
        // Stelle sicher, dass der Rückgabewert ein `FormObject` oder `ListObject` ist
        if ($this instanceof ListObject) {
            return $this; // Rückgabe als ListObject
        } elseif ($this instanceof FormObject) {
            return $this; // Rückgabe als FormObject
        }
        throw new UnexpectedValueException('Return value must be of type FormObject or ListObject');
    }

    /**
     * Sets a property of the object.
     * This method takes a property and a value and sets the object's property to this value.
     *
     * @param string $property The name of the property to be set.
     * @param mixed $value The value to which the property should be set.
     *
     * @return self Returns the object after its property has been set.
     */
    public function setObjectProperty(string $property, mixed $value): self {
        $this->setLog(__METHOD__);
        $this->objectProperties[$property] = $value;
        return $this;
    }

    /**
     * Retrieves the DataSourceContainer instance.
     *
     * This method is a getter for the 'dataSourceProperties' property, which is an object of the
     * DataSourceContainer class. It is used for generating and managing the data source related properties,
     * like setting up the connection configuration, creating queries, etc.
     *
     * @return DataSourceContainer The 'dataSourceProperties' object which contains the configuration and functionality
     *                             for the data source.
     */
    public function sourceProperties(): DataSourceContainer {
        return $this->dataSourceProperties;
    }

    /**
     * defineProperties()
     * Updates the properties of the object based on the provided configuration.
     * The method iterates through the properties of the object and updates them
     * with the corresponding values from the configuration. The method supports
     * updating properties for list, dataTable, form, and general object categories.
     * By specifying a category, the method only updates properties for that category.
     * If no category is provided, all properties are updated.
     *
     * @param array<string, mixed>|Collection<string, mixed> $config
     * @param string|null $cat The category of properties to update. Defaults to null.
     *
     * @return self Returns the object after its properties have been updated.
     */
    protected function defineProperties(array|Collection $config, string $cat = null): self {
        $this->setLog(__METHOD__);
        if ($this instanceof ListObject) {
            foreach ($this->listProperties as $property => $value) {
                if (isset($config[$property]))
                    $this->listProperties[$property] = $config[$property];
            }
            foreach ($this->dataTableProperties as $property => $value) {
                if (isset($config[$property]))
                    $this->dataTableProperties[$property] = $config[$property];
            }
        }
        if ($this instanceof FormObject) {
            foreach ($this->formProperties as $property => $value) {
                if (isset($config[$property]))
                    $this->formProperties[$property] = $config[$property];
            }
        }
        foreach ($this->objectProperties as $property => $value) {
            if (isset($config[$property]))
                $this->objectProperties[$property] = $config[$property];
        }
        return $this;
    }

    /**
     * Additional Methods
     */

    /**
     * Defines a form with the provided object, its ID, and configuration.
     * This method sets properties for the object, assigns fields to the form,
     * and saves the object in a data store with its ID.
     *
     * @param String $objectID The ID of the object.
     * @param Collection $config The configuration for defining the form.
     * @return FormObject
     *
     * and it has been saved.
     * @throws Exception
     */
    protected function defineForm(string $objectID, Collection $config): FormObject {
        $this->setLog(__METHOD__);
        $config->put('objectID', $objectID);
        $config->put('objectType', "form");
        $object = new FormObject();
        $object->implementProperties($config)
            ->implementFields($config['fields'], 'form')
            ->dataSources()
            ->create(
                SourceControl::setQuery(
                    $object->sourceProperties()
                        ->getPrimarySource()
                ), $object->sourceProperties()
                ->whichPrimary()
            );
        return $object;
    }

    /**
     * Retrieves the query builder from the primary data source stack.
     * If no exception occurs during the retrieval of the query builder, it will be returned.
     * If an exception occurs, the error information will be retrieved using the getDataSourceError() method,
     * and returned as an array.
     *
     * @return array|\Illuminate\Contracts\Database\Eloquent\Builder|Builder|string
     *                                                                                                  builder from
     *                                                                                                  the primary
     *                                                                                                  data source
     *                                                                                                  stack, or the
     *                                                                                                  error
     *                                                                                                  information as
     *                                                                                                  an array. If
     *                                                                                                  the retrieval
     *                                                                                                  is successful:
     *   - The query builder object will be returned.
     * If an exception occurs:
     *   - The error information array will be returned. The array structure is as follows:
     *     - 'state': The state of the error, always set to 'error'.
     *     - 'message': The error message indicating that the data source could not be loaded, appended with the error
     *     message from the exception.
     *     - 'trace': The stack trace of the exception as a string.
     */
    protected function getQueryBuilder(): array|\Illuminate\Contracts\Database\Eloquent\Builder|Builder|string {
        try {
            return $this->dataSources()->getPrimarySource();
        } catch (Exception $e) {
            return $this->getDataSourceError($e);
        }
    }

    /**
     * Retrieves the error information for a data source exception.
     *
     * @param Exception $e The exception object representing the data source error.
     *
     * @return array The array containing the error information. The array structure is as follows:
     *               - 'state': The state of the error, always set to 'error'.
     *               - 'message': The error message indicating that the data source could not be loaded,
     *                            appended with the error message from the exception.
     *               - 'trace': The stack trace of the exception as a string.
     */
    protected function getDataSourceError(Exception $e): array {
        return ['state' => "error", 'message' => 'Datenquelle konnte nicht geladen werden: ' . $e->getMessage(), 'trace' => $e->getTraceAsString(),];
    }

    /**
     * Retrieves the builder error details.
     * Returns an array containing the builder error details, including the following keys:
     * - 'data': An empty array.
     * - 'recordsTotal': The total number of records.
     * - 'recordsFiltered': The number of records after filtering.
     * - 'state': The state of the error, which is set to "error".
     * - 'message': The error message, which states that no data source is specified.
     *
     * @return array The builder error details as an associative array.
     */
    protected function getBuilderError(): array {
        return ['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0, 'state' => "error", 'error' => 'Keine Datenquelle angegeben.',];
    }
}
