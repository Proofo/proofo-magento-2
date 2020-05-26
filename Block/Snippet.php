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
use Avada\Proofo\Helper\Data;
use Avada\Proofo\Helper\WebHookSync;

/**
 * Class Snippet
 * @package Avada\Proofo\Block
 */
class Snippet extends Template
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Snippet constructor.
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        $this->_registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppId()
    {
        return $this->helperData->getAppId();
    }

    /**
     * @return array|bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled()
    {
        return $this->helperData->isEnabled();
    }

    /**
     * @return string
     */
    public function getAppUrl()
    {
        return WebHookSync::APP_URL;
    }

    /**
     * @return mixed
     */
    public function getCurrentProductId()
    {
        $currentProduct = $this->_registry->registry('current_product');
        if ($currentProduct) {
            return $this->_registry->registry('current_product')->getId();
        }

        return json_encode(null);
    }

    /**
     * @return false|string
     */
    public function isProductPage()
    {
        return json_encode(
            $this->_request->getFullActionName() === 'catalog_product_view'
        );
    }
}
