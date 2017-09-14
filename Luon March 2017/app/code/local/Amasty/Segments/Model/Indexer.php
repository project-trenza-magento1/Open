<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


class Amasty_Segments_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    const EVENT_MATCH_RESULT_KEY = 'amsegments_match_result';

    public function getName()
    {
        return 'Customers Segmentation';
    }

    public function getDescription()
    {
        return 'Customers Segmentation';
    }

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return true;
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return true;
    }

    /**
     * match whether the reindexing should be fired
     *
     * @param Mage_Index_Model_Event $event
     *
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        return true;
    }

    public function reindexAll($isClearAll = false)
    {
        if ($isClearAll) {
            Amasty_Segments_Model_Mysql4_Abstract::getLastExecuted(); // call to save current execution time
        }
        $this->doReindexAll($isClearAll);
    }

    function doReindexAll($isClearAll = false)
    {
        Mage::getModel("amsegments/customer")->getResource()->bulkUpdate($isClearAll);
        Mage::getModel("amsegments/order")->getResource()->bulkUpdate($isClearAll);
        Mage::getModel("amsegments/cart")->getResource()->bulkUpdate();

        $segments = Mage::getModel("amsegments/segment")->getCollection();

        foreach ($segments as $segment) {
            if ($segment->getIsActive() == Amasty_Segments_Model_Segment::STATUS_ACTIVE) {
                $segment->process();
            }
        }
    }
}
