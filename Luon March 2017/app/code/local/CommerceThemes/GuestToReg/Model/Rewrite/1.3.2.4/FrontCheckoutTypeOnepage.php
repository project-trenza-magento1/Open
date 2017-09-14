<?php
/**
 * FrontCheckoutTypeOnepage.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @package    CommerceThemes_GuestToReg
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */ 
class CommerceThemes_GuestToReg_Model_Rewrite_FrontCheckoutTypeOnepage extends Mage_Checkout_Model_Type_Onepage
{
	 public function _CreateCustomerFromGuest($company="", $city, $telephone, $fax="", $email, $prefix="", $firstname, $middlename="", $lastname, $suffix="", $taxvat="", $street1, $street2="", $postcode, $region_id, $country_id, $customer_group_id, $storeId) {
		
				$customer = Mage::getModel('customer/customer');
				$street_r=array("0"=>$street1,"1"=>$street2);
				$group_id=$customer_group_id;
				$website_id=Mage::getModel('core/store')->load($storeId)->getWebsiteId();;
						
				
				$default_billing="_item1";
				$index="_item1";
				
				$customerData=array(
						"prefix"=>$prefix,
						"firstname"=>$firstname,
						"middlename"=>$middlename,
						"lastname"=>$lastname,
						"suffix"=>$suffix,
						"email"=>$email,
						"group_id"=>$group_id,
						"taxvat"=>$taxvat,
						"website_id"=>$website_id,
						"default_billing"=>$default_billing
				);
		
				$customer->addData($customerData); ///make sure this is enclosed in arrays correctly
		
				$addressData=array(
						"prefix"=>$prefix,
						"firstname"=>$firstname,
						"middlename"=>$middlename,
						"lastname"=>$lastname,
						"suffix"=>$suffix,
						"company"=>$company,
						"street"=>$street_r,
						"city"=>$city,
						"region"=>$region_id,
						"country_id"=>$country_id,
						"postcode"=>$postcode,
						"telephone"=>$telephone,
						"fax"=>$fax
				);
				
				
				$address = Mage::getModel('customer/address');
				$address->setData($addressData);
		
				/// We need set post_index for detect default addresses
				///pretty sure index is a 0 or 1
				$address->setPostIndex($index);
				$customer->addAddress($address);
				$customer->setIsSubscribed(false);
				$customer->setPassword($customer->generatePassword(8));
				
				///adminhtml_customer_prepare_save
				$customer->save();
				
				$disable_new_customer_email = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_new_customer_email');
				if ($disable_new_customer_email != true) {
					$customer->sendNewAccountEmail();
				}
		
				///adminhtml_customer_save_after
				$customerId=$customer->getId();
		
				Mage::log("customerId:$customerId");
		
				return $customerId;
	} 
		
    public function saveOrder()
    {
        
		
        $oResult = parent::saveOrder();
		$allow_guesttoreg_at_checkout = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_ext');
		if ($allow_guesttoreg_at_checkout == true) {
		
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastOrderId());
		$entity_id = $order->getData('entity_id');
		
        #$oReq = Mage::app()->getFrontController()->getRequest();
		$store_id = Mage::app()->getStore()->getId();
		$valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
		//DUPLICATE CUSTOMERS are appearing after import this value above is likely not found.. so we have a little check here
		if($valueid < 1) { $valueid =1; }
		#exit;
		$isNewCustomer = true;
		
        switch ($this->getQuote()->getCheckoutMethod()) {
        #switch (parent::getCheckoutMehod()) {
            case self::METHOD_REGISTER:
                $isNewCustomer = false;
                break;
        }

        if ($isNewCustomer) {
		
			$customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());
			
			if ($customer->getId()) {
			$customerId = $customer->getId();
			/* SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO THE NEWLY CREATED CUSTOMER */
			$entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
			$resource = Mage::getSingleton('core/resource');
			$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');
			//1.3.x
			$select_qry = $read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE attribute_code = 'customer_is_guest' AND entity_type_id = '". $entityTypeId ."'");
			$eav_attribute_row = $select_qry->fetch();
			$write_qry = $write->query("UPDATE `".$prefix."sales_order_int` SET value = '0' WHERE attribute_id = '". $eav_attribute_row['attribute_id'] ."' AND entity_id = '". $entity_id ."'");
			$write_qry = $write->query("UPDATE `".$prefix."sales_order` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
		
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			$write_qry = $write->query("UPDATE `".$prefix."downloadable_link_purchased` SET customer_id = '". $customerId ."' WHERE order_id = '". $entity_id ."'");
			
			//1.4.x+
			#$write_qry = $write->query("UPDATE `".$prefix."sales_flat_order` SET customer_id = '". $customerId ."', customer_is_guest = '0', customer_group_id = '1' WHERE entity_id = '". $entity_id ."'");
			#$write_qry = $write->query("UPDATE `".$prefix."sales_flat_order_grid` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
		
			} else {
		 	$customerId = $this->_CreateCustomerFromGuest($order->getBillingAddress()->getData('company'), $order->getBillingAddress()->getData('city'), $order->getBillingAddress()->getData('telephone'), $order->getBillingAddress()->getData('fax'), $order->getCustomerEmail(), $order->getBillingAddress()->getData('prefix'), $order->getBillingAddress()->getData('firstname'), $middlename="", $order->getBillingAddress()->getData('lastname'), $suffix="", $taxvat="", $order->getBillingAddress()->getStreet(1), $order->getBillingAddress()->getStreet(2), $order->getBillingAddress()->getData('postcode'), $order->getBillingAddress()->getData('region'), $order->getBillingAddress()->getData('country_id'), "1", $store_id);
		
			
			/* SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO THE NEWLY CREATED CUSTOMER */
			$entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
			$resource = Mage::getSingleton('core/resource');
			$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');
			//1.3.x
			$select_qry = $read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE attribute_code = 'customer_is_guest' AND entity_type_id = '". $entityTypeId ."'");
			$eav_attribute_row = $select_qry->fetch();
			$write_qry = $write->query("UPDATE `".$prefix."sales_order_int` SET value = '0' WHERE attribute_id = '". $eav_attribute_row['attribute_id'] ."' AND entity_id = '". $entity_id ."'");
			$write_qry = $write->query("UPDATE `".$prefix."sales_order` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
			
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			$write_qry = $write->query("UPDATE `".$prefix."downloadable_link_purchased` SET customer_id = '". $customerId ."' WHERE order_id = '". $entity_id ."'");
			
			//1.4.x
			#$write_qry = $write->query("UPDATE `".$prefix."sales_flat_order` SET customer_id = '". $customerId ."', customer_is_guest = '0', customer_group_id = '1' WHERE entity_id = '". $entity_id ."'");
			#$write_qry = $write->query("UPDATE `".$prefix."sales_flat_order_grid` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
			
			}
									
		}
		}
        return $oResult;
    }    
}
