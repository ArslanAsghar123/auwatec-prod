<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Command\Backup;

use Exception;
use League\Flysystem\FilesystemException;
use Swpa\SwpaBackup\Service\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command: run backup
 *
 * @package   Swpa\SwpaBackup\Command\Backup
 * @copyright See COPYING.txt for license details.
 * @author    swpa <info@swpa.dev>
 */
class BackupCommand extends Command
{
    const FORCE = 'force';
    
    public function __construct(
        private readonly Manager $manager,
        string                   $name = null
    )
    {
        parent::__construct($name);
    }
    
    /**
     * configure command
     */
    protected function configure(): void
    {
        $this
            ->setName('swpa:backup:run')
            ->setDescription('run backup scheduler, use "force" for immediately backup')
            ->addArgument(static::FORCE, InputArgument::OPTIONAL)
            ->setHelp("The <info>%command.name%</info> run backup scheduler");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->manager->makeBackup($input->getArgument(static::FORCE) == static::FORCE);
            $this->manager->clearBackup();
        } catch (FilesystemException|Exception $e) {
            $output->writeln("Exception: " . $e->getMessage());
            return 0;
        }
        return 1;
    }
}
