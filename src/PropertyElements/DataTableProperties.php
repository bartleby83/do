<?php

    namespace DO\Main\PropertyElements;

/**
 * @extends AbstractProperties<string, string|int|bool|float>
 */
class DataTableProperties extends AbstractProperties {
    protected bool $grouping = false;
    protected bool $columnSum = false;
    protected array $lengthMenu = [
        [5, 10, 25, 50, 100, 250, 500, -1], // Values
        [5, 10, 25, 50, 100, 250, 500, 'Alle'] // Bezeichnungen
    ];
    protected int $listLength = 50;
    protected int $listStart = 0;
    protected bool $rowSum = false;
    protected bool $scrollCollapse = false;
    protected bool $selectable = false;
    protected bool $sortable = false;
    protected bool $searchable = false;
    protected bool $filterable = false;
    protected bool $serverSide = true;
    protected bool $showTableInformation = true;
    protected bool $csvExportLoaded = false;
    protected bool $csvExportAll = false;

    public function __construct() {
        parent::__construct();
        $this->items = [
            'grouping' => $this->getProperty('grouping'),
            'columnSum' => $this->getProperty('columnSum'),
            'lengthMenu' => $this->getProperty('lengthMenu'),
            'listLength' => $this->getProperty('listLength'),
            'listStart' => $this->getProperty('listStart'),
            'rowSum' => $this->getProperty('rowSum'),
            'scrollCollapse' => $this->getProperty('scrollCollapse'),
            'selectable' => $this->getProperty('selectable'),
            'sortable' => $this->getProperty('sortable'),
            'searchable' => $this->getProperty('searchable'),
            'filterable' => $this->getProperty('filterable'),
            'serverSide' => $this->getProperty('serverSide'),
            'showTableInformation' => $this->getProperty('showTableInformation'),
            'csvExportLoaded' => $this->getProperty('csvExportLoaded'),
            'csvExportAll' => $this->getProperty('csvExportAll'),
        ];
    }
}