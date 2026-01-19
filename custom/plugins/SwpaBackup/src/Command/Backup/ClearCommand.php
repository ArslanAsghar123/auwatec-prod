<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Command\Backup;

use League\Flysystem\FilesystemException;
use Swpa\SwpaBackup\Filesystems\FilesystemTypeNotSupported;
use Swpa\SwpaBackup\Service\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command: clear old backups
 *
 * @package   Swpa\SwpaBackup\Command\Backup
 * @copyright See COPYING.txt for license details.
 * @author    swpa <info@swpa.dev>
 */
class ClearCommand extends Command
{
    public function __construct(
        private readonly Manager $manager,
        string                   $name = null
    )
    {
        parent::__construct($name);
    }
    
    protected function configure(): void
    {
        $this
            ->setName('swpa:backup:clear')
            ->setDescription('Clear old backups')
            ->setHelp("The command <info>%command.name%</info> - clear old backups");
    }
    
    /**
     * @throws FilesystemException
     * @throws FilesystemTypeNotSupported
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->manager->clearBackup();
    }
}
