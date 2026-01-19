<?php declare(strict_types=1);

namespace Weedesign\Images2WebP\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class DeleteMediaFiles
{

    private SystemConfigService $systemConfigService;
    
    private ?int $imageCount;
    private ?int $dirCount;
    private $cwd;
    private $rootDir;

    public function __construct(
        SystemConfigService $systemConfigService,
        ?string $rootDir = null
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->imageCount = 0;
        $this->dirCount = 0;
        $reflector = new \ReflectionClass('Weedesign\Images2WebP\WeedesignImages2WebP');
        $this->cwd = dirname($reflector->getFileName());
        $this->rootDir = $rootDir;
    }

    public function delete(): JsonResponse
    {
        $images_file = $this->cwd."/Data/files.json";
        $thumbnail_sizes_file = $this->cwd."/Data/thumbs.json";
        $error_file = $this->cwd."/Data/error.json";
        if(!$this->systemConfigService->get('WeedesignImages2WebP.config.upgrade')) {
            if (is_dir($this->rootDir."/public/media/weedesign_images2webp/")||is_dir($this->rootDir."/public/thumbnail/weedesign_images2webp/")) { 
                $this->rrmdir($this->rootDir."/public/media/weedesign_images2webp/");
                $this->rrmdir($this->rootDir."/public/thumbnail/weedesign_images2webp/");
                unlink($images_file);
                unlink($thumbnail_sizes_file);
                if(file_exists($error_file)) {
                    unlink($error_file);
                }
                return new JsonResponse(['success' => $this->imageCount]);
            } else {
                return new JsonResponse(['success' => "empty"]);
            }
        } else {
            if(file_exists($images_file)) {
                $images = json_decode(file_get_contents($images_file));
                if(file_exists($thumbnail_sizes_file)) {
                $thumbnail_sizes = json_decode(file_get_contents($thumbnail_sizes_file));
                if(count($images)>0) {
                    foreach($images as $image) {
                        $filename_split = explode("/",$image);
                        $filename_final = $filename_split[count($filename_split)-1];
                        $filename_final.= "weedesign";
                        $filetypes = ["jpeg","jpg","png","JPG","JPEG","PNG"];
                        foreach($filetypes as $ft) {
                            $filename_final = str_replace(".".$ft."weedesign","weedesign",$filename_final);
                        }
                        $filename_final = $this->slugify($filename_final);
                        $filename_final = str_replace("weedesign",".webp",$filename_final);
                        $directory_path = $filename_split[0]."/";
                        for($i=1;$i<count($filename_split)-1;$i++) {
                            $directory_path.= $filename_split[$i]."/";
                        }
                        $extensionArray = explode(".",$image);
                        $extension = strtolower($extensionArray[count($extensionArray)-1]);
                        foreach($thumbnail_sizes as $size) {
                            if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') {
                                $filename_thumbnail_final = str_replace(".webp","_".$size.".webp",$filename_final);
                                if(file_exists($this->rootDir."/public/thumbnail/".$directory_path.$filename_thumbnail_final)) {
                                    $this->imageCount++;
                                    unlink($this->rootDir."/public/thumbnail/".$directory_path.$filename_thumbnail_final);
                                }
                            }
                        }
                        if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') {
                            if(file_exists($this->rootDir."/public/media/".$directory_path.$filename_final)) {
                                $this->imageCount++;
                                unlink($this->rootDir."/public/media/".$directory_path.$filename_final);
                            }
                        } 
                    }
                    unlink($images_file);
                    unlink($thumbnail_sizes_file);
                    if(file_exists($error_file)) {
                        unlink($error_file);
                    }
                    return new JsonResponse(['success' => $this->imageCount]);
                }
                }
            }
            return new JsonResponse(['success' => "empty"]);
        }
        
    }

    public static function slugify($text, string $divider = '-') {
        $text_backup = $text;
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        if(!is_bool($text)) {
            $text = preg_replace('~[^-\w]+~', '', $text);
            $text = trim($text, $divider);
            $text = preg_replace('~-+~', $divider, $text);
            $text = strtolower($text);
            if (empty($text)) {
                return 'n-a-'.md5($text);
            }
            return $text;
        } else {
            return $text_backup;
        }        
    }

    public function rrmdir($dir) { 
        if (is_dir($dir)) { 
            $objects = scandir($dir);
            foreach ($objects as $object) { 
                if ($object != "." && $object != "..") { 
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object)) {
                    $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                    $this->dirCount++;
                } else {
                    unlink($dir. DIRECTORY_SEPARATOR .$object); 
                    $this->imageCount++;
                }
                } 
            }
            rmdir($dir); 
        } 
    }
    
}
