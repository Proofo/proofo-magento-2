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
            $lineItems = [];
            if (in_array($product->getId(), $cartAllProductIds)) {
                /**
                 * @var $item \Magento\Sales\Model\Order\Item
                 */
                foreach ($cartItems as $item) {
                    $lineItems[] = [
                        "title" => $item->getName(),
                        "quantity" => $item->getQtyOrdered(),
                        "price" => $item->getPrice(),
                        "product_link" => $item->getProduct()->getProductUrl(),
                        "product_image" => $this->_helperData->getProductImage($item->getProduct())
                    ];
                }
            }

            $hookData = [
                "id" => $quote->getId(),
                "created_at" => $quote->getCreatedAt(),
                "line_items" => $lineItems
            ];
            $this->_webHookSync->syncToWebHook($hookData, WebHookSync::CART_WEBHOOK);
        } catch (\Exception $e) {
            $this->_helperData->criticalLog($e->getMessage());
        }
    }
}
