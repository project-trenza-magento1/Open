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

class Plumrocket_Invitations_Block_Adminhtml_Addressbooks_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
		
		$this->_controller = 'adminhtml_invitations_edit_tabs_tags';
        $this->_blockGroup = 'invitations';
		
        $this->setId('edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('invitations')->__('Edit invitations'));
    }
	
}
