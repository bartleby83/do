<?php

    namespace DO\Main\Elements\MenuSupport;

class MenuGroup {
    public string $name;
    public string $label;
    public string $icon;
    public string $route;
    public string $type;
    public string $target;
    public array $roles;
    public array $permission;
    public string $parent;
    public string $order;
    public bool $hidden;
    public bool $active;

    public function __construct(array $config = []) {
        $this->name = $config['name'] ?? '';
        $this->label = $config['label'] ?? '';
        $this->icon = $config['icon'] ?? '';
        $this->route = $config['route'] ?? '';
        $this->type = $config['type'] ?? '';
        $this->target = $config['target'] ?? '';
        $this->roles = $config['roles'] ?? [];
        $this->permission = $config['permission'] ?? [];
        $this->parent = $config['parent'] ?? '';
        $this->order = $config['order'] ?? '';
        $this->hidden = $config['hidden'] ?? false;
        $this->active = $config['active'] ?? false;
        return $this;
    }
}
