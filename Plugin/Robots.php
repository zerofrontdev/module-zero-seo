<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\Seo\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * NOINDEX,FOLLOW on the pages a product list can turn into without end: a
 * category or brand page with a filter, a sort order, a page size or a view
 * mode in its URL, and every search result. Search engines still follow
 * their links to the products, but don't index thousands of copies of one
 * category (?color=…&size=…&climate=…). The category itself and its pages
 * (?p=2) stay indexed. Robots.txt (etc/config.xml) keeps crawlers off those
 * URLs in the first place; this is for those that arrive anyway.
 */
class Robots
{
    public const NOINDEX = 'NOINDEX,FOLLOW';

    /** Product lists: indexed only as themselves and their pages. */
    private const LISTS = ['catalog_category_view', 'zero_brands_brand_view'];

    /** Never indexed: the store's own search results. */
    private const SEARCH = ['catalogsearch_result_index', 'catalogsearch_advanced_result'];

    /** Query parameters that keep a list page what it is. */
    private const KEEP = ['p'];

    public function __construct(
        private readonly Http $request,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @see Config::getRobots()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetRobots(Config $subject, $result)
    {
        if (!$this->scopeConfig->isSetFlag('zero_seo/general/noindex_variants', ScopeInterface::SCOPE_STORE)) {
            return $result;
        }

        return self::robots(
            (string) $result,
            (string) $this->request->getFullActionName(),
            array_keys((array) $this->request->getQuery()->toArray())
        );
    }

    /**
     * @param string[] $queryKeys the names in the URL's query string
     */
    public static function robots(string $robots, string $action, array $queryKeys): string
    {
        if (in_array($action, self::SEARCH, true)) {
            return self::NOINDEX;
        }
        if (in_array($action, self::LISTS, true) && array_diff($queryKeys, self::KEEP) !== []) {
            return self::NOINDEX;
        }

        return $robots;
    }
}
