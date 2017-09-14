<?php

/**
 * Intenso Premium Theme
 *
 * @category    Itactica
 * @package     Itactica_Intenso
 * @copyright   Copyright (c) 2014-2016 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

class Itactica_Intenso_Model_Config_Adaptiveresizeposition
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '1',
                'label' => Mage::helper('itactica_intenso')->__('Center')),

            array('value' => '2',
                'label' => Mage::helper('itactica_intenso')->__('Top')),

            array('value' => '3',
                'label' => Mage::helper('itactica_intenso')->__('Bottom'))
        );
    }
}