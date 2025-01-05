<?php

    namespace DO\Main\Elements\FieldSupport;

use Illuminate\Support\Collection;

/**
 * Class FieldStacks
 *
 * Manages a collection of FieldObject instances.
 * The FieldObject instances are stored in a Collection in a key-value format where
 * each key is the field ID of the FieldObject.
 */
class FieldStacks {
    protected Collection $stacks;

    /**
     * Constructs a new object.
     *
     * Initializes the "stacks" property with an empty collection.
     */
    private function __construct() {
        $this->stacks = \collect([]);
    }

    /**
     * Retrieves an indexed array of field IDs from the collection
     * Optionally, swaps the keys and values in the array
     * @param bool $swap (Optional) Whether to swap the keys and values in the resulting array. Default is false.
     * @return array An array of field IDs. If $swap is true, the resulting array will have values as keys or keys as values (swap).
     */
    public function index(bool $swap = false): array {
        $result = [];
        foreach ($this->stacks->toArray() as $stack) {
            $result[] = $stack->getFieldProperty('fieldID');
        }
        if ($swap) {
            $result = array_flip($result);
        }
        return $result;
    }

    /**
     * Retrieves a FieldObject instance from collection using its field ID
     *
     * @param string $fieldID The unique identifier of a field.
     * @return FieldObject The FieldObject instance associated with the supplied field ID.
     */
    public function get(string $fieldID): ListFieldObject|FormFieldObject|FieldObject|null {
        return $this->stacks->get($fieldID);
    }

    /**
     * Returns all the stacks in the collection.
     * @return Collection The collection of stacks.
     */
    public function all(): Collection {
        return $this->stacks;
    }

    /**
     * Adds a new FieldObject to the collection or update if the field ID already exists.
     *
     * @param string $fieldID The FieldObject's unique identifier.
     * @param FieldObject $fieldObject The FieldObject instance to add to the collection.
     * @return $this Returns the same instance to allow method chaining.
     */
    public function set(string $fieldID, ListFieldObject|FormFieldObject|FieldObject $fieldObject): self {
        $fieldObject->setFieldProperty('fieldID', $fieldID);
        $this->stacks->put($fieldID, $fieldObject);
        return $this;
    }

    /**
     * Retrieves an array of field properties from collection
     * @param string $property The name of the property to retrieve.
     * @return array An array containing the specified property for each index in the collection.
     */
    public function properties(string $property): array {
        return array_map(function ($index) use ($property) {
            return $index->getFieldProperty($property);
        }, $this->stacks->toArray());
    }

    /**
     * Removes a FieldObject from the collection using its field ID
     *
     * @param string $fieldID The unique identifier of a field.
     * @return $this Returns the same instance to allow method chaining.
     */
    public function destroy(string $fieldID): self {
        $this->stacks->forget($fieldID);
        return $this;
    }

    public function create($type) {
        return $this->stacks->put(uniqid(), FieldObject::implement());
    }

    /**
     * Creates and returns a new instance of the FieldStacks class.
     *
     * @return self A new instance of FieldStacks.
     */
    public static function implement(): self {
        return new self;
    }
}
