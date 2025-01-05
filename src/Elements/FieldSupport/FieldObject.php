<?php

    namespace DO\Main\Elements\FieldSupport;

use Illuminate\Support\Collection;
use ReflectionClass;

/**
 * Class FieldObject
 *
 * This class acts as a base class for objects with a set of properties. It
 * provides methods for managing these properties.
 *
 * @package App\Generic\DataObjects
 */
class FieldObject {
    /**
     * @var Collection Field object general properties
     */
    protected Collection $fieldObjectProperties;
    /**
     * @var Collection Specific properties for list type fields
     */
    protected Collection $fieldListProperties;
    /**
     * @var Collection Field data table properties
     */
    protected Collection $fieldDataTableProperties;
    /**
     * @var Collection Specific properties for form fields
     */
    protected Collection $fieldFormProperties;

    /**
     * FieldObject constructor.
     *
     * Initializes default properties of the FieldObject. Property values
     * are fetched from FieldObjectConstructor.
     *
     * @return self Returns the FieldObject instance
     */
    function __construct() {
        $this->fieldObjectProperties = \collect(FieldObjectConstructor::fetchDefaults('fieldObjectProperties'));
        $this->fieldListProperties = \collect(FieldObjectConstructor::fetchDefaults('fieldListProperties'));
        $this->fieldDataTableProperties = \collect(FieldObjectConstructor::fetchDefaults('fieldDataTableProperties'));
        $this->fieldFormProperties = \collect(FieldObjectConstructor::fetchDefaults('fieldFormProperties'));
        return $this;
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
     * Getter / Setter
     */
    /**
     * Get the value of a property.
     * Retrieves the value of the specified property from the FieldObject.
     *
     * @param string|null $prop The name of the property to get. If not provided, returns all properties.
     *
     * @return mixed The value of the specified property. Returns NULL if the property does not exist.
     */
    final public function property(string $prop = NULL): mixed {
        return $this->getFieldProperty($prop);
    }

    /**
     * Retrieves a specified property from the object. The property
     * returned might depend on the type of the parent object.
     *
     * @param string|null $prop The property name to fetch.
     *
     * @return mixed The property value if available, NULL otherwise.
     */
    final public function getFieldProperty(string $prop = NULL): mixed {
        if ($this->fieldObjectProperties->has($prop)) {
            return $this->fieldObjectProperties->get($prop);
        }
        $type = (new ReflectionClass($this))->getShortName();
        switch ($type) {
            case "ListFieldObject":
                if ($this->fieldListProperties->has($prop)) {
                    return $this->fieldListProperties->get($prop) ?? null;
                } else if ($this->fieldDataTableProperties->has($prop)) {
                    return $this->fieldDataTableProperties->get($prop) ?? null;
                }
                break;
            case "FormFieldObject":
                if ($this->fieldFormProperties->has($prop)) {
                    return $this->fieldFormProperties->get($prop);
                }
                break;
        }
        if ($prop === NULL) {
            return [
                'fieldObjectProperties' => $this->fieldObjectProperties->all(),
                'fieldListProperties' => $this->fieldListProperties->all(),
                'fieldDataTableProperties' => $this->fieldDataTableProperties->all(),
                'fieldFormProperties' => $this->fieldFormProperties->all(),
            ];
        } else {
            if ($this->fieldObjectProperties->get($prop) !== null) return $this->fieldObjectProperties->get($prop);
            elseif ($this->fieldFormProperties->get($prop) !== null) return $this->fieldFormProperties->get($prop);
            elseif ($this->fieldListProperties->get($prop) !== null) return $this->fieldListProperties->get($prop);
            elseif ($this->fieldDataTableProperties->get($prop) !== null) return $this->fieldDataTableProperties->get($prop);
        }
        return null;
    }

    /**
     * Sets the value for a specified property in the FieldObject.
     *
     * This updates the object property with the provided value, assuming
     * the property exists.
     *
     * @param string $prop The property name to set.
     * @param String|array|Int|Bool|Collection|null $value The new value for the property.
     *
     * @return self The FieldObject instance.
     */
    final public function setFieldProperty(string $prop, string|array|int|bool|Collection|null $value): self {
        if ($this->fieldObjectProperties->has($prop)) {
            $this->fieldObjectProperties->put($prop, $value);
        }
        $type = (new ReflectionClass($this))->getShortName();
        switch ($type) {
            case "ListFieldObject":
                if ($this->fieldListProperties->has($prop)) {
                    $this->fieldListProperties->put($prop, $value);
                } else if ($this->fieldDataTableProperties->has($prop)) {
                    $this->fieldDataTableProperties->put($prop, $value);
                }
                break;
            case "FormFieldObject":
                if ($this->fieldFormProperties->has($prop)) {
                    $this->fieldFormProperties->put($prop, $value);
                }
                break;
        }
        return $this;
    }

    /**
     * Static methods
     */
    /**
     * Process properties by filtering the values based on the given properties.
     *
     * @param array<string, mixed> $properties An array containing the properties to filter.
     * @param array<string, mixed> $values An array containing the values to be filtered.
     *
     * @return array<string, mixed> The filtered result array.
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
