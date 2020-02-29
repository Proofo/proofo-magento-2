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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Proofo\Helper\Data as Helper;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Checkout\Model\Cart;
use \Mageplaza\Proofo\Helper\WebHookSync;
use Mageplaza\Proofo\Model\Config\Webhooks;

/**
 * todo : Fix first cart item issue
 *
 * Class SyncAddToCart
 * @package Mageplaza\Proofo\Observer
 */
class SyncAddToCart implements ObserverInterface
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
     * @var Cart
     */
    protected $_cart;

    /**
     * @var WebHookSync
     */
    protected $_webHookSync;

    /**
     * SyncAddToCart constructor.
     *
     * @param Helper $helper
     * @param CountryFactory $countryFactory
     * @param Cart $cart
     * @param WebHookSync $webHookSync
     */
    public function __construct(
        Helper $helper,
        CountryFactory $countryFactory,
        Cart $cart,
        WebHookSync $webHookSync
    )
    {
        $this->_helperData = $helper;
        $this->_countryFactory = $countryFactory;
        $this->_cart = $cart;
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
            if (!in_array(Webhooks::CART_HOOK, $enabledWebHooks)) {
                return $this;
            }

            /**
             * @var $product \Magento\Catalog\Model\Product
             */
            $product = $observer->getEvent()->getProduct();
            $quote = $this->_cart->getQuote();
            $cartItems = $quote->getAllVisibleItems();

            $cartAllProductIds = [];
            foreach ($cartItems as $item) {
                $cartAllProductIds[] = $item->getProduct()->getId();
            }
            if (!in_array($product->getId(), $cartAllProductIds)) {
                /**
                 * @var $item \Magento\Sales\Model\Order\Item
                 */
                $addedProduct = [
                    "product_name" => $product->getName(),
                    "price" => $product->getPrice(),
                    "product_link" => $product->getProductUrl(),
                    "product_image" => $this->_helperData->getProductImage($product),
                    "product_id" => $product->getId()
                ];
                $updatedAt = $quote->getUpdatedAt() === null
                    ? date("c")
                    : date("c", strtotime($quote->getUpdatedAt()));
                $hookData = [
                    "id" => $quote->getId(),
                    "updated_at" => $updatedAt,
                    "added_item" => $addedProduct
                ];
                $this->_webHookSync->syncToWebHook($hookData, WebHookSync::CART_WEBHOOK, WebHookSync::CART_UPDATE_TOPIC);
            }


        } catch (\Exception $e) {
            $this->_helperData->criticalLog($e->getMessage());
        }
    }
}
