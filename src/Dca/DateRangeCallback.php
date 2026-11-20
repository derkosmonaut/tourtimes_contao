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
    public function validateStop(mixed $value): mixed
    {
        if (null === $value || '' === $value) {
            return $value;
        }

        $startInput = (string) Input::post('start');

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
