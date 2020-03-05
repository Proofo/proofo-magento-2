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

use \Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @type \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @type \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $isArea = [];

    /**
     * @var \Magento\Backend\App\Config
     */
    protected $backendConfig;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager
    )
    {
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;

        parent::__construct($context);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSecretKey() {
        $storeId = $this->getStoreId();

        return $this->getConfigValue('proofo/general/secret_key', $storeId);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppId() {
        $storeId = $this->getStoreId();

        return $this->getConfigValue('proofo/general/app_id', $storeId);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEnabledWebHooks() {
        $storeId = $this->getStoreId();
        $configValues = $this->getConfigValue('proofo/webhook/enabled_webhooks', $storeId);

        return $configValues ? preg_split("/\,/", $configValues) : [];
    }

    public function getBundleAsMultipleItems() {
        $storeId = $this->getStoreId();

        return $this->getConfigValue('proofo/webhook/bundle_as_multiple', $storeId);
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductImage($product)
    {
        $imageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

        return str_replace('\\', '/', $imageUrl);
    }

    /**
     * @param $message
     * @return null|void
     */
    public function criticalLog($message) {
        $this->_logger->critical($message);
    }

    /**
     * @param null $storeId
     * @return array|bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled($storeId = null)
    {
        $storeId = $this->getStoreId();

        return $this->getConfigValue('proofo/general/enabled', $storeId);
    }

    /**
     * @param $field
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
