<?php

    namespace DO\Main\Elements\FieldSupport;

/**
 * Class FormFieldObject
 *
 * This class extends the base FieldObject specifically for form fields. As of now,
 * it inherits all properties, behavior and methods of its parent class without additional modifications.
 * It is instantiable and might be extended with form specific behavior and properties in the future.
 *
 * @package App\Generic\DataObjects
 *
 */
final class FormFieldObject extends FieldObject {
    /**
     * FormFieldObject constructor.
     *
     * Initializes the FormFieldObject by calling the parent constructor.
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Implement the FieldObject.
     *
     * Creates and returns a new instance of the FieldObject.
     *
     * @return self Returns the new instance of FieldObject
     */
    public static function implement(): self {
        return new self;
    }

    public function validatePassword($incomingValue): bool {
        //var_dump($incomingValue);
        $ruleSet = $this->getRuleSet();
        //var_dump($ruleSet);
//        foreach ($ruleSet as $rule => $ruleValue) {
//            //echo $rule . " " . $ruleValue . "\n";
//        }
        $score = 0;
        // Prüfen, ob null erlaubt ist und der Wert null ist
        //echo "Prüfe, ob Wert leer ist: ";
        if ($ruleSet['allowNull'] === false && $incomingValue === null) {
            return false;
        }
        //echo "Ok\n";
        // Mindestlänge prüfen
        //echo "Prüfe, ob eine bestimmte Länge erreicht wurde: ";
        if ($ruleSet['minLength'] !== null) {
            if (strlen($incomingValue) >= $ruleSet['minLength']) {
                //echo "Ok\n";
                $score++;
            } else {
                // echo "NOT OK\n";
                return false; // Mindestlänge nicht erfüllt
            }
        } //else echo "NOT REQUIRED\n";
        // Maximale Länge prüfen (optional, falls in den Regeln definiert)
        // echo "Prüfe, ob die maximale Länge erreicht oder überschritten wurde: ";
        if ($ruleSet['maxLength'] !== null) {
            if (strlen($incomingValue) <= $ruleSet['maxLength']) {
                // echo "Ok\n";
                $score++;
            }
        } //else echo "NOT REQUIRED\n";
        // Großbuchstaben prüfen
        //echo "Prüfe, ob Großbuchstaben eingesetzt wurden: ";
        if (isset($ruleSet['requireUpperCase']) && $ruleSet['requireUpperCase'] === true) {
            if (preg_match('/[A-Z]/', $incomingValue)) {
                // echo "Ok\n";
                $score++;
            } else {
                // echo "NOT OK\n";
                return false; // Kein Großbuchstabe vorhanden
            }
        } //else echo "NOT REQUIRED\n";
        // Kleinbuchstaben prüfen
        // echo "Prüfe, ob Kleinbuchstaben eingesetzt wurden: ";
        if (isset($ruleSet['requireLowerCase']) && $ruleSet['requireLowerCase'] === true) {
            if (preg_match('/[a-z]/', $incomingValue)) {
                // echo "Ok\n";
                $score++;
            } else {
                // echo "NOT OK\n";
                return false; // Kein Kleinbuchstabe vorhanden
            }
        } // else echo "NOT REQUIRED\n";
        // Zahlen prüfen
        // echo "Prüfe, ob Zahlen eingesetzt wurden: ";
        if (isset($ruleSet['requireNumber']) && $ruleSet['requireNumber'] === true) {
            if (preg_match('/\d/', $incomingValue)) {
                // echo "Ok\n";
                $score++;
            } else {
                // echo "NOT OK\n";
                return false; // Keine Zahl vorhanden
            }
        } // else echo "NOT REQUIRED\n";
        // Sonderzeichen prüfen
        // echo "Prüfe, ob Sonderzeichen eingesetzt wurden: ";
        if (isset($ruleSet['requireSpecialCharacters']) && $ruleSet['requireSpecialCharacters'] === true) {
            if (isset($ruleSet['definedSpecialCharacters'])) {
                // Prüfen gegen eine definierte Menge von Sonderzeichen
                $specialCharacters = preg_quote($ruleSet['definedSpecialCharacters'], '/');
                if (preg_match('/[' . $specialCharacters . ']/', $incomingValue)) {
                    // echo "Ok\n";
                    $score++;
                } else {
                    // echo "NOT OK\n";
                    return false; // Keine definierten Sonderzeichen vorhanden
                }
            } else {
                // Standard-Sonderzeichen überprüfen
                if (preg_match('/[@$!%*?&]/', $incomingValue)) {
                    // echo "Ok\n";
                    $score++;
                } else {
                    // echo "NOT OK\n";
                    return false; // Keine Standard-Sonderzeichen vorhanden
                }
            }
        } // else echo "NOT REQUIRED\n";
        // Wenn alle Prüfungen bestanden sind, ist das Passwort gültig
        // echo "Der Password-Score liegt bei " . $score . "\n";
        return $score >= 3; // Mindestanforderung für die Passwortstärke, kann angepasst werden
    }

    public function getRuleSet(): array {
        return [
            'allowNull' => $this->getFieldProperty('allowNull'),
            'defaultValue' => $this->getFieldProperty('defaultValue'),
            'minLength' => $this->getFieldProperty('minLength'),
            'minValue' => $this->getFieldProperty('minValue'),
            'maxLength' => $this->getFieldProperty('maxLength'),
            'maxValue' => $this->getFieldProperty('maxValue'),
            'requireUpperCase' => $this->getFieldProperty('requireUpperCase'),
            'requireLowerCase' => $this->getFieldProperty('requireLowerCase'),
            'requireNumber' => $this->getFieldProperty('requireNumber'),
            'requireSpecialCharacters' => $this->getFieldProperty('requireSpecialCharacters'),
            'definedSpecialCharacters' => preg_quote($this->getFieldProperty('definedSpecialCharacters') ?? '', '/'),
        ];
    }
}
