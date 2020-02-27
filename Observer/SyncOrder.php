<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Proofo
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Proofo\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Proofo\Helper\Data as Helper;
use \Magento\Directory\Model\CountryFactory;
use \Mageplaza\Proofo\Helper\WebHookSync;

class SyncOrder implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $_helperData;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var WebHookSync
     */
    protected $_webHookSync;

    /**
     * SyncOrder constructor.
     *
     * @param Helper $helper
     * @param CountryFactory $countryFactory
     * @param WebHookSync $webHookSync
     */
    public function __construct(
        Helper $helper,
        CountryFactory $countryFactory,
        WebHookSync $webHookSync
    )
    {
        $this->_helperData = $helper;
        $this->_countryFactory = $countryFactory;
        $this->_webHookSync = $webHookSync;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $order  \Magento\Sales\Model\Order
         */
        $order = $observer->getEvent()->getOrder();

        /**
         * @var $billingAddress \Magento\Sales\Api\Data\OrderAddressInterface
         */
        $billingAddress = $order->getBillingAddress();
        $country = $this->_countryFactory->create()->load($billingAddress->getCountryId());
        $orderItems = $order->getAllVisibleItems();

        $lineItems = [];
        /**
         * @var $item \Magento\Sales\Model\Order\Item
         */
        foreach ($orderItems as $item) {
            $lineItems[] = [
                "title" => $item->getName(),
                "quantity" => $item->getQtyOrdered(),
                "price" => $item->getPrice(),
                "product_link" => $item->getProduct()->getProductUrl(),
                "product_image" => $this->_helperData->getProductImage($item->getProduct())
            ];
        }
        $hookData = [
            "billing_address" => [
                "city" => $billingAddress->getCity(),
                "country" => $country->getName(),
                "first_name" => $order->getCustomerFirstname(),
                "last_name" => $order->getCustomerLastname(),
            ],
            "created_at" => $order->getCreatedAt(),
            "line_items" => $lineItems
        ];
        $this->_webHookSync->syncToWebHook($hookData, WebHookSync::ORDER_WEBHOOK);
    }
}
