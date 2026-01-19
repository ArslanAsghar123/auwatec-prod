<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Command\ProgressCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class AbstractBaseProgressCommand extends Command
{
    /**
     * @var int
     */
    protected $defaultInterval = 50;

    /**
     * @var int|null
     */
    protected $interval = null;

    /**
     * @var bool
     */
    protected $enableTimeLimit = true;

    /**
     * @var int|null
     */
    protected $defaultTimeLimit = null;

    /**
     * @var int|null
     */
    protected $timeLimit = null;

    /**
     * @var int
     */
    protected $startTimeStamp;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var ProgressBar|null
     */
    protected $progressBar = null;

    /**
     * @var Stopwatch
     */
    protected $stopwatch = null;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->configureProgress();
    }

    protected function configureProgress() :void
    {
        $this->addOption(
            'interval',
            'i',
            InputOption::VALUE_OPTIONAL,
            'Number of elements being progressed at once',
            $this->defaultInterval
        );

        if (true === $this->enableTimeLimit) {
            $this->addOption(
                'timeLimit',
                't',
                InputOption::VALUE_OPTIONAL,
                'Sets the time in minutes until the bulk generator no longer processes new intervals',
                $this->defaultTimeLimit
            );
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        /** Space */
        $output->write("\n");

        /** Start a stopwatch */
        $this->stopwatch = new Stopwatch(true);
        $this->stopwatch->start(self::class);

        /** Fetch the interval options */
        $this->interval = (int) $input->getOption('interval');

        /** Fetch the timeLimit options */
        if (true === $this->enableTimeLimit) {
            $timeLimit = $input->getOption('timeLimit');
            $this->timeLimit = null === $timeLimit ? null : intval($timeLimit);
        } else {
            $timeLimit = null;
        }

        /** Save the start time */
        $this->startTimeStamp = time();

        /** Run beforeStartProgress method */
        $this->beforeStartProgress($input, $output);

        /** Initialize the progress bar */
        $this->initializeProgressBar($output);

        /** Starts the progress */
        $this->startProgress($input, $output);

        /** Finish the progress bar */
        $this->finishProgressBar($input, $output);

        return 0;
    }

    protected function beforeStartProgress(InputInterface $input, OutputInterface $output): void { }

    protected function initializeProgressBar(OutputInterface $output): void
    {
        if (null === $this->totalCount) {
            return;
        }

        /** Initialize the progress bar */
        $this->progressBar = new ProgressBar($output, $this->totalCount);
        $this->progressBar->start();
    }

    protected function advanceProgressBar(int $step = 1): void
    {
        if (null === $this->progressBar) {
            return;
        }

        $this->progressBar->advance($step);
    }

    protected function finishProgressBar(InputInterface $input, OutputInterface $output): void
    {
        if (null === $this->progressBar) {
            return;
        }

        $this->progressBar->finish();

        /** Output the stopwatch result */
        $stopwatchEvent = $this->stopwatch->stop(self::class);

        $output->writeln(sprintf(
            "\nResources: %.2F MiB - %d ms\n",
            $stopwatchEvent->getMemory() / 1024 / 1024,
            $stopwatchEvent->getDuration()
        ));
    }

    /**
     * Calculate the remaining time
     *
     * @param $runProgress
     */
    protected function calculatedRemainingTime(bool &$runProgress): string
    {
        $remainingTime = ($this->startTimeStamp + $this->timeLimit*60) +  - time();
        if($remainingTime <= 0) {
            /** The time has expired */
            $runProgress = false;
        }
        $convertedRemainingTime = $this->convertSecToMinAndSec($remainingTime);

        return sprintf(
            " » %02d:%02d left",
            $convertedRemainingTime['min'],
            $convertedRemainingTime['sec']
        );
    }

    /**
     * Calculates the given seconds in minutes and seconds
     *
     * @param $sec
     */
    protected function convertSecToMinAndSec($sec): array
    {
        $min = 0;
        if($sec > 0) {
            $min = floor($sec/60);
            if($min > 0) {
                $sec = $sec - ($min*60);
            }
        }
        return [
            'min' => $min,
            'sec' => $sec
        ];
    }

    abstract protected function startProgress(InputInterface $input, OutputInterface $output): void;
}
