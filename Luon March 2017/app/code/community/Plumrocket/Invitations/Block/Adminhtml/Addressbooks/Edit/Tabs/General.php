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

class Plumrocket_Invitations_Block_Adminhtml_Addressbooks_Edit_Tabs_General 
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
		/*
		$a = array(
			'application_id' => array(
				'type'	=> 'text',
				'label'		=> 'Application Id',
				'class'		=> 'required-entry',
				'required'	=> true,
				'value'		=> '',
			),
			'secret_key' => array(
				'type'	=> 'text',
				'label'		=> 'Secret Key',
				'class'		=> 'required-entry',
				'required'	=> true,
				'value'		=> '',
			),
		
		);
		var_dump(json_encode($a));
		*/
		
        $addressBook = Mage::registry('current_invitee_addressbook'); 
        $isElementDisabled = false;

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('invitations_');
		
        $fieldset = $form->addFieldset('general_fieldset', array('legend' => Mage::helper('invitations')->__('General'), 'class' => 'fieldset-wide'));
		
	
		$fieldset->addField('id', 'hidden', array(
			'name'		=> 'id',
			'class'		=> 'required-entry',
			'required'	=> true,
			'disabled'	=> $isElementDisabled,
			'value'		=> $addressBook->getId(),
		));
		
		$fieldset->addField('name', 'text', array(
			'name'		=> 'name',
			'label'		=> Mage::helper('invitations')->__('Name'),
			'class'		=> 'required-entry',
			'required'	=> true,
			'disabled'	=> $isElementDisabled,
			'value'		=> $addressBook->getName(),
		));
		
		
		$fieldset->addField('type', 'select', array(
			'name'      => 'type',
            'label'     => Mage::helper('invitations')->__('Type'),
            'title'     => Mage::helper('invitations')->__('Type'),
            'required'  => true,
            'disabled'  => $isElementDisabled,
			'values'	=> array('EMAIL' => 'Email Service', 'SOCIAL' => 'Social Network'),
			'value'		=> $addressBook->getType(),
        ));
         
        
        $fieldset->addField('status', 'select', array(
			'name'      => 'status',
            'label'     => Mage::helper('invitations')->__('Status'),
            'title'     => Mage::helper('invitations')->__('Status'),
            'required'  => true,
            'disabled'  => $isElementDisabled,
			'values'	=> array(Plumrocket_Invitations_Model_Addressbooks::STATUS_ENABLED => 'Enabled', Plumrocket_Invitations_Model_Addressbooks::STATUS_DISABLED => 'Disabled'),
			'value'		=> $addressBook->getStatus(),
        ));

        $fieldset->addField('position', 'text', array(
			'name'      => 'position',
            'label'     => Mage::helper('invitations')->__('Position'),
            'class'		=> 'required-entry',
            'required'  => true,
            'disabled'  => $isElementDisabled,
			'value'		=> $addressBook->getPosition(),
        ));
		
		if (
			$addressBook->getSettings()
			&& ($settingsArr = $addressBook->getSettingsArray())
		)
		{
			foreach($settingsArr as $name => $attr)
			{
				if (!empty($attr['label']))
					$attr['label'] = Mage::helper('invitations')->__($attr['label']);
				if (!empty($attr['title']))
					$attr['title'] = Mage::helper('invitations')->__($attr['title']);	
				
				$attr['name'] = 'settings['.$name.']';
				if ($attr['type'] == 'password' && !empty($attr['value']))
					$attr['value'] = $addressBook->getDefaultHiddenValue();
					
				$fieldset->addField($attr['name'], $attr['type'], $attr);
			}		
		}
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('invitations')->__('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('invitations')->__('General');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

}
