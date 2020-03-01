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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Avada\Proofo\Controller\Adminhtml\Webhook;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use Mageplaza\Avada\Helper\Data as Helper;

class Sync extends Action
{
    /**
     * Page result factory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var Helper
     */
    protected $_helperData;

    /**
     * Sync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Curl $curl
     * @param JsonFactory $jsonResultFactory
     * @param JsonHelper $jsonHelper
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Curl $curl,
        JsonFactory $jsonResultFactory,
        JsonHelper $jsonHelper,
        Helper $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_curl      = $curl;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_helperData = $helper;

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $result = $this->jsonResultFactory->create();
            $result->setData([
                'data' => $this->_helperData->getEnabledWebHooks()
            ]);

            return $result;
        } catch (\Exception $e) {
            $result = $this->jsonResultFactory->create();
            $result->setData([
                'message' => $e->getMessage()
            ]);

            return $result;
        }
    }
}
