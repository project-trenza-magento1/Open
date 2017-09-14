<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
class CommerceThemes_GuestToReg_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
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
	public function postAction() {
		if($this->getRequest()->getPost()) {
		
			$data = $this->getRequest()->getPost();
			#print_r($data);
			#echo "ORDER ID: " . $data['customer_order_id'];
		
			$resource = Mage::getSingleton('core/resource');
			$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');
			 
			#$select_qry =$read->query("SELECT entity_id FROM `".$prefix."sales_flat_order` WHERE `increment_id`='".$data['customer_order_id']."'");
			$select_qry =$read->query("SELECT entity_id FROM `".$prefix."sales_order` WHERE `increment_id`='".$data['customer_order_id']."'");
			$row = $select_qry->fetch();
			$entity_id = $row['entity_id'];
			
			$order = Mage::getModel('sales/order');
			$order->load($entity_id); //needs entity_id NOT order_Id
			
			$store_id = Mage::app()->getStore()->getId();
			$valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
			//DUPLICATE CUSTOMERS are appearing after import this value above is likely not found.. so we have a little check here
			if($valueid < 1) { $valueid =1; }
			#exit;
		
			$customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());
			
			if ($customer->getId()) {
			$customerexistedmessage = true;
			$customerId = $customer->getId();
			/* SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO THE NEWLY CREATED CUSTOMER */
			$entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
			
			//1.3.x
			$select_qry = $read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE attribute_code = 'customer_is_guest' AND entity_type_id = '". $entityTypeId ."'");
			$eav_attribute_row = $select_qry->fetch();
			$write_qry = $write->query("UPDATE `".$prefix."sales_order_int` SET value = '0' WHERE attribute_id = '". $eav_attribute_row['attribute_id'] ."' AND entity_id = '". $entity_id ."'");
			$write_qry = $write->query("UPDATE `".$prefix."sales_order` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
			
			
		
			} else {
			$customerexistedmessage = false;
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
			
			//1.4.x
			#$write_qry = $write->query("UPDATE `".$prefix."sales_flat_order` SET customer_id = '". $customerId ."', customer_is_guest = '0', customer_group_id = '1' WHERE entity_id = '". $entity_id ."'");
			#$write_qry = $write->query("UPDATE `".$prefix."sales_flat_order_grid` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
			
			}
			if($customerexistedmessage == true){	
	 			$message = $this->__('The email address already exists so the order has been merged to the existing account.');
			} else {
				$message = $this->__('Your account has been created and a email has been sent to you with their username and password.');
			}
			Mage::getSingleton('core/session')->addSuccess($message);
		
		}
		else {
			Mage::getSingleton('core/session')->addError($this->__('Sorry this order could not be converted to a customer account.'));
		}
		#exit;
		
		if($this->getRequest()->getParam("backurl")!="")
			$this->_redirect($this->getRequest()->getParam("backurl"));
		else 
			$this->_redirect("/");
	}
}