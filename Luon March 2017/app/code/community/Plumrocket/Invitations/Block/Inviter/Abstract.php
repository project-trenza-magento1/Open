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
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php
  
class Plumrocket_Invitations_Block_Inviter_Abstract extends Mage_Core_Block_Template
{   
	protected $_addressBooks = null;
	
	public function getAddressBook($aBook = null){
		if (is_null($this->_addressBooks)){
			$this->_addressBooks = Mage::registry('current_address_book');
		}
		return $this->_addressBooks;
	}
	
	public function setAddressBook($aBook){
		$this->_addressBooks = $aBook;
		return $this;
	}

}
