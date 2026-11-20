<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Util;

/**
 * Prüft und vereinheitlicht Uhrzeiten im Format "HH:MM".
 */
final class TimeFormat
{
    /**
     * @param bool $allow24 "24:00" zulassen (nur sinnvoll als Ende eines Termins)
     *
     * @return string|null "HH:MM" oder null, wenn der Wert keine gültige Uhrzeit ist
     */
    public static function normalize(string $value, bool $allow24 = false): ?string
    {
        $value = trim(str_replace('.', ':', $value));

        if ('' === $value) {
            return null;
        }

        // "10" oder "9" als volle Stunde zulassen
        if (preg_match('/^\d{1,2}$/', $value)) {
            $value .= ':00';
        }

        if (!preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m)) {
            return null;
        }

        $hour = (int) $m[1];
        $minute = (int) $m[2];

        if ($minute > 59) {
            return null;
        }

        if ($hour > 23 && !($allow24 && 24 === $hour && 0 === $minute)) {
            return null;
        }

        return \sprintf('%02d:%02d', $hour, $minute);
    }
}
