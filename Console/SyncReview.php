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
namespace Avada\Proofo\Console;

use Avada\Proofo\Helper\Data as Helper;
use Avada\Proofo\Helper\WebHookSync;
use Magento\Review\Model\RatingFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;

/**
 * Class SyncReview
 * @package Avada\Proofo\Console
 */
class SyncReview extends Command
{
    /**
     * @var CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var WebHookSync
     */
    protected $_webHookSync;

    /**
     * @var Helper
     */
    protected $_helperData;

    /**
     * @var object
     */
    protected $connection;

    /**
     * @var string
     */
    protected $mainTable = '';

    /**
     * SyncReview constructor.
     * @param CollectionFactory $reviewCollectionFactory
     * @param WebHookSync $webHookSync
     * @param Helper $helper
     * @param RatingFactory $ratingFactory
     * @param string|null $name
     */
    public function __construct(
        CollectionFactory $reviewCollectionFactory,
        WebHookSync $webHookSync,
        Helper $helper,
        string $name = null
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->_webHookSync            = $webHookSync;
        $this->_helperData             = $helper;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('proofo:sync-reviews');
        $this->setDescription('Sync reviews');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reviewCollection = $this->reviewCollectionFactory->create();
        $this->connection = $reviewCollection->getConnection();
        $this->mainTable  = $reviewCollection->getMainTable();

        $reviews = $reviewCollection->addFieldToFilter('is_synced_proofo', 0);
        $size = $reviews->getSize();
        if ($size === 0) {
            $output->writeln(__('Total: %1 records', $size));
            return $this;
        }

        $data = [];
        $output->writeln(__('Total: %1 records', $size));
        $synced = 0;
        $ids = [];
        foreach ($reviews as $review) {
            $data[] = $this->_helperData->getReviewData($review);
            $ids[] = $review->getReviewId();

            if (count($data) === 100) {
                $this->_webHookSync->syncReview(['data' => $data]);
                $this->updateReview($ids);
                $synced += 100;
                $output->writeln(__('Synced: %1 records', $synced));
                $data = [];
                $ids = [];
            }
        }

        $this->_webHookSync->syncReview(['data' => $data]);
        $this->updateReview($ids);
        $output->writeln(__('Synced: %1 records', $size));
        $output->writeln('<info>Synced reviews successfully.</info>');

        return $this;
    }

    /**
     * @param array $ids
     */
    public function updateReview($ids)
    {
        $connection = $this->connection;
        try {
            $connection->beginTransaction();
            $connection->update($this->mainTable, ['is_synced_proofo' => 1], ['review_id IN (?)' => $ids]);

            $connection->commit();
        } catch (\Exception $e) {
            $this->_helperData->criticalLog($e->getMessage());
            $connection->rollBack();
        }
    }
}
