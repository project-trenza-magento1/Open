<?php

class Trenza_Pricecomparison_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*'),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data',
                                      'name' => 'edit_form'
                                   )
        );
      
      $form->setUseContainer(true);        
      //$fieldset = $form->addFieldset('template_form', array('legend'=>Mage::helper('listingwizard')->__('Set Template')));
      
      
      
      
      
      $fieldset = $form->addFieldset('pricecomparison_form', array('legend'=>Mage::helper('pricecomparison')->__('Upload Price Comparison CSV File Here')));
     
      $fieldset->addField('csvfile', 'file', array(
          'label'     => Mage::helper('pricecomparison')->__('CSV File'),          
          'name'      => 'csvfile',
          'after_element_html' => '<button type="button" onclick="this.form.submit()")">Save</button>'
      ));
     
		
      
      $this->setForm($form);
      return parent::_prepareForm();
  }
}
