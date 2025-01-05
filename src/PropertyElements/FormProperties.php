<?php

    namespace DO\Main\PropertyElements;

/**
 * @extends AbstractProperties<string, string|int|bool|float>
 */
class FormProperties extends AbstractProperties {
    protected ?string $dataRequestURI = null;
    protected string $primaryKey = 'id';
    protected string $identifier = 'id';
    protected bool $writable = false;
    protected string $viewMode = 'view';
    protected string $dialogHandler = 'inCard';
    protected ?string $postUrl = null;
    protected ?string $postAction = null;
    protected bool $grouping = false;

    public function __construct() {
        parent::__construct();
        $this->items = [
            'dataRequestURI' => $this->dataRequestURI,
            'primaryKey' => $this->primaryKey,
            'identifier' => $this->identifier,
            'writable' => $this->writable,
            'viewMode' => $this->viewMode,
            'dialogHandler' => $this->dialogHandler,
            'postUrl' => $this->postUrl,
            'postAction' => $this->postAction,
            'grouping' => $this->grouping,
        ];
    }
}