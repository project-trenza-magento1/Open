<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


class Amasty_Segments_MainController extends Mage_Core_Controller_Front_Action
{
    public function testAction()
    {
        $process = Mage::getSingleton('index/indexer')->getProcessByCode("amsegemnts_indexer");
        $process->reindexEverything();
    }
}
