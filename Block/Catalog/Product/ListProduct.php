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

namespace Avada\Proofo\Block\Catalog\Product;

use Magento\Catalog\Block\Product\ListProduct as CoreListProduct;

/**
 * Class ListProduct
 * @package Avada\Proofo\Block\Catalog\Product
 */
class ListProduct extends CoreListProduct
{
    /**
     * @return false|string
     */
    public function getProducts ()
    {
        $productCollection = $this->getLoadedProductCollection();
        $productJsonData = [];

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $productJsonData[] = [
                "id" => (int)$product->getId(),
                "handle" => $product->getProductUrl()
            ];
        }

        return json_encode($productJsonData);
    }
}
