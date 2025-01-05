<?php

    namespace DO\Main\SourceControl;

use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Class DataSourceContainer
 *
 * This class acts as a container for multiple sources of data. Each source is associated with a specific name, and it
 * consists of a configuration array detailing the data source properties.
 */
final class DataSourceContainer {
    protected array $sources = [];
    protected string|null $primaryStack = null;
    private array $config = [
        'model' => null, // null|string
        'table' => null, // null|string
        'primaryKey' => 'id',
        'select' => [],
        'where' => [],
        'order' => [
            ['column' => 'created_at', 'direction' => 'desc']
        ],
        'with' => [],
        'limit' => 100
    ];

    /**
     * Constructor for DataSourceContainer
     *
     * @param null $primaryStack
     */
    public function __construct($primaryStack = null) {
        $this->primaryStack = $primaryStack;
        return $this;
    }

    /**
     * Factory method to create a new instance of DataSourceContainer
     *
     * @return self
     */
    public static function make(): self {
        return new self();
    }

    /**
     * new instance
     *
     * @return DataSourceContainer
     */
    public static function implement(): self {
        return new self();
    }

    /**
     * Read configuration data and set up sources
     *
     * @param array $config
     *
     * @return $this
     */
    public function readConfig(array $config = []): self {
        if (array_key_exists('stacks', $config)) {
            $i = 0;
            foreach ($config['stacks'] as $name => $source) {
                if ($i === 0 && !isset($config['primaryStack']))
                    $this->setPrimaryStack($name);
                $this->addSource($name, $source);
                $i++;
            }
        }
        if (isset($config['primaryStack']))
            $this->setPrimaryStack($config['primaryStack']);
        return $this;
    }

    /**
     * Set the primary stack
     *
     * @param $primaryStack
     *
     * @return $this
     */
    public function setPrimaryStack($primaryStack): self {
        $this->primaryStack = $primaryStack;
        return $this;
    }

    /**
     * Add a data source with a specific name as the key
     *
     * @param string $name
     * @param array $source
     *
     * @return self
     */
    public function addSource(string $name, array $source = []): self {
        $this->sources[$name] = $this->config;
        foreach ($this->sources[$name] as $key => $value) {
            if (isset($source[$key])) {
                $this->sources[$name][$key] = $source[$key];
            }
        }
        return $this;
    }

    /**
     * Get the primary data source
     *
     * @return array
     */
    public function getPrimarySource(): array {
        return $this->getSource($this->whichPrimary());
    }

    /**
     * Get a specific source by name
     *
     * @param string|null $name
     *
     * @return mixed
     */
    public function getSource(string|null $name): mixed {
        if ($name === null)
            return $this->sources;
        return $this->sources[$name];
    }

    /**
     * Get the primary stack
     *
     * @return string|null
     */
    public function whichPrimary(): string|null {
        return $this->primaryStack;
    }

    /**
     * Remove a source by its name
     *
     * @param string $name
     *
     * @return self
     */
    public function removeSource(string $name): self {
        unset($this->sources[$name]);
        return $this;
    }

    /**
     * Get the limit for a specific stack
     *
     * @param string|null $stack
     *
     * @return int
     */
    public function whichLimit(string|null $stack = null): int {
        if ($stack !== null)
            return $this->sources[$stack]['limit'] ?? 25;
        return $this->sources[$this->whichPrimary()]['limit'] ?? 25;
    }

    /**
     * Get all sources
     *
     * @return array
     */
    public function getSources(): array {
        return $this->sources;
    }

    /**
     * Get a specific property of a source
     *
     * @param string $source
     * @param string $name
     *
     * @return Builder|array|null
     */
    public function getSourceProperty(string $source, string $name): Builder|array|null {
        if (isset($this->sources[$source])) {
            return $this->sources[$source][$name];
        } else return null;
    }
}
