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
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Helper_Data extends Plumrocket_Rewards_Helper_Main
{


    public function getCurrentCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }


    public function getCurrentCustomerId()
    {
        if ($customer = $this->getCurrentCustomer()) {
            return $customer->getId();
        }
        return false;
    }


    public function modulePlumrocketInvitationsEnabled()
    {
        if ((($module = Mage::getConfig()->getModuleConfig('Plumrocket_Invitations')) && ($module->is('active', 'true'))))
        {
            $result = Mage::getStoreConfig('invitations/general/enabled'); 
            return !empty($result);
        }
        return false;
    }


    public function moduleEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig('rewards/general/enabled', $store);
    }


    /* history page */
    public function getOrderLink($orderId, $text){
        $order = Mage::getModel('sales/order')->load($orderId);
        return '<a href="'.Mage::getUrl('sales/order/view/',array('order_id' => $orderId)).'" title="'.$this->__('View Order').'">'.$this->__($text).' #'.$order->getIncrementId().'</a>';
    }


    protected function _getOrderLink($orderId, $text){
        return $this->getOrderLink($orderId, $text);
    }


    public function getFrontendHistoryItemDescription($item){

        $objId = $item->getObjId();
        $pointsCount = $item->getPoints();
        $desc = '';

        switch ($item->getObjType())
        {
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER  :
                if ($item->getPoints() < 0){
                    $desc = $this->__('Reward Points redeemed on purchase (%1$s).', $this->_getOrderLink($objId, 'order'));
                } else {
                    $desc = $this->__('Reward Points refunded due to the %1$s cancellation.', $this->_getOrderLink($objId, 'order'));
                }
                break;
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_REFFERRAL :
                $desc = $this->__('Reward Points earned for inviting a friend who made a first purchase.');
                break;
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_REFFERRAL_1 :
                $desc = $this->__('Reward Points earned for inviting a friend.');
                break;
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_REGISTRATION :
                $desc = $this->__('Reward Points earned for registration.');
                break;
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_REVIEW :
                $review = Mage::getModel('review/review')->load($objId);
                switch($review->getEntityId()) {
                    case 1 :
                        $title = 'product';
                        $obj = Mage::getModel('catalog/'.$title)->load($review->getEntityPkValue());
                        $url = $obj->getProductUrl();
                        break;
                    case 3 :
                        $title = 'category';
                        $obj = Mage::getModel('catalog/'.$title)->load($review->getEntityPkValue());
                        $url = $obj->getUrl();
                        break;
                    case 2 :
                        $title = 'customer';
                        $url = '#';
                        break;
                }

                $desc = $this->__('Reward Points earned for <a href="%s" target="_blank">'.$title.' review</a>.', $url);
                break;
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_CREDITS :
                if ($item->getPoints() > 0){
                    $desc = $this->__('Reward Points earned for purchasing a product or service (%1$s).', $this->_getOrderLink($objId, 'order'));
                } else {
                    $desc = $this->__('Reward Points earned on an %1$s that was later cancelled or refunded are no longer valid.', $this->_getOrderLink($objId, 'order'));
                }
                break;
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_ITEM_REFUND :
                $desc = $this->__('Reward Points refunded due to the partial or full %1$s refund.', $this->_getOrderLink($objId, 'order'));
                break;
            case Plumrocket_Rewards_Model_History::OBJ_TYPE_EXPIRED :
                $desc = $this->__('Reward Points have been expired.');
                break;
            default:
                $desc = $item->getDescription();
                break;
        }

        return $desc;
    }
    /* end history page */


    public function getDiscountDescription($str, $substr)
    {
        if ($str) {
            $str .= ' & ';
        }

        return $str.$substr;
    }


    public function disableExtension()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $connection->delete($resource->getTableName('core/config_data'), array($connection->quoteInto('path IN (?)', array('rewards/general/enabled','rewards/notification/enabled'))));
        $config = Mage::getConfig();
        $config->reinit();
        Mage::app()->reinitStores();
    }


    public function getOrderPotentialPoints($orderIncrementId)
    {
        $order      = Mage::getModel('sales/order')->load($orderIncrementId, 'increment_id');
        $_config    = Mage::getModel('rewards/config')->setStoreId($order->getStoreId());

        if ($_config->getEarnPointsRate() && $_config->customerCanEarnPoints($order->getCustomerId())) {
            $totalAmount = $this->getOrderItemsBasePrice($order, true, true);
            return Mage::getModel('rewards/points')->convertPoints($totalAmount, false);
        }
        return null;
    }


    public function getCartPotentialPoints()
    {
        $_config    = Mage::getModel('rewards/config')->setStoreId();
        $_custmer   = Mage::getSingleton('customer/session')->getCustomer();
        if ($_config->getEarnPointsRate() && $_config->customerCanEarnPoints($_custmer)) {
            $totalAmount = $this->getCartItemsBasePrice(true, true);
            return Mage::getModel('rewards/points')->convertPoints($totalAmount, false);
        }
        return null;
    }


    public function getCartItemsBasePrice($withDiscount = false, $skipDPI = false)
    {
        $quote = Mage::getModel('checkout/cart')->getQuote();
        if (!$quote) {
            return 0;
        }

        $_config = Mage::getModel('rewards/config')->setStoreId($order->getStoreId());
        $incTax = $_config->getEarnPointsIncTax();

        $price = 0;
        foreach($quote->getAllVisibleItems() as $item){

            if ($skipDPI && $this->disabledOrderItemPurchasePoints($item)){
                continue;
            }

            //$itemsOriginalPrice += $item->getOriginalPrice() * $item->getQtyOrdered();
            if (!in_array($item->getProductType(), array('grouped'))) {
                $price += ($incTax ? $item->getBasePriceInclTax() : $item->getBasePrice()) * $item->getQty();
                if ($withDiscount){
                    $price -= $item->getBaseDiscountAmount();
                }
            } else {
                foreach ($item->getChildrenItems() as $child) {

                    if ($skipDPI && $this->disabledOrderItemPurchasePoints($child)){
                        continue;
                    }

                    $price += ($incTax ? $child->getBasePriceInclTax() : $child->getBasePrice()) * $child->getQty();
                    if ($withDiscount){
                        $price -= $child->getBaseDiscountAmount();
                    }
                }
            }

        }
        return $price;
    }


    public function getOrderItemsBasePrice($order, $withDiscount = false, $skipDPI = false)
    {
        $_config = Mage::getModel('rewards/config')->setStoreId($order->getStoreId());
        $incTax = $_config->getEarnPointsIncTax();

        $price = 0;
        foreach ($order->getAllVisibleItems() as $item) {

            if ($skipDPI && $this->disabledOrderItemPurchasePoints($item)) {
                continue;
            }

            //$itemsOriginalPrice += $item->getOriginalPrice() * $item->getQtyOrdered();
            if (!in_array($item->getProductType(), array('grouped'))) {
                $price += ($incTax ? $item->getBasePriceInclTax() : $item->getBasePrice()) * $item->getQtyOrdered();
                if ($withDiscount){
                    $price -= $item->getBaseDiscountAmount();
                }
            } else {
                foreach ($item->getChildrenItems() as $child) {

                    if ($skipDPI && $this->disabledOrderItemPurchasePoints($child)){
                        continue;
                    }

                    $price += ($incTax ? $child->getBasePriceInclTax() : $child->getBasePrice()) * $child->getQtyOrdered();
                    if ($withDiscount){
                        $price -= $child->getBaseDiscountAmount();
                    }
                }
            }

        }
        return $price;
    }


    public function getAdditionalPurchasePoints($order)
    {
        $points = 0;

        foreach ($order->getAllVisibleItems() as $item) {
            if ($this->disabledOrderItemPurchasePoints($item)) {
                continue;
            }

            if (!in_array($item->getProductType(), array('bundle', 'grouped'))) {
                $product = $this->_loadItemProduct($item);
                $_points = $product->getData('additional_purchase_points');
                if ((float)$product->getPrice()) {
                    $_points *= $item->getQtyOrdered();
                }
                $points += $_points;
            } else {
                foreach ($item->getChildrenItems() as $child) {

                    if ($this->disabledOrderItemPurchasePoints($child)){
                        continue;
                    }

                    $product = $this->_loadItemProduct($child);
                    $_points = $product->getData('additional_purchase_points');
                    if ((float)$product->getPrice()) {
                        $_points *= $item->getQtyOrdered();
                    }
                    $points += $_points;
                }
            }
        }

        return ($points > 0) ? $points : 0;
    }


    public function disabledOrderItemPurchasePoints($item)
    {
        $product = $this->_loadItemProduct($item);

        if ($product->getId() && !$product->getDisallowEarningPoints()){
            return false;
        }
        return true;
    }


    protected function _loadItemProduct($item)
    {
        $product = $item->getProduct();
        if (!$product || !$product->getIsPrLoaded()) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $product->setIsPrLoaded(true);
            $item->setProduct($product);
        }

        return $product;
    }


}
