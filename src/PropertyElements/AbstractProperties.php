<?php

    namespace DO\Main\PropertyElements;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
abstract class AbstractProperties extends Collection {
    /**
     * @param array<string, bool|int|string|float|null> $properties
     */
    public function __construct(array $properties = []) {
        parent::__construct();
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
                $this->items[$name] = $value;
            }
        }
    }

    /**
     * @return static
     */
    final static public function implement(): static {
        $class = get_called_class();
        return new $class();
    }

    /**
     * Get the value of a property.
     *
     * @param string|null $name The name of the property to retrieve. If not provided, returns all properties.
     * @return bool|int|string|float|array<string, bool|int|string|float|null>|null The value of the property. If $name is null, returns an array containing all properties.
     */
    final public function getProperty(string $name = null): bool|int|string|float|array|null {
        if ($name === null) {
            return $this->getProperties();
        } else {
            return $this->items[$name] ?? null;
        }
    }

    /**
     * Get the properties of the object.
     *
     * @return array<string, bool|int|string|float|null>|null The properties of the object.
     */
    final public function getProperties(): array|null {
        return $this->items;
    }

    /**
     * Set the value of a property.
     * @param string $name The name of the property to set.
     * @param string $value The value of the property to set.
     * @return self<TKey, TValue>
     */
    final public function setProperty(string $name, string $value): self {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            $this->items[$name] = $value;
        }
        return $this;
    }
}
