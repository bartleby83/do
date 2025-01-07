<?php

namespace DO\Tools\Enums;

/**
 * Enumeration representing various currencies.
 */
enum States: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case PENDING = 'pending';
    case CANCELED = 'canceled';

    /**
     * @return string
     */
    public function label(): string {
        return match ($this) {
            self::ACTIVE => 'Aktiv',
            self::INACTIVE => 'Inaktiv',
            self::SUCCEEDED => 'Erfolgreich ausgeführt',
            self::FAILED => 'Fehlgeschlagen',
            self::PENDING => 'Ausstehend',
            self::CANCELED => 'Storniert',
            default => 'Unbekannt'
        };
    }

    /**
     * @param string $label
     * @return self|null
     */
    public static function fromLabel(string $label): ?self {
        foreach (self::cases() as $states) {
            if ($states->label() === $label) {
                return $states;
            }
        }
        return null;
    }
}