<?php

    namespace DO\Main\SourceControl;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Class ModelFactory
 *
 * Factory class to create instances of models.
 */
class ModelFactory {
    /**
     * Creates an instance of a model.
     *
     * @param string $modelName The name of the model to create.
     * @param array $config Optional configuration for the model.
     * @return Builder Returns an instance of the model.
     * @throws Exception If the model does not exist.
     */
    public static function make(string $modelName, array $config = []): Builder {
        $modelClass = "App\\" . $modelName;
        if (!class_exists($modelClass)) {
            $modelClass = "App\\Models\\" . $modelName;
            if (!class_exists($modelClass)) throw new Exception("Model '" . $modelName . "' existiert nicht.");
        }
        $model = new $modelClass;
        if ($config['table'] !== null) {
            $model->setTable($config['table']);
        }
        return $model;
    }
}
