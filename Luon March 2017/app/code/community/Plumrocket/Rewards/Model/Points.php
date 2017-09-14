<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Reward_Points
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Model_Points extends Mage_Core_Model_Abstract
{
	protected static $disabledProductForApply = null;

	protected $_config		= null;
	protected $_items		= null;
	protected $_isLoaded	= null;
	protected $_customer 	= null;

	protected $_storeId		= null;
	protected $_needNotify 	= true;


	public function _construct()
    {
    	if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
	        parent::_construct();
	        $this->_init('rewards/points');
	    }
    }

    public function needNotify($v)
    {
    	$this->_needNotify = $v;
    	return $this;
    }

    public function getConfig()
    {
		if (is_null($this->_config)){
			$this->_config = Mage::getModel('rewards/config')->setStoreId($this->getStoreId());
		}
		return $this->_config;
	}


	public function getCustomerId()
	{
		$k = 'customer_id';
		if (!$this->hasData($k)) {
			$this->setData($k, Mage::helper('rewards')->getCurrentCustomerId());
		}

		return $this->getData($k);
	}


	public function getCustomer()
	{
		if (is_null($this->_customer)){
			$this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
		}
		return $this->_customer;
	}


	public function getStoreId()
	{
		return $this->_storeId;
	}


	public function getByCustomer($customerId = null, $storeGroupId = null, $isStoreId = false)
	{
		if (is_null($customerId)){
			$customerId = $this->getCustomerId();
		} else {
			$this->setCustomerId($customerId);
		}

		if (!$customerId){
			return $this;
		}

		if ($customerId == $this->getData('current_customer_id')){
			return $this;
		}
		$this->setData('current_customer_id', $customerId);

		$storeGroupId = $this->_getGroupId($storeGroupId, $isStoreId);
		$this->setStoreGroupId($storeGroupId);

		$this->setAvailable(0)->setSpent(0)->setActivated(0);

		foreach($this->_getItems() as $item){
			$this
				->setAvailable($this->getAvailable() + $item->getAvailable())
				->setSpent($this->getSpent() + $item->getSpent())
				->setActivated($this->getActivated() + $item->getActivated());
		}

		$this->_isLoaded = true;
		return $this;
	}

	protected function _checkLoad()
	{
		if (!$this->_isLoaded){
			$this->getByCustomer();
		}
		return $this;
	}

	protected function _getGroupId($id, $isStoreId)
	{
		if (!$isStoreId){
			return $id;
		}

		$this->_storeId = $id;

		if (is_null($id)){
			return null;
		}

		foreach(Mage::app()->getWebsites() as $website){
			foreach($website->getGroups() as $group){
				foreach($group->getStores() as $store){
					if ($id == $store->getId()){
						return $group->getId();
					}
				}
			}
		}

		return null;
	}

	public function getStoreGroupsId()
	{
		if (!Mage::app()->isSingleStoreMode() && $this->getConfig()->isGlobalCredits()){
			foreach(Mage::app()->getWebsites() as $website){
				foreach($website->getGroups() as $group){
					$storeGroups[] = $group->getId();
				}
			}
		} else {
			$storeGroups = array(Mage::app()->getGroup()->getId());
		}
		return $storeGroups;
	}

	protected function _getItems()
	{
		if (is_null($this->_items)){

			$storeGroupId	= $this->getStoreGroupId();
			$customerId		= $this->getCustomerId();

			$rCode	= 'customer_reward_points_items_'.$customerId.'_'.$storeGroupId;
			if ($this->_items = Mage::registry($rCode)){
				return $this->_items;
			}

			if (is_null($storeGroupId)){
				$storeGroups = $this->getStoreGroupsId();
			} else {
				$storeGroups = array($storeGroupId);
			}

			$this->_items = $this->getCollection()
				->addFieldToFilter('customer_id', $this->getCustomerId())
				->addFieldToFilter('store_group_id',array('in' => $storeGroups))
				->setOrder('expire_at');

			Mage::register($rCode, $this->_items);
		}

		return $this->_items;
	}

	protected function _getNewItem()
	{
		$item = new self();
		return $item->setData($this->getData())
			->setAvailable(0)->setSpent(0)->setActivated(0);
	}



	public function getAccumulated(){
		return $this->getAvailable() + $this->getSpent();
	}

	public function getMinCountAvailble()
	{
		if (!$this->getCustomerId()){
			return 0;
		}

		$this->_checkLoad();
		return $this->getConfig()->getMinRedeemableCredit();
	}

	public function getMaxCountAvailble()
	{
		if (!$this->getCustomerId()){
			return 0;
		}

		$this->_checkLoad();
		$_config = $this->getConfig();

		$customerCount		= $this->getAvailable();
		$minCountAvailble	= $_config->getMinRedeemableCredit();
		if ($customerCount < $minCountAvailble || $customerCount == 0){
			$result = 0;
		} else {

			$maxCountAvailble	= $_config->getMaxRedeemableCredit();
			if (!$maxCountAvailble){
				$maxCountAvailble = 99999999;
			}

			$total = $this->convertPoints($this->_getQuoteMaxTotal(), true); //, false);

			if ($total > 0) {
				$result = min($maxCountAvailble, $customerCount, $total);
				if ($result < $minCountAvailble) {
					$result = $minCountAvailble;
				}
				if ($result < 0) {
					$result = 0;
				}
				$result;
			} else {
				$result = 0;
			}

		}

		if (!$result && $customerCount){
			$this->deactivate();
		}


		return $result;

	}

	public function recalculateActivated()
	{
		$this->_checkLoad();

		$total = $this->convertPoints($this->_getQuoteMaxTotal(), true);
		if ($total < $this->getActivated()){
			$this->activate($total);
		}
		return $this;
	}


	protected function _getQuoteMaxTotal()
	{
		$subtotal = 0;
		$fSubtotal = 0;

		$_quote = $this->getQuote() ? $this->getQuote() : Mage::getSingleton('checkout/cart')->getQuote();
		$_disabledProductsIds = $this->getDisabledProductForApply()->getAllIds();

		foreach($_quote->getAllVisibleItems() as $_item){

			if ($_item->isDeleted() || in_array($_item->getProductId(), $_disabledProductsIds)){
				continue;
			}
			$fSubtotal += $_item->getBasePriceInclTax() * $_item->getQty();
			$subtotal += $_item->getBasePriceInclTax() * $_item->getQty() - $_item->getBaseDiscountAmount();
		}

		if ($subtotal >= 0 && $fSubtotal > 0) {
			$subtotal += $this->getBaseRpdiscountAmount(false);
		}

		return $subtotal;
	}


	public function getJsData()
	{
		$this->_checkLoad();

		$_config = $this->getConfig();

		$_activated = $this->getActivatedCount();
		$data = array(
			'accumulated'			=> $this->getAccumulated(),
			'available'				=> $this->getAvailable(),
			'spent'					=> $this->getSpent(),
			'activated'				=> ($_activated > $_config->getMinRedeemableCredit()) ? $_activated : $_config->getMinRedeemableCredit(),

			'priceFormat'			=> Mage::app()->getLocale()->getJsPriceFormat(),

			'redeemPointsRate'		=> $_config->getRedeemPointsRate(),
			'earnPointsRate'		=> $_config->getEarnPointsRate(),

			'maxCountAvailble'		=> $this->getMaxCountAvailble(),
			'minCountAvailble'		=> $_config->getMinRedeemableCredit(),
		);
		return Mage::helper('core')->jsonEncode($data);
	}

	/*
	public function setActivatedCount($value){
		//Mage::getSingleton('core/session')->setRewardsActivatedPointsCount($value);
		$this->getByCustomer();
		$this->setActivated($value)->save();
		return $this;
	}

	public function addActivatedCount($value){
		$value = $this->getActivatedCount() + $value;
		if ($value < 0){
			$value = 0;
		}
		$this->setActivatedCount($value);
		return $this;
	}
	*/

	public function getBaseCurrency()
    {
        $currency = $this->getData('base_currency');
        if (is_null($currency)) {
        	$currencyCode = $this->getBaseCurrencyCode();
        	if (is_null($currencyCode)){
        		$currencyCode = Mage::app()->getStore($this->_storeId)->getBaseCurrencyCode();
        		$this->setBaseCurrencyCode($currencyCode);
        	}
            $currency = Mage::getModel('directory/currency')->load($currencyCode);
            $this->setData('base_currency', $currency);
        }
        return $currency;
    }

    public function getCurrentCurrency()
    {
        $currency = $this->getData('current_currency');

        if (is_null($currency)) {
        	$currencyCode = $this->getCurrentCurrencyCode();
        	if (is_null($currencyCode)){
        		$currencyCode = Mage::app()->getStore($this->_storeId)->getCurrentCurrencyCode();
        		$this->setCurrentCurrencyCode($currencyCode);
        	}
            $currency     = Mage::getModel('directory/currency')->load($currencyCode);
            $baseCurrency = $this->getBaseCurrency();

            if (! $baseCurrency->getRate($currency)) {
                $currency = $baseCurrency;
                $this->setCurrentCurrencyCode($baseCurrency->getCode());
            }

            $this->setData('current_currency', $currency);
        }

        return $currency;
    }

    public function currencyExchange($price, $baseToCurrent = null)
    {
    	if (!$price) {
    		return 0;
    	}

    	if (!is_null($baseToCurrent)){
	        if ($this->getCurrentCurrency() && $this->getBaseCurrency()) {

	        	$koef = $price / $this->getBaseCurrency()->convert($price, $this->getCurrentCurrency());

	        	if ($baseToCurrent === true){
	        		$price /= $koef;
	            } else {
	            	$price *= $koef;
	            }
	        }
        }

        return $price;
    }


	public function convertPoints($count, $convert = null, $exchange = null)
	{
		if (!is_null($convert)){
			if ($convert === true){
				$count *= $this->getConfig()->getRedeemPointsRate();
			} else if ($convert === false){
				$count *= $this->getConfig()->getEarnPointsRate();
			}
		}

		$count = $this->currencyExchange($count, $exchange);

		return (int)$count;
	}


	public function deconvertPoints($count, $convert = null, $exchange = null)
	{
		if (!is_null($convert)){
			if ($convert === true){
				if ($r = $this->getConfig()->getRedeemPointsRate()) {
					$count /= $r;
				} else {
					$count = 0;
				}
			} elseif ($convert === false) {
				if ($r = $this->getConfig()->getEarnPointsRate()) {
					$count /= $r;
				} else {
					$count = 0;
				}
			}
		}

		$count = $this->currencyExchange($count, $exchange);

		return (int)$count;
	}


	public function add($count, $description, $convert = null)
	{
		$this->_checkLoad();

		$count 		= $this->convertPoints($count, $convert);
		$history 	= $this->addToHistory($count, $description);


		$item = $this->_getNewItem();
		if ($ed = $history->getExpireAt()){
			$item->setExpireAt($ed);
		} else {
			$ed = '0000-00-00';
		}

		if ($count > 0) {
			foreach($this->_getItems() as $itm) {
				if ($itm->getAvailable() > 0) {
					$itm->setExpireAt($ed)->save();
				}
			}
		}

		$item->setAvailable($count)
			->setNotify($this->_needNotify)
			->save();

		$this->setAvailable($this->getAvailable() + $count)
			->_getItems()
			->addItem($item);

		return $this;
	}


	public function take($count, $description, $isSpend = true)
	{
		$this->_checkLoad();

		//$count = $this->convertPoints($count, $convert);

		$history = $this->addToHistory(-$count, $description);

		$items = $this->_getItems();
		foreach($items as $item){

			if ($count <= 0){
				break;
			}

			$maxCount = $item->getAvailable();
			if ($maxCount > 0)
			{
				if ($count < $maxCount){
					$value = $count;
					$count = 0;
				} else {
					$value = $maxCount;
					$count = $count - $maxCount;
				}

				$item->setAvailable($item->getAvailable() - $value);
				$this->setAvailable($this->getAvailable() - $value);
				if ($isSpend) {
					$item->setSpent($item->getSpent() + $value);
					$this->setSpent($this->getSpent() + $value);
				}

				$item->save();
			}
		}

		return $this;
	}


	public function canActivate($count)
	{
		if ($count > 0) {
			return ($this->getMinCountAvailble() <= $count && $this->getMaxCountAvailble() >= $count);
		}
		return false;
	}


	public function activate($count)
	{
		if ($count <= 0){
			$this->deactivate();
			return $this;
		}

		$this->_checkLoad();

		$this->deactivate();
		$items = $this->_getItems();

		foreach($items as $item){

			if ($count <= 0){
				break;
			}

			$this->setActivated($count);
			$maxCount = $item->getAvailable();
			if ($maxCount > 0)
			{
				if ($count < $maxCount){
					$item->setActivated($count);
					$count = 0;
				} else {
					$item->setActivated($maxCount);
					$count = $count - $maxCount;
				}
				$item->save();
			}
		}

		return $this;
	}


	public function deactivate()
	{
		$this->_checkLoad();

		$items = $this->_getItems();
		foreach($items as $item){
			if ($item->getActivated()){
				$item->setActivated(0)->save();
			}
		}
		$this->setActivated(0);

		return $this;
	}

	/*
	public function deactivate($count = null)
	{
		$this->_checkLoad();

		$items = $this->_getItems();
		if (is_null($count)){
			foreach($items as $item){
				$item->setActivated(0)->save();
			}
			$this->setActivated(0);
		} else {
		    foreach($items as $item){
				if ($count <= 0){
					break;
				}

				$maxCount = $item->getActivated();
				if ($maxCount)
				{
					if ($count < $maxCount){
						$item->setActivated($item->getActivated() - $count);
						$count = 0;
					} else {
						$item->setActivated($item->getActivated() - $maxCount);
						$count = $count - $maxCount;
					}
					$item->save();
				}
			}
			$this->setActivated($this->getActivated() - $count);
		}


		return $this;
	}
	*/


	public function getActivatedCount($minMaxCheck = true){

		if ($minMaxCheck) {

			$activated	= $this->_checkLoad()->getActivated();
			$max		= $this->getMaxCountAvailble();
			$min		= $this->getMinCountAvailble();

			if ($activated > $max){
				$this->activate($max);
			}

			if ($activated < $min){
				$this->deactivate();
			}
		}

		return $this->getActivated();
	}

	public function getBaseRpdiscountAmount($minMaxCheck = true){
		$activated = $this->_checkLoad()->getActivatedCount($minMaxCheck);
		return $activated / $this->getConfig()->getRedeemPointsRate();
	}




	public function addToHistory($count, $description)
	{
		$data = array(
			'customer_id' => $this->getCustomerId(),
			'store_group_id' => $this->getStoreGroupId(),
			'obj_id'      => $description['objId'],
			'obj_type'	  => $description['objType'],
			'points'	  => $count,
			'description' => $description['text'],
			'created_at'  => Mage::getModel('core/date')->date('Y-m-d H:i:s'),
			'expiration'  => isset($description['expiration']) ? $description['expiration'] : null

		);
		return Mage::getModel('rewards/history')->add($data);
	}

	public function getDisabledProductForApply()
	{
		if (is_null(self::$disabledProductForApply)) {
			self::$disabledProductForApply = Mage::getSingleton('catalog/product')->getCollection()
                ->addFieldToFilter(array(array('attribute' => 'disallow_apply_points', 'eq' => 1)))
                ->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
                ->addFieldToFilter(array(array('attribute' => 'status', 'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)))
                ->addAttributeToSelect('sku')
                ->addWebsiteFilter();

			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection(self::$disabledProductForApply);
		}
		return self::$disabledProductForApply;
	}

}
