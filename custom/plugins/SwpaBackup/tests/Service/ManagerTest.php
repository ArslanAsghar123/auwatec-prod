<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Test\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swpa\SwpaBackup\Filesystems\LocalFilesystemAdapterInterface;

/**
 * Manager Test
 *
 * @package   Swpa\SwpaBackup\Test
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class ManagerTest extends TestCase {

    use KernelTestBehaviour;

    /**
     * @return array
     */
    public function dataProvider() : array {
        $localRoot = $this->getLocalRootPath();

        return [
            [['SwpaBackup.settings.generalBackupType'=>0,'SwpaBackup.settings.filesystemLocalRoot'=>$localRoot]], // database
            [['SwpaBackup.settings.generalBackupType'=>1,'SwpaBackup.settings.filesystemLocalRoot'=>$localRoot]], // database+media
            [['SwpaBackup.settings.generalBackupType'=>2,'SwpaBackup.settings.filesystemLocalRoot'=>$localRoot]], // system
            [['SwpaBackup.settings.generalBackupType'=>3,'SwpaBackup.settings.filesystemLocalRoot'=>$localRoot]], // system without media
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMakeBackup( array $settings ) : void {
        $manager = $this->getContainer()->get('swpa.backup.backup_manager');
        $this->setConfig($settings);
        $manager->makeBackup(true);
        $filesystemProvider = new LocalFilesystemAdapterInterface();
        $config = [
            'root' => $this->getLocalRootPath(),
        ];
        $filesystem = $filesystemProvider->get($config);
        $backups = $filesystem->listContents();
        $count=0;
        // todo: fix - type of backup not change
        foreach( $backups as $backup ){
            if($filesystem->has($backup['path'])){
                $filesystem->delete($backup['path']);
                $count++;
            }
        }
        $this->assertSame(1,$count);
    }

    protected function getLocalRootPath() : string {

        return $this->getKernel()->getProjectDir() . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR .'test' . DIRECTORY_SEPARATOR . 'backup';
    }

    protected function setConfig($settings){
        $configService = $this->getContainer()->get(SystemConfigService::class);
        foreach( $settings as $key=>$value ) {
            $configService->set($key,$value);
        }
    }
}
