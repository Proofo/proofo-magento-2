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

namespace Mageplaza\Proofo\Controller\Adminhtml\Webhook;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\Json\Helper\Data;

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
     * @var Data
     */
    protected $jsonHelper;

    /**
     * Sync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Curl $curl
     * @param JsonFactory $jsonResultFactory
     * @param Data $jsonHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Curl $curl,
        JsonFactory $jsonResultFactory,
        Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_curl      = $curl;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->jsonHelper = $jsonHelper;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_curl->setHeaders([
            'Content-Type' => 'application/json'
        ]);
        $body = $this->jsonHelper->jsonEncode(["name" => "Thomas"]);
        $this->_curl->post('https://e7728858.ngrok.io/webhook/order', $body);
        $response = json_decode($this->_curl->getBody());
        $result = $this->jsonResultFactory->create();
        $result->setData($response);

        return $result;
    }
}
