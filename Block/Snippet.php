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

namespace Mageplaza\Proofo\Block;

use Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use Mageplaza\Proofo\Helper\Data;

/**
 * Class Snippet
 * @package Mageplaza\Proofo\Block
 */
class Snippet extends Template
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Snippet constructor.
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

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
}
