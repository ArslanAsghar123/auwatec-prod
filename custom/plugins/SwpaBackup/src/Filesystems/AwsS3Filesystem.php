<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

use AsyncAws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;

/**
 * AWS S3 Filesystem
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class AwsS3Filesystem implements FilesystemAdapterInterface
{
    
    public function handles(string $type): bool
    {
        return strtolower($type) == 'aws';
    }
    
    public function get(array $config): Filesystem
    {
        if(empty($config['key']) || empty($config['secret']) || empty($config['region']) || empty($config['bucket'])){
            throw new ClientConfigurationException("configuration is empty, please check AWS backup settings");
        }
        $client = new S3Client([
            'accessKeyId' => $config['key'],
            'accessKeySecret' => $config['secret'],
            'region' => $config['region'],
        ]);
        
        return new Filesystem(new AsyncAwsS3Adapter($client, $config['bucket'], $config['root']?:'/'));
    }
}
