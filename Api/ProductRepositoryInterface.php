<?php
/**
 * Copyright © Landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\Productlist\Api;

interface ProductRepositoryInterface
{
    /**
     * Retrieve newarrival Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNewarrivalProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve lastest Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLatestProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve best seller Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBestsellerProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve random Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRandomProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve top rated Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTopratedProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve special Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSpecialProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve deals Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDealsProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve most viewed Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMostViewedProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve featured Products matching the specified criteria.
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFeaturedProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve featured Products matching the specified criteria.
     * @param string $sourceKey (support there keys: latest, new_arrival, special, most_popular, best_seller, top_rated, random, featured, deals)
     * @param string $sellerUrl
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsBySource(
        $sourceKey = "latest",
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}

