<?php

namespace DO\Main;

use DO\Main\PropertyElements\AbstractProperties;
use DO\Main\PropertyElements\DataTableProperties;
use DO\Main\PropertyElements\FormProperties;
use DO\Main\PropertyElements\ListProperties;
use DO\Main\PropertyElements\ObjectProperties;
use Illuminate\Support\Collection;

/**
 * Class DataObjectsConstructor
 *
 * Contains default properties for Data Objects and methods to fetch them.
 *
 * @package App\Generic\DataObjects
 */
class DataObjectsConstructor {
    /**
     * @var array Default properties specific to menu objects
     */
    protected static array $menu = [];
    /**
     * @var array Default properties specific to field objects
     */
    protected static array $fields;

    /**
     * Fetches default values for the specified property category.
     *
     * @param string $var The category of the default properties that need to be fetched.
     *
     * @return AbstractProperties|Collection
     */
    public static function fetchDefaults(string $var): AbstractProperties|Collection {
        return \collect(
            self::getDefaults($var)
        );
    }

    /**
     * Returns the default properties array of the specified property category.
     *
     * @param string $var The category of the default properties.
     *
     * @return ObjectProperties|array<string, mixed>|null
     */
    protected static function getDefaults(string $var): ObjectProperties|array|null {
        $array = \collect([
            'objectProperties' => ObjectProperties::implement(),
            'listProperties' => ListProperties::implement(),
            'formProperties' => FormProperties::implement(),
            'dataTableProperties' => DataTableProperties::implement(),
        ]);
        return $array->get($var);
    }
}
