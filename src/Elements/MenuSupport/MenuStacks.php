<?php

    namespace DO\Main\Elements\MenuSupport;

use App\Helpers\AuthentificationHelper as Auth;
use Illuminate\Support\Collection;

/**
 * Class MenuStacks
 *
 * This class manages a stack of menus for DataObjects
 *
 * @package DO\Main\Elements\MenuSupport
 */
class MenuStacks {
    /**
     * @var Collection<string, array<string, mixed>>
     */
    private Collection $stacks;

    function __construct() {
        $this->stacks = \collect();
        return $this;
    }

    /**
     * @param array<string, array<string, mixed>>|null $stacks
     * @return $this
     */
    function addStacks(array|null $stacks): self {
        foreach ($stacks as $name => $basket) {
            $this->addStack($name, $basket);
        }
        return $this;
    }

    public function addStack($name, array|null $basket = []): self {
        $this->stacks->push([
            'name' => $name,
            'basket' => MenuItem::implement($basket)
        ]);
        return $this;
    }

    /**
     * method implement
     */
    public static function implement(): self {
        return new self;
    }

    function getStacks(): Collection {
        $stacks = \collect();
        foreach ($this->stacks as $stack) {
            if (count($stack['basket']->get('permissions')) === 0) {
                $stacks->push($stack);
            } else {
                if (Auth::user()->hasPermission($stack['basket']->get('permissions'))) {
                    $stacks->push($stack);
                }
            }
        }
        return $stacks;
    }

    function getStack(string $name): MenuItem {
        return $this->stacks->where('name', $name)->map(function ($item) {
            return $item['basket'];
        })->first();
    }

    function removeStack(string $name): self {
        $this->stacks = $this->stacks->filter(function ($stack) use ($name) {
            return $stack['name'] !== $name;
        });
        return $this;
    }
}
