<?php
/**
 * Avada
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the avada.io license that is
 * available through the world-wide-web at this URL:
 * https://www.avada.io/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Avada
 * @package     Avada_Proofo
 * @copyright   Copyright (c) Avada (https://www.avada.io/)
 * @license     https://www.avada.io/LICENSE.txt
 */

namespace Avada\Proofo\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Avada\Proofo\Helper\Data as Helper;
use Magento\Checkout\Model\Cart;
use Avada\Proofo\Helper\WebHookSync;
use Avada\Proofo\Model\Config\Review;

/**
 * Class SyncReview
 * @package Avada\Proofo\Observer
 */
class SyncReview implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $_helperData;

    /**
     * @var WebHookSync
     */
    protected $_webHookSync;

    /**
     * SyncAddToCart constructor.
     * @param Helper $helper
     * @param Cart $cart
     * @param WebHookSync $webHookSync
     */
    public function __construct(
        Helper $helper,
        WebHookSync $webHookSync
    ) {
        $this->_helperData  = $helper;
        $this->_webHookSync = $webHookSync;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->_helperData->isEnabled() || !$this->_helperData->getSyncReviews()) {
                return $this;
            }

            $review = $observer->getEvent()->getDataObject();
            if ($this->_helperData->getSyncReviews() === Review::ONLY_CUSTOMER && !$review->getCustomerId()) {
                return $this;
            }

            $review->setIsLoadOptionId(true);

            $hookData = $this->_helperData->getReviewData($review);
            $hookData = ['data' => [$hookData]];
            $this->_webHookSync->syncReview($hookData);
            $review->setData('is_synced_proofo', 1)->save();
        } catch (Exception $e) {
            $this->_helperData->criticalLog($e->getMessage());
        }
    }
}
