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

namespace Avada\Proofo\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\Helper\Data;
use Avada\Proofo\Helper\Data as Helper;

/**
 * Class WebHookSync
 *
 * @package Avada\Proofo\Helper
 */
class WebHookSync
{
    const APP_URL = 'https://app.proofo.io';
    const CART_WEBHOOK = 'cart';
    const ORDER_WEBHOOK = 'order';
    const CUSTOMER_WEBHOOK = 'customer';
    const CART_UPDATE_TOPIC = 'cart/update';
    const ORDER_CREATE_TOPIC = 'order/create';
    const CUSTOMER_CREATE_TOPIC = 'customer/create';

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
     * @var null
     */
    protected $_secretKey = null;

    /**
     * @var null
     */
    protected $_appId = null;

    /**
     * WebHookSync constructor.
     *
     * @param Curl $curl
     * @param Data $jsonHelper
     * @param Helper $helper
     */
    public function __construct(
        Curl $curl,
        Data $jsonHelper,
        Helper $helper
    )
    {
        $this->_curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->_helperData = $helper;
    }

    /**
     * @return mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSecretKey()
    {
        if (!$this->_secretKey) {
            $this->_secretKey = $this->_helperData->getSecretKey();
        }

        return $this->_secretKey;
    }

    /**
     * @return mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppId()
    {
        if (!$this->_appId) {
            $this->_appId = $this->_helperData->getAppId();
        }

        return $this->_appId;
    }

    /**
     * @param array $hookData
     * @param string $type
     * @param string $topic
     * @param bool $isTest
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function syncToWebHook($hookData, $type, $topic, $isTest = false)
    {
        $url = self::APP_URL;
        $sharedSecret = $this->getSecretKey();
        $appId = $this->getAppId();
        $body = $this->jsonHelper->jsonEncode($hookData);
        $generatedHash = base64_encode(hash_hmac('sha256', $body, $sharedSecret, true));
        $this->_curl->setHeaders([
            'Content-Type' => 'application/json',
            'X-Proofo-Hmac-Sha256' => $generatedHash,
            'X-Proofo-App-Id' => $appId,
            'X-Proofo-Topic' => $topic,
            'X-Proofo-Connection-Test' => $isTest
        ]);
        $this->_curl->post("$url/webhook/$type", $body);
        if ($this->_curl->getStatus() !== 200) {
            $body = $this->_curl->getBody();
            $bodyData = $this->jsonHelper->jsonDecode($body);
            throw new LocalizedException(__($bodyData['message']));
        }
    }

    /**
     * @param array $items
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function syncOrders($items)
    {
        $url = self::APP_URL;
        $sharedSecret = $this->getSecretKey();
        $appId = $this->getAppId();
        $body = $this->jsonHelper->jsonEncode($items);
        $generatedHash = base64_encode(hash_hmac('sha256', $body, $sharedSecret, true));

        $this->_curl->setHeaders([
            'Content-Type' => 'application/json',
            'X-Proofo-Hmac-Sha256' => $generatedHash,
            'X-Proofo-App-Id' => $appId
        ]);

        $this->_curl->post("$url/webhook/sync/orders", $body);
        $body = $this->_curl->getBody();
        $bodyData = $this->jsonHelper->jsonDecode($body);
        if (!$bodyData['success']) {
            throw new LocalizedException(__($bodyData['message']));
        }
    }

    /**
     * @param array $items
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function syncOrderStatistics($items)
    {
        $url = self::APP_URL;
        $sharedSecret = $this->getSecretKey();
        $appId = $this->getAppId();
        $body = $this->jsonHelper->jsonEncode($items);
        $generatedHash = base64_encode(hash_hmac('sha256', $body, $sharedSecret, true));

        $this->_curl->setHeaders([
            'Content-Type' => 'application/json',
            'X-Proofo-Hmac-Sha256' => $generatedHash,
            'X-Proofo-App-Id' => $appId
        ]);

        $this->_curl->post("$url/webhook/statistic/sync", $body);
        $body = $this->_curl->getBody();
        $bodyData = $this->jsonHelper->jsonDecode($body);
        if (!$bodyData['success']) {
            throw new LocalizedException(__($bodyData['message']));
        }
    }
}
