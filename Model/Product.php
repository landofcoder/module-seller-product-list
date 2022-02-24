<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lofmp_Productlist
 * @copyright  Copyright (c) 2022 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

declare(strict_types = 1);

namespace Lofmp\Productlist\Model;

use Lof\MarketPlace\Model\ResourceMode\Seller\CollectionFactory as SellerCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Product extends \Magento\Framework\DataObject
{
     /**
     * Block cache tag
     */
     const CACHE_CATEGORY_TAG = 'lofmp_productlist_categorytab';

    /**
     * Page cache tag
     */
    const CACHE_TAG = 'lofmp_productlist_tab';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_reportCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * \Magento\Framework\App\ResourceConnection
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration = null;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockFilter;

    /**
     * @var SellerCollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var int[]
     */
    protected $sellerIds = [];

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogProductVisibility
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                    $date
     * @param \Magento\Framework\App\ResourceConnection                      $resource
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State              $productState
     * @param \Magento\Catalog\Model\ProductFactory                          $productFactory
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface      $stockConfiguration
     * @param \Magento\CatalogInventory\Helper\Stock                         $stockFilter
     * @param SellerCollectionFactory $sellerCollectionFactory
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $productState,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        SellerCollectionFactory $sellerCollectionFactory,
        array $data = []
        ) {
        $this->_localeDate               = $localeDate;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_reportCollection         = $reportCollection;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_storeManager             = $storeManager;
        $this->date                      = $date;
        $this->_resource                 = $resource;
        $this->productState              = $productState;
        $this->productFactory            = $productFactory;
        $this->stockConfiguration        = $stockConfiguration;
        $this->stockFilter               = $stockFilter;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        parent::__construct($data);
    }

    /**
     * New arrival product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getNewarrivalProducts($sellerId, $config = [])
    {
        $todayStartOfDayDate = $this->_localeDate->date()
            ->setTime(0, 0)
            ->format('Y-m-d H:i:s');

        $todayEndOfDayDate = $this->_localeDate->date()
            ->setTime(23, 59, 59)
            ->format('Y-m-d H:i:s');

        //$product = $this->productFactory->create();

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories']) && $config['categories']) {
            if (!is_array($config['categories'])) {
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
                ->addAttributeToSelect('*')
                ->addStoreFilter()
                ->addAttributeToFilter('seller_id', $sellerId)
                ->addAttributeToFilter(
                    'news_from_date',
                    [
                    'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                    ],
                    'left'
                )
                ->addAttributeToFilter(
                    'news_to_date',
                    [
                    'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                    ],
                    'left'
                )
                ->addAttributeToFilter(
                    [
                    ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                    ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                    ]
                )
                ->addAttributeToSort(
                'news_from_date',
                'desc'
                );

        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }

        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }

        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Latest product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getLatestProducts($sellerId, $config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories']) && $config['categories']) {
            if (!is_array($config['categories'])) {
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter("seller_id", $sellerId)
                ->addStoreFilter();

        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }

        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Best seller product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getBestsellerProducts($sellerId, $config = [])
    {
        $storeId = $this->_storeManager->getStore(true)->getId();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories']) && $config['categories']) {
            if (!is_array($config['categories'])) {
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter("seller_id", $sellerId)
            ->addStoreFilter()
            ->joinField(
                'qty_ordered',
                $this->_resource->getTableName('sales_bestsellers_aggregated_monthly'),
                'qty_ordered',
                'product_id=entity_id',
                'at_qty_ordered.store_id=' . (int)$storeId,
                'at_qty_ordered.qty_ordered > 0',
                'left'
            );
        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }

        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Random product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getRandomProducts($sellerId, $config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories']) && $config['categories']) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter("seller_id", $sellerId)
            ->addStoreFilter();

        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->group("e.entity_id");
        $collection->getSelect()->order('rand()');
        return $collection;
    }

    /**
     * Top rated product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getTopratedProducts($sellerId, $config = [])
    {
        $storeId = $this->_storeManager->getStore(true)->getId();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories']) && $config['categories']) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter("seller_id", $sellerId)
            ->addStoreFilter()
            ->joinField(
                'ves_review',
                $this->_resource->getTableName('review_entity_summary'),
                'reviews_count',
                'entity_pk_value=entity_id',
                'at_ves_review.store_id=' . (int)$storeId,
                'ves_review > 0',
                'left'
            );
        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->group("e.entity_id");
        $collection->getSelect()->order('ves_review DESC');
        return $collection;
    }

    /**
     * Special product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getSpecialProducts($sellerId, $config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories']) && $config['categories']) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter("seller_id", $sellerId)
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addTaxPercents()
            ->addFinalPrice();
        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->group("e.entity_id");
        $collection->getSelect()->order("e.entity_id DESC")->where('price_index.final_price < price_index.price');
        return $collection;
    }

    /**
     * Speical product collection
     *
     * @param int $sellerId
     * @param mixed|array
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getDealsProducts($sellerId, $config = [])
    {
        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $product = $this->productFactory->create();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $product->getResourceCollection();
        if (isset($config['categories']) && $config['categories']) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
                ->addAttributeToSelect('*');

        $collection->addStoreFilter()->addAttributeToFilter(
            'special_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'special_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter([
            ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
            ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
        ])
        ->addAttributeToFilter("seller_id", $sellerId)
        ->addMinimalPrice()
        ->addUrlRewrite()
        ->addTaxPercents()
        ->addFinalPrice();

        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->order("e.entity_id DESC")->where('price_index.final_price < price_index.price');
        return $collection;
    }

    /**
     * Most viewed product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getMostViewedProducts($sellerId, $config = [])
    {
        /** @var $collection \Magento\Reports\Model\ResourceModel\Product\CollectionFactory */
        $collection = $this->_reportCollection->create()->addAttributeToSelect('*')->addViewsCount();
        if (isset($config['categories']) && $config['categories']) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter("seller_id", $sellerId)
                ->addStoreFilter();
        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Featured product collection
     *
     * @param int $sellerId
     * @param mixed|array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getFeaturedProducts($sellerId, $config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories']) && $config['categories']) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id',
                    'product_id = entity_id',
                    'category_id in (' . implode(",", $config['categories']) . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    array(
                        array( 'attribute'=> 'featured', 'eq' => '1')
                    )
                )
                ->addAttributeToFilter("seller_id", $sellerId)
                ->addStoreFilter();

        if (isset($config['pagesize']) && $config['pagesize']) {
            $collection->setPageSize((int)$config['pagesize']);
        }
        if (isset($config['curpage']) && $config['curpage']) {
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Get product by source code
     * @param string $sourceKey (support there keys: latest, new_arrival, special, most_popular, best_seller, top_rated, random, featured, deals)
     * @param int $sellerId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection|null
     */
    public function getProductBySource($sourceKey, $sellerId, $config = [])
    {
        $collection = null;
        switch ($sourceKey) {
            case 'latest':
                $collection = $this->getLatestProducts($sellerId, $config);
            break;
            case 'new_arrival':
                $collection = $this->getNewarrivalProducts($config);
            break;
            case 'special':
                $collection = $this->getSpecialProducts($config);
            break;
            case 'most_popular':
                $collection = $this->getMostViewedProducts($config);
            break;
            case 'best_seller':
                $collection = $this->getBestsellerProducts($config);
            break;
            case 'top_rated':
                $collection = $this->getTopratedProducts($config);
            break;
            case 'random':
                $collection = $this->getRandomProducts($config);
            break;
            case 'featured':
                $collection = $this->getFeaturedProducts($config);
            break;
            case 'deals':
                $collection = $this->getDealsProducts($config);
            break;
            default:
            break;
        }

        if (!$this->stockConfiguration->isShowOutOfStock()) {
            $this->stockFilter->addInStockFilterToCollection($collection);
        }
        return $collection;
    }

    /**
     * Get seller id by url
     *
     * @param string $url
     * @return int
     */
    private function getSellerIdByUrl(string $url): int
    {
        if (!isset($this->sellerIds[$url])) {
            $seller = $this->sellerCollectionFactory->create()
                        ->addFieldToFilter('url_key', ['eq' => $url])
                        ->getFirstItem();
            $this->sellerIds[$url] = ($seller && $seller->getId()) ? $seller->getId() : 0;
        }
        return $this->sellerIds[$url];
    }

}
