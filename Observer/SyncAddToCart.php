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
use Avada\Proofo\Model\Config\Webhooks;

/**
 * todo : Fix first cart item issue
 *
 * Class SyncAddToCart
 * @package Avada\Proofo\Observer
 */
class SyncAddToCart implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $_helperData;

    /**
     * @var Cart
     */
    protected $_cart;

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
        Cart $cart,
        WebHookSync $webHookSync
    ) {
        $this->_helperData  = $helper;
        $this->_cart        = $cart;
        $this->_webHookSync = $webHookSync;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->_helperData->isEnabled()) {
                return $this;
            }

            $enabledWebHooks = $this->_helperData->getEnabledWebHooks();
            if (!in_array(Webhooks::CART_HOOK, $enabledWebHooks, true)) {
                return $this;
            }

            /**
             * @var \Magento\Catalog\Model\Product $product
             */
            $product = $observer->getEvent()->getProduct();

            /**
             * @var \Magento\Quote\Model\Quote\Item|string $quoteItem
             */
            $quoteItem = $observer->getEvent()->getQuoteItem();
            $quote     = $this->_cart->getQuote();

            if ((int)$quoteItem->getQty() === 1) {
                $addedProducts = [];
                if ($quoteItem->getHasChildren() &&
                    $quoteItem->isChildrenCalculated() &&
                    $this->_helperData->getBundleAsMultipleItems()
                ) {
                    /** @var \Magento\Sales\Model\Order\Item $childItem */
                    foreach ($quoteItem->getChildren() as $childItem) {
                        if ($childItem->getQtyOrdered() === 0) continue;

                        $childProduct    = $childItem->getProduct();
                        $addedProducts[] = [
                            'product_name'  => $childProduct->getName(),
                            'price'         => $childProduct->getPrice(),
                            'product_link'  => $childProduct->getProductUrl(),
                            'product_image' => $this->_helperData->getProductImage($childProduct),
                            'product_id'    => $childProduct->getId()
                        ];
                    }
                } else {
                    $addedProducts[] = [
                        'product_name'  => $product->getName(),
                        'price'         => $product->getPrice(),
                        'product_link'  => $product->getProductUrl(),
                        'product_image' => $this->_helperData->getProductImage($product),
                        'product_id'    => $product->getId()
                    ];
                }

                $updatedAt = $quote->getUpdatedAt() === null
                    ? date('c')
                    : date('c', strtotime($quote->getUpdatedAt()));
                $hookData  = [
                    'id'         => $quote->getId(),
                    'updated_at' => $updatedAt,
                    'line_items' => $addedProducts
                ];
                $this->_webHookSync->syncToWebHook(
                    $hookData,
                    WebHookSync::CART_WEBHOOK,
                    WebHookSync::CART_UPDATE_TOPIC
                );
            }
        } catch (Exception $e) {
            $this->_helperData->criticalLog($e->getMessage());
        }
    }
}
