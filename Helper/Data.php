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

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Avada\Proofo\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var array
     */
    protected $isArea = [];

    /**
     * @var null
     */
    protected $backendConfig = null;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        EncryptorInterface $encryptor
    ) {
        $this->storeManager  = $storeManager;
        $this->objectManager = $objectManager;
        $this->encryptor     = $encryptor;

        parent::__construct($context);
    }

    /**
     * @param number $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId($storeId = null)
    {
        if ($storeId !== null && $storeId !== 0) {
            return $storeId;
        }

        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param number $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSecretKey($storeId = null)
    {
        $secretKey = $this->getConfigValue('proofo/general/secret_key', $this->getStoreId($storeId));

        return $this->encryptor->decrypt($secretKey);
    }

    /**
     * @param string $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppId($storeId = null)
    {
        return $this->getConfigValue('proofo/general/app_id', $this->getStoreId($storeId));
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEnabledWebHooks()
    {
        $configValues = $this->getConfigValue('proofo/webhook/enabled_webhooks', $this->getStoreId());

        return $configValues ? preg_split("/\,/", $configValues) : [];
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBundleAsMultipleItems()
    {
        return $this->getConfigValue('proofo/webhook/bundle_as_multiple', $this->getStoreId());
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductImage($product)
    {
        $baseUrl  = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $imageUrl = $baseUrl . 'catalog/product/' . $product->getImage();

        return str_replace('\\', '/', $imageUrl);
    }

    /**
     * @param string $message
     * @return null|void
     */
    public function criticalLog($message)
    {
        $this->_logger->critical($message);
    }

    /**
     * @return array|bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled()
    {
        return $this->getConfigValue('proofo/general/enabled', $this->getStoreId());
    }

    /**
     * @param string $field
     * @param null $scopeValue
     * @param string $scopeType
     *
     * @return array|mixed
     */
    public function getConfigValue($field, $scopeValue = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if (!$this->isArea() && is_null($scopeValue)) {
            /** @var \Magento\Backend\App\Config $backendConfig */
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager->get('Magento\Backend\App\ConfigInterface');
            }

            return $this->backendConfig->getValue($field);
        }

        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }

    /**
     * @param string $area
     *
     * @return mixed
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var \Magento\Framework\App\State $state */
            $state = $this->objectManager->get('Magento\Framework\App\State');

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (\Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }
}
