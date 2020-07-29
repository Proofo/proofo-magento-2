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

namespace Avada\Proofo\Plugin;

use Avada\Proofo\Helper\Data as Helper;

/**
 * Class ReviewTabTitle
 * @package Avada\Proofo\Plugin
 */
class ReviewTabTitle
{
    /**
     * @var Helper
     */
    protected $_helperData;

    /**
     * ReviewTabTitle constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->_helperData = $helper;
    }

    /**
     * @param \Magento\Review\Block\Product\Review $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSetTabTitle(\Magento\Review\Block\Product\Review $subject, $result)
    {
        if ($this->_helperData->isPhotoReviewsEnabled()) {
            $title = __('Reviews %1', '<span class="Avada-Pr__Counter"></span>');
            $subject->setTitle($title);
        }

        return $result;
    }
}
