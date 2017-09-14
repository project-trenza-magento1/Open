<?php
/**
 * Trenza - MINIFY HTML
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the The MIT License (MIT)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://blog.gaiterjones.com/dropdev/magento/LICENSE.txt
 *
 * @category    Trenza
 * @package     Trenza_Minifyhtml
 * @copyright   Copyright (c) 2015 Trenza
 * @license     http://blog.gaiterjones.com/dropdev/magento/LICENSE.txt  The MIT License (MIT)
 */


class Trenza_Minifyhtml_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isActive()
    {
        return Mage::getStoreConfig('minifyhtml/general/enable_minify');
    }
}