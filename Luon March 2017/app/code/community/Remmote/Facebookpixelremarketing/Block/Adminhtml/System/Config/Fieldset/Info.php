<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Remmote Info block
 */
class Remmote_Facebookpixelremarketing_Block_Adminhtml_System_Config_Fieldset_Info 
	extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

		protected $_template = 'remmote/facebookpixelremarketing/system/config/fieldset/info.phtml';

		public function render(Varien_Data_Form_Element_Abstract $element) {
        	return $this->toHtml();
    	}
}