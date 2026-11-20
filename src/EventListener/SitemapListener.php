<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\EventListener;

use Contao\CoreBundle\Event\SitemapEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use DateTimeImmutable;
use Derkosmonaut\TourTimesBundle\Output\OfferViewBuilder;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Nimmt die Detailseiten aller veröffentlichten, nicht abgelaufenen Angebote in die Sitemap auf.
 *
 * Detailseiten sind die Weiterleitungsseiten (jumpTo) der Module "Angebotsliste" und
 * "Termine heute". Seiten, die unveröffentlicht, geschützt oder auf "noindex" gestellt
 * sind, werden übersprungen.
 */
#[AsEventListener]
class SitemapListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly OfferRepository $repository,
        private readonly OccurrenceResolver $resolver,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function __invoke(SitemapEvent $event): void
    {
        $this->framework->initialize();

        $pageIds = array_map('intval', $this->connection->fetchFirstColumn(
            "SELECT DISTINCT jumpTo FROM tl_module WHERE type IN ('tourtimes_list', 'tourtimes_today') AND jumpTo > 0",
        ));

        if ([] === $pageIds) {
            return;
        }

        $now = new DateTimeImmutable('now', OfferViewBuilder::timezone());
        $offers = array_filter(
            $this->repository->findPublished(),
            fn (array $offer): bool => !$this->resolver->hasEnded($this->repository->loadPlan($offer['id'], $offer['duration'], $now->getTimezone()), $now),
        );

        foreach ($pageIds as $pageId) {
            $page = PageModel::findWithDetails($pageId);

            if (null === $page || !$this->isIndexable($page, $event->getRootPageIds(), $now->getTimestamp())) {
                continue;
            }

            foreach ($offers as $offer) {
                $item = '' !== $offer['alias'] ? $offer['alias'] : (string) $offer['id'];

                try {
                    $event->addUrlToDefaultUrlSet($this->urlGenerator->generate($page, ['parameters' => '/'.$item], UrlGeneratorInterface::ABSOLUTE_URL));
                } catch (\Throwable) {
                    // Seite ohne erzeugbare URL (z. B. fehlende Domain) überspringen
                }
            }
        }
    }

    /**
     * @param array<int|string> $rootPageIds
     */
    private function isIndexable(PageModel $page, array $rootPageIds, int $time): bool
    {
        if (!\in_array((int) $page->rootId, array_map('intval', $rootPageIds), true) || 'regular' !== $page->type) {
            return false;
        }

        if (!$page->published || ($page->start && (int) $page->start > $time) || ($page->stop && (int) $page->stop <= $time)) {
            return false;
        }

        return !$page->protected && !str_contains((string) $page->robots, 'noindex');
    }
}
