<?php declare(strict_types=1);

namespace Weedesign\Images2WebP\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class GenerateMedia extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'weedesign.images2webp.generate.media';
    }

    public static function getDefaultInterval(): int
    {
        $reflector = new \ReflectionClass('Weedesign\Images2WebP\WeedesignImages2WebP');
        $cwd = dirname($reflector->getFileName());
        $data = $cwd."/Data";
        if(!is_dir($data)) {
            try {
                mkdir($data);
            }
            catch(\Exception $e) {
                
            }
        }
        if(is_dir($data)) {
            if(!file_exists($data."/scheduled.task.int")) {
                $fsh = new Filesystem();
                $fsh->dumpFile($data."/scheduled.task.int", "60");
            }
        }
        $interval = file_get_contents($data."/scheduled.task.int")*1;
        return $interval*60;
    }
}