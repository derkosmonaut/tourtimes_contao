<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * Auswahl des Lesermoduls in der Angebotsliste: alle Detailmodule des Themes.
 */
#[AsCallback(table: 'tl_module', target: 'fields.tourtimes_readerModule.options')]
class ReaderModuleOptionsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<int, string>
     */
    public function __invoke(?DataContainer $dc = null): array
    {
        $themeId = (int) ($dc?->getCurrentRecord()['pid'] ?? 0);
        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, name FROM tl_module WHERE type = 'tourtimes_reader'".($themeId > 0 ? ' AND pid = ?' : '').' ORDER BY name',
            $themeId > 0 ? [$themeId] : [],
        );

        $options = [];

        foreach ($rows as $row) {
            $options[(int) $row['id']] = (string) $row['name'];
        }

        return $options;
    }
}
