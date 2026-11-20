<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Dca;

use Derkosmonaut\TourTimesBundle\Util\TimeFormat;

/**
 * save_callback für die Uhrzeit-Felder: prüft die Eingabe und speichert sie als "HH:MM".
 */
class TimeInputCallback
{
    public function validateOpens(mixed $value): string
    {
        return $this->validate($value, false);
    }

    /**
     * Beim Ende eines Zeitfensters ist auch "24:00" erlaubt.
     */
    public function validateCloses(mixed $value): string
    {
        return $this->validate($value, true);
    }

    private function validate(mixed $value, bool $allow24): string
    {
        $value = trim((string) $value);

        // optionale Felder (zweites Fenster) dürfen leer bleiben
        if ('' === $value) {
            return '';
        }

        $time = TimeFormat::normalize($value, $allow24);

        if (null === $time) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['tourtimes_time'] ?? 'Bitte eine Uhrzeit im Format HH:MM angeben (z. B. 09:00).');
        }

        return $time;
    }
}
