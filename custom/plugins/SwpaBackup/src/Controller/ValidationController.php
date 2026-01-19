<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Controller;

use Exception;
use League\Flysystem\FilesystemException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Swpa\SwpaBackup\Filesystems\AwsS3Filesystem;
use Swpa\SwpaBackup\Filesystems\FtpFilesystem;
use Swpa\SwpaBackup\Filesystems\LocalFilesystem;
use Swpa\SwpaBackup\Filesystems\SftpFilesystem;
use Swpa\SwpaBackup\Service\Config;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Validation controller
 *
 * @package   Swpa\SwpaBackup\Controller
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class ValidationController extends AbstractController
{
    public function __construct(protected Config $config)
    {
    }
    
    #[Route(path: '/api/_action/swpa-backup/validate-local-credentials', name: 'api.action.swpa-backup.validate.local.credentials', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function validateLocalApiCredentials(RequestDataBag $requestDataBag): JsonResponse
    {
        $message = '';
        $credentialsValid = true;
        try {
            $filesystemProvider = new LocalFilesystem();
            $config = [
                'root' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemLocalRoot'),
            ];
            $filesystem = $filesystemProvider->get($config);
            $testBackupDirectoryName = md5('test-backup-directory');
            $filesystem->createDirectory($testBackupDirectoryName);
            $filesystem->deleteDirectory($testBackupDirectoryName);
        } catch (FilesystemException|Exception $e) {
            $credentialsValid = false;
            $message = mb_strimwidth($e->getMessage(), 0, 100, '...');
        }
        return new JsonResponse(['credentialsValid' => $credentialsValid, 'message' => $message]);
    }
    
    #[Route(path: '/api/_action/swpa-backup/validate-ftp-credentials', name: 'api.action.swpa-backup.validate.ftp.credentials', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function validateFtpApiCredentials(RequestDataBag $requestDataBag): JsonResponse
    {
        
        $message = '';
        $credentialsValid = true;
        try {
            $filesystemProvider = new FtpFilesystem();
            $config = [
                'host' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemFtpHost'),
                'password' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemFtpPassword'),
                'port' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemFtpPort'),
                'root' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemFtpRoot'),
                'timeout' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemFtpTimeout'),
                'username' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemFtpUsername'),
                'passive' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemFtpPassive'),
            ];
            $filesystem = $filesystemProvider->get($config);
            $testBackupDirectoryName = md5('test-backup-directory');
            $filesystem->createDirectory($testBackupDirectoryName);
            $filesystem->deleteDirectory($testBackupDirectoryName);
        } catch (FilesystemException|Exception $e) {
            $credentialsValid = false;
            $message = mb_strimwidth($e->getMessage(), 0, 100, '...');
        }
        
        return new JsonResponse(['credentialsValid' => $credentialsValid, 'message' => $message]);
    }
    
    #[Route(path: '/api/_action/swpa-backup/validate-sftp-credentials', name: 'api.action.swpa-backup.validate.sftp.credentials', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function validateSftpApiCredentials(RequestDataBag $requestDataBag): JsonResponse
    {
        
        $message = '';
        $credentialsValid = true;
        try {
            $filesystemProvider = new SftpFilesystem();
            $config = [
                'host' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemSftpHost'),
                'password' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemSftpPassword'),
                'port' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemSftpPort'),
                'root' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemSftpRoot'),
                'timeout' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemSftpTimeout'),
                'username' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemSftpUsername'),
                'privateKey' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemSftpPrivateKey')
            ];
            $filesystem = $filesystemProvider->get($config);
            $testBackupDirectoryName = md5('test-backup-directory');
            $filesystem->createDirectory($testBackupDirectoryName);
            $filesystem->deleteDirectory($testBackupDirectoryName);
        } catch (FilesystemException|Exception $e) {
            $credentialsValid = false;
            $message = mb_strimwidth($e->getMessage(), 0, 100, '...');
        }
        return new JsonResponse(['credentialsValid' => $credentialsValid, 'message' => $message]);
    }
    
    #[Route(path: '/api/_action/swpa-backup/validate-aws-credentials', name: 'api.action.swpa-backup.validate.aws.credentials', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function validateAwsCredentials(RequestDataBag $requestDataBag): JsonResponse
    {
        $message = '';
        $credentialsValid = true;
        try {
            $filesystemProvider = new AwsS3Filesystem();
            $config = [
                'key' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemAwsKey'),
                'secret' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemAwsSecret'),
                'region' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemAwsRegion'),
                'version' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemAwsVersion'),
                'bucket' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemAwsBucket'),
                'root' => $requestDataBag->get('config')->get('SwpaBackup.settings.filesystemAwsRoot')
            ];
            $filesystem = $filesystemProvider->get($config);
            $testBackupDirectoryName = md5('test-backup-directory');
            $filesystem->createDirectory($testBackupDirectoryName);
            $filesystem->deleteDirectory($testBackupDirectoryName);
        } catch (FilesystemException|Exception $e) {
            $credentialsValid = false;
            $message = mb_strimwidth($e->getMessage(), 0, 200, '...');
        }
        
        return new JsonResponse(['credentialsValid' => $credentialsValid, 'message' => $message]);
    }
}
