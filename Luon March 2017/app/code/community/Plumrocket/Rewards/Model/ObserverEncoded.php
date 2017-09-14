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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Model_ObserverEncoded
{
    protected $_isInvitation = false;

    public function salesOrderPlaceBefore($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ( (float)$order->getBaseRpdiscountAmount() && ($customerId = $order->getCustomerId()) ) {

            $points = Mage::getModel('rewards/points')->getByCustomer($customerId, $order->getStoreId(), true)
                ->setBaseCurrencyCode($order->getBaseCurrencyCode())
                ->setCurrentCurrencyCode($order->getOrderCurrencyCode());

            $rpDiscountPoints = $points->convertPoints($order->getBaseRpdiscountAmount(), true);

            $desc = array(
                'objId' => $order->getId(),
                'objType' => Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER,
                'text'  => '',
            );
            $points->take($rpDiscountPoints, $desc)->deactivate();
        }
    }

    public function orderCancelAfter($observer)
    {
        $order  = $observer->getEvent()->getOrder();
        $points = Mage::getModel('rewards/points')->getByCustomer($order->getCustomerId(),  $order->getStoreId(), true);

        if ((float)$order->getBaseRpdiscountAmount()) {

            $HISTORY_TYPE   = Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER;
            $item           = Mage::getModel('rewards/history')->getItem($order->getId(), $HISTORY_TYPE);

            if ($item->getId()){
                $hist = Mage::getModel('rewards/history')->getItem($order->getId(), $HISTORY_TYPE, 1);
                if (!$hist->getId()){
                    $desc = array(
                        'objId' => $order->getId(),
                        'objType' => $HISTORY_TYPE,
                        'text'  => '',
                    );
                    $points->add( -$item->getPoints(), $desc);
                }
            }
        }

        $HISTORY_TYPE   = Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_CREDITS;
        $item           = Mage::getModel('rewards/history')->getItem($order->getId(), $HISTORY_TYPE, 1);
        if ($item->getId()){
            $desc = array(
                'objId' => $order->getId(),
                'objType' => $HISTORY_TYPE,
                'text'  => '',
            );
            $points->take($item->getPoints(), $desc)->deactivate();
        }
    }

    public function salesOrderInvoiceRegister($observer)
    {
        $order      = $observer->getEvent()->getOrder();
        $_config    = Mage::getModel('rewards/config')->setStoreId($order->getStoreId());
        $_helper    = Mage::helper('rewards');
        $customerId = $order->getCustomerId();

        if (!$_config->customerCanEarnPoints($customerId)) {
            return;
        }

        $HISTORY_TYPE = Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_CREDITS;
        if (Mage::getModel('rewards/history')->getItem($order->getId(), $HISTORY_TYPE)->getId()){
            return;
        }

        $additionalPurchasePoints = $_helper->getAdditionalPurchasePoints($order);
        $totalAmount = $_helper->getOrderItemsBasePrice($order, true, true);
        $epRate = $_config->getEarnPointsRate();

        if ($additionalPurchasePoints || $totalAmount && $epRate) {

            $points = Mage::getModel('rewards/points')->getByCustomer($customerId, $order->getStoreId(), true)
                ->setBaseCurrencyCode($order->getBaseCurrencyCode())
                ->setCurrentCurrencyCode($order->getOrderCurrencyCode());

            $desc = array(
                'objId' => $order->getId(),
                'objType' => $HISTORY_TYPE,
                'text'  => '',
            );

            if ($additionalPurchasePoints) {
                if (!$epRate) {
                    $points->add($additionalPurchasePoints, $desc);
                    return;
                } else {
                    $totalAmount += $points->deconvertPoints($additionalPurchasePoints, false);
                }
            }

            $points->add($totalAmount, $desc, false);
        }

        return;
    }


    public function salesOrderCreditmemoRefund($observer)
    {
        $_helper    = Mage::helper('rewards');
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order      = $creditmemo->getOrder();
        $points     = Mage::getModel('rewards/points')->getByCustomer($order->getCustomerId(), $order->getStoreId(), true);

        if ((float)$order->getBaseRpdiscountAmount()) {

            $koef = $order->getBaseRpdiscountAmount() / $order->getBaseDiscountAmount();
            $rpDiscountRefunded = abs($creditmemo->getBaseDiscountAmount() * $koef);

            if ($rpDiscountRefunded > 0){
                $desc = array(
                    'objId' => $order->getId(),
                    'objType' => Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_ITEM_REFUND,
                    'text'  => '',
                );
                $points->add($rpDiscountRefunded, $desc, true);
            }
        }


        $HISTORY_TYPE   = Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_CREDITS;
        $item           = Mage::getModel('rewards/history')->getItem($order->getId(), $HISTORY_TYPE, 1);
        if ($item->getId()){
            $desc = array(
                'objId' => $order->getId(),
                'objType' => $HISTORY_TYPE,
                'text'  => '',
            );

            $_skipCreditmemoSubtotal = 0;
            foreach ($creditmemo->getAllItems() as $cItem) {
                if ($cItem->getQty() > 0) {
                    if ($_helper->disabledOrderItemPurchasePoints($cItem)){
                        $_skipCreditmemoSubtotal += $cItem->getPrice() * $cItem->getQty();
                    }
                }
            }

            $_skipOrderSubtotal = 0;
            foreach ($order->getAllVisibleItems() as $oItem) {
                if ($_helper->disabledOrderItemPurchasePoints($oItem)){
                    $_skipOrderSubtotal += $oItem->getOriginalPrice() * $oItem->getQtyInvoiced();
                }
            }


            $cmSubtotal = (int)($creditmemo->getSubtotal() + $creditmemo->getDiscountAmount() - $_skipCreditmemoSubtotal);
            $oSubstotal = (int)($order->getSubtotal() + $order->getDiscountAmount() - $_skipOrderSubtotal);
            if ( $cmSubtotal > 0 && $oSubstotal > 0){
                $koef = $cmSubtotal / $oSubstotal;
                $points->take((int)($item->getPoints() * $koef), $desc)->deactivate();
            }
        }
    }


    public function invitationsInviteeCustomerFirstOrder($observer)
    {
        $order      = $observer->getEvent()->getOrder();
        $_config    = Mage::getModel('rewards/config')->setStoreId($order->getStoreId());

        $modelCustomer  = Mage::getModel('customer/customer');
        $data = $observer->getData();

        if (
            !$_config->getInviteAwardReason()
            && ($pointsCount = $_config->getInviteCredit())
            && ($inviteeCustomer = $modelCustomer->load($data['invitee_customer_id']))
            && ($inviteeCustomerId = $inviteeCustomer->getId())
            && ($customer = $modelCustomer->load($data['customer_id']))
            && ($customerId = $customer->getId())
        ) {
            $desc = array(
                'objId' => $inviteeCustomerId,
                'objType' => Plumrocket_Rewards_Model_History::OBJ_TYPE_REFFERRAL,
                'text'  => '',
            );

            Mage::getModel('rewards/points')->getByCustomer($customerId, $order->getStoreId(), true)->add($pointsCount, $desc);
        }
    }

    public function invitationsInviteeAccept($observer)
    {
        $this->_isInvitation = true;

        $invitation = $observer->getEvent()->getInvitation();
        $_config    = Mage::getModel('rewards/config');

        $modelCustomer  = Mage::getModel('customer/customer');

        if (
            $_config->getInviteAwardReason() == 1
            && ($pointsCount = $_config->getInviteCredit())
            && $invitation->getInviteeCustomerId()
            && ($customer = $modelCustomer->load($invitation->getCustomerId()))
            && ($customerId = $customer->getId())
        ) {
            $desc = array(
                'objId' => $invitation->getInviteeCustomerId(),
                'objType' => Plumrocket_Rewards_Model_History::OBJ_TYPE_REFFERRAL_1,
                'text'  => '',
            );

            Mage::getModel('rewards/points')->getByCustomer($customerId, Mage::app()->getStore()->getStoreId(), true)->add($pointsCount, $desc);
        }
    }


    public function customerRegisterSuccess($customer)
    {
        if ($customer && ($customerId = $customer->getId())){
            $_config = Mage::getModel('rewards/config');

            $pointsCount = $_config->getRegistrationCredit();
            if ($pointsCount <= 0){
                return $this;
            }

            $restriction = $_config->getRegistrationRestriction();
            if (!$restriction
                || ($restriction == 2 && !Mage::helper('rewards')->modulePlumrocketInvitationsEnabled())
                || ($restriction == 2 && !$this->_isInvitation)
                || ($restriction == 1 && $this->_isInvitation)
            ) {

                $desc = array(
                    'objId' => $customerId,
                    'objType' => Plumrocket_Rewards_Model_History::OBJ_TYPE_REGISTRATION,
                    'text'  => '',
                );

                Mage::getModel('rewards/points')->getByCustomer($customerId, Mage::app()->getGroup()->getId())
                    ->add($pointsCount, $desc);
            }
        }

        return $this;
    }

    public function reviewSaveAfter($observer)
    {

        $review     = $observer->getEvent()->getObject();
        $_config    = Mage::getModel('rewards/config')->setStoreId($review->getStoreId());
        $_helper    = Mage::helper('rewards');

        //if (!$review->getCustomerId() || !$review->isApproved() || !($review->getEntityId() == Mage_Review_Model_Review::ENTITY_PRODUCT)){
        if (!$review->getCustomerId() || !$review->isApproved() ){
            return $observer;
        }

        if (!$_config->getDoubleReview() && $this->_isDoubleReview($review)){
            return $observer;
        }

        if ($_config->getReviewsCreditOnlyForBuyers()){

            $collection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('customer_id', $review->getCustomerId());

            $collection->getSelect()
                ->joinRight( array(
                    'items' => ((string) Mage::getConfig()->getTablePrefix()).'sales_flat_order_item'),
                    'items.order_id = main_table.entity_id and items.product_id = "'.$review->getEntityPkValue().'"',
                    array()
                );
            $collection->getSelect()->group('main_table.entity_id');
            $count = count($collection->load());
            if (!$count){
                return $observer;
            }
        }

        $HISTORY_TYPE   = Plumrocket_Rewards_Model_History::OBJ_TYPE_REVIEW;
        $addItem        = Mage::getModel('rewards/history')->getItem($review->getId(), $HISTORY_TYPE, 1);
        $pointsCount    = $_config->getReviewsCredit();

        if ($addItem->getId() || !$pointsCount) {
            return $observer;
        }


        $desc = array(
            'objId'     => $review->getId(),
            'objType'   => $HISTORY_TYPE,
            'text'      => '',
        );

        Mage::getModel('rewards/points')->getByCustomer($review->getCustomerId(), $review->getStoreId(), true)->add($pointsCount, $desc);

        return $observer;
    }

    protected function _isDoubleReview($review)
    {
        return Mage::getModel('review/review')->getCollection()
            ->addFieldToFilter('main_table.entity_pk_value', $review->getEntityPkValue())
            ->addFieldToFilter('main_table.entity_id', $review->getEntityId())
            ->addFieldToFilter('main_table.review_id', array('neq' => $review->getId()))
            ->addFieldToFilter('customer_id', $review->getCustomerId())
            ->addFieldToFilter('main_table.status_id', Mage_Review_Model_Review::STATUS_APPROVED)
            ->count();
    }

}

