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

namespace Avada\Proofo\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Webhooks
 * @package Avada\Proofo\Model\Config
 */
class Webhooks implements OptionSourceInterface
{
    const ORDER_HOOK  = 'orders_hook';
    const CART_HOOK   = 'cart_hook';
    const SIGNUP_HOOK = 'signup_hook';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];

        foreach ($this->getOptionArray() as $key => $value) {
            $optionArray[] = ['value' => $key, 'label' => $value];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::ORDER_HOOK  => __('New Order'),
            self::CART_HOOK   => __('Customer Add item to cart'),
            self::SIGNUP_HOOK => __('New Customer')
        ];
    }
}
