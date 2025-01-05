<?php

    namespace DO\Main;

    use Carbon\Carbon;
    use DO\Main\PropertyElements\FormProperties;
    use DO\Main\PropertyElements\ObjectProperties;
    use DO\Main\SourceControl\DataResultStacks;
    use DO\Main\SourceControl\DataSourceStacks;
    use Exception;
    use Illuminate\Contracts\Database\Eloquent\Builder;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\View\View;
    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\NotFoundExceptionInterface;

    /**
 * FormObject is the base class for all form-based objects. It extends the DataObjectsCore class and adds
 *
 * @link DataObjectsCore
 */
final class FormObject extends DataObjectsCore {
    /**
     * @var ObjectProperties
     * informs about the ListObject
     * @property string $objectID
     * @property string $objectType
     * @property string $objectName
     * @accessible with getObjectProperties('propertyName');
     */
    protected ObjectProperties $objectProperties;
    /**
     * @var FormProperties
     * informs about the Form Properties
     */
    protected FormProperties $formProperties;
    protected DataSourceStacks $dataSource;
    protected DataResultStacks $dataResults;
    protected int|string|null $dataSetID = null;

    function __construct() {
        parent::__construct();
    }

    /**
     * Loads an object with the given object ID, using the provided object and array.
     *
     * @param string $objectID The ID of the object to load.
     * @param Collection<string, mixed> $array The data to be assigned to the object.
     *
     * @return FormObject The loaded object.
     * @throws Exception
     */
    public static function loadObject(string $objectID, Collection $array): FormObject {
        return DataObject::bindObject($objectID, (new FormObject())->defineObject($objectID, $array));
    }

    /**
     * Outputs the object as a view with the specified configuration.
     *
     * @return View The view representing the output object.
     * @throws Exception
     */
    public function outputObject(): View {
        $this->setLog(__METHOD__);
        //        if (!empty($this->getFormProperty('dataSource')) && $this->getFormProperty('dataSource')['stacks'] !== NULL)
        //            $this->fetchDataSource();
        $functionButtons = false;
        $editButton = true;
        $menuItems = false;
        $fieldOutput = $this->getFieldIndex();
        $fieldGroups = $this->getFormProperty('grouping');
        $dataSetID = null;
        if ($this->getDataSetID() !== null)
            $dataSetID = $this->getDataSetID();
        if ($fieldGroups !== false) {
            foreach ($fieldGroups as $fieldGroup) {
                $fieldOutput = array_diff($fieldOutput, $fieldGroup);
            }
        }
        if ($this->getFormProperty('viewMode') === 'view')
            $viewModeForm = 'formInfoCard';
        else $viewModeForm = 'formEditCard';
        return view('DO::' . $viewModeForm)
            ->with('functionButtons', $functionButtons)
            ->with('editButton', $editButton)
            ->with('menuItems', $menuItems)
            ->with('fieldOutput', $fieldOutput)
            ->with('fieldGroups', $fieldGroups)
            ->with('outputFormFields', $this->outputFormFields())
            ->with('object', $this)
            ->with('dataSetID', $dataSetID);
    }

    /**
     * Retrieves the data set ID.
     *
     * @return string|int|null The data set ID.
     */
    public function getDataSetID(): string|int|null {
        $this->setLog(__METHOD__);
        return $this->dataSetID === null ? null : $this->dataSetID;
    }

    /**
     * Sets the data set ID for the object.
     *
     * @param string|int|null $id The ID of the data set.
     *
     * @return self The current object.
     */
    public function setDataSetID(string|int|null $id = null): self {
        $this->setLog(__METHOD__);
        if($this->dataSetID <> $id) {
            if($this->dataResults()->whichPrimary() !== null) $this->dataResults()->destroy($this->dataResults()->whichPrimary());
        }
        $this->dataSetID = $id;
        return $this;
    }



    /**
     * Outputs the form fields.
     *
     * This method iterates over the field index array of the object and outputs each field using the outputField
     * method. The output is stored in an associative array where the field index is the key and the output is the
     * value.
     *
     * @return array The associative array containing the output of each form field.
     * @throws Exception
     */
    function outputFormFields(): array {
        $this->setLog(__METHOD__);
        $output = [];
        foreach ($this->getFieldIndex() as $fieldIndex) {
            $output[$fieldIndex] = $this->outputField($fieldIndex)->render();
        }
        return $output;
    }

