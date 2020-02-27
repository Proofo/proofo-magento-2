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

namespace Mageplaza\Proofo\Helper;

use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\Json\Helper\Data;
use Mageplaza\Proofo\Helper\Data as Helper;
use \Psr\Log\LoggerInterface as Logger;

class WebHookSync
{
    const CART_WEBHOOK = "cart";

    const ORDER_WEBHOOK = "order";

    const CUSTOMER_WEBHOOK = "customer";

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var Helper
     */
    protected $_helperData;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var $_secretKey
     * @cache
     */
    protected $_secretKey = null;

    /**
     * @var $_appId
     * @cache
     */
    protected $_appId = null;

    /**
     * WebHookSync constructor.
     *
     * @param Curl $curl
     * @param Data $jsonHelper
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        Data $jsonHelper,
        Helper $helper,
        Logger $logger
    )
    {
        $this->_curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->_helperData = $helper;
        $this->_logger = $logger;
    }

    /**
     * @return mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSecretKey ()
    {
        if (!$this->_secretKey) {
            $secretKey = $this->_helperData->getSecretKey();
            $this->_secretKey = $secretKey;

            return $secretKey;
        }

        return $this->_secretKey;
    }

    /**
     * @return mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppId ()
    {
        if (!$this->_appId) {
            $appId = $this->_helperData->getAppId();
            $this->_appId = $appId;

            return $appId;
        }

        return $this->_appId;
    }

    /**
     * @param $hookData
     * @param $type
     */
    public function syncToWebHook ($hookData, $type)
    {
        try {
            $sharedSecret = $this->getSecretKey();
            $appId = $this->getAppId();
            $body = $this->jsonHelper->jsonEncode($hookData);
            $generatedHash = base64_encode(hash_hmac('sha256', $body, $sharedSecret, true));
            $this->_curl->setHeaders([
                'Content-Type' => 'application/json',
                'X-Proofo-Hmac-Sha256' => $generatedHash,
                'X-Proofo-App-Id' => $appId
            ]);
            $this->_curl->post("https:///0be6e0ce.ngrok.io/webhook/$type", $body);
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }
}
