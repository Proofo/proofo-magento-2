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
use Avada\Proofo\Helper\WebHookSync;
use Avada\Proofo\Model\Config\Webhooks;

/**
 * Class NewCustomer
 * @package Avada\Proofo\Observer
 */
class NewCustomer implements ObserverInterface
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
     * NewCustomer constructor.
     * @param Helper $helper
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
            if (!$this->_helperData->isEnabled()) {
                return $this;
            }

            $enabledWebHooks = $this->_helperData->getEnabledWebHooks();
            if (!in_array(Webhooks::SIGNUP_HOOK, $enabledWebHooks, true)) {
                return $this;
            }

            /**
             * @var \Magento\Customer\Model\Data\Customer $customer
             */
            $customer = $observer->getEvent()->getCustomer();
            $hookData = [
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'created_at' => date('c'),
                'first_name' => $customer->getFirstname(),
                'last_name' => $customer->getLastname()
            ];
            $this->_webHookSync->syncToWebHook(
                $hookData,
                WebHookSync::CUSTOMER_WEBHOOK,
                WebHookSync::CUSTOMER_CREATE_TOPIC
            );
        } catch (Exception $e) {
            $this->_helperData->criticalLog($e->getMessage());
        }
    }
}
