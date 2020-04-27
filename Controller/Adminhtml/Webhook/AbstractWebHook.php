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

namespace Avada\Proofo\Controller\Adminhtml\Webhook;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Avada\Proofo\Helper\Data as ProofoHelper;

abstract class AbstractWebHook extends Action
{
    /**
     * @var ProofoHelper
     */
    protected $_helperData;

    /**
     * AbstractWebHook constructor.
     * @param Context $context
     * @param ProofoHelper $helper
     */
    public function __construct(
        Context $context,
        ProofoHelper $helper
    )
    {
        $this->_helperData = $helper;

        parent::__construct($context);
    }


    /**
     * If no store id param provided, get the default store id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_request->getParam("store")
            ? $this->_request->getParam("store")
            : $this->_helperData->getStoreId();
    }
}