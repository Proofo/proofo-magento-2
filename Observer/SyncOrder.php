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

namespace Mageplaza\Proofo\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\Json\Helper\Data;

class SyncOrder implements ObserverInterface
{
    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * SyncOrder constructor.
     *
     * @param Curl $curl
     * @param Data $jsonHelper
     */
    public function __construct(
        Curl $curl,
        Data $jsonHelper
    )
    {
        $this->_curl = $curl;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $secret = "d02eabd29d9f37eb01d05d0763a23bdc";
        $order = $observer->getEvent()->getOrder();
        /**
         * @var \Magento\Sales\Model\Order
         */
        $body = json_encode(["id" => $order->getIncrementId()]);

        $generatedHash = base64_encode(hash_hmac('sha256', $body, $secret, true));

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/proofo.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($generatedHash);

        $this->_curl->setHeaders([
            'Content-Type' => 'application/json',
            'X-Proofo-Hmac-Sha256' => $generatedHash
        ]);
        $logger->info($body);
        $this->_curl->post('https://e7728858.ngrok.io/webhook/order', $body);
    }
}
