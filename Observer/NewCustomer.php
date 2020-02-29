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

class NewCustomer implements ObserverInterface
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
     * NewCustomer constructor.
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
            if (!in_array(Webhooks::SIGNUP_HOOK, $enabledWebHooks)) {
                return $this;
            }
            /**
             * @var $customer \Magento\Customer\Model\Data\Customer
             */
            $customer = $observer->getEvent()->getCustomer();
            $hookData = [
                "id" => $customer->getId(),
                "email" => $customer->getEmail(),
                "created_at" => $customer->getCreatedAt(),
                "first_name" => $customer->getFirstname(),
                "last_name" => $customer->getLastname()
            ];
            $this->_webHookSync->syncToWebHook($hookData, WebHookSync::CUSTOMER_WEBHOOK, WebHookSync::CUSTOMER_CREATE_TOPIC);
        } catch (\Exception $e) {
            $this->_helperData->criticalLog($e->getMessage());
        }
    }
}
