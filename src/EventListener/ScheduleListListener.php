<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Image;
use Contao\StringUtil;

/**
 * Zeile in der Plan-Liste: Titel mit Art (Standard-/Saisonplan) und Icon.
 */
#[AsCallback(table: 'tl_tt_schedule', target: 'list.sorting.child_record')]
class ScheduleListListener
{
    private const ICON_SEASON = 'bundles/derkosmonauttourtimes/icons/sun.svg';
    private const ICON_DEFAULT = 'children.svg';

    /**
     * @param array<string, mixed> $row
     */
    public function __invoke(array $row): string
    {
        $type = 'season' === ($row['type'] ?? '') ? 'season' : 'default';
        $typeLabel = $GLOBALS['TL_LANG']['tl_tt_schedule']['types'][$type] ?? $type;

        return \sprintf(
            '<div class="tl_content_left">%s %s <span class="tl_gray">(%s)</span></div>',
            Image::getHtml('season' === $type ? self::ICON_SEASON : self::ICON_DEFAULT, '', 'style="vertical-align:text-bottom"'),
            StringUtil::specialchars((string) ($row['title'] ?? '')),
            StringUtil::specialchars((string) $typeLabel),
        );
    }
}
