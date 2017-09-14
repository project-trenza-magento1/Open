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

class Plumrocket_Invitations_Block_Adminhtml_Addressbooks_List extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_addressbooks_list';
        $this->_blockGroup = 'invitations';
        $this->_headerText = Mage::helper('invitations')->__('Manage Invitations');
		$this->removeButton('add');
    }

    
    protected function _prepareLayout()
   	{
       $this->setChild( 'grid',
           $this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
           $this->_controller . '.grid')->setSaveParametersInSession(true) );
       return parent::_prepareLayout();
   	}
  	
}
