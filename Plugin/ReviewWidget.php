<?php

namespace Avada\Proofo\Plugin;

class ReviewWidget
{
    /**
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $subject
     * @param $result
     * @return string
     */
    public function afterGetPagerHtml(\Magento\CatalogWidget\Block\Product\ProductsList $subject, $result)
    {
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

    /**
     * @param \Magento\Catalog\Block\Product\AbstractProduct $subject
     * @param $result
     * @return string
     */
    public function afterGetReviewsSummaryHtml(\Magento\Catalog\Block\Product\AbstractProduct $subject, $result) {
        return '';
    }
}
