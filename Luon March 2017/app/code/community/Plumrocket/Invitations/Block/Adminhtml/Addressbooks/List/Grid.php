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

class Plumrocket_Invitations_Block_Adminhtml_Addressbooks_List_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
	
	private $modelAddressBook;

	public function __construct()
    {
       	parent::__construct();
        $this->setId('manage_invitations_addressbooks');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->modelAddressBook = Mage::getModel('invitations/addressbooks');
    }

	
	protected function _prepareCollection()
    {
    	$collection = $this->modelAddressBook->getCollection();
		$this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {

		/*
		$this->addColumn('id', array(
            'header'    => Mage::helper('invitations')->__('ID'),
            'align'     =>'left',
            'index'     => 'id',
            'type' => 'number',  
        ));
		*/
		
		$this->addColumn('image', array(
            'header'    => Mage::helper('invitations')->__('Image'),
            'align'     => 'left',
            'width'     => '100px',
            'index'     => 'type',
            'type'      => 'image',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => new Plumrocket_Invitations_Block_Adminhtml_Grid_Renderer_AddressbookImage,
			'align'		=> 'center',
        ));
		
		$this->addColumn('name', array(
            'header'    => Mage::helper('invitations')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
            'type' => 'text',
        
        ));
		
		$this->addColumn('type', array(
            'header'    => Mage::helper('invitations')->__('Type'),
            'align'     =>'left',
            'index'     => 'type',
			'type'		=> 'options', 
			'options'	=> $this->modelAddressBook->getTypes(), 
        ));
		
		$this->addColumn('status', array(
            'header'    => Mage::helper('invitations')->__('Status'),
            'align'     =>'left',
            'index'     => 'status',
            'type'		=> 'options', 
			'options'	=> $this->modelAddressBook->getStatuses(),
        
        ));

        $this->addColumn('position', array(
            'header'    => Mage::helper('invitations')->__('Position'),
            'align'     =>'left',
            'index'     => 'position',
            'type' => 'number',
        ));
		
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

		
		$this->getMassactionBlock()->addItem('enable', array(
             'label'	=> Mage::helper('invitations')->__('Enable'),
             'url'  	=> $this->getUrl('*/*/enable', array('_current'=>true)),
			 'confirm'  => Mage::helper('invitations')->__('Are you sure?'),
        ));
		
		$this->getMassactionBlock()->addItem('disable', array(
             'label'	=> Mage::helper('invitations')->__('Disable'),
             'url'  	=> $this->getUrl('*/*/disable', array('_current'=>true)),
			 'confirm'  => Mage::helper('invitations')->__('Are you sure?'),
        ));
        
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
