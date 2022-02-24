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

namespace Lofmp\Productlist\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;

	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

    /**
     * constructor helper data
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param TimezoneInterface $timezoneInterface
     * @param DateTime $dateTime
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		TimezoneInterface $timezoneInterface,
        DateTime $dateTime
		)
    {
		parent::__construct($context);
		$this->_storeManager = $storeManager;
		$this->_dateTime = $dateTime;
        $this->_timezoneInterface = $timezoneInterface;
	}

    /**
     * Get date time
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->_dateTime;
    }

    /**
     * Get timezone date time
     *
     * @param string $dateTime
     * @return string
     */
    public function getTimezoneDateTime($dateTime = "today")
    {
        if($dateTime === "today" || !$dateTime){
            $dateTime = $this->_dateTime->gmtDate();
        }

        $today = $this->_timezoneInterface
            ->date(
                new \DateTime($dateTime)
            )->format('Y-m-d H:i:s');
        return $today;
    }

    /**
     * Return module config value by key and store
     *
     * @param string $key
     * @param Store|int|string $store
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        return $this->scopeConfig->getValue(
            'lofmpproductlist/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $storeId
     * @return int|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled($storeId = 0)
    {
        return (int)$this->getConfig('general/enabled', $storeId);
    }

	/**
     * Check product is new
     *
     * @param  \Magento\Catalog\Model\Product|null $product
     * @return bool
     */
	public function checkProductIsNew($product = null)
    {
        if (!$product) {
            return false;
        }
		$fromDate = $product->getNewsFromDate();
		$toDate = $product->getNewsToDate();
		$isNew = false;
		$isNew = $this->isNewProduct($fromDate, $toDate);
        $todayDate = $this->getTimezoneDateTime();
		$today = strtotime($todayDate);

		if (!($fromDate && $toDate)) {
			return false;
		}

		if ($fromDate && $toDate) {
			$fromDate = strtotime($fromDate);
			$toDate = strtotime($toDate);
			if ($fromDate <= $today && $toDate >= $today) {
				$isNew = true;
			}
		} elseif ($fromDate && !$toDate) {
			$fromDate = strtotime($fromDate);
			if ($fromDate <= $today) {
				$isNew = true;
			}
		} elseif (!$fromDate && $toDate) {
			$toDate = strtotime($toDate);
			if ($toDate >= $today) {
				$isNew = true;
			}
		}
		return $isNew;
	}

    /**
     * Is new product
     *
     * @param string $created_date
     * @param int $num_days_new
     * @return bool
     */
	public function isNewProduct( $created_date, $num_days_new = 3)
    {
		$check = false;
        $todayDate = $this->getTimezoneDateTime();
		$startTimeStamp = strtotime($created_date);
		$endTimeStamp = strtotime($todayDate);

		$timeDiff = abs($endTimeStamp - $startTimeStamp);
        $numberDays = $timeDiff/86400;// 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if ($numberDays <= $num_days_new) {
        	$check = true;
        }

        return $check;
    }

    /**
     * sub string
     *
     * @param string $text
     * @param int $length
     * @param string $replacer
     * @param bool $is_striped
     * @return string
     */
    public function subString($text, $length = 100, $replacer = '...', $is_striped = true)
    {
    	$text = ($is_striped == true) ? strip_tags($text) : $text;
    	if (strlen($text) <= $length) {
    		return $text;
    	}
    	$text = substr($text, 0, $length);
    	$pos_space = strrpos($text, ' ');
    	return substr($text, 0, $pos_space) . $replacer;
    }
}
