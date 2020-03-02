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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Webhooks
 * @package Avada\Proofo\Model\Config
 */
class Webhooks implements ArrayInterface
{
    const ORDER_HOOK = 'orders_hook';

    const CART_HOOK = 'cart_hook';

    const SIGNUP_HOOK = 'signup_hook';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $optionArray[] = ['value' => '', 'label' => __('-- Please Select --')];

        foreach ($this->toArray() as $key => $value) {
            $optionArray[] = ['value' => $key, 'label' => $value];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ORDER_HOOK => __('Order hook'),
            self::CART_HOOK => __('Add-to-cart hook'),
            self::SIGNUP_HOOK => __('Sign-up hook')
        ];
    }
}
