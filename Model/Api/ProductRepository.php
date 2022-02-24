<?php
/**
 * Copyright © Landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\Productlist\Model\Api;

use Lofmp\Productlist\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Lofmp\Productlist\Model\ProductFactory as ProductlistProductFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ProductRepository implements ProductRepositoryInterface
{

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var ProductlistProductFactory
     */
    protected $productFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductInterfaceFactory
     */
    protected $dataProductFactory;

    /**
     * @var ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var Product[]
     */
    protected $instancesById = [];


    /**
     * @param ProductlistProductFactory $productFactory
     * @param ProductInterfaceFactory $dataProductFactory
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param int $cacheLimit [optional]
     */
    public function __construct(
        ProductlistProductFactory $productFactory,
        ProductInterfaceFactory $dataProductFactory,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        $cacheLimit = 1000
    ) {
        $this->productFactory = $productFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProductFactory = $dataProductFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->cacheLimit = (int)$cacheLimit;
    }
    /**
     * {@inheritdoc}
     */
    public function getNewarrivalProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("new_arrival", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("latest", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecialProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("special", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getMostViewedProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("most_popular", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getBestsellerProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("best_seller", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getTopratedProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("top_rated", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("random", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getFeaturedProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("featured", $sellerUrl, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getDealsProducts(
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getProductsBySource("deals", $sellerUrl, $criteria);
    }

     /**
     * {@inheritdoc}
     */
    public function getProductsBySource(
        $sourceKey = "latest",
        string $sellerUrl,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        $product = $this->productFactory->create();
        $sellerId = $product->getSellerIdByUrl($sellerUrl);
        if (!$sellerId) {
            throw new NoSuchEntityException(__('Not found any seller for url "%1".', $sellerUrl));
        }

        $config = [];
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $filters = $filterGroup->getFilters();
            if(is_array($filters)) {
                foreach ($filters as $filter) {
                    if("categories" == $filter->getField()){
                        $config['categories'] = explode(",",$filter->getValue());
                        break;
                    }
                }
            }
        }
        $collection = null;
        switch ($sourceKey) {
            case 'latest':
                $collection = $product->getLatestProducts($sellerId, $config);
            break;
            case 'new_arrival':
                $collection = $product->getNewarrivalProducts($sellerId, $config);
            break;
            case 'special':
                $collection = $product->getSpecialProducts($sellerId, $config);
            break;
            case 'most_popular':
                $collection = $product->getMostViewedProducts($sellerId, $config);
            break;
            case 'best_seller':
                $collection = $product->getBestsellerProducts($sellerId, $config);
            break;
            case 'top_rated':
                $collection = $product->getTopratedProducts($sellerId, $config);
            break;
            case 'random':
                $collection = $product->getRandomProducts($sellerId, $config);
            break;
            case 'featured':
                $collection = $product->getFeaturedProducts($sellerId, $config);
            break;
            case 'deals':
                $collection = $product->getDealsProducts($sellerId, $config);
            break;
            default:
            break;
        }
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        if ($collection) {
            $this->extensionAttributesJoinProcessor->process($collection);
            $this->collectionProcessor->process($searchCriteria, $collection);
            $collection->load();
            $searchResult->setItems($collection->getItems());
            $searchResult->setTotalCount($collection->getSize());

            foreach ($collection->getItems() as $product) {
                $this->cacheProduct(
                    $this->getCacheKey(
                        [
                            false,
                            $product->getStoreId()
                        ]
                    ),
                    $product
                );
            }
        } else {
            $searchResult->setItems([]);
            $searchResult->setTotalCount(0);
        }

        return $searchResult;
    }

    /**
     * Get key for cache
     *
     * @param mixed|array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param ProductInterface $product
     * @return void
     */
    private function cacheProduct($cacheKey, ProductInterface $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, (int)$offset, null, true);
            $this->instances = array_slice($this->instances, (int)$offset, null, true);
        }
    }

    /**
     * Saves product in the local cache by sku.
     *
     * @param Product $product
     * @param string $cacheKey
     * @return void
     */
    private function saveProductInLocalCache(Product $product, string $cacheKey): void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }

    /**
     * Converts SKU to lower case and trims.
     *
     * @param string $sku
     * @return string
     */
    private function prepareSku(string $sku): string
    {
        return mb_strtolower(trim($sku));
    }
}

