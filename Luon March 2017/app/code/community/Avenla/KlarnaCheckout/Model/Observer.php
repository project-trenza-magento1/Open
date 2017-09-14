<?php
/**
 * This file is released under a custom license by Avenla Oy.
 * All rights reserved
 *
 * License and more information can be found at http://productdownloads.avenla.com/magento-modules/klarna-checkout/
 * For questions and support - klarna-support@avenla.com
 *
 * @category   Avenla
 * @package    Avenla_KlarnaCheckout
 * @copyright  Copyright (c) Avenla Oy
 * @link       http://www.avenla.fi
 */

/**
 * Avenla KlarnaCheckout
 *
 * @category   Avenla
 * @package    Avenla_KlarnaCheckout
 */

class Avenla_KlarnaCheckout_Model_Observer
{
	/**
	 * Process order after status change
	 *
	 * @param	Varien_Event_Observer $observer
	 */
	public function orderStatusChanged($observer)
	{
		if(Mage::helper('klarnaCheckout')->isKcoSave())
			return $this;

		$order = $observer->getEvent()->getOrder();
		$isKcoOrder = Mage::helper('klarnaCheckout')->isKcoOrder($order);

		if(!$isKcoOrder)
			return $this;

		$kco = $order->getPayment()->getMethodInstance()->setStore($order->getStore());
		switch ($order->getState()){
			case Mage_Sales_Model_Order::STATE_COMPLETE:
				if($order->canInvoice()){
					$kco->activateReservation($order);
				}
				else{
					$kco->cancelReservation($order);
				}

				break;
			case Mage_Sales_Model_Order::STATE_CANCELED:
				$kco->cancelReservation($order);
				break;
			default:
				$mixed = false;
				foreach($order->getAllItems() as $item){
					if($item->getQtyShipped() > $item->getQtyInvoiced())
						$mixed = true;
				}

				if($mixed)
					$kco->activateReservation($order);
		}
        
        #$this->_convertGuestToRegGoogle($observer);
        
        
	}

