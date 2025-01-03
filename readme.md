# DataObjects für Formulare und Listen

## Inhalt

- [DataObjects:Übersicht](#DataObjects-Übersicht)
- [Konfiguration](#Konfiguration)
- [DataObject:Liste](#DataObject-Liste)
- [DataObject:Formular](#Dataobject-Formular)

## DataObjects-Übersicht

DataObjects sind spezialisierte Objekte, die zur Darstellung von Listen und Formularen verwendet werden. Sie dienen
als Container für die Daten, die in diesen Listen oder Formularen angezeigt werden sollen. Diese Daten werden
typischerweise aus einer Datenbank abgerufen und in ein DataObject eingelesen. Der Hauptvorteil von DataObjects
liegt in ihrer Fähigkeit, unterschiedliche Datenquellen zu integrieren und die abgerufenen Daten in ein einheitliches
Format zu transformieren. Dies ermöglicht eine konsistente und flexible Datenverwaltung sowie eine vereinfachte
Darstellung der Daten in der Benutzeroberfläche.

## Einleitung

Die Entwicklung moderner Webanwendungen erfordert flexible, skalierbare und wartbare Lösungen, um Daten effizient und
konsistent zu verwalten. Oft entstehen komplexe Strukturen durch unterschiedliche Datenquellen und Benutzeroberflächen,
die synchronisiert werden müssen.

DataObjects bieten eine Lösung für diese Herausforderung, indem sie eine klare Trennung zwischen Daten und deren
Darstellung in der Benutzeroberfläche schaffen. Durch die zentrale Konfiguration und die Möglichkeit, unterschiedliche
Datenquellen zu integrieren, ermöglichen sie eine konsistente Verwaltung und Anzeige von Daten, ohne Redundanz und
Unstimmigkeiten in der Anwendung.

Ein DataObject fungiert als Datencontainer, der nicht nur die strukturierten Daten selbst, sondern auch ihre
Konfiguration und Darstellung innerhalb einer Webanwendung übernimmt. Die wichtigsten Vorteile sind:

Skalierbarkeit: DataObjects sind leicht erweiterbar, um mit großen Datenmengen umzugehen.
Wiederverwendbarkeit: Einmal konfiguriert, können DataObjects in verschiedenen Teilen einer Anwendung verwendet werden.
Effizienz: Mit asynchronem Datenabruf und flexibler AJAX-Integration können Ladezeiten und Serverlast reduziert werden.
In dieser Dokumentation zeigen wir, wie DataObjects verwendet werden können, um einheitlich generierte Listen und
Formulare zu erstellen, die unabhängig von ihrer Datenquelle dynamisch in der Benutzeroberfläche angezeigt werden
können.

- **Trennung von Daten und Darstellung**
    - Durch die Trennung von Daten und Darstellung können Entwickler die Datenstruktur unabhängig von der
      Benutzeroberfläche verwalten. Dies führt zu einer besseren Wartbarkeit und Flexibilität des Codes.

- **Wiederverwendbarkeit**
    - DataObjects ermöglichen die Wiederverwendung derselben Datenstrukturen in verschiedenen Teilen der Anwendung. Dies
      spart Entwicklungszeit und reduziert Redundanz.

- **Modularität**
    - DataObjects fördern die Modularität, da einzelne Komponenten unabhängig voneinander entwickelt und getestet werden
      können. Dies erleichtert die Fehlerbehebung und das Hinzufügen neuer Funktionen.

- **Vereinheitlichung**
    - Durch die Nutzung von DataObjects wird ein einheitliches Format für verschiedene Datenquellen geschaffen. Dies
      erleichtert die Integration und Verwaltung von Daten aus unterschiedlichen Quellen.

- **Einfache Erweiterbarkeit**
    - Die Konfiguration und Anpassung von DataObjects ist einfach, was es ermöglicht, schnell neue Listen oder Formulare
      zu erstellen oder bestehende zu erweitern.

- **Asynchrone Datenverarbeitung**'
    - Durch die Verwendung von AJAX-Requests für das Nachladen von Daten und Konfigurationen wird die Benutzeroberfläche
      dynamisch und reaktionsfähig, ohne dass die gesamte Seite neu geladen werden muss.

- **Konsistenz**
    - DataObjects sorgen für konsistente Darstellungen von Daten, da sie zentral konfiguriert und verwaltet werden. Dies
      reduziert das Risiko von Inkonsistenzen in der Benutzeroberfläche.

- **Effizienz**
    - Die asynchrone Datenverarbeitung ermöglicht eine effizientere Nutzung der Serverressourcen und verbessert die
      Ladezeiten der Anwendung, da nur benötigte Daten nachgeladen werden.

- **Bessere Wartbarkeit**
    - Durch die klare Trennung von Datenlogik und Präsentationslogik wird der Code übersichtlicher und leichter zu
      warten. Änderungen an der Datenstruktur oder der Darstellung können unabhängig voneinander vorgenommen werden.

- **Skalierbarkeit**
    - DataObjects sind gut skalierbar, da sie es ermöglichen, mit großen Datenmengen und verschiedenen Datenquellen
      effizient umzugehen. Neue Datenquellen können problemlos integriert werden.

Diese Vorteile machen DataObjects zu einem leistungsfähigen Werkzeug für die Entwicklung moderner, dynamischer
Webanwendungen, die flexibel, erweiterbar und wartbar sind.

## ListObject

Die Darstellung des DataObjects und deren Daten erfolgt voneinander getrennt und wird mittels zusätzlichen
AJAX-Requests realisiert. Damit werden die Struktur und die Daten voneinander getrennt und auf der Client-Seite
zusammengeführt.

Grundprinzip im Aufruf (*vereinfachtes Beispiel*):

```php
<?php

namespace App\Http\Controller\DesiredController;

use DO\Main\DataObject;

class DesiredController extends Controller
{
    public function index()
    {
        DataObject::build('list', 'desiredListID');
        return view('desiredView', ['object' => DataObject::getDataObject('desiredListID')]);
    }
}
```

Innerhalb des Blades erfolgt die Ausgabe des DataObjects an beliebiger Stelle in einem Blade-Template:

```html

{{ $object->outputObject() }}

```

Das blade ```resources/views/DataObjects/table.blade.php``` wird automatisch eingebunden.

Sobald der angesteuerte Controller die View Response ausgeliefert hat, werden per AJAX zusätzliche Daten nachgeladen.

- Die Konfiguration des DataObjects wird per AJAX-Request abgerufen
- Die Daten des DataObjects werden per AJAX-Request abgerufen

# Konfiguration

Jedes DataObject kann eine Vorkonfiguration erhalten.

- **objectProperties**
    - *objectID*: Eindeutige ID des DataObjects
    - *objectName*: Label des DataObjects
    - *objectType*: Typ des DataObjects (list, form)
    - *configRequestUri*: URI für die Abfrage der Konfiguration
- **listProperties**
- **formProperties**
- **dataTableProperties**
- **fields**
- **menu**
    - Enthält Elemente, die weitere Verlinkungen erlauben

# DataObject-Liste

Für die Bereitstellung einer Tabelle inklusive Auslieferung der Daten erfolgt der Aufruf mittels

```php 
DataObject::build('list', 'objectID');
```

Sofern eine Konfiguration zu dieser objectID vorhanden ist, wird diese verarbeitet und DataObjects bildet das gewünschte
Tabellen-Objekt.

Um nach der Instanziierung mit dem DataObject weiterzuarbeiten, gibt es folgende Möglichkeiten:

- ```DataObject::build('list', 'objectID')``` gibt das Object selbst zurück
    - ```$variable = DataObject::build('list', 'objectID') ``` speichert das Object in einer variable
    - ```DataObject::callObject('objectID') ``` gibt ebenso das Object zurück, womit weitergearbeitet werden kann
    - ```getDataObject('objectID')``` hat den gleichen Effekt wie die vorigen Beispiele

Es besteht auch die Möglichkeit, den Aufruf des DataObjects statisch abzuwickeln:
```DataObject::list('objectID');```

Diverse Methoden stellen die Eigenschaften des Objects zur Verfügung:

- ```getDataObject('objectID')->getObjectProperty('propertyName');``` stellt objektbezogene Eigenschaften bereit
- ```getDataObject('objectID')->getListProperty('propertyName');``` stellt auf den Listenabruf bezogene Eigenschaften
  bereit
- ```getDataObject('objectID')->getDataTableProperty('propertyName');``` stellt auf die Listendarstellung bezogene
  Eigenschaften bereit

**Hinweis**: *der Übergebene Parameter ('propertyName') kann ein String oder leer/null sein. Sollten die Methoden ohne
Parameter aufgerufen werden, werden alle Properties dieser Kategorie ausgegeben.*

## Ausgabe einer Tabelle

Die Ausgabe einer Tabelle erfolgt mittels ```outputObject()```, z.B. ```getDataObject('objectID')->outputObject();```

# DataObject-Formular