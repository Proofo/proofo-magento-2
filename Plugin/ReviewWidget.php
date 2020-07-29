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
 * Class ReviewWidget
 * @package Avada\Proofo\Plugin
 */
class ReviewWidget
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
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetPagerHtml(\Magento\CatalogWidget\Block\Product\ProductsList $subject, $result)
    {
        if ($this->_helperData->isPhotoReviewsEnabled()) {
            $productCollection = $subject->getProductCollection();
            $productJsonData = [];

            /** @var $product \Magento\Catalog\Model\Product */
            foreach ($productCollection as $product) {
                $productJsonData[] = [
                    "id" => (int)$product->getId(),
                    "handle" => $product->getProductUrl()
                ];
            }
            $jsonProducts = json_encode($productJsonData);

            return $result . "
            <script>
                window.AVADA_PR_PRODUCT_COLLECTION = window.AVADA_PR_PRODUCT_COLLECTION || [];
                window.AVADA_PR_PRODUCT_COLLECTION = $jsonProducts
            </script>
        ";
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Block\Product\AbstractProduct $subject
     * @param $result
     * @return string
     */
    public function afterGetReviewsSummaryHtml(\Magento\Catalog\Block\Product\AbstractProduct $subject, $result)
    {
        return '';
    }
}
