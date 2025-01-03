<?php

    namespace DO\Main;

use Illuminate\Support\Collection;

/**
 * Class Collection
 * The Collection class provides a fluent API for working with arrays or iterable objects.
 * It is a convenient wrapper around PHP's array functions while providing additional methods
 * for manipulating and filtering the data.
 */
class DataObjectsConfigTemplate {
    /**
     * Offers a default configuration template for data objects.
     *
     * This method returns a collection of configuration items
     * that detail the properties and behaviors of different objects.
     *
     * @return Collection Returns a collection containing
     *                    the default configuration template.
     */
    public static function configTemplate(): Collection {
        return \collect([
            // Konfiguration der Tabelle
            'objectProperties' => \collect([
                'objectID' => 'defaultConfigTemplate',
                'objectName' => "defaultConfigTemplateName",
                'objectType' => null,
                'configRequestURI' => null
            ]),
            'listProperties' => \collect([
                'dataRequestURI' => null, // Quelle zum Abruf der Daten, die innerhalb der Tabelle dargestellt werden sollen
                'searching' => false, // Soll die Suche aktiviert werden
                'selectKey' => 'id',
                'sorting' => [], // Wie sollen die Daten in der Tabelle sortiert werden (array)
                'writable' => false, // In Vorbereitung
                'filter' => null,
                'primaryKey' => 'id',
            ]),
            'dataTableProperties' => \collect([
                'columnSum' => false,
                'grouping' => false,
                'listLength' => 100, // Wie viele Ergebnisse sollen in der Liste dargestellt werden?
                'rowSum' => false,
                'scrollCollapse' => false,
                'selectable' => false, // Sollen die Zeilen auswählbar sein?
                'serverSide' => true, // Sollen die Daten serverseitig verarbeitet werden?
                'showTableInformation' => true,
                'csvExportLoaded' => false,
                'csvExportAll' => false,
            ]),
            'formProperties' => \collect([
                'dataRequestURI' => null, // Quelle zum Abruf der Daten, die innerhalb der Tabelle dargestellt werden sollen
                'primaryKey' => 'id',
                'identifier' => 'id',
                'writable' => false,
                'viewMode' => 'view', // edit, view
                'dialogHandler' => 'inCard', // inCard, inModal
                'postUrl' => null,
                'postAction' => null, // reload
                'grouping' => false,
            ]),
            'dataSource' => \collect([
                'type' => false,
                'primaryStack' => null,
                'stacks' => [],
            ]),
            'fieldConfigs' => \collect([
                'names' => [],
                //                'types'              => [], // unspecific, obsolete
                'fieldTypes' => [],
                'fieldContentTypes' => [],
                'fieldOptions' => [],
                'fieldWidths' => [],
                'hidden' => [],
                'hiddenView' => [],
                'fieldSources' => [],
                'fieldLinks' => [],
                'grouping' => [],
                'fieldSortable' => [],
                'fieldEditable' => [],
                'sortAssign' => [],
                'searchAssign' => [],
                'fieldFunctions' => [],
                'fieldRenderOptions' => [],
                'fieldDescription' => [],
                'fieldSearchable' => [],
                'fieldFilter' => [],
                'required' => [],
                'fieldSum' => [],
                'fieldMaxLength' => [],
                'fieldMaxWidth' => [],
                'fieldMinLength' => [],
                'fieldMinWidth' => [],
                'fieldMinValue' => [],
                'fieldMaxValue' => [],
                'fieldRequireSpecialCharacters' => [],
                'fieldDefinedSpecialCharacters' => [],
                'fieldRequireLowerCase' => [],
                'fieldRequireUpperCase' => [],
                'fieldRequireNumber' => [],
                'fieldAllowNull' => [],
                'writeable' => [],
                'ignoreFields' => []
            ]),
            'fields' => \collect(),
            'menu' => \collect(),
        ]);
    }
}
