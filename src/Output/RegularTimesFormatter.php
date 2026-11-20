<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Output;

use Derkosmonaut\TourTimesBundle\Resolver\Model\TimeSlot;

/**
 * Fasst die regelmäßigen Termine für die Ausgabe zusammen:
 * gleiche Uhrzeit an mehreren Tagen wird zu einer Zeile, z. B. "Mo–Mi 10:00".
 */
final class RegularTimesFormatter
{
    /**
     * @param list<TimeSlot>     $slots
     * @param array<int, string> $dayNames Kurznamen, 1 = Montag ... 7 = Sonntag
     *
     * @return list<array{days: string, weekdays: list<int>, starts: string, ends: string|null, note: string}>
     */
    public static function group(array $slots, array $dayNames): array
    {
        $groups = [];

        foreach ($slots as $slot) {
            $key = $slot->starts.'|'.($slot->ends ?? '').'|'.$slot->note;
            $groups[$key] ??= ['weekdays' => [], 'starts' => $slot->starts, 'ends' => $slot->ends, 'note' => $slot->note];
            $groups[$key]['weekdays'] = array_values(array_unique([...$groups[$key]['weekdays'], ...$slot->weekdays]));
        }

        $rows = [];

        foreach ($groups as $group) {
            sort($group['weekdays']);
            $rows[] = ['days' => self::formatDays($group['weekdays'], $dayNames), ...$group];
        }

        usort($rows, static fn (array $a, array $b): int => [$a['weekdays'][0] ?? 8, $a['starts']] <=> [$b['weekdays'][0] ?? 8, $b['starts']]);

        return $rows;
    }

    /**
     * Wochentage als lesbare Folge: Läufe ab drei Tagen werden zum Bereich ("Mo–Mi").
     *
     * @param list<int>          $weekdays sortiert
     * @param array<int, string> $dayNames
     */
    public static function formatDays(array $weekdays, array $dayNames): string
    {
        if ([1, 2, 3, 4, 5, 6, 7] === $weekdays) {
            return ($dayNames[1] ?? '1').'–'.($dayNames[7] ?? '7');
        }

        $runs = [];

        foreach ($weekdays as $day) {
            $last = array_key_last($runs);

            if (null !== $last && end($runs[$last]) === $day - 1) {
                $runs[$last][] = $day;
            } else {
                $runs[] = [$day];
            }
        }

        $parts = [];

        foreach ($runs as $run) {
            $names = array_map(static fn (int $d): string => $dayNames[$d] ?? (string) $d, $run);
            $parts[] = \count($run) >= 3 ? $names[0].'–'.end($names) : implode(', ', $names);
        }

        return implode(', ', $parts);
    }
}
