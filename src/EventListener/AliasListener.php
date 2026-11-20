<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;

/**
 * Erzeugt den Alias eines Angebots aus dem Titel, wenn das Feld leer bleibt.
 */
#[AsCallback(table: 'tl_tt_offer', target: 'fields.alias.save')]
class AliasListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Slug $slug,
    ) {
    }

    public function __invoke(mixed $value, DataContainer $dc): string
    {
        $value = (string) $value;
        $id = (int) $dc->id;

        $exists = fn (string $alias): bool => false !== $this->connection->fetchOne(
            'SELECT id FROM tl_tt_offer WHERE alias = ? AND id != ?',
            [$alias, $id],
        );

        if ('' === $value) {
            $title = (string) (Input::post('title') ?? $dc->getCurrentRecord()['title'] ?? '');

            return $this->slug->generate($title, [], $exists);
        }

        if (preg_match('/^[1-9]\d*$/', $value)) {
            throw new \RuntimeException(\sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'] ?? 'Der Alias "%s" darf nicht nur aus Ziffern bestehen.', $value));
        }

        if ($exists($value)) {
            throw new \RuntimeException(\sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'] ?? 'Der Alias "%s" existiert bereits.', $value));
        }

        return $value;
    }
}
