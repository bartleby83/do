<?php

namespace DO\Main;

use Exception;
use Illuminate\Support\Collection;

/**
 * DataObject verwaltet {@link ListObject} und {@link FormObject} und stellt eine Schnittstelle zu den Views dar.
 * @config $config : Array|Collection config("DataObjects.".$objectID)
 *
 * @objects : array of objects
 */
final class DataObject extends DataObjectsCore {
    /** @var array<string, ListObject|FormObject> */
    private static array $objects = [];

    /**
     * Retrieve a specific form object by its ID.
     *
     * @param string $objectID The ID of the form object to retrieve.
     *
     * @return FormObject The form object with the specified ID.
     * @throws Exception If there is an error while retrieving the form object.
     */
    public static function form(string $objectID): FormObject {
        if (self::build('form', $objectID) instanceof FormObject) {
            return self::build('form', $objectID);
        } else throw new Exception("The form object with ID '$objectID' could not be found.");
    }

    /**
     * Instantiates an object of type {@see ListObject} or {@see FormObject}
     *
     * @param string|null $type
     * @param string|null $objectID
     * @param Collection<string, array<string, string|array<string, string>>>|array<string, array<string, string|array<string, string>>>|null $config
     *
     * @return ListObject|FormObject
     * @throws Exception
     */
    public static function build(string|null $type = null, string|null $objectID = null, array|Collection|null $config = []): ListObject|FormObject {
        if ($type === null || $objectID === null) {
            throw new Exception("Type and ObjectID must be set to build an object");
        }
        if (self::callObject($objectID) !== null) {
            return self::callObject($objectID);
        }
        // Die Standardkonfiguration aus der config() Funktion holen
        $defaultConfig = config('DataObjects.' . $type . '.' . $objectID) ?? [];
        // Die übergebene Konfiguration mit der Standardkonfiguration zusammenführen
        // Sicherstellen, dass die übergebene Konfiguration ($config) die Standardwerte übersteuert
        $combinedConfig = array_merge($defaultConfig, $config ?? []);
        // Konfiguration aufbauen
        $finalConfig = self::buildConfig($combinedConfig);
        return match ($type) {
            'list' => self::bindObject($objectID, ListObject::loadObject($objectID, $finalConfig)),
            'form' => self::bindObject($objectID, FormObject::loadObject($objectID, $finalConfig)),
            default => throw new Exception("Unknown object type: $type"),
        };
    }

    /**
     * Retrieves an object from the static objects array using an ID.
     *
     * @param string $id The ID associated with the object.
     *
     * @return FormObject|ListObject|null The object if found, NULL otherwise.
     */
    public static function callObject(string $id): FormObject|ListObject|null {
        return self::$objects[$id] ?? null;
    }

    /**
     * Method that builds a configuration Collection for DataObjects.
     *
     * Given an input configuration array, this method constructs a Collection that contains
     * a merged configuration set. This configuration merges the default values defined in
     * {@link DataObjectsConfigTemplate} with provided $config array values.
     *
     * 'fields' and 'menu' configurations are handled separately. If they are not present
     * in the provided $config array, they'll be initialized as empty arrays.
     *
     * @param array<string, array<string, string|array<string, string>>>|mixed|null $config Configuration values to build upon.
     *
     * @return Collection<string, mixed> A Collection object that represents the fully formed configuration.
     */
    public static function buildConfig(mixed $config = []): Collection {
        $defaults = DataObjectsConfigTemplate::configTemplate();
        /** @var array<string, array<string, array<string, mixed>>> $newConfig */
        $newConfig = [];
        foreach ($defaults as $type => $data) {
            if ($type === 'fields')
                continue;
            $newConfig[$type] = $data;
            foreach ($newConfig[$type] as $prop => $value) {
                if (is_array($config) && isset($config[$type][$prop]))
                    $newConfig[$type]->put($prop, $config[$type][$prop]);
            }
        }
        $newConfig['dataSource'] = is_array($config) && array_key_exists('dataSource', $config) ? $config['dataSource'] : [];
        $newConfig['menu'] = is_array($config) && array_key_exists('menu', $config) ? $config['menu'] : [];
        $newConfig['fields'] = is_array($config) && array_key_exists('fields', $config) ? $config['fields'] : [];
        return \collect($newConfig);
    }

    /**
     * Stores an object in the static objects array with an associated ID.
     *
     * @param string $id The ID with which the object will be associated.
     * @param FormObject|ListObject $object The object to be stored.
     *
     * @return FormObject|ListObject
     * @throws Exception
     */
    public static function bindObject(string $id, FormObject|ListObject $object): FormObject|ListObject {
        self::$objects[$id] = $object;
        return self::$objects[$id];
    }

    /**
     * Build a ListObject for the given object ID.
     *
     * @param string $objectID The ID of the object.
     *
     * @return ListObject The built ListObject.
     * @throws Exception
     */
    public static function list(string $objectID): ListObject {
        if (self::build('list', $objectID) instanceof ListObject)
            return self::build('list', $objectID);
        else throw new Exception("The form object with ID '$objectID' could not be found.");
    }

    /**
     * Retrieve all the objects from the $objects array.
     *
     * @return array<int, array<string, mixed>> An array containing all the objects.
     */
    public static function showObjects(): array {
        $result = [];
        foreach (self::$objects as $object => $value) {
            $result[] = ['objectID' => $object, 'objectName' => $value->getObjectProperty('objectName'), 'objectType' => $value->getObjectProperty('objectType'),];
        }
        return $result;
    }

    /**
     * destroys the object
     *
     * @param string $objectID the object ID
     */
    public static function destroy(string $objectID): void {
        self::unbindObject($objectID);
    }

    /**
     * Removes an object from the static objects array using an ID.
     *
     * @param string $id The ID of the object to be removed.
     *
     * @return void
     */
    private static function unbindObject(string $id): void {
        unset(self::$objects[$id]);
    }

    /**
     * Creates an Empty Object
     * @throws Exception
     */
    public static function create(string $type) {
        $objectID = uniqid();
        if (!in_array($type, ['list', 'form'])) {
            throw new Exception("Unknown object type: $type");
        }
        return self::build($type, $objectID);
    }
}
