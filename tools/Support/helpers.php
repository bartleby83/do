<?php

use DO\Main\DataObject;

if (!function_exists('getDataObject')) {
    /**
     * @throws Exception
     */
    function getDataObject($objectID)
    {
        $formConfig = config('DataObjects.form.' . $objectID, null);
        $listConfig = config('DataObjects.list.' . $objectID, null);
        
        
        $data = null;
        
        // Prüfen, ob `objectType` in einer der Configs existiert
        if (is_array($formConfig) && isset($formConfig['objectProperties']['objectType'])) {
            $data = $formConfig;
        } elseif (is_array($listConfig) && isset($listConfig['objectProperties']['objectType'])) {
            $data = $listConfig;
        }
        
        // Weiterverarbeitung, falls `data` gesetzt wurde
        if ($data) {
            $objectType = $data['objectProperties']['objectType'];
            // Hier kannst du mit `$objectType` arbeiten
        } else {
            // Keine gültigen Daten gefunden
            throw new Exception("Kein gültiger objectType für ObjectID {$objectID} gefunden.");
        }
        
        return DataObject::build($objectType, $objectID);
    }
}