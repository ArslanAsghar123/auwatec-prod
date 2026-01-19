<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Command;

use DreiscSeoPro\Core\Foundation\Command\ProgressCommand\AbstractBaseProgressCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractProgressCommand extends AbstractBaseProgressCommand
{
    protected function beforeStartProgress(InputInterface $input, OutputInterface $output): void
    {
        /** Fetch the total count */
        $this->totalCount = $this->fetchTotalCount();
    }

    protected function startProgress(InputInterface $input, OutputInterface $output): void
    {
        $offset = 0;
        $runProgress = true;
        while($offset < $this->totalCount && $runProgress) {
            /** Progress the current run */
            $this->runProgress($input, $output, $this->interval, $offset);

            /** Raise the progress bar by the interval */
            $this->advanceProgressBar($this->interval);

            /** Calculate the new offset */
            $offset += $this->interval;

            /** Calculate remaining time, if necessary */
            if (null !== $this->timeLimit) {
                /** Calculates the time and sets $runProgress to false, if the time has expired */
                $message = $this->calculatedRemainingTime($runProgress);

                /** Output of the remaining time */
                $output->writeln($message);
            }
        }
    }

    abstract protected function fetchTotalCount(): int;
    abstract protected function runProgress(InputInterface $input, OutputInterface $output, int $interval, int $offset): void;
}
