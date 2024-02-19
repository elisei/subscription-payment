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

namespace O2TI\SubscriptionPayment\Model;

use Magento\Payment\Model\Method\Logger;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractModel
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Output.
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Console Write.
     *
     * @param string $text
     *
     * @return void
     */
    protected function write(string $text)
    {
        if ($this->output instanceof OutputInterface) {
            $this->output->write($text);
        }
    }

    /**
     * Console WriteLn.
     *
     * @param string $text
     *
     * @return void
     */
    protected function writeln($text)
    {
        if ($this->output instanceof OutputInterface) {
            $this->output->writeln($text);
        }
    }
}
