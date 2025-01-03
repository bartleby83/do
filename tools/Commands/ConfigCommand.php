<?php

namespace DO\Tools\Commands;

use DO\Main\DataObjectsConfigTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 *
 */
class ConfigCommand extends Command {
    protected $signature = 'make:dataObjectConfig {type} {name}';
    protected $description = 'Erstellt eine neue DataObject Configuration File basierend auf Typ und Name.';

    public function handle(): void {
        $type = $this->argument('type');
        $name = $this->argument('name');
        // Pfad zur Datei, die erstellt werden soll
        $path = config_path("DataObjects/" . strtolower($type) . "/" . lcfirst($name) . ".php");
        // Überprüfen, ob die Datei bereits existiert
        if (file_exists($path)) {
            $this->info("DataObject-Konfigurationsdatei '{$name}' existiert bereits unter '{$path}'.");
            return;
        }
        // Abrufen der Konfigurationsdaten
        $configCollection = DataObjectsConfigTemplate::configTemplate();
        // Umwandlung der Collection in ein Array für die Dateischreibung
        $configArray = $this->collectionToArray($configCollection);
        // Anpassen der Konfigurationsdaten
        $configArray['objectProperties']['objectType'] = $type;
        $configArray['objectProperties']['objectID'] = $name;
        // Erstellen des Ordners, falls er nicht existiert
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        // Schreiben der Daten in eine PHP-Konfigurationsdatei mit angepasster Formatierung
        $configString = $this->arrayToPhpConfig($configArray);
        file_put_contents($path, $configString);
        $this->info("DataObject-Konfigurationsdatei '{$name}' wurde erfolgreich erstellt unter '{$path}'.");
    }

    /**
     * Hilfsmethode zum Umwandeln einer Collection (und aller untergeordneten Collections) in ein Array
     */
    private function collectionToArray(Collection $collection): array {
        return $collection->map(function ($item) {
            return $item instanceof Collection ? $this->collectionToArray($item) : $item;
        })->toArray();
    }

    /**
     * Hilfsmethode zum Konvertieren eines Arrays in eine formatierte PHP-Konfigurationsdatei
     */
    private function arrayToPhpConfig(array $array): string {
        // var_export zum Erstellen der PHP-Array-Syntax verwenden
        $export = var_export($array, true);
        // Einrückung der Ausgabe verdoppeln
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        // `array` durch `[]` ersetzen und ein Leerzeichen vor den `[]` einfügen
        $arrayString = preg_replace(
            ["/\s*array\s\(/", "/\)(,?)$/m", "/=>\s*\[\s*\]/", "/\[\s*\]/"],
            ["[", "]$1", "=> []", "[]"],
            $export
        );
        // Ein Leerzeichen vor und nach den `=>` Operator einfügen
        $arrayString = preg_replace("/=>\s*/", "=> ", $arrayString);
        return "<?php\nreturn " . $arrayString . ";\n";
    }
}
