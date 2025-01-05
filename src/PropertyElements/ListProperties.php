<?php

    namespace DO\Main\PropertyElements;

/**
 * @extends AbstractProperties<string, string|int|bool|float>
 */
class ListProperties extends AbstractProperties {
    protected string $primaryKey = 'id';
    protected string $identifier = 'id';
    protected string $selectKey = 'id';
    protected ?string $dataRequestURI = null;
    protected bool $orderCellsTop = true;
    protected bool $fixedHeader = true;
    protected bool $searching = false;
    protected bool $fieldSearch = false;
    protected bool $fieldSearchBar = false;
    protected bool $processing = true;
    protected bool $deferRender = true;
    /** @var array<int, array<string, string>> */
    protected array $sorting = [['id', 'asc']];
    /** @var bool|array<int, array<string, string>> */
    protected bool|array $grouping = false;
    protected bool $writable = false;
    protected ?string $filter = null;

    public function __construct() {
        parent::__construct();
        $this->items = [
            'primaryKey' => $this->primaryKey,
            'identifier' => $this->identifier,
            'selectKey' => $this->selectKey,
            'dataRequestURI' => $this->dataRequestURI,
            'orderCellsTop' => $this->orderCellsTop,
            'fixedHeader' => $this->fixedHeader,
            'searching' => $this->searching,
            'fieldSearch' => $this->fieldSearch,
            'fieldSearchBar' => $this->fieldSearchBar,
            'processing' => $this->processing,
            'deferRender' => $this->deferRender,
            'sorting' => $this->sorting,
            'grouping' => $this->grouping,
            'writable' => $this->writable,
            'filter' => $this->filter,
        ];
    }
}