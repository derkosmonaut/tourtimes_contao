<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * Auswahl der Slots bei Ausnahmen und Absagen: alle Slots aller Pläne des Angebots,
 * beschriftet mit Plan, Wochentagen und Uhrzeit (z. B. "Standard: Mo, Di, Mi 10:00").
 */
#[AsCallback(table: 'tl_tt_exception', target: 'fields.slots.options')]
#[AsCallback(table: 'tl_tt_cancellation', target: 'fields.slots.options')]
class SlotOptionsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<int, string>
     */
    public function __invoke(?DataContainer $dc = null): array
    {
        $offerId = (int) ($dc?->getCurrentRecord()['pid'] ?? 0);

        if ($offerId < 1) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT s.id, s.weekdays, s.starts, s.ends, s.note, p.title AS plan
               FROM tl_tt_slot s
         INNER JOIN tl_tt_schedule p ON p.id = s.pid
              WHERE p.pid = ?
           ORDER BY p.type, p.id, s.starts',
            [$offerId],
        );

        $names = $GLOBALS['TL_LANG']['tl_tt_slot']['weekday_names'] ?? [];
        $options = [];

        foreach ($rows as $row) {
            $days = array_map(
                static fn ($day): string => mb_substr((string) ($names[(int) $day] ?? $day), 0, 2),
                StringUtil::deserialize($row['weekdays'], true),
            );

            $label = \sprintf('%s: %s %s', $row['plan'], implode(', ', $days), $row['starts'].('' !== (string) $row['ends'] ? '–'.$row['ends'] : ''));

            if ('' !== (string) $row['note']) {
                $label .= ' ('.$row['note'].')';
            }

            $options[(int) $row['id']] = $label;
        }

        return $options;
    }
}
