<?php
/**
 * Class MenuItem
 * This class represents a menu item for using in DataObjects
 */

    namespace DO\Main\Elements\MenuSupport;

use Faker\Factory;
use Illuminate\Support\Collection;

/**
 * Represents a menu item.
 */
class MenuItem {
    protected string $name;
    protected string $label;
    protected string $icon;
    protected string $link;
    protected string $type;
    protected string $target;
    protected string $identifier;
    protected string $class;
    protected string $parent;
    protected string $order;
    protected bool $inList;
    protected bool $inForm;
    protected bool $inRow;
    protected bool $hidden;
    protected bool $active;
    protected array $roles;
    protected array $permissions;
    protected array $conditions;
    protected int $sortOrder;
    protected mixed $iconType;

    /**
     * Constructs a new object and initializes its properties with the given configuration array.
     * @param array $config An optional configuration array with the following possible keys:
     */
    public function __construct(array $config) {
        $this->name = $config['name'] ?? Factory::create()->uuid;
        $this->label = $config['label'] ?? trim(Factory::create()->sentence());
        $this->iconType = $config['iconType'] ?? 'bi';
        $this->icon = $config['icon'] ?? Factory::create()->filePath();
        $this->iconType = $config['iconType'] ?? 'bi';
        $this->link = $config['link'] ?? "#" . Factory::create()->url;
        $this->type = $config['type'] ?? 'link';
        $this->target = $config['target'] ?? '_blank'; // '_blank', '_self', '_parent', '_top'
        $this->identifier = $config['identifier'] ?? 'id';
        $this->roles = $config['roles'] ?? [];
        $this->permissions = $config['permission'] ?? [];
        $this->conditions = $config['conditions'] ?? [];
        $this->parent = $config['parent'] ?? '';
        $this->order = $config['order'] ?? '';
        $this->hidden = $config['hidden'] ?? false;
        $this->active = $config['active'] ?? false;
        $this->inList = $config['inList'] ?? false;
        $this->inForm = $config['inForm'] ?? false;
        $this->inRow = $config['inRow'] ?? false;
        $this->sortOrder = $config['sortOrder'] ?? 0;
        return $this;
    }

    /**
     * Implements the functionality to create a new instance of the class with the provided configuration array.
     * @param array $config An associative array containing the configuration options.
     *                      The optional keys and their default values are the same as the constructor method's documentation.
     * @return self A new instance of the class initialized with the provided configuration array.
     */
    public static function implement(array $config = []): self {
        return new self($config);
    }

    /**
     * Retrieves the value of the specified property.
     * @param string $property The name of the property to retrieve.
     * @return mixed The value of the specified property.
     */
    public function get(string $property): mixed {
        return $this->$property;
    }

    /**
     * Set the value of a property.
     * @param string $property The name of the property to set.
     * @param mixed $value The value to assign to the property.
     * @return self Returns the instance of the class for method chaining.
     */
    public function set(string $property, mixed $value): self {
        $this->$property = $value;
        return $this;
    }

    /**
     * Item Method
     * Returns a Laravel collection containing all the properties of the item for using @return Collection The item properties.
     * @link DataObjects
     */
    public function item(): Collection {
        return \collect([
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'iconType' => $this->iconType,
            'link' => $this->link,
            'type' => $this->type,
            'target' => $this->target, // '_blank', '_self', '_parent', '_top'
            'identifier' => $this->identifier,
            'roles' => $this->roles,
            'permission' => $this->permissions,
            'parent' => $this->parent,
            'order' => $this->order,
            'hidden' => $this->hidden,
            'active' => $this->active,
            'inList' => $this->inList,
            'inForm' => $this->inForm,
            'inRow' => $this->inRow,
            'conditions' => $this->conditions,
            'sortOrder' => $this->sortOrder
        ]);
    }
}
