<?php

    namespace DO\Main\Elements\FieldSupport;

use Illuminate\Support\Collection;

/**
 * Class FieldObjectConstructor
 *
 * This class provides default field configurations for different scenarios.
 * It contains different sets of properties for general fields, list fields, and form fields.
 * Using in DataObjects, specially in {@link ListObject} or {@link FormObject}
 *
 * @package App\Generic\DataObjects
 */
class FieldObjectConstructor {
    /**
     * @var array Default properties for all field objects
     */
    protected static array $fieldObjectProperties = [
        'fieldID' => "default_field_id",
        'fieldName' => "default_field_name",
        'fieldType' => "text",
        'fieldAssocObjectID' => "associated_object_id",
        'systemField' => false,
    ];
    /**
     * @var array Default properties specific to list field objects
     */
    protected static array $fieldListProperties = [
        'fieldSource' => null,
        'fieldType' => 'text',
        'fieldContentType' => "text",
        'fieldSubType' => 'default',
        'fieldHiddenInList' => false,
        'dataSourceInList' => [],
        'fieldSortable' => false,
        'fieldSortOrder' => 'asc',
        'fieldSortAssign' => null,
        'fieldSearchable' => false,
        'fieldSearchAssign' => null,
        'fieldFunctions' => [],
        'fieldFilterable' => false,
        'fieldFilter' => false,
        'fieldEditable' => false,
        'fieldRenderOptions' => [],
        'fieldOptions' => [],
        'fieldColumnWidth' => 'auto',
        'fieldLink' => [],
        'grouping' => [],
    ];
    protected static array $fieldDataTableProperties = [
        'columnSum' => [],
        'rowSum' => [],
    ];
    /**
     * @var array Default properties specific to form field objects
     */
    protected static array $fieldFormProperties = [
        'allowNull' => true,
        'fieldSource' => null,
        'fieldHiddenInForm' => false,
        'fieldHiddenInView' => false,
        'fieldType' => 'text',
        'fieldContentType' => "text",
        'fieldFunctions' => [],
        'fieldRenderOptions' => [],
        'fieldOptions' => [],
        'fieldDescription' => "",
        'fieldTooltip' => "",
        'fieldRequired' => false,
        'fieldReadOnly' => false,
        'dataSourceInForm' => [],
        'onChangeHandling' => [],
        'placeholder' => null,
        'writeable' => true,
        'minLength' => null,
        'minWidth' => null,
        'maxLength' => null,
        'maxWidth' => null,
        'minValue' => null,
        'maxValue' => null,
        'fieldPattern' => null,
        'autocomplete' => 'off',
        'defaultValue' => null,
        'requireUpperCase' => false,
        'requireLowerCase' => false,
        'requireNumber' => false,
        'requireSpecialCharacters' => false,
        'definedSpecialCharacters' => null,
        'ignoreField' => false,
    ];

    /**
     * Retrieves the default properties for a specific type of field.
     *
     * This method fetches the default properties based on the received parameter.
     *
     * @param string $var The type of default properties to be fetched.
     *                    It can be "fieldObjectProperties", "fieldListProperties", or "fieldFormProperties".
     *
     * @return Collection Returns a collection of default properties for the specified type.
     */
    public static function fetchDefaults(string $var): Collection {
        $array = \collect([
            'fieldObjectProperties' => \collect(self::$fieldObjectProperties),
            'fieldListProperties' => \collect(self::$fieldListProperties),
            'fieldDataTableProperties' => \collect(self::$fieldDataTableProperties),
            'fieldFormProperties' => \collect(self::$fieldFormProperties)
        ]);
        return $array->get($var);
    }
}
