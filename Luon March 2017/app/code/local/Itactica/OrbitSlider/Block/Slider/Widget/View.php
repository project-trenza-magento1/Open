<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_OrbitSlider
 * @copyright   Copyright (c) 2014-2016 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

class Itactica_OrbitSlider_Block_Slider_Widget_View extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    protected $_htmlTemplate = 'itactica_orbitslider/view.phtml';

    const FORM_KEY_PLACEHOLDER = '%%form_key_placeholder%%';

    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        if (Mage::getStoreConfigFlag('itactica_orbitslider/cache/cache_enabled')) {
            $this->addData(array('cache_lifetime' => false));
            $this->addCacheTag(array(
                Mage_Core_Model_Store::CACHE_TAG,
                Mage_Cms_Model_Block::CACHE_TAG
            ));
        }
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $identifier = $this->getData('slider_id');
        $cacheArray = array(
            'ORBIT_SLIDER_'.strtoupper($identifier),
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::app()->getStore()->getCurrentCurrencyCode(),
            Mage::getSingleton('customer/session')->isLoggedIn()
        );

        return $cacheArray;
    }

    /**
     * Prepare slider for widget
     * @access protected
     * @return Itactica_OrbitSlider_Block_Slider_Widget_View
     */
    protected function _beforeToHtml() {
        parent::_beforeToHtml();
        $sliderId = $this->getData('slider_id');
        if ($sliderId) {
            $slider = Mage::getModel('itactica_orbitslider/slider')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($sliderId);
            if ($slider->getStatus()) {
                $this->setCurrentSlider($slider);
                $this->setTemplate($this->_htmlTemplate);
            }
        }
        return $this;
    }

    /**
     * Replace form_key by a placeholder
     * This prevent the block caching the form_key of the first user that refresh cache
     * @access protected
     * @return Itactica_OrbitSlider_Block_Slider_Widget_View
     */
    protected function _toHtml() {
        $html = parent::_toHtml();
        $session = Mage::getSingleton('core/session');
        $formKey = $session->getFormKey();

        $html = str_replace(
            $formKey,
            self::FORM_KEY_PLACEHOLDER,
            $html
        );
        return $html;
    }

    /**
     * Replace placeholder by the user's form_key
     * @access protected
     * @param string $html
     * @return Itactica_OrbitSlider_Block_Slider_Widget_View
     */
    protected function _afterToHtml($html) {
        $session = Mage::getSingleton('core/session');
        $formKey = $session->getFormKey();
        
        $html = str_replace(
            self::FORM_KEY_PLACEHOLDER,
            $formKey,
            $html
        );
        return $html;
    }

    /**
     * get slides collection
     * @access public
     * @param int $sliderId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getSlides($sliderId)
    {
        $collection = Mage::getModel('itactica_orbitslider/slides')
            ->getCollection()
            ->addFieldToFilter('status', 1);
        $select = $collection->getSelect()->join(
                array('orbitslider_slider_slides' => $collection->getTable('itactica_orbitslider/slider_slide')),
                'main_table.entity_id = orbitslider_slider_slides.slide_id',
                array('slide_id')
            )
            ->where('orbitslider_slider_slides.slider_id = (?)', $sliderId)
            ->order('orbitslider_slider_slides.position ASC');

        return $collection;
    }

    /**
     * get css translations for text box alignment
     * @access public
     * @param Mage_Eav_Model_Entity_Collection_Abstract $slide
     * @return string
     */
    public function getCssTranslations($slide)
    {
        if ($slide->getVerticalAlignment() && $slide->getTextBlockAlignment() == 'center') {
            $style = ' -ms-transform: translate(-50%,0); ';
            $style .= '-moz-transform: translate(-50%,0); ';
            $style .= '-o-transform: translate(-50%,0); ';
            $style .= '-webkit-transform: translate(-50%,0); ';
            $style .= 'transform: translate(-50%,0); ';
            $style .= 'margin-left: 0; ';
            $style .= 'top: ' . $slide->getTextBlockTop() . '; ';
            $style .= 'left: 50%;';
            return $style;
        } else if (!$slide->getVerticalAlignment() && $slide->getTextBlockAlignment() == 'center') {
            return ' -ms-transform: translate(-50%,-50%); -moz-transform: translate(-50%,-50%); -o-transform: translate(-50%,-50%); -webkit-transform: translate(-50%,-50%); transform: translate(-50%,-50%);';
        } else if ($slide->getVerticalAlignment() && $slide->getTextBlockAlignment() != 'center') {
            return ' -ms-transform: translateY(0); -moz-transform: translateY(0); -o-transform: translateY(0); -webkit-transform: translateY(0); transform: translateY(0); top: ' . $slide->getTextBlockTop() . ';';
        } else {
            return;
        }
    }
}
