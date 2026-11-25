<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * Ersetzt die Einstellung "Lesermodul" der Angebotsliste (bis 0.1.x) durch
 * "In der Detailansicht ausblenden": Wo ein Lesermodul eingetragen war, wird der Haken gesetzt.
 * Die alte Spalte entfernt anschließend das Datenbank-Update.
 */
class HideInReaderMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $columns = $this->columns();

        if (!isset($columns['tourtimes_readermodule'])) {
            return false;
        }

        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_module WHERE tourtimes_readerModule > 0') > 0;
    }

    public function run(): MigrationResult
    {
        if (!isset($this->columns()['tourtimes_hide_in_reader'])) {
            $this->connection->executeStatement("ALTER TABLE tl_module ADD tourtimes_hide_in_reader CHAR(1) DEFAULT '' NOT NULL");
        }

        $count = $this->connection->executeStatement(
            "UPDATE tl_module SET tourtimes_hide_in_reader = '1', tourtimes_readerModule = 0 WHERE tourtimes_readerModule > 0",
        );

        return $this->createResult(true, \sprintf('tourtimes: "Lesermodul" bei %d Modul(en) durch "In der Detailansicht ausblenden" ersetzt. Detailmodul bitte selbst auf der Seite einbinden.', $count));
    }

    /**
     * @return array<string, mixed>
     */
    private function columns(): array
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_module'])) {
            return [];
        }

        return array_change_key_case($schemaManager->listTableColumns('tl_module'), CASE_LOWER);
    }
}
