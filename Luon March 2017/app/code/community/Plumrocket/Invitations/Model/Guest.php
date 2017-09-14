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
 * @package     Plumrocket_Invitations
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php

class Plumrocket_Invitations_Model_Guest extends Mage_Core_Model_Abstract
{
	protected $_customer = null;
	
	public function _construct()
    {
        parent::_construct();
        $this->_init('invitations/guest');
    }
    
    public function getGuestId($email, $autoCreate = true)
    {
		//$email = strtolower($email);
		if (Zend_Validate::is($email, 'EmailAddress')){
			$this->loadByEmail($email);
			if ($this->getId()){
				return $this->getId();
			}
			
			if ($autoCreate){
				return $this->setEmail($email)->save()->getId();
			}
		}
		
		return null;
	}
	
	public function loadByEmail($email){
		return $this->load($email, 'email');
	}
	
	public function loadByCode($code){
		return $this->load($code, 'code');
	}
	
	public function getCustomer()
	{
		if (is_null($this->_customer)){
			$this->_customer = Mage::helper('invitations')->getCustomerByEmail($this->getEmail());
		}
		return $this->_customer;
	}
	
	public function setCustomer($customer)
	{
		$this->_customer = $customer;
		return $this;
	}
	
	public function neadMigration()
	{
		return Mage::getModel('invitations/invitations')->getCollection()
			->addFieldToFilter('guest_id', $this->getId())
			->addFieldToFilter('customer_id', 0)
			->addFieldToFilter('deactivated', 0)
			->setPageSize(1)
			->getFirstItem()
			->getId();
	}
	
	public function migration()
	{
		$code = $this->getCode();
		$this->setCode('')->save();
		
		$_customer = $this->getCustomer();
		
		$resource	= Mage::getModel('core/resource');
		$db			= $resource->getConnection('core_write');
		
		$updatedCount = $db->update(
			$resource->getTableName('invitations/invitations'),
			array('customer_id' => $_customer->getId()),
			array(
				'guest_id = '.$this->getId(),
				'customer_id = 0',
				'deactivated = 0',
			)
		);
		
		return $this;
	}

	public function neadValidateGuestOwnership()
	{
		return Mage::getModel('invitations/config')->validateGuestOwnership();
	}
	
	public function sendVerificationEmail()
	{	
		$customer = $this->getCustomer();

		$code = Mage::helper('core')->getRandomString(32);
			
		$this->setCode($code)->save();
		
		$emailTemplateCode	= Mage::getModel('invitations/config')->getGuestVerificationEmailTemplate();
        if (is_numeric($emailTemplateCode)){
			$emailTemplate  = Mage::getModel('core/email_template')->load($emailTemplateCode); 
		} else {
			$emailTemplate  = Mage::getModel('core/email_template')->loadDefault($emailTemplateCode); 
		}
		
		$emailTemplateVariables = array(
			'customer'				=> $customer,
			'validate_url'	=> Mage::getUrl('invitations/promo/validate', array('code' => $code)),
		);
		$emailTemplate
			->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'))
			->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
				
		return $emailTemplate->send($customer->getEmail(), $customer->getName(), $emailTemplateVariables); 
	}
	
	public function getName()
	{
		return Mage::helper('customer')->__('Visitor');
	}

}
