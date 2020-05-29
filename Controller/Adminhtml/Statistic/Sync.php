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

namespace Avada\Proofo\Controller\Adminhtml\Statistic;

use Avada\Proofo\Controller\Adminhtml\Webhook\AbstractWebHook;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Avada\Proofo\Helper\WebHookSync;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderFactory;
use Magento\Sales\Model\Order;
use Avada\Proofo\Helper\Data as ProofoHelper;
use Magento\Directory\Model\CountryFactory;

/**
 * Class Sync
 * @package Avada\Proofo\Controller\Adminhtml\Statistic
 */
class Sync extends AbstractWebHook
{
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var WebHookSync
     */
    protected $_webHookSync;

    /**
     * @var OrderFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var ProofoHelper
     */
    protected $_helperData;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * Sync constructor.
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param WebHookSync $webHookSync
     * @param OrderFactory $orderFactory
     * @param ProofoHelper $helper
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        WebHookSync $webHookSync,
        OrderFactory $orderFactory,
        ProofoHelper $helper,
        CountryFactory $countryFactory
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->_webHookSync = $webHookSync;
        $this->_orderCollectionFactory = $orderFactory;
        $this->_helperData = $helper;
        $this->_countryFactory = $countryFactory;

        parent::__construct($context, $helper);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $storeId = $this->getStoreId();

            $orders = $this->_orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter("store_id", $storeId)
                ->setOrder(
                    'entity_id',
                    'desc'
                );

            $items = [];
            /**
             * @var Order $order
             */
            foreach ($orders as $order) {
                $orderLineItems = $order->getAllVisibleItems();

                /**
                 * @var \Magento\Sales\Model\Order\Item $item
                 */
                foreach ($orderLineItems as $item) {
                    if ($item->getHasChildren() &&
                        $item->isChildrenCalculated() &&
                        $this->_helperData->getBundleAsMultipleItems()
                    ) {
                        /** @var \Magento\Sales\Model\Order\Item $childItem */
                        foreach ($item->getChildrenItems() as $childItem) {
                            /** @var \Magento\Catalog\Model\Product $childProduct */
                            $childProduct = $childItem->getProduct();
                            $items[] = $this->formatItemData($childProduct, $order);
                        }
                    } else {
                        $product = $item->getProduct();
                        if ($product) {
                            $items[] = $this->formatItemData($product, $order);
                        }
                    }
                }
            }

            $this->_webHookSync->syncOrderStatistics($items, $storeId);

            $result = $this->jsonHelper->jsonEncode([
                'status' => true,
                'content' => __('Sync successfully')
            ]);
            return $this->getResponse()->representJson($result);
        } catch (\Exception $e) {
            $result = $this->jsonHelper->jsonEncode([
                'status' => false,
                'content' => $e->getMessage()
            ]);
            return $this->getResponse()->representJson($result);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param Order $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatItemData($product, $order)
    {
        return [
            'orderId' => $order->getId(),
            'name' => $product->getName(),
            'productImage' => $this->_helperData->getProductImage($product),
            'productLink' => $product->getProductUrl(),
            'productId' => $product->getId(),
            'timestamp' => $order->getCreatedAt() === null
                ? date('c')
                : date('c', strtotime($order->getCreatedAt())),
            'title' => $product->getName(),
            'sku' => $product->getSku(),
            'productHandle' => null
        ];
    }
}
