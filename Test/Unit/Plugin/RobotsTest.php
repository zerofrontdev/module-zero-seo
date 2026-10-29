<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\Seo\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use Zero\Seo\Plugin\Robots;

/**
 * Which pages are NOINDEX: a list with anything but its page number in the
 * URL, and every search result; nothing else changes.
 */
class RobotsTest extends TestCase
{
    public function testACategoryAndItsPagesStayIndexed(): void
    {
        $this->assertSame('INDEX,FOLLOW', Robots::robots('INDEX,FOLLOW', 'catalog_category_view', []));
        $this->assertSame('INDEX,FOLLOW', Robots::robots('INDEX,FOLLOW', 'catalog_category_view', ['p']));
    }

    public function testAFilterSortOrViewModeMakesACategoryNoindex(): void
    {
        foreach ([['color'], ['color', 'size', 'climate'], ['product_list_order'], ['product_list_mode'], ['p', 'color']] as $keys) {
            $this->assertSame(Robots::NOINDEX, Robots::robots('INDEX,FOLLOW', 'catalog_category_view', $keys), implode(',', $keys));
        }
    }

    public function testABrandPageWithAFilterIsNoindex(): void
    {
        $this->assertSame('INDEX,FOLLOW', Robots::robots('INDEX,FOLLOW', 'zero_brands_brand_view', []));
        $this->assertSame(Robots::NOINDEX, Robots::robots('INDEX,FOLLOW', 'zero_brands_brand_view', ['price']));
    }

    public function testSearchResultsAreAlwaysNoindex(): void
    {
        $this->assertSame(Robots::NOINDEX, Robots::robots('INDEX,FOLLOW', 'catalogsearch_result_index', ['q']));
        $this->assertSame(Robots::NOINDEX, Robots::robots('INDEX,FOLLOW', 'catalogsearch_advanced_result', []));
    }

    public function testOtherPagesKeepTheirValue(): void
    {
        $this->assertSame('INDEX,FOLLOW', Robots::robots('INDEX,FOLLOW', 'catalog_product_view', ['utm_source']));
        $this->assertSame('NOINDEX,NOFOLLOW', Robots::robots('NOINDEX,NOFOLLOW', 'cms_page_view', []));
    }
}
