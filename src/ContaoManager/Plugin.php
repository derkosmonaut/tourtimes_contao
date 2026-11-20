<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Derkosmonaut\TourTimesBundle\DerkosmonautTourTimesBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(DerkosmonautTourTimesBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
