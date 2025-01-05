<?php

    namespace DO\Main\SourceControl;

use Illuminate\Database\Query\Builder;

/**
 * Class DataSourceStacks
 *
 * @package DO\Main\SourceControl
 */
class DataSourceStacks {
    /**
     * @var array
     */
    private array $sources;
    /**
     * @var null|string
     */
    private null|string $primaryStack = null;
    /**
     * @var true
     */
    private bool $manual = true;

    /**
     * DataSourceStacks constructor.
     */
    public function __construct() {
        $this->sources = [];
    }

    /**
     * Implement a new instance of the class.
     *
     * @return self
     */
    public static function implement(): self {
        return new self;
    }

    /**
     * Create a new source stack.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder|Builder|null $builder to
     *                                                                                                         access
     *                                                                                                         in
     *                                                                                                         {@link ListObjects}|{@link FormObjects}
     * @param string|null $alias
     *
     * @return self
     */
    public function create(\Illuminate\Contracts\Database\Eloquent\Builder|Builder|null $builder = null, string|null $alias = null): self {
        if ($alias === null) {
            return $this;
        }
        if ($builder instanceof \Illuminate\Contracts\Database\Eloquent\Builder) {
            $this->implementStackEloquentBuilder($builder, $alias);
        }
        if ($builder instanceof Builder) {
            $this->implementStackQueryBuilder($builder, $alias);
        }
        return $this;
    }

    /**
     * Implement the stack for the Eloquent builder.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder The Eloquent builder instance.
     * @param string $alias The alias for the Eloquent builder.
     *
     * @return void
     */
    private function implementStackEloquentBuilder(\Illuminate\Contracts\Database\Eloquent\Builder $builder, string $alias): void {
        $this->sources[$alias] = $builder;
        if ($this->primaryStack === null && count($this->sources) === 1) $this->setPrimaryStack($alias);
    }

    /**
     * Set the primary stack.
     *
     * @param $stack
     *
     * @return self
     */
    public function setPrimaryStack($stack): self {
        $this->primaryStack = $stack;
        return $this;
    }

    /**
     * Implement stack query builder.
     *
     * @param Builder $builder
     * @param string $alias
     *
     * @return void
     */
    private function implementStackQueryBuilder(Builder $builder, string $alias): void {
        $this->sources[$alias] = $builder;
        if ($this->primaryStack === null && count($this->sources) === 1) $this->setPrimaryStack($alias);
    }

    /**
     * Set the "manual" flag to true in order to indicate that the query is manually constructed.
     *
     * @return self
     */
    public function manualQuery(): self {
        $this->manual = true;
        return $this;
    }

    /**
     * Set manual flag to false, indicating that the query will be generated automatically.
     *
     * @return self
     */
    public function automaticQuery(): self {
        $this->manual = false;
        return $this;
    }

    /**
     * Check if the item is manual.
     *
     * @return bool Returns true if the item is manual, false otherwise.
     */
    public function isManual(): bool {
        return $this->manual;
    }

    /**
     * returns string of actual primary stack name
     *
     * @return string|null
     */
    public function whichPrimary(): string|null {
        return $this->primaryStack;
    }

    /**
     * Get the primary stack.
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder|Builder|null
     */
    public function getPrimarySource(): \Illuminate\Contracts\Database\Eloquent\Builder|Builder|null {
        return $this->get($this->primaryStack) ?? null;
    }

    /**
     * Get a specific source stack.
     *
     * @param string|null $alias
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder|Builder|null
     */
    public function get(string|null $alias = null): \Illuminate\Contracts\Database\Eloquent\Builder|Builder|null {
        return $this->sources[$alias] ?? null;
    }

    /**
     * check are source stacks exists
     */
    public function hasSources(): bool {
        return count($this->sources) > 0;
    }

    /**
     * Destroy a specific source stack.
     *
     * @param string $alias
     *
     * @return self
     */
    public function destroy(string $alias): self {
        unset($this->sources[$alias]);
        if ($this->primaryStack === $alias) $this->setPrimaryStack(null);
        return $this;
    }
}