	/**
	 * Process invoice after save
	 *
	 * @param	Varien_Event_Observer $observer
	 */
	public function invoiceSaved($observer)
	{
		if(Mage::helper('klarnaCheckout')->isKcoSave())
			return $this;

		if($kcoInvoiceKey = Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_INVOICE)){
			$invoice = $observer->getEvent()->getInvoice();
			$order = $invoice->getOrder();

			if(Mage::helper('klarnaCheckout')->isKcoOrder($order)){
				if(false !== $klarnainvoices = Mage::helper('klarnaCheckout')->getKlarnaInvoices($order)){
					if (!array_key_exists($invoice->getId(), $klarnainvoices)){
						$klarnainvoices[$invoice->getId()] = $klarnainvoices[$kcoInvoiceKey];
						unset($klarnainvoices[$kcoInvoiceKey]);
						$order = Mage::helper('klarnaCheckout')->saveKlarnaInvoices($order, $klarnainvoices);
						Mage::helper('klarnaCheckout')->prepareKcoSave();
						$order->save();
						Mage::helper('klarnaCheckout')->finishKcoSave();
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Add Klarna link in default Checkout
	 *
	 * @param	Varien_Event_Observer $observer
	 */
	public function insertKlarnaLink($observer)
	{
		$block = $observer->getBlock();
		$isLogged = Mage::helper('customer')->isLoggedIn();

		$kco = Mage::helper('klarnaCheckout')->getKco();

		if(!$kco->getConfig()->isActive())
			return $this;

		if (
			$block->getType() == 'checkout/onepage_login' ||
			($isLogged && $block->getType() == 'checkout/onepage_billing') ||
			($block->getType() == 'checkout/onepage_payment_methods' && $block->getBlockAlias() != 'methods') &&
			$kco->isAvailable()
			)
		{
			$child = clone $block;
			$child->setType('klarnaCheckout/KCO_Link');
			$block->setChild('original', $child);
			$block->setTemplate('KCO/link.phtml');
		}
	}

	/**
	 * Add activate reservation button to admin order view
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addActivate($observer)
	{
		$block = $observer->getEvent()->getBlock();

		if(get_class($block) =='Mage_Adminhtml_Block_Sales_Order_View'
			&& $block->getRequest()->getControllerName() == 'sales_order')
		{
			$order = $block->getOrder();
			if(!Mage::helper('klarnaCheckout')->isKcoOrder($order))
				return $this;

			$block->addButton('activate_klarna_reservation', array(
				'label'     => Mage::helper('klarnaCheckout')->__('Activate Klarna reservation'),
				'onclick'   => 'setLocation(\'' . $block->getUrl('adminhtml/klarnaCheckout_KCO/activateReservation', array('order_id' => $order->getId())) . '\')',
				'class'     => 'save'
			));
		}
	}

	/**
	 * Add new layout handle if needed
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function layoutLoadBefore($observer)
	{
		if (Mage::getModel('klarnaCheckout/config')->hideDefaultCheckout())
			$observer->getEvent()->getLayout()->getUpdate()->addHandle('kco_only');

		$kcoLayout = Mage::getModel('klarnaCheckout/config')->getKcoLayout();
		if($observer->getAction()->getFullActionName() == "checkout_cart_index" && ($kcoLayout && $kcoLayout != "default"))
			$observer->getEvent()->getLayout()->getUpdate()->addHandle($kcoLayout);
	}
    
    
    public function _convertGuestToRegGoogle(Varien_Event_Observer $observer) {
		// This method would be called when google order has been scuccesfully
		// created
		//Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote $quote
		
		$order=$observer->getEvent()->getOrder();
		$quote=$observer->getEvent()->getQuote();
		
		$covertor = new CommerceThemes_GuestToReg_Model_Rewrite_FrontCheckoutTypeOnepage ();
		$paymentMethod = $covertor->getOrderPayment ( $order );
		$payMeth = $paymentMethod->getData ( 'method' );
		#Mage::log ( "observer writing payment method " .$payMeth);
		
		if ($payMeth == 'googlecheckout' || $payMeth == 'google checkout') 
		{
			$allow_guesttoreg_at_checkout = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_ext');
			#Mage::log ( "I am in observer" );
			#Mage::log ( "allow_guesttoreg_at_checkout" .$allow_guesttoreg_at_checkout);
			//Mage::log ( $covertor->isOrderPaypal ( $order ) );
			// $this->isOrderPaypal($order);
			//if (($allow_guesttoreg_at_checkout == true) || ($covertor->isOrderPaypal ( $order ))) {
				#Mage::log ( "I am in condition" );
				// $order = Mage::getModel('sales/order');
				// $order->load($this-> getCheckout()->getLastOrderId());
				$entity_id = $order->getData ( 'entity_id' );
				
				// oReq = Mage::app()->getFrontController()->getRequest();
				$store_id = Mage::app ()->getStore ()->getId ();
				$valueid = Mage::getModel ( 'core/store' )->load ( $store_id )->getWebsiteId ();
				// DUPLICATE CUSTOMERS are appearing after import this value
				// above is likely not found.. so we have a little check here
				if ($valueid < 1) {
					$valueid = 1;
				}
				// xit;
				$isNewCustomer = true;
				#Mage::log ( "I am in quote->getCheckoutMethod ()".$quote->getCheckoutMethod () );
				switch ($quote->getCheckoutMethod ()) {
					case CommerceThemes_GuestToReg_Model_Rewrite_FrontCheckoutTypeOnepage::METHOD_REGISTER :
						$isNewCustomer = false;
						break;
				}
				
				if ($isNewCustomer) {
					#Mage::log ( "new customer");
					$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( $valueid )->loadByEmail ( $order->getCustomerEmail () );
					
					if ($customer->getId ()) {
						$customerId = $customer->getId ();
						/*
						 * SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO
						 * THE NEWLY CREATED CUSTOMER
						 */
						$entityTypeId = Mage::getModel ( 'eav/entity' )->setType ( 'order' )->getTypeId ();
						$resource = Mage::getSingleton ( 'core/resource' );
						#$prefix = Mage::getConfig ()->getNode ( 'global/resources/db/table_prefix' );
						$write = $resource->getConnection ( 'core_write' );
						$read = $resource->getConnection ( 'core_read' );
						
						// 1.4.2 +
						$write_qry = $write->query ( "UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order')." SET customer_id = '" . $customerId . "', customer_is_guest = '0', customer_group_id = '1' WHERE entity_id = '" . $entity_id . "'" );
						$write_qry = $write->query ( "UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid')." SET customer_id = '" . $customerId . "' WHERE entity_id = '" . $entity_id . "'" );
						
						// UPDATE FOR DOWNLOADABLE PRODUCTS
						$write_qry = $write->query ( "UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '" . $customerId . "' WHERE order_id = '" . $entity_id . "'" );
					
					} else {
						#Mage::log ( "I am not new customer" );
						$entityTypeId = Mage::getModel ( 'eav/entity' )->setType ( 'order' )->getTypeId ();
						$resource = Mage::getSingleton ( 'core/resource' );
						#$prefix = Mage::getConfig ()->getNode ( 'global/resources/db/table_prefix' );
						$write = $resource->getConnection ( 'core_write' );
						$read = $resource->getConnection ( 'core_read' );
						$select_qry5 = $read->query ( "SELECT subscriber_status FROM ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." WHERE subscriber_email = '" . $order->getCustomerEmail () . "'" );
						$newsletter_subscriber_status = $select_qry5->fetch ();
						
						$customerId = $covertor->_CreateCustomerFromGuest ( $order->getBillingAddress ()->getData ( 'company' ), $order->getBillingAddress ()->getData ( 'city' ), $order->getBillingAddress ()->getData ( 'telephone' ), $order->getBillingAddress ()->getData ( 'fax' ), $order->getCustomerEmail (), $order->getBillingAddress ()->getData ( 'prefix' ), $order->getBillingAddress ()->getData ( 'firstname' ), $middlename = "", $order->getBillingAddress ()->getData ( 'lastname' ), $suffix = "", $taxvat = "", $order->getBillingAddress ()->getStreet ( 1 ), $order->getBillingAddress ()->getStreet ( 2 ), $order->getBillingAddress ()->getData ( 'postcode' ), $order->getBillingAddress ()->getData ( 'region' ), $order->getBillingAddress ()->getData ( 'country_id' ), "1", $store_id );
						
						/*
						 * SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO
						 * THE NEWLY CREATED CUSTOMER
						 */
						// 1.4.2 +
						$write_qry = $write->query ( "UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order')." SET customer_id = '" . $customerId . "', customer_is_guest = '0', customer_group_id = '1' WHERE entity_id = '" . $entity_id . "'" );
						$write_qry = $write->query ( "UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid')." SET customer_id = '" . $customerId . "' WHERE entity_id = '" . $entity_id . "'" );
						
						// UPDATE FOR DOWNLOADABLE PRODUCTS
						$write_qry = $write->query ( "UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '" . $customerId . "' WHERE order_id = '" . $entity_id . "'" );
						// UPDATE FOR NEWSLETTER
						if ($newsletter_subscriber_status ['subscriber_status'] != "" && $newsletter_subscriber_status ['subscriber_status'] > 0) {
							$write_qry = $write->query ( "UPDATE ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." SET subscriber_status = '" . $newsletter_subscriber_status ['subscriber_status'] . "' WHERE subscriber_email = '" . $order->getCustomerEmail () . "'" );
						}
					
					}
				
				}
			// enable to condition of  payment method}
		}
	
	}
    
    
}