<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_CallToAction
 * @copyright   Copyright (c) 2014-2015 Itactica (https://www.getintenso.com)
 * @license     https://getintenso.com/license
 */

class Itactica_CallToAction_Model_Cta_Attribute_Source_Fontstyle extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    /**
     * get possible values
     * @access public
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false){
        $options =  array(
            array(
                'label' => Mage::helper('itactica_calltoaction')->__('Normal'),
                'value' => 'normal'
            ),
            array(
                'label' => Mage::helper('itactica_calltoaction')->__('Italic'),
                'value' => 'italic'
            ),
            array(
                'label' => Mage::helper('itactica_calltoaction')->__('Oblique'),
                'value' => 'oblique'
            ),
        );
        if ($withEmpty) {
            array_unshift($options, array('label'=>'', 'value'=>''));
        }
        return $options;

    }
    /**
     * get options as array
     * @access public
     * @param bool $withEmpty
     * @return string
     */
    public function getOptionsArray($withEmpty = true) {
        $options = array();
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
    /**
     * get option text
     * @access public
     * @param mixed $value
     * @return string
     */
    public function getOptionText($value) {
        $options = $this->getOptionsArray();
        if (!is_array($value)) {
            $value = array($value);
        }
        $texts = array();
        foreach ($value as $v) {
            if (isset($options[$v])) {
                $texts[] = $options[$v];
            }
        }
        return implode(', ', $texts);
    }
}
