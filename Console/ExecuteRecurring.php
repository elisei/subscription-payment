<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

declare(strict_types=1);

namespace O2TI\SubscriptionPayment\Console;

use Magento\Framework\App\State;
use O2TI\SubscriptionPayment\Model\PlaceNewOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clas Execute Recurring.
 */
class ExecuteRecurring extends Command
{
    /**
     * Order Increment Id.
     */
    public const INCREMENT_ID = 'increment_id';

    /**
     * @var PlaceNewOrder
     */
    protected $placeNewOrder;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param State         $state
     * @param PlaceNewOrder $placeNewOrder
     */
    public function __construct(
        State $state,
        PlaceNewOrder $placeNewOrder
    ) {
        $this->state = $state;
        $this->placeNewOrder = $placeNewOrder;
        parent::__construct();
    }

    /**
     * Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->placeNewOrder->setOutput($output);

        $incrementId = $input->getArgument(self::INCREMENT_ID);

        return $this->placeNewOrder->createOrder($incrementId);
    }

    /**
     * Configure.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('o2ti:recurring:new_order');
        $this->setDescription('Manually new Order');
        $this->setDefinition(
            [new InputArgument(self::INCREMENT_ID, InputArgument::REQUIRED, 'Order Increment Id')]
        );
        parent::configure();
    }
}
