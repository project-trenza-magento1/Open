<?php
 
class Trenza_Inventorylog_Block_Adminhtml_Inventorylog_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
         
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
        ));
 
        $form->setUseContainer(true); 
        $this->setForm($form);
 
        $fieldset = $form->addFieldset('log_details_form', array(
             'legend' =>Mage::helper('inventorylog')->__('Log Details')
        ));
 
        
        
        $fieldset->addField('sku', 'text', array(
          'label'     => Mage::helper('inventorylog')->__('Sku'),          
          'name'      => 'sku',
          'required'  => true,
          'style' => 'text-transform:uppercase',
        ));
        
        
            
                
        $form->setValues(Mage::registry('log_data')->getData()); 
 
        return parent::_prepareForm();
    }
}