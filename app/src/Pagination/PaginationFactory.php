<?php

declare(strict_types=1);

namespace App\Pagination;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @see https://symfonycasts.com/screencast/symfony-rest3/reusable-pagination-system
 */
class PaginationFactory
{
    private RouterInterface $router;

    private const DEFAULT_PAGE = 1;

    private const LINK_FIRST = 'first';
    private const LINK_LAST = 'last';
    private const LINK_NEXT = 'next';
    private const LINK_PREV = 'prev';
    private const LINK_SELF = 'self';


    private const MAX_PER_PAGE = 10;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createCollection(
        QueryBuilder $qb,
        Request $request,
        string $route,
        array $routeParams = []
    ): PaginatedCollection
    {
        $page = $request->query->get('page', self::DEFAULT_PAGE);
        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(self::MAX_PER_PAGE);
        $pagerfanta->setCurrentPage($page);
        $nearEarthObjects = [];

        //TODO: create method toArray or convert via (array)
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $nearEarthObjects[] = $result;
        }

        $paginatedCollection = new PaginatedCollection($nearEarthObjects, $pagerfanta->getNbResults());

        $createLinkUrl = function ($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                ['page' => $targetPage]
            ));
        };

        $paginatedCollection->addLink(self::LINK_SELF, $createLinkUrl($page));
        $paginatedCollection->addLink(self::LINK_FIRST, $createLinkUrl(self::DEFAULT_PAGE));
        $paginatedCollection->addLink(self::LINK_LAST, $createLinkUrl($pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink(self::LINK_NEXT, $createLinkUrl($pagerfanta->getNextPage()));
        }

        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink(self::LINK_PREV, $createLinkUrl($pagerfanta->getPreviousPage()));
        }

        return $paginatedCollection;
    }
}
