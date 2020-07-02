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

namespace Avada\Proofo\Block;

use Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Json\Helper\Data;

/**
 * Class OrderSuccess
 * @package Avada\Proofo\Block
 */
class OrderSuccess extends Template
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var Order|null
     */
    protected $_order = null;

    /**
     * OrderSuccess constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param OrderFactory $orderFactory
     * @param Context $context
     * @param Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderFactory $orderFactory,
        Context $context,
        Data $jsonHelper,
        array $data = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->jsonHelper = $jsonHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return Order|null
     */
    public function getOrderItems()
    {
        if (!$this->_order) {
            $this->_order = $this->_orderFactory->create()
                ->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
        }

        $lineItems = [];
        $orderItems = $this->_order->getAllVisibleItems();
        /**
         * @var \Magento\Sales\Model\Order\Item $item
         */
        foreach ($orderItems as $item) {
            $product = $item->getProduct();
            if ($product) {
                $lineItems[] = [
                    'title' => $item->getName(),
                    'line_price' => $item->getBaseRowTotal(),
                    'product_id' => $product->getId()
                ];
            }
        }

        return $this->jsonHelper->jsonEncode($lineItems);
    }
}