    public function outputButton($type, $title) {
        match($type) {
            'submit' => $buttonType = 'submit',
            default => $buttonType = 'button'
        };


        return view('DO::buttons.primaryNormal')
            ->with([
                'buttonType' => $buttonType,
                'buttonTitle' => $title,
            ]);
    }

    /**
     * Generates the output field for a given field.
     *
     * @param string $field The name of the field.
     *
     * @return View|string The generated output field.
     * @throws Exception
     */
    public function outputField(string $field): View|string {
        $this->setLog(__METHOD__);
//        echo "Feld: " . $field . " fieldType: " . $this->getFieldProperty($field, 'fieldType') . " fieldContentType: " . $this->getFieldProperty($field, 'fieldContentType') . "\n";
        $fieldDescription = $this->getFieldProperty($field, 'fieldDescription');
        $fieldType = $this->getFieldProperty($field, 'fieldType');
        $fieldContentType = $this->getFieldProperty($field, "fieldContentType");
        $fieldTemplate = 'textSimple';
        $minLength = $this->getFieldProperty($field, 'minLength');
        $minWidth = $this->getFieldProperty($field, 'minWidth');
        $maxLength = $this->getFieldProperty($field, 'maxLength');
        $maxWidth = $this->getFieldProperty($field, 'maxWidth');
        $minValue = $this->getFieldProperty($field, 'minValue');
        $maxValue = $this->getFieldProperty($field, 'maxValue');
        $fieldPattern = $this->getFieldProperty($field, 'fieldPattern');
        $writeable = $this->getFieldProperty($field, 'writeable');
        $fieldValue = null;
        $rawData = [];
        switch ($fieldType) {
            case 'color':
                $fieldTemplate = 'sliderSimple';
                break;
            case 'text':
                if ($fieldContentType === 'password')
                    $fieldTemplate = 'password';
                break;
            case 'textarea':
                $fieldTemplate = 'textareaSimple';
                break;
            case 'select':
            case 'multiselect':
                $options = [];
                if (array_key_exists($field, $this->fieldConfigs['fieldRenderOptions']) && is_array($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'])) {
//                    var_dump("A");
                    if (method_exists($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][0], $this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][1])) {
                        $options = call_user_func($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][0] . "::" . $this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][1], $rawData, $field);
//                        var_dump($options);
                        $this->setFieldProperty($field, 'fieldOptions', $options);
                    }
                } elseif (array_key_exists($field, $this->fieldConfigs['fieldRenderOptions']) && is_string($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'])) {
//                    var_dump("B");
                    $functionArray = explode('::', $this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction']);
//                    var_dump($functionArray, method_exists($functionArray[0], $functionArray[1]));
                    if (method_exists($functionArray[0], $functionArray[1])) {
                        $funcResult = call_user_func($functionArray, $rawData, $field);
                        foreach ($funcResult as $value) {
                            $options[$value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['value']]] = ['value' => $value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['value']], 'identifier' => $value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['identifier']], 'text' => $value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['output']]];
                        }
                        $this->setFieldProperty($field, 'fieldOptions', $options);
                    }
                } elseif (array_key_exists($field, $this->fieldConfigs['fieldOptions'])) {
                    foreach ($this->fieldConfigs['fieldOptions'][$field] as $value => $option) {
                        $options[$value] = ['value' => $value, 'identifier' => $value, 'text' => $option];
                    }
                    $this->setFieldProperty($field, 'fieldOptions', $options);
                } else {
                    $options[0] = ['value' => '0', 'identifier' => '0', 'text' => 'Nein'];
                    $options[1] = ['value' => '1', 'identifier' => '1', 'text' => 'Ja'];
                    $this->setFieldProperty($field, 'fieldContentType', 'bool');
                }
                $fieldTemplate = 'select2';
                break;
            case 'checkbox':
                // selectable options
                $options = [];
                if (isset($this->fieldConfigs['fieldRenderOptions'][$field]) && is_array($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'])) {
                    if (method_exists($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][0], $this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][1])) {
                        $rawData[$field] = call_user_func($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][0] . "::" . $this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'][1], $rawData, $field);
                        $this->setFieldProperty($field, 'fieldOptions', $options);
                    }
                } elseif (isset($this->fieldConfigs['fieldRenderOptions'][$field]) && is_string($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'])) {
                    if (method_exists(explode("::", $this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'])[0], explode("::", $this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'])[1])) {
                        $funcResult = call_user_func($this->fieldConfigs['fieldRenderOptions'][$field]['renderFunction'], $rawData, $field);
                        foreach ($funcResult as $value) {
                            $options[$value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['value']]] = ['value' => $value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['value']], 'identifier' => $value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['identifier']], 'text' => $value[$this->fieldConfigs['fieldRenderOptions'][$field]['renderOutput']['output']]];
                        }
                        $this->setFieldProperty($field, 'fieldOptions', $options);
                    }
                } else {
                    $data = $this->getFieldProperty($field, 'fieldOptions');
                    $renderFunction = $this->getFieldProperty($field, 'fieldRenderOptions');
                    foreach ($data as $value) {
                        $options[$value[$renderFunction['renderOutput']['value']]] = ['value' => $value[$renderFunction['renderOutput']['value']], 'identifier' => $value[$renderFunction['renderOutput']['identifier']], 'text' => $value[$renderFunction['renderOutput']['output']]];
                    }
                }
                $this->setFieldProperty($field, 'fieldOptions', $options);
                $fieldTemplate = 'checkboxSimple';
                break;
            case 'radio':
                $options = [];
                $fieldTemplate = 'radioSimple';
                break;
            case 'date':
            case 'datetime':
                $fieldTemplate = 'datetimeSimple';
                break;
            case 'time':
                $fieldTemplate = 'timeSimple';
                break;
            case 'file':
                $fieldTemplate = 'fileSimple';
                break;
            // Füge hier weitere Feldtypen hinzu, falls benötigt
        }
        //req
        if ($this->getFieldProperty($field, "fieldRequired") === true)
            $requiredField = "required";
        else $requiredField = "";
        $readOnly = "";
        if ($this->getFieldProperty($field, "fieldReadOnly") === true)
            $readOnly = "readonly";
        if ($this->getFieldProperty($field, "fieldHiddenInForm") === true)
            $fieldTemplate = "hiddenField";
        return view("DO::formularFields." . $fieldTemplate)
            ->with('fieldValue', $fieldValue)
            ->with('options', $options ?? [])
            ->with('minLength', $minLength)
            ->with('minWidth', $minWidth)
            ->with('maxLength', $maxLength)
            ->with('maxWidth', $maxWidth)
            ->with('minValue', $minValue)
            ->with('maxValue', $maxValue)
            ->with('fieldReadOnly', $readOnly)
            ->with('fieldPattern', $fieldPattern)
            ->with('requiredField', $requiredField)
            ->with('fieldReadOnly', $readOnly)
            ->with('fieldDescription', $fieldDescription)
            ->with('fieldID', $this->getFieldProperty($field, "fieldID"))
            ->with('fieldFormularID', $this->getObjectProperty('objectID') . "_" . $this->getFieldProperty($field, "fieldID"))
            ->with('fieldName', $this->getFieldProperty($field, "fieldName"))
            ->with('renderOutput', $this->getFieldProperty($field, "fieldRenderOptions"))
            ->with('fieldPlaceholder', $this->getFieldProperty($field, "placeholder") !== null ? $this->getFieldProperty($field, "placeholder") : $this->getFieldProperty($field, "fieldName"));
    }

    /**
     * Retrieves an object and returns an array containing its properties, form properties, output form fields,
     * menu items, fields, and additional results based on the specified processing method.
     *
     * @return array An array containing the retrieved object properties, form properties, output form fields,
     *    menu items, fields, and additional results.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getObject(): array {
        $this->setLog(__METHOD__);
        $objectResult = [];
        $this->setDataSetID();
        $objectResult['objectID'] = $this->getObjectProperty('objectID');
        if (request()->get('processingMethod') === 'getObject' || count(request()->all()) > 0) {
            $objectResult['objectProperties'] = $this->getObjectProperty();
            $objectResult['formProperties'] = $this->getFormProperty();
            $objectResult['outputFormFields'] = $this->outputFormFields();
            $objectResult['menu'] = $this->getMenuItems();
            $objectResult['fields'] = $this->getFieldProperty();
        }
        if (request()->get('processingMethod') === 'getDataSet') {
            if (request()->has('dataSetID'))
                if (request()->get('dataSetID') === "") $this->setDataSetID();
                else $this->setDataSetID(request()->get('dataSetID'));
            $objectResult['fieldValues'] = $this->getResults() ?? [];
        }
        if (request()->get('processingMethod') === 'saveEntry') {
            if (request()->has('dataSetID'))
                $this->setDataSetID(request()->get('dataSetID'));

            var_dump($this->getDataSetID());
            if ($this->save()) {
                $this->processingQuery();
                $objectResult['fieldValues'] = $this->getResults();
                $objectResult['success'] = true;
            } else {
                $objectResult['error'] = true;
                $objectResult['errorMessage'] = $this->getErrorMessages();
            }
        }
        return $objectResult;
    }

    /**
     * Retrieves the results of the current object.
     *
     * @return array The results of the object.
     * @throws Exception
     */
    public function getResults(): array {
        $this->setLog(__METHOD__);
        $results = [];
        if(! $this->hasDataResults()) {
            if ($this->hasDataSources()) {
                $this->processingQuery();
            }
        }

        return $this->dataResults()->get($this->dataResults()->whichPrimary())->toArray();
    }

    /**
     * Processes the query and returns the result as a Collection or an array.
     *
     * @return Collection|array The result of the query as a Collection or an array.
     * @throws Exception If an error occurs during query processing.
     */
    private function processingQuery(): Collection|array {
        $this->setLog(__METHOD__);
        $queryBuilder = clone $this->dataSources()->getPrimarySource();
        if ($this->getDataSetID() !== "") {
            if ($queryBuilder instanceof Builder) {
                $this->processingEloquentQuery($queryBuilder);
                return $this->dataResults()->get($this->dataResults()->whichPrimary())->toArray();
            }
        } else {
            return $this->fields()->properties('defaultValue');
        }
        throw new Exception();
    }

    /**
     * @throws Exception
     */
    private function processingEloquentQuery(Builder $queryBuilder): void {
        $this->setLog(__METHOD__);
        $results = $queryBuilder->find($this->getDataSetID());
        $results = $this->processingFieldData($results);
        $this->dataResults()->add(\collect($results), $this->dataSources()->whichPrimary());
    }

    /**
     * @throws Exception
     */
    private function processingFieldData(mixed $row): array
    {
        $fields = $this->getFieldIndex();
        $results = [];
        if(!is_array($row)) {
            $row = $row->toArray();
        }
        echo "objectID " . $this->getObjectProperty('objectID') . "\n";

        foreach($fields as $field) {
            if($field === 'checkbox') continue;
            if($field === 'tools') continue;
            $fieldSources = $this->getFieldProperty($field, 'fieldSource');

            $results[$field] = $row[$field] ?? "nicht gesetzt (0)";

            if (isset($this->getFieldProperty($field, 'fieldRenderOptions')['renderOutput']['output'])) {
                continue;
            }


            foreach($fieldSources ?? [] as $source) {
                if(is_array($source)) {
                    if(count($source) === 2) {
                        if(array_key_exists($source[1], $row)) {
                            $results[$field] = $row[$source[1]];
                        }
                    }
                    if(count($source) === 3) {
                        if(array_key_exists($source[1], $row)) {
                            $results[$field] = $row[$source[1]][$source[2]];
                        }
                    }
                }
            }

            if (array_key_exists($field, $results)) {
                if ($this->getFieldProperty($field, 'fieldType') === 'checkbox')
                    $results[$field] = '';
                if ($this->getFieldProperty($field, 'fieldType') === 'tools')
                    $results[$field] = '';
                if ($this->getFieldProperty($field, 'fieldType') === 'date') {
                    if ($results[$field] !== "") $results[$field] = Carbon::parse($results[$field])->format('d.m.Y');
                }
                if ($this->getFieldProperty($field, 'fieldType') === 'datetime') {
                    if ($results[$field] !== "") $results[$field] = Carbon::parse($results[$field])->format("d.m.Y H:i:s");
                }
                if ($this->getFieldProperty($field, 'fieldType') === 'time') {
                    if ($results[$field] !== "") $results[$field] = Carbon::parse($results[$field])->format('H:i');
                }
                if ($this->getFieldProperty($field, 'fieldType') === 'filesize')
                    $results[$field] = formatFileSize($results[$field]);
            }
        }


        return $results;
    }

        /**
     * @throws Exception
     */
    private function processingFieldData_obsolete(mixed $row): array {
        $this->setLog(__METHOD__);
        $result = [];
        $data = $row?->toArray() ?? [];
        $fields = $this->getFieldIndex();
        foreach ($fields as $field) {
            if($field === 'checkbox') continue;
            if($field === 'tools') continue;
            $result[$field] = $data[$field] ?? $this->getFieldProperty($field, 'defaultValue');
//            if ($this->getFieldProperty($field, 'ignoreField') === true) continue;
            // check fieldSource
            if (is_array($this->getFieldProperty($field, 'fieldSource')) && count($this->getFieldProperty($field, 'fieldSource')) > 0) {
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
                                        if (is_array($item) && array_key_exists('id', $item) && array_key_exists('name', $item)) {
                                            $result[$field] = $value;
                                        }
                                    }
                                }
                            }
                        }

                        if (count($source) === 3) {
                            $nestedData = [];
                            if (isset($data[$source[1]]) && is_array($data[$source[1]])) {
                                array_walk($data[$source[1]], function ($item) use ($sources, $source, &$result, $field, &$nestedData) {
                                    // Prüfen, ob der Schlüssel $source[2] existiert
                                    if (array_key_exists($source[2], $item)) {
                                        $nestedData[] = $item[$source[2]];
                                        if (count($sources) > 1) {
                                            $result[$field] .= $nestedData;
                                        } else $result[$field] = $nestedData;
                                    }
                                });
                            } else {
                                $case1 = $source[1];
                                $case2 = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($source[1])));
                                if (array_key_exists($case1, $data)) {
                                    foreach ($data[$case1] ?? [] as $subData) {
                                        if (is_array($subData) && array_key_exists($source[2], \collect($subData)->last())) {
                                            $result[$field] = \collect($subData)->last()[$source[2]] ?? $source[2];
                                        }
                                    }
                                } else {
                                    $result[$field] = $data[$case2][$source[2]] ?? "";
                                }
                            }
                        }
                    }
                }
            }
            if (array_key_exists($field, $result)) {
                if ($this->getFieldProperty($field, 'fieldType') === 'checkbox')
                    $result[$field] = '';
                if ($this->getFieldProperty($field, 'fieldType') === 'tools')
                    $result[$field] = '';
                if ($this->getFieldProperty($field, 'fieldType') === 'date') {
                    if ($result[$field] !== "") $result[$field] = Carbon::parse($result[$field])->format('d.m.Y');
                }
                if ($this->getFieldProperty($field, 'fieldType') === 'datetime') {
                    if ($result[$field] !== "") $result[$field] = Carbon::parse($result[$field])->format("d.m.Y H:i:s");
                }
                if ($this->getFieldProperty($field, 'fieldType') === 'time') {
                    if ($result[$field] !== "") $result[$field] = Carbon::parse($result[$field])->format('H:i');
                }
                if ($this->getFieldProperty($field, 'fieldType') === 'filesize')
                    $result[$field] = formatFileSize($result[$field]);
            }
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    function save(): bool {
        $this->setLog(__METHOD__);
        if ($this->hasDataSources()) {
            $queryBuilder = clone $this->dataSources()->getPrimarySource();
            if ($queryBuilder instanceof Builder) {
                return $this->saveEloquent($queryBuilder);
            }
        }
        return false;
    }

    /**
     * @throws Exception
     */
    private function saveEloquent(Builder $queryBuilder1): bool {
        $this->setLog(__METHOD__);
        $queryBuilder = $queryBuilder1->find($this->getDataSetID());
        $fields = $this->getFieldIndex(); // Deine Methode, um die Feldinformationen zu holen
        $data = request('parameters');
        $direct = [];
        $relations = [];
        foreach ($fields as $field) {
            if ($this->getFieldProperty($field, 'fieldHiddenInForm') === true) continue;
            if ($this->getFieldProperty($field, 'ignoreField') === true) continue;
            $sources = $this->getFieldProperty($field, 'fieldSource');
            if (is_array($sources) && count($sources) > 0) {
                foreach ($sources as $source) {
                    if (count($source) === 2) {
                        $direct[$source[1]] = $this->validateFieldInput($field, $data[$field]);
                    }
                    if (count($source) === 3) {
                        $case1 = $source[1];
                        $relations[$case1][$source[2]] = $this->validateFieldInput($field, $data[$field]);
                    }
                }
            } else {
                $direct[$field] = $this->validateFieldInput($field, $data[$field]);
            }
        }
        if ($queryBuilder === null) {
            $queryBuilder1->create($direct);
        } else {
            foreach ($direct as $key => $value) {
                $queryBuilder->{$key} = $value;
                if ($value === null) $queryBuilder->{$key} = "null";
            }
            $queryBuilder->save();
            foreach ($relations as $relation => $relationData) {
                $relatedModel = $queryBuilder->{$relation}()->firstOrNew([$this->getFormProperty('primaryKey') => $queryBuilder->getKey()]);
                if ($relatedModel->id === null) $relatedModel->{$relatedModel->getKeyName()} = $data[$relatedModel->getKeyName()];
                foreach ($relationData as $field => $value) {
                    $relatedModel->{$field} = $value;
                    if ($value === null) $queryBuilder->{$field} = "null";
                }
                $relatedModel->save();
            }
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function validateFieldInput($field, $incomingValue): string|int|bool|null {
        $this->setLog(__METHOD__);
        $fieldType = $this->getFieldProperty($field, 'fieldType');
        $fieldContentType = $this->getFieldProperty($field, 'fieldContentType');
        $fieldRequired = $this->getFieldProperty($field, 'fieldRequired');
        $fieldMinLength = $this->getFieldProperty($field, 'fieldMinLength');
        $fieldMaxLength = $this->getFieldProperty($field, 'fieldMaxLength');
        $fieldMinValue = $this->getFieldProperty($field, 'fieldMinValue');
        $fieldMaxValue = $this->getFieldProperty($field, 'fieldMaxValue');
        $fieldPattern = $this->getFieldProperty($field, 'fieldPattern');
        $fieldOptions = $this->getFieldProperty($field, 'fieldOptions');
        $fieldValue = null;
        switch ($fieldType) {
            case 'select':
                if ($fieldContentType === 'options' || $fieldContentType === 'bool') {
                    if (array_key_exists($incomingValue, $fieldOptions) || array_key_exists((int)$incomingValue, $fieldOptions)) {
                        $fieldValue = $incomingValue;
                    } else {
                        $this->setErrorMessage("Ausgewählter Wert im Feld '" . $field . "' ist ungültig");
                    }
                }
                break;
            case 'int':
                $fieldValue = (int)$incomingValue;
                break;
            case 'date':
                if ($incomingValue === "") {
                    $fieldValue = NULL;
                } else {
                    $fieldValue = Carbon::parse($incomingValue)->format('Y-m-d');
                }
                break;
            case "time":
                $fieldValue = Carbon::parse($incomingValue)->format('H:i');
                break;
            case "datetime":
                $fieldValue = Carbon::parse($incomingValue)->format('Y-m-d H:i:s');
                break;
            case 'textarea':
                if ($fieldContentType === 'password') {
//                    var_dump("PASSWORD SOLL GEÄNDERT WERDEN");
                    $ruleSet = $this->getField($field)->getRuleSet();
                    if ($this->getField($field)->validatePassword($incomingValue)) {
//                        var_dump("Das Password wurde geprüft: OK");
                        $fieldValue = Hash::make($incomingValue);
                    } //else var_dump("Das Password wurde geprüft: NICHT OK");
//                    $fieldValue = $this->getField($field)->validatePassword();
                }
                if ($fieldContentType === 'text' || $fieldContentType === 'textarea') {
                    $fieldValue = $incomingValue;
                }
                break;
            default:
                $fieldValue = $incomingValue;
                break;
        }
        return $fieldValue;
    }

    function getReloadState(): bool {
        if ($this->getFormProperty('postAction') === 'reload')
            return true;
        else return false;
    }

    /**
     * @throws Exception
     */
    function setFieldDefaultValue($field, $value): self {
        $this->setLog(__METHOD__);
        $this->setFieldProperty($field, 'defaultValue', $value);
        return $this;
    }

    // in case of fieldType select/checkbox/radio, this function implement the options for the field
    function setReload(): self {
        if ($this->getFormProperty('postAction') === 'reload')
            $this->setFormProperty('postAction', null);
        elseif ($this->getFormProperty('postAction') !== 'reload' && $this->getFormProperty('postAction') === null)
            $this->setFormProperty('postAction', 'reload');
        return $this;
    }

    /**
     * @throws Exception
     */
    function fetchFieldSource($field) {
        $this->setLog(__METHOD__);
        return $this->getFieldProperty($field, 'fieldSource');
    }

    function validateInput() {
        //            $this->setLog(__METHOD__);
        //
        //            $results = [];
        //            $messages = [];
        //
        //            foreach($this->getFieldIndex() as $field) {
        //                $results[$field] = $this->validateFieldInput($field, request()->get($field));
        //                $message = $field;
        //            }
        //
        //            return $results;
    }

    /**
     * @param string $fieldId
     * @return mixed
     * @throws Exception
     */
    public function getFieldValue(string $fieldId) {
        $data = collect($this->getResults());
        if($data->has($fieldId)) return $data->get($fieldId) ?? null;
    }

    /**
     * @throws Exception
     */
    private function getFormObjectFieldRelations(): array {
        $this->setLog(__METHOD__);
        $fieldRelations = [];
        // Alle definierten Felder werden hier gesammelt.
        $fieldStack = [];
        // Alle Felder zusammensammeln, die konfiguriert sind
        $fieldIndex = $this->getFieldIndex();
        foreach ($fieldIndex as $field) {
            $fieldStack[$field] = [];
            $fieldSource = $this->getFieldProperty($field, 'fieldSource') !== "" ? $this->getFieldProperty($field, 'fieldSource') : NULL;
            if ($fieldSource !== NULL) {
                if (is_string($fieldSource)) {
                    $fieldStack[$field]['fieldSource'][] = $fieldSource;
                } else {
                    foreach ($fieldSource as $fsK => $fsV) {
                        $fsV = str_replace("(:)", "(?)", $fsV);
                        $fieldStack[$field]['fieldSource'][$fsK] = explode(":", $fsV);
                    }
                    foreach ($fieldStack[$field]['fieldSource'] as $rel) {
                        if (isset($rel[0]) && isset($rel[1]) && !isset($rel[2]))
                            $fieldRelations[$rel[0]][] = $rel[1];
                    }
                    $fieldStack[$field]['fieldResult'] = NULL;
                }
            } else {
                $fieldRelations[$field] = [];
            }
        }
        return [
            'fieldStack' => $fieldStack,
            'fieldRelations' => $fieldRelations
        ];
    }

    /**
     * @throws Exception
     */
    private function processingFieldSources($fields, $values) {
        $this->setLog(__METHOD__);
        $results = $fields;
        foreach ($fields as $field => $data) {
            if (count($data['fieldSource']) > 0) {
                $data['result'] = "";
                foreach ($data['fieldSource'] as $srcV) {
                    if (is_array($srcV)) {
                        if (isset($values['subid'])) {
                            if (isset($srcV[1])) {
                                if (isset($srcV[2])) {
                                    if (isset($this->dataResults[$srcV[0]][$values['id']][$values['subid']][$srcV[1]][$srcV[2]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$values['subid']][$srcV[1]][$srcV[2]];
                                    elseif (isset($this->dataResults[$srcV[0]][$values['id']][$srcV[1]][$srcV[2]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$srcV[1]][$srcV[2]];
                                    else $data['result'] .= "";
                                } else {
                                    if (isset($this->dataResults[$srcV[0]][$values['id']][$values['subid']][$srcV[1]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$values['subid']][$srcV[1]];
                                    elseif (isset($this->dataResults[$srcV[0]][$values['id']][$srcV[1]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$srcV[1]];
                                    else $data['result'] .= "";
                                }
                            } else $data['result'] .= $srcV[0] . " ";
                        } else {
                            if (isset($srcV[1])) {
                                if (isset($srcV[2])) {
                                    if (isset($this->dataResults[$srcV[0]][$values['id']][$srcV[1]][$srcV[2]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$srcV[1]][$srcV[2]];
                                    elseif (isset($this->dataResults[$srcV[0]][$values['id']][$srcV[1]][$srcV[2]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$srcV[1]][$srcV[2]];
                                    else $data['result'] .= "";
                                } else {
                                    if (isset($this->dataResults[$srcV[0]][$values['id']][$srcV[1]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$srcV[1]];
                                    elseif (isset($this->dataResults[$srcV[0]][$values['id']][$srcV[1]]))
                                        $data['result'] .= $this->dataResults[$srcV[0]][$values['id']][$srcV[1]];
                                    else $data['result'] .= "";
                                }
                            } else {
                                $data['result'] .= $srcV[0];
                            }
                        }
                        $data['result'] = $this->format($data['result'], $this->getFieldProperty($field, 'fieldType'));
                    } else $data['result'] = $srcV . "";
                }
                $data['result'] = str_replace("(?)", ":", $data['result']);
                $results[$field] = $data['result'];
            } else $results[$field] = "";
        }
        return $results;
    }
}
