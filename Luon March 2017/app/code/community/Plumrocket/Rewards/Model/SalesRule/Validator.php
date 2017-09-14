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


if (Mage::getStoreConfig('facebookdiscount/general/enable')) {
    class Plumrocket_Rewards_Model_SalesRule_ValidatorBase extends Plumrocket_Facebookdiscount_Model_SalesRule_Validator {}
} else {

    $amastyCoupons = Mage::getConfig()->getModuleConfig('Amasty_Coupons');
    if ($amastyCoupons && $amastyCoupons->is('active', 'true')) {
        class Plumrocket_Rewards_Model_SalesRule_ValidatorBase extends Amasty_Coupons_Model_SalesRule_Validator {}
    } else {
        class Plumrocket_Rewards_Model_SalesRule_ValidatorBase extends Mage_SalesRule_Model_Validator {}
    }
}

class Plumrocket_Rewards_Model_SalesRule_Validator extends Plumrocket_Rewards_Model_SalesRule_ValidatorBase
{

    protected $_rewardsRules = array();
    protected $_points  = null;

    protected $_baseRpdiscountAmount    = null;

    public function init($websiteId, $customerGroupId, $couponCode)
    {
        parent::init($websiteId, $customerGroupId, $couponCode);

        Mage::dispatchEvent('plumrocket_rewards_salesrule_validator_before', array(
            'validator' => $this,
        ));

        $config = Mage::getModel('rewards/config');

        if (!$config->modulEnabled()){
            return $this;
        }

        if ($couponCode && !$config->getRedeemPointsWithCoupon()) {
            if ($this->_getBaseRpdiscountAmount()) {
                $this->_getPoints()->deactivate();
            }
            return $this;
        }

        if ($this->_getBaseRpdiscountAmount()) {

            $rItemId = 439999;

            $key = $websiteId . '_' . $customerGroupId . '_' . $couponCode;
            if (isset($this->_rules[$key]) && !isset($this->_rewardsRules[$key]) ) {

                $this->_rewardsRules[$key] = Mage::getModel('salesrule/rule')->setData(array(
                    'name'      => 'Reward Points Rule',
                    'description' => 'Reward Points Rule',
                    'is_active' => 1,
                    'simple_action' => Mage_SalesRule_Model_Rule::CART_FIXED_ACTION,
                    'discount_amount' => $this->_getBaseRpdiscountAmount(),
                    'store_labels' => array(
                        0 => $this->_getPoints()->getActivated().' '.Mage::helper('rewards')->__('reward points'),
                    ),
                    'coupon_type' => Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON,
                ))->setId($rItemId);


                $products       = $this->_getPoints()->getDisabledProductForApply();
                $productSkus    = array();
                foreach($products as $product) {
                    $productSkus[] = $product->getSku();
                }

                if (count($productSkus)) {

                    $actionsSerialized = array(
                        'type'          => 'salesrule/rule_condition_product_combine',
                        'attribute'     => null,
                        'operator'      => null,
                        'value'         => '1',
                        'is_value_processed' => null,
                        'aggregator'    => 'all',
                        'conditions'    => array(
                            array(
                                'type' => 'salesrule/rule_condition_product',
                                'attribute' => 'sku',
                                'operator'  => '!=',
                                'value'     => implode(', ', $productSkus),
                                'is_value_processed' => false,
                            )
                        )
                    );

                    $this->_rewardsRules[$key]->setActionsSerialized(serialize($actionsSerialized));
                }

                $removeRule = false;
                foreach($this->_rules[$key] as $rule) {
                    if ($removeRule) {
                        $this->_rules[$key]->removeItemByKey($rule->getId());
                        continue;
                    }

                    if ($rule->getStopRulesProcessing()) {
                        $removeRule = true;
                        $rule->setStopRulesProcessing(false);
                    }
                }

                if ($this->_rules[$key]->getItemById($rItemId)) {
                    $this->_rules[$key]->removeItemByKey($rItemId);
                }
                $this->_rules[$key]->addItem($this->_rewardsRules[$key]);

            }
        }

        return $this;
    }

    protected function _getPoints()
    {
        if(is_null($this->_points)){
            $this->_points = Mage::getSingleton('rewards/points');
            if ($quote = Mage::registry('adminhtml_rewards_point_quote')) {
                $this->_points
                    ->setQuote($quote)
                    ->getByCustomer($quote->getCustomerId(), $quote->getStoreId(), true);
            }
        }
        return $this->_points;
    }

    protected function _getBaseRpdiscountAmount()
    {
        if (is_null($this->_baseRpdiscountAmount)) {
            $this->_baseRpdiscountAmount = abs($this->_getPoints()->getBaseRpdiscountAmount(false));
        }

        return $this->_baseRpdiscountAmount;
    }

    public function addRuleItem($key, $item)
    {
        $this->_rules[$key]->addItem($item);
        return $this;
    }
}