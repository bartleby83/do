<?php

    namespace DO\Main\Elements\FieldSupport;

use Exception;

/**
 * Class ListFieldObject
 *
 * An extension of the FieldObject class that sets specific behaviors and properties for list type field objects.
 *
 * @package App\Generic\DataObjects
 */
class ListFieldObject extends FieldObject {
    /**
     * ListFieldObject constructor.
     *
     * Initializes the ListFieldObject by calling the parent FieldObject's construct method.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Implement the FieldObject.
     *
     * Creates and returns a new instance of the FieldObject.
     *
     * @return self Returns the new instance of FieldObject
     */
    public static function implement(): self {
        return new self;
    }

    /**
     * Call a specific function related to the field object.
     *
     * This method currently returns a string mentioning its own method name.
     *
     * @return string Returns a string mentioning its own method name.
     * @throws Exception
     */
    public function callFieldFunction(): mixed {
        throw new Exception("ListFieldObject->callFieldFunction() is not implemented yet.");
    }

    /**
     * Process the properties of the field object.
     *
     * This method currently does nothing.
     * TODO: Implement the processingProperties method.
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function processingProperties(array $properties = [], array $values = []): array {
        $result = [];
        foreach ($properties as $property) {
            if (isset($values[$property])) {
                $result[$property] = $values[$property];
            }
        }
        return $result;
    }
}
