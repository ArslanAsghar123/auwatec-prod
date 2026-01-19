<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Command;

use DreiscSeoPro\Core\Foundation\Command\ProgressCommand\AbstractBaseProgressCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractIteratorProgressCommand extends AbstractBaseProgressCommand
{
    /**
     * @var IterableQuery
     */
    protected $iterator;

    protected function beforeStartProgress(InputInterface $input, OutputInterface $output): void
    {
        /** Fetch the iterator */
        $this->iterator = $this->getIterator();

        /** Fetch the total count */
        $this->totalCount = $this->iterator->fetchCount();
    }

    protected function startProgress(InputInterface $input, OutputInterface $output): void
    {
        $runProgress = true;
        while($ids = $this->iterator->fetch()) {
            /** Break if $runProgress is false */
            if (false === $runProgress) {
                break;
            }

            /** Progress the current run */
            $this->runProgress($input, $output, $ids);

            /** Raise the progress bar by the interval */
            if (count($ids) > 0) {
                $this->advanceProgressBar(count($ids));
            }

            /** Calculate remaining time, if necessary */
            if (null !== $this->timeLimit) {
                /** Calculates the time and sets $runProgress to false, if the time has expired */
                $message = $this->calculatedRemainingTime($runProgress);

                /** Output of the remaining time */
                $output->writeln($message);
            }
        }
    }

    abstract protected function getIterator(): IterableQuery;
    abstract protected function runProgress(InputInterface $input, OutputInterface $output, array $ids): void;
}
