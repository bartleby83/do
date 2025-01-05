<?php

    namespace DO\Main\PropertyElements;

/**
 * @extends AbstractProperties<string, string|int|bool|float>
 */
final class ObjectProperties extends AbstractProperties {
    protected string $objectID;
    protected string $objectName;
    protected string $objectType;
    protected string|null $configRequestURI;

    public function __construct(string $objectID = "", string $objectName = "", string $objectType = "", string|null $configRequestURI = null) {
        parent::__construct();
        $this->objectID = $objectID;
        $this->objectName = $objectName;
        $this->objectType = $objectType;
        $this->configRequestURI = $configRequestURI;
        $this->items = [
            'objectID' => $this->objectID,
            'objectName' => $this->objectName,
            'objectType' => $this->objectType,
            'configRequestURI' => $this->configRequestURI,
        ];
    }
}
