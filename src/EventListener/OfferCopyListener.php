<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * Beim Duplizieren eines Angebots kopiert Contao Pläne, Slots, Ausnahmen und Absagen mit.
 * Ausnahmen und Absagen für einzelne Slots verweisen danach aber noch auf die Slots des
 * Originals. Dieser Listener ordnet sie den kopierten Slots zu (gleiche Reihenfolge).
 */
#[AsCallback(table: 'tl_tt_offer', target: 'config.oncopy')]
class OfferCopyListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(int|string $insertId, DataContainer $dc): void
    {
        $map = self::mapIds($this->slotIds((int) $dc->id), $this->slotIds((int) $insertId));

        if ([] === $map) {
            return;
        }

        foreach (['tl_tt_exception', 'tl_tt_cancellation'] as $table) {
            $rows = $this->connection->fetchAllAssociative("SELECT id, slots FROM $table WHERE pid = ? AND slots IS NOT NULL", [(int) $insertId]);

            foreach ($rows as $row) {
                $slots = self::remap((string) $row['slots'], $map);

                if (null !== $slots) {
                    $this->connection->update($table, ['slots' => $slots], ['id' => (int) $row['id']]);
                }
            }
        }
    }

    /**
     * Ordnet alte und neue Slot-IDs anhand ihrer Reihenfolge zu.
     *
     * @param list<int> $old
     * @param list<int> $new
     *
     * @return array<int, int>
     */
    public static function mapIds(array $old, array $new): array
    {
        if ([] === $old || \count($old) !== \count($new)) {
            return [];
        }

        return array_combine($old, $new);
    }

    /**
     * Ersetzt die Slot-IDs im serialisierten Array; null = nichts zu ändern.
     *
     * @param array<int, int> $map
     */
    public static function remap(string $serialized, array $map): ?string
    {
        $ids = @unserialize($serialized, ['allowed_classes' => false]);

        if (!\is_array($ids) || [] === $ids) {
            return null;
        }

        $mapped = array_map(static fn ($id): string => (string) ($map[(int) $id] ?? $id), $ids);

        return $mapped === array_map('strval', $ids) ? null : serialize(array_values($mapped));
    }

    /**
     * @return list<int>
     */
    private function slotIds(int $offerId): array
    {
        return array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT s.id FROM tl_tt_slot s INNER JOIN tl_tt_schedule p ON p.id = s.pid WHERE p.pid = ? ORDER BY p.id, s.id',
            [$offerId],
        ));
    }
}
