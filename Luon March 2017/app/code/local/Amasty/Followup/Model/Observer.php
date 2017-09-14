<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Model_Observer 
{
    protected static $_onCustomerSaveAfterChecked = false;
    protected static $_onNewsletterSubscriberSaveAfterChecked = false;
    
    function clearCoupons(){
        $allCouponsCollection = Mage::getModel('salesrule/rule')->getCollection();
        
        $allCouponsCollection->join(

            array('history' => 'amfollowup/history'),
            'main_table.rule_id = history.sales_rule_id', 
            array('history.history_id')
        );
        
        $allCouponsCollection->getSelect()->where(
            'main_table.to_date < ?', date('Y-m-d', time())
        );
        
        foreach ($allCouponsCollection->getItems() as $aCoupon) {
            $aCoupon->delete();
        }
    }

    public function clearHistory()
    {
        $period = Mage::getStoreConfig('amfollowup/general/clean_up_period');
        if ($period > 0) {
            $historyCollection = Mage::getModel('amfollowup/history')->getCollection();

            $historyCollection->getSelect()->where(
                'main_table.finished_at < ?', date('Y-m-d', strtotime('-' . $period . ' day'))
            );

            foreach ($historyCollection->getItems() as $history) {
                $history->delete();
            }
        }
    }
    
    function refreshHistory(){
        Mage::getModel('amfollowup/schedule')->run(TRUE);
    }
    
    function onCustomerSaveAfter($observer){
        
        $customer = $observer->getCustomer();
        
        if (!self::$_onCustomerSaveAfterChecked) {
            
            $customer->setTargetCreatedAt($customer->getCreatedAt());
            
            Mage::getModel('amfollowup/schedule')->checkCustomerRules($customer, array(
                Amasty_Followup_Model_Rule::TYPE_CUSTOMER_GROUP,
                Amasty_Followup_Model_Rule::TYPE_CUSTOMER_NEW
            ));  
            self::$_onCustomerSaveAfterChecked = true;
        }
    }
    
    function onNewsletterSubscriberSaveAfter($observer){
        $subscriber = $observer->getSubscriber();
        if (!self::$_onNewsletterSubscriberSaveAfterChecked && !$subscriber->getChangeStatusAt()) {
            $customer = NULL;
            if (!$subscriber->getCustomerId()){
                
                $customer = Mage::getModel('customer/customer');
                $customer->addData(array(
                    "email" => $subscriber->getSubscriberEmail(),
                    "store_id" => $subscriber->getStoreId(),
                ));
                
            } else {
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            }
            
            Mage::getModel('amfollowup/schedule')->checkSubscribtionRules($subscriber, $customer, array(
                Amasty_Followup_Model_Rule::TYPE_CUSTOMER_SUBSCRIPTION
            ));  
            
            self::$_onNewsletterSubscriberSaveAfterChecked = true;
            $subscriber->setChangeStatusAt(date("Y-m-d H:i:s"));
            $subscriber->save();
        }
    }
    
    function onWishlistShare($observer){
        $wishlist = $observer->getWishlist();
        $customer = Mage::getModel('customer/customer')->load($wishlist->getCustomerId());
        
        Mage::getModel('amfollowup/schedule')->checkCustomerRules($customer, array(
            Amasty_Followup_Model_Rule::TYPE_CUSTOMER_WISHLIST_SHARED
        ));
    }
    
    function onSalesruleValidatorProcess($observer)
    {
        
        $ret = true;
        $ruleId = $observer->getEvent()->getRule()->getRuleId();

        $history = null;

        foreach(Mage::getModel("amfollowup/history")->getCollection()
                    ->addFieldToFilter("sales_rule_id", $ruleId) as $item){
            if ($item->getCouponCode() == $observer->getEvent()->getRule()->getCode()){
                $history = $item;
                break;
            }
        }
        
        if ($history && $history->getId()){
            
            $customerEmail = $history->getCustomerId() ?
                    $observer->getEvent()->getQuote()->getCustomer()->getEmail() :
                    $observer->getEvent()->getQuote()->getBillingAddress()->getEmail()
                ;

            $customerCoupon = Mage::getStoreConfig("amfollowup/general/customer_coupon");
            if ($customerCoupon && $customerEmail != $history->getEmail()) {
                $observer->getEvent()->getQuote()->setCouponCode("");
            }
        }
        return $ret;
    }

    function onCatalogProductSaveAfter($observer){

        $product = $observer->getData('data_object');
        if ($product instanceof Mage_Catalog_Model_Product){
            $oldData = $product->getOrigData();

            $oldSpecialPrice = isset($oldData['special_price']) ? $oldData['special_price'] : null;
            $newSpecialPrice = $product->getSpecialPrice();

            $oldSpecialFromDate = isset($oldData['special_from_date']) ? $oldData['special_from_date'] : null;
            $newSpecialFromDate = $product->getSpecialFromDate();

            $oldSpecialToDate = isset($oldData['special_to_date']) ? $oldData['special_to_date'] : null;
            $newSpecialToDate = $product->getSpecialToDate();

            $onSale = false;
            $backInstock = false;

            if (($oldSpecialPrice != $newSpecialPrice
                || $oldSpecialFromDate != $newSpecialFromDate
                || $oldSpecialToDate != $newSpecialToDate) && $newSpecialPrice != ''
            ) {
                $onSale = true;
            }

            $oldIsInStock = isset($oldData['quantity_and_stock_status'])
            && isset($oldData['quantity_and_stock_status']['is_in_stock'])
                ? $oldData['quantity_and_stock_status']['is_in_stock']
                : null;
                
            $qtyAndStock = array();
            $stockItem = $product->getStockItem();
            if (is_object($stockItem)) {
                $qtyAndStock = $stockItem->getData();
            }

            $newIsInStock = isset($qtyAndStock['is_in_stock']) ? $qtyAndStock['is_in_stock'] : null;

            if ($oldIsInStock != $newIsInStock && $newIsInStock == 1) {
                $backInstock = true;
            }

            if ($onSale || $backInstock) {
                $types = array();

                if ($onSale) {
                    $types[] = Amasty_Followup_Model_Rule::TYPE_CUSTOMER_WISHLIST_SALE;
                } elseif ($backInstock) {
                    $types[] = Amasty_Followup_Model_Rule::TYPE_CUSTOMER_WISHLIST_BACK_INSTOCK;
                }

                $collection = Mage::getResourceModel('wishlist/wishlist_collection');
                $collection->getSelect()->joinLeft(
                    array('wishlist_item' => $collection->getTable('wishlist/item')),
                    'main_table.wishlist_id = wishlist_item.wishlist_id',
                    array()
                );
                $collection->getSelect()->where('wishlist_item.product_id = ' . $product->getId());
                $collection->getSelect()->group('main_table.wishlist_id');
                
                if ($collection->getSize()) {
                    /** @var Mage_Wishlist_Model_Wishlist $model */
                    foreach ($collection as $model) {
                        $customer = Mage::getModel('customer/customer')->load($model->getCustomerId());
                        Mage::getModel('amfollowup/schedule')->checkCustomerRules($customer, $types);
                    }
                }
            }
        }
    }
}