<?php

    namespace DO\Main\SourceControl;

use Illuminate\Support\Collection;

/**
 * Class DataResultStacks
 * manages a stack of data results after a query has been executed.
 * this is useful for {@link DataObjectsCore} to manage multiple queries and results.
 *
 * @package DO\Main\SourceControl
 */
class DataResultStacks {
    /**
     * @var array
     */
    private array $dataResults;
    private string|null $primaryStack;

    /**
     * DataResultStacks constructor.
     *
     * @see self::implement method to create a new instance of the class.
     */
    private function __construct() {
        $this->dataResults = [];
        $this->primaryStack = null;
        return $this;
    }

    /**
     * Implement a new instance of the class.
     *
     * @return self
     */
    public static function implement(): self {
        return new self();
    }

    /**
     * Add a collection of results to the stack.
     *
     * @param Collection $results
     * @param string|null $stack
     *
     * @return self
     */
    public function add(Collection $results, string|null $stack = null): self {
        foreach ($results as $key => $result) {
            if (is_array($result)) {
                $results[$key] = array_merge($result, ['checkbox' => '', 'tools' => '']);
            }
            if (is_object($result)) {
                $result->checkbox = '';
                $result->tools = '';
            }
        }
        if ($stack === null) {
            $this->dataResults[] = $results;
        } else {
            $this->dataResults[$stack] = $results;
        }
        if ($stack !== null) {
            $this->primaryStack = $stack;
        }
        return $this;
    }

    /**
     * Get a specific stack of results or all stacks if no stack is specified.
     *
     * @param string|null $stack
     *
     * @return Collection|array
     */
    public function get(string $stack = null): Collection|array {
        if ($stack === null) {
            return $this->dataResults;
        } else {
            return $this->dataResults[$stack];
        }
    }

    /**
     * check has stacks of results
     *
     * @return bool
     */
    public function hasResults(): bool {
        return count($this->dataResults) > 0;
    }

    /**
     * Get the primary stack.
     *
     * @return string|null
     */
    public function whichPrimary(): string|null {
        return $this->primaryStack;
    }

    /**
     * Destroy a specific stack of results.
     *
     * @param string $stack
     */
    public function destroy(string $stack): void {
        unset($this->dataResults[$stack]);
    }
}
