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

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Review\Model\ResourceModel\Rating\Option as RatingOption;

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
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var RatingOption
     */
    protected $ratingOption;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param EncryptorInterface $encryptor
     * @param ProductRepository $productRepository
     * @param CustomerRegistry $customerRegistry
     * @param RatingFactory $ratingFactory
     * @param RatingOption $ratingOption
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        EncryptorInterface $encryptor,
        ProductRepository $productRepository,
        CustomerRegistry $customerRegistry,
        RatingFactory $ratingFactory,
        RatingOption $ratingOption
    ) {
        $this->storeManager      = $storeManager;
        $this->objectManager     = $objectManager;
        $this->encryptor         = $encryptor;
        $this->productRepository = $productRepository;
        $this->customerRegistry  = $customerRegistry;
        $this->ratingFactory     = $ratingFactory;
        $this->ratingOption      = $ratingOption;

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
     * @param string $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSyncReviews($storeId = null)
    {
        return $this->getConfigValue('proofo/webhook/reviews', $this->getStoreId($storeId));
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
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * @param int $id
     * @return \Magento\Customer\Model\Customer|string
     */
    public function getCustomerById($id)
    {
        try {
            return $this->customerRegistry->retrieve($id);
        } catch (Exception $exception) {
            return '';
        }
    }

    /**
     * @param Review $review
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReviewData(Review $review)
    {
        $productId = $review->getEntityPkValue();
        $product   = $this->productRepository->getById($productId);
        $customer  = $this->getCustomerById($review->getCustomerId());
        $firstName = $review->getNickname();
        $lastName  = false;
        $email     = '';
        if ($customer && $customer->getId()) {
            $firstName = $customer->getFirstname();
            $lastName  = $customer->getLastname();
            $email     = $customer->getEmail();
        }

        return [
            'email'     => $email ,
            'title'     => $review->getTitle(),
            'content'   => $review->getDetail(),
            'rate'      => $this->getAverageRates($review),
            'product'   => [
                'image'       => $this->getProductImage($product),
                'productLink' => $product->getProductUrl(),
                'title'       => $product->getName()
            ],
            'productId' => $review->getEntityPkValue(),
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'createdAt' => $review->getCreatedAt(),
            'status'    => $this->getReviewStatuses($review->getStatusId())
        ];
    }

    /**
     * @param Review $review
     * @return float|int
     */
    public function getAverageRates(Review $review)
    {
        if ($review->getIsLoadOptionId() && $review->getRatings()) {
            $value = 0;
            foreach ($review->getRatings() as $optionId) {
                $option = $this->ratingOption->loadDataById($optionId);
                if (isset($option['value'])) {
                    $value += $option['value'];
                }
            }

            return $value === 0 ?: ($value / count($review->getRatings()));
        }

        $ratingSummary = $this->ratingFactory->create()->getReviewSummary($review->getReviewId());
        if ($ratingSummary->getCount()) {
            $percent = ceil($ratingSummary->getSum() / ($ratingSummary->getCount()));

            return ($percent / 100) * 5;
        }

        return 0;
    }

    /**
     * @param int $id
     * @return int|mixed
     */
    public function getReviewStatuses($id)
    {

        $status = [
            Review::STATUS_APPROVED     => 'approved',
            Review::STATUS_PENDING      => 'pending',
            Review::STATUS_NOT_APPROVED => 'disapproved'
        ];

        if (isset($status[$id])) {
            return $status[$id];
        }

        return 0;
    }
}

