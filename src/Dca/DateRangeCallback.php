<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Dca;

use Contao\Config;
use Contao\Date;
use Contao\Input;

/**
 * save_callback für das Enddatum: es darf nicht vor dem Beginn liegen.
 *
 * Ein Ende vor dem Beginn würde bei jährlicher Wiederholung fast das ganze Jahr abdecken.
 */
class DateRangeCallback
{
    /**
     * "Endet nach X Terminen" braucht einen Beginn, ab dem gezählt wird.
     */
    public function requireStartForCount(mixed $value): mixed
    {
        if ((int) $value > 0 && '' === (string) Input::post('date_from')) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['tourtimes_count'] ?? 'Für eine Anzahl an Terminen bitte auch „Termine ab“ angeben.');
        }

        return $value;
    }

    public function validateStop(mixed $value): mixed
    {
        if (null === $value || '' === $value) {
            return $value;
        }

        // Angebot: date_until gegen date_from, sonst stop gegen start
        $startInput = (string) (Input::post('date_from') ?? Input::post('start'));

        if ('' === $startInput) {
            return $value;
        }

        try {
            $start = (new Date($startInput, Config::get('dateFormat')))->dayBegin;
        } catch (\Throwable) {
            return $value;
        }

        if ((int) $value < $start) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['tourtimes_range'] ?? 'Das Ende darf nicht vor dem Beginn liegen.');
        }

        return $value;
    }
}
