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
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php  

class Plumrocket_Rewards_Block_Adminhtml_Points_List_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
    protected $_storeId     = null;
    protected $_websiteId   = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->setId('manage_rewards');
        $this->setDefaultSort('available');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        
        $_request = $this->getRequest();
        if (! $_request->getParam('store') && ($store = $this->getSession()->getPlumrocketRewardsGridStor())){
             $_request->setParam('store', $store);
        }
        
        $this->_storeId = $_request->getParam('store');
        if ($this->_storeId){
            $this->getSession()->setPlumrocketRewardsGridStor($this->_storeId);
        }

        foreach (Mage::app()->getWebsites() as $website){
            foreach ($website->getGroups() as $store){
                if (!$this->_storeId){
                    $this->_storeId = $store->getId();
                }
                        
                if ($this->_storeId == $store->getId()){
                    $this->_websiteId = $store->getWebsiteId();
                    break;
                }
            }
        }

    }


    protected function _prepareCollection()
    {
        $storeId = $this->getStoreId();
        
        $collection = Mage::getModel('rewards/points')->getCollection()->useCustomerCountAsSize(true,  $this->getWebsiteId())
            ->addFieldToSelect('activated')
            ->addExpressionFieldToSelect('available', 'SUM(available)', null)
            ->addExpressionFieldToSelect('spent', 'SUM(spent)', null)
            ->addFilterToMap('firstname', 'fname.value')
            ->addFilterToMap('lastname', 'lname.value')
            ->addFilterToMap('entity_id', 'customer.entity_id');

        $collection->getSelect()
            ->joinRight( array(
                'customer' => ((string) Mage::getConfig()->getTablePrefix()).'customer_entity'),
                'main_table.customer_id = customer.entity_id and (main_table.store_group_id = '.$storeId.' or main_table.store_group_id IS NULL)',
                array('email', 'entity_id')
            )
            ->where('customer.website_id = ?', $this->getWebsiteId())
            ->group('customer.entity_id');

        $cR = Mage::getModel('customer/customer')->getResource();
        $collection->getSelect()
            ->joinLeft( array(
                'fname' => ((string) Mage::getConfig()->getTablePrefix()).'customer_entity_varchar'),
                'customer.entity_id = fname.entity_id and fname.attribute_id = '.$cR->getAttribute('firstname')->getId(),
                array('firstname' => 'value')
            )
            ->joinLeft( array(
                'lname' => ((string) Mage::getConfig()->getTablePrefix()).'customer_entity_varchar'),
                'customer.entity_id = lname.entity_id and lname.attribute_id = '.$cR->getAttribute('lastname')->getId(),
                array('lastname' => 'value')
            ); 
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {   
        $this->addColumn('id', array(
            'header'    => Mage::helper('rewards')->__('Customer ID'),
            'align'     =>'left',
            'index'     => 'entity_id',
        
        ));
        
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('rewards')->__('Customer Firstname'),
            'align'     =>'left',
            'index'     => 'firstname',
            'type' => 'text',
        )); 
        
       
        $this->addColumn('lastname', array(
            'header'    => Mage::helper('rewards')->__('Customer Lastname'),
            'align'     =>'left',
            'index'     => 'lastname',
            'type' => 'text',
        )); 
        
        $this->addColumn('email', array(
            'header'    => Mage::helper('rewards')->__('Customer Email'),
            'align'     =>'left',
            'index'     => 'email',
            'type' => 'text',
        
        )); 

        
        $availableRender = new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_Points;
        
        $availableRender->setData('column','available');
        $this->addColumn('available', array(
            'header'    => Mage::helper('rewards')->__('Available Points'),
            'align'     =>'left',
            'index'     => 'available',
            'renderer'  => $availableRender,
            'type' => 'number',
        ));
        
        $availableRender = new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_Points;
        $availableRender->setData('column','spent');
        $this->addColumn('spent', array(
            'header'    => Mage::helper('rewards')->__('Redeemed Points'),
            'align'     => 'left',
            'index'     => 'spent',
            'renderer'  => $availableRender,
            'type' => 'number',
        ));
        
        $accumulatedPoints = new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_AccumulatedPoints;
        $this->addColumn('rewards_accumulated_points', array(
            'header'    => Mage::helper('rewards')->__('Accumulated Points'),
            'align'     => 'left',
            'index'     => 'rewards_spent_points',
            'escape'    => true,
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => $accumulatedPoints,
            'type' => 'number',
        ));
        
        $render = new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_PointsEdit;
        $render->setStoreId($this->getStoreId());

        $this->addColumn('action', array(
            'header'    => Mage::helper('rewards')->__('Action'),
            'align'     => 'left',
            'index'     => 'customer_id',
            'escape'    => true,
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => $render,
            'type' => 'text',
        ));


        return parent::_prepareColumns();
    }
    
    public function getStoreId()
    {
        return $this->_storeId;
    }
    
    public function getWebsiteId()
    {
        return $this->_websiteId;
    }
    
    public function getSession()
    {
        return Mage::getSingleton('admin/session');
    }
   
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('customer_id' => $row->getEntityId(), 'store' => $this->getStoreId()));
    }
}
