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

namespace Mageplaza\Proofo\Model\Config;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Showlink
 * @package Mageplaza\Affiliate\Model\Config\Source
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
