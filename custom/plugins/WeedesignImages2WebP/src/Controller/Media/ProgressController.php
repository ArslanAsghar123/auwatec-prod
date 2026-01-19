<?php

namespace Weedesign\Images2WebP\Controller\Media;

use OpenApi\Attributes as OA;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Weedesign\Images2WebP\Service\GenerateMediaFiles;
use Weedesign\Images2WebP\API\Media\Progress;
use Weedesign\Images2WebP\API\Media\Progress\Item;

#[Route(defaults: ['_routeScope' => ['api']])]
class ProgressController extends AbstractController
{

  private $systemConfigService;
  private $generateMediaFiles;

  private $maxImageSize;
  private $attempt;
  private $cwd;
  private $rootDir;
  private $imageCount;

  /**
   * GenerateController constructor.
   */
  public function __construct(
    SystemConfigService $systemConfigService,
    GenerateMediaFiles $generateMediaFiles,
    ?string $rootDir = null
  )
  {
    $this->systemConfigService = $systemConfigService;
    $this->generateMediaFiles = $generateMediaFiles;
    $this->maxImageSize = $this->setMaxImageSize();
    $this->attempt = 1;
    $this->imageCount = 0;
    $reflector = new \ReflectionClass('Weedesign\Images2WebP\WeedesignImages2WebP');
    $this->cwd = dirname($reflector->getFileName());
    $this->rootDir = $rootDir;
  }

  #[Route(path: '/api/_action/weedesign/images2webp/media/progress/check', methods: ['GET','POST'])]
  public function check(Context $context): JsonResponse
  {

    $webp = 0;
    $count = 0;

    $images_file = $this->cwd."/Data/files.json";

    if(file_exists($images_file)) {

      $images = json_decode(file_get_contents($images_file));
      
      $thumbnail_sizes_file = $this->cwd."/Data/thumbs.json";

      if(file_exists($thumbnail_sizes_file)) {

        $thumbnail_sizes = json_decode(file_get_contents($thumbnail_sizes_file));

        if(count($images)>0) {

          if(!$this->systemConfigService->get('WeedesignImages2WebP.config.upgrade')) {

            $getCountWebP = $this->getCountWebP();
            $count = count($images);
            foreach($thumbnail_sizes as $size) {
              $count = $count+count($images);
            }
            $webp = $this->imageCount;

          } else {

            foreach($images as $image) {

              $filename_split = explode("/",$image);
              $filename_final = $filename_split[count($filename_split)-1];
              $filename_final.= "weedesign";
              $filetypes = ["jpeg","jpg","png","JPEG","JPG","PNG"];
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
                if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'JPG' || $extension === 'JPEG') {
                  if($this->systemConfigService->get('WeedesignImages2WebP.config.jpg')) {
                    $filename_thumbnail_final = str_replace(".webp","_".$size.".webp",$filename_final);
                    $count++;
                    if(file_exists($this->rootDir."/public/thumbnail/".$directory_path.$filename_thumbnail_final)) {
                      $webp++;
                    }
                  }
                } else if($extension === 'png' || $extension === 'PNG') {
                  if($this->systemConfigService->get('WeedesignImages2WebP.config.png')) {
                    $filename_thumbnail_final = str_replace(".webp","_".$size.".webp",$filename_final);
                    $count++;
                    if(file_exists($this->rootDir."/public/thumbnail/".$directory_path.$filename_thumbnail_final)) {
                      $webp++;
                    }
                  }
                }
              }

              if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'JPG' || $extension === 'JPEG') {
                if($this->systemConfigService->get('WeedesignImages2WebP.config.jpg')) {
                  $count++;
                  if(file_exists($this->rootDir."/public/media/".$directory_path.$filename_final)) {
                    $webp++;
                  }
                }
              } else if($extension === 'png' || $extension === 'PNG') {
                if($this->systemConfigService->get('WeedesignImages2WebP.config.png')) {
                  $count++;
                  if(file_exists($this->rootDir."/public/media/".$directory_path.$filename_final)) {
                    $webp++;
                  }
                }
              }
              
            }
          }
          
        }

        return new JsonResponse([
          'success' => true,
          "webp" => $webp,
          'thumbs' => $thumbnail_sizes,
          "images" => $count,
          "images_orginal" => count($images)
        ]);

      } else {
        return new JsonResponse([
          'success' => false
        ]);
      }

    } else {
      if($this->attempt==1) {
        if(!extension_loaded('gd')) {
          return new JsonResponse(['error' => true]);
        } else {
          $this->attempt++;
          $images = $this->generateMediaFiles->createThumbnails(true);
          return $this->check($context);
        }
      }
    }
    
  }

  public function getCountWebP() {
    $this->imageCount = 0;
    $this->countWebP($this->rootDir."/public/media/weedesign_images2webp/");
    $this->countWebP($this->rootDir."/public/thumbnail/weedesign_images2webp/");
  }

  public function countWebP($dir) { 
    if (is_dir($dir)) { 
      $objects = scandir($dir);
      foreach ($objects as $object) { 
        if ($object != "." && $object != "..") { 
          if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object)) {
            $this->countWebP($dir. DIRECTORY_SEPARATOR .$object);
          } else {
            $this->imageCount++;
          }
        } 
      }
    } 
  }

  public function setMaxImageSize() {
      if($this->systemConfigService->get('WeedesignImages2WebP.config.maxImageSize')) {
          if(is_int($this->systemConfigService->get('WeedesignImages2WebP.config.maxImageSize'))) {
              if(is_null($this->systemConfigService->get('WeedesignImages2WebP.config.maxImageSize'))) {
                  return 2000;
              } else {
                  return $this->systemConfigService->get('WeedesignImages2WebP.config.maxImageSize');
              }
          } else {
              return 2000;
          }
      } else {
          return 2000;
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

}