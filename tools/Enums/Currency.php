<?php

namespace DO\Tools\Enums;

/**
 * Enumeration representing various currencies.
 */
enum Currency: string
{
    case EUR = 'eur';
    case USD = 'usd';
    case GBP = 'gbp';
    case CHF = 'chf';
    case JPY = 'jpy';
    case CAD = 'cad';
    case AUD = 'aud';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::EUR => 'Euro',
            self::USD => 'US-Dollar',
            self::GBP => 'Britisches Pfund',
            self::CHF => 'Schweizer Franken',
            self::JPY => 'Japanischer Yen',
            self::CAD => 'Kanadischer Dollar',
            self::AUD => 'Australischer Dollar',
        };
    }

    /**
     * @param string $label
     * @return self|null
     */
    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $currency) {
            if ($currency->label() === $label) {
                return $currency;
            }
        }
        return null; // Rückgabe null, wenn kein Treffer gefunden wird
    }
}