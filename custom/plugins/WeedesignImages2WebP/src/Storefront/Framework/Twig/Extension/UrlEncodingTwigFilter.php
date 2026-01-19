<?php declare(strict_types=1);

namespace Weedesign\Images2WebP\Storefront\Framework\Twig\Extension;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Weedesign\Images2WebP\Service\GenerateMediaFiles;

#[AutoconfigureTag('twig.extension')]
class UrlEncodingTwigFilter extends AbstractExtension
{

    private RequestStack $requestStack;

    private SystemConfigService $systemConfigService;

    private GenerateMediaFiles $generateMediaFiles;
    
    private ?string $baseUrl;

    private ?string $fallbackBaseUrl = null;

    private $maxImageSize;

    private $rootDir;

    public function __construct(
        RequestStack $requestStack,
        SystemConfigService $systemConfigService,
        GenerateMediaFiles $generateMediaFiles,
        ?string $baseUrl = null,
        ?string $rootDir = null
    )
    {
        $this->requestStack = $requestStack;
        $this->systemConfigService = $systemConfigService;
        $this->generateMediaFiles = $generateMediaFiles;
        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
        $this->maxImageSize = $this->setMaxImageSize();
        $reflector = new \ReflectionClass('Weedesign\Images2WebP\WeedesignImages2WebP');
        $this->cwd = dirname($reflector->getFileName());
        $this->rootDir = $rootDir;
    }
    
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('weedesign_images2webp_webp_url', [$this, 'encodeUrl']),
            new TwigFilter('weedesign_images2webp_webp_url_nope', [$this, 'encodeUrlNope']),
            new TwigFilter('weedesign_images2webp_webp_url_media', [$this, 'getAbsoluteMediaUrl']),
            new TwigFilter('weedesign_images2webp_webp_url_thumbnail', [$this, 'getAbsoluteThumbnailUrl'])
        ];
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

    public function encodeUrl($mediaUrl)
    {


        if ($mediaUrl === null) {
            return null;
        }

        if(is_string($mediaUrl)) {
            if($this->systemConfigService->get('WeedesignImages2WebP.config.webp')) {
                $mediaUrlArr = explode("?",$mediaUrl);
                $mediaUrlCheck = strtolower($mediaUrlArr[0]."weedesign");
                $webp = false;
                if($this->systemConfigService->get('WeedesignImages2WebP.config.jpg')) {
                    $mediaUrlCheckJPG = explode(".jpg",$mediaUrlCheck);
                    if(count($mediaUrlCheckJPG)>1) {
                        if($mediaUrlCheckJPG[1]=="weedesign") {
                            $webp = true;
                        }
                    }
                    if($webp===false) {
                        $mediaUrlCheckJPEG = explode(".jpeg",$mediaUrlCheck);
                        if(count($mediaUrlCheckJPEG)>1) {
                            if($mediaUrlCheckJPEG[1]=="weedesign") {
                                $webp = true;
                            }
                        }
                    }
                } 
                if($webp===false&&$this->systemConfigService->get('WeedesignImages2WebP.config.png')) {
                    $mediaUrlCheckPNG = explode(".png",$mediaUrlCheck);
                    if(count($mediaUrlCheckPNG)>1) {
                        if($mediaUrlCheckPNG[1]=="weedesign") {
                            $webp = true;
                        }
                    }
                }
                if($webp===true) {
                    if($this->systemConfigService->get('WeedesignImages2WebP.config.upgrade')==false) {
                        $mediaUrlArr = explode("?",$mediaUrl);
                        $image_name_array = explode("/",$mediaUrlArr[0]);
                        $image_name_array_2 = explode(".",$image_name_array[count($image_name_array)-1]);
                        $image_name_end = $image_name_array_2[0];
                        if(count($image_name_array_2)>2) {
                            $image_name_counter = 0;
                            foreach($image_name_array_2 as $image_name_part) {
                                if($image_name_counter > 0 && $image_name_counter < count($image_name_array_2)-1) {
                                    $image_name_end.= ".".$image_name_part;
                                }
                                $image_name_counter++;
                            }
                        }
                        $filename_system = $this->slugify($image_name_end).".webp";
                        if(file_exists($this->rootDir."/public/thumbnail/weedesign_images2webp/".$this->maxImageSize."/".$filename_system)) {
                            $image_url = $this->getBaseUrl()."/thumbnail/weedesign_images2webp/".$this->maxImageSize."/".$filename_system;
                        }
                    } else {
                        $mediaUrlArr = explode("?",$mediaUrl);
                        $filename_split = explode("/",$mediaUrlArr[0]);
                        $filename_final = $filename_split[count($filename_split)-1];
                        $filename_final.= "weedesign";
                        $filetypes = ["jpeg","jpg","png","JPEG","JPG","PNG"];
                        foreach($filetypes as $ft) {
                            $filename_final = str_replace(".".$ft."weedesign","weedesign",$filename_final);
                        }
                        $filename_final = $this->slugify($filename_final);
                        $filename_final = str_replace("weedesign",".webp",$filename_final);
                        $directory_split = explode("thumbnail/",$mediaUrlArr[0]);
                        if(count($directory_split)<2) {
                            $directory_split = explode("media/",$mediaUrlArr[0]);
                        }
                        $directory = explode("/",$directory_split[count($directory_split)-1]);
                        
                        $directory_path = $directory[0]."/";
                        for($i=1;$i<count($directory)-1;$i++) {
                            $directory_path.= $directory[$i]."/";
                        }

                        if(file_exists($this->rootDir."/public/media/".$directory_path.$filename_final)) {
                            $image_url = $this->getBaseUrl()."/"."media/".$directory_path.$filename_final;
                        }
                    }
                }
            }
            if(isset($image_url)) {
                $urlInfo = parse_url($image_url);
                if (!isset($urlInfo['path'])) {
                    return $image_url;
                }
                $mediaParts = ['media', 'thumbnail'];
                foreach ($mediaParts as $mediaPart) {
                    $paths = \explode(\sprintf('/%s/', $mediaPart), $urlInfo['path']);
                    if (count($paths) < 2) {
                        continue;
                    }
                    $paths[0] .= '/' . $mediaPart;

                    $relativeImagePath = $paths[1];
                    $relativeImagePathSegments = explode('/', $relativeImagePath);
                    foreach ($relativeImagePathSegments as $index => $segment) {
                        $relativeImagePathSegments[$index] = \rawurlencode($segment);
                    }

                    $paths[1] = implode('/', $relativeImagePathSegments);
                    $path = implode('/', $paths);
                    if (isset($urlInfo['query'])) {
                        $path .= "?{$urlInfo['query']}";
                    }

                    $encodedPath = '';
                    if (isset($urlInfo['scheme'])) {
                        $encodedPath = "{$urlInfo['scheme']}://";
                    }
                    if (isset($urlInfo['host'])) {
                        $encodedPath .= "{$urlInfo['host']}";
                    }
                    if (isset($urlInfo['port'])) {
                        $encodedPath .= ":{$urlInfo['port']}";
                    }
                    return $encodedPath . $path;
                }
            }
            return $mediaUrl;
        }
        return null;
    }

    public function getAbsoluteMediaUrl($media)
    {
        
        if(!is_null($media)) {

            if(!is_bool($media)) {

                if(!is_null($media->getFileExtension())) {
                
                    $extension = strtolower($media->getFileExtension());

                    $mediaUrlArr = explode("?",$media->getUrl());
                    $filename = $mediaUrlArr[0];
                    
                    if (!($media->getMediaType() instanceof ImageType)) {
                        return $filename;
                    }

                    if ($extension === 'svg') {
                        return $filename;
                    }

                    if($this->systemConfigService->get('WeedesignImages2WebP.config.webp')) {
                    
                        if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') {
                            $image_loaded = false;
                            if($extension === 'png') {
                                if($this->systemConfigService->get('WeedesignImages2WebP.config.png')) {
                                    $image_loaded = true;
                                    return $this->generateMediaFiles->getThumbnail($filename,$this->maxImageSize,$media->getFileName(),$extension,"media",true);
                                } else {
                                    return $filename;
                                }
                            }
                            if($extension === 'jpg' || $extension === 'jpeg') {
                                if($this->systemConfigService->get('WeedesignImages2WebP.config.jpg')) {
                                    $image_loaded = true;
                                    return $this->generateMediaFiles->getThumbnail($filename,$this->maxImageSize,$media->getFileName(),$extension,"media",true);
                                } else {
                                    return $filename;
                                }
                            }
                            if($image_loaded===false) {
                                return $filename;
                            }
                        } else {
                            return $filename;
                        }

                    } else {
                        $mediaUrlArr = explode("?",$media->getUrl());
                        return $mediaUrlArr[0];
                    }

                } else {
                    return null;
                }

            } else {
                return null;
            }

        } else {
            return null;
        }

    }

    public function getAbsoluteThumbnailUrl($media)
    {
        if(!is_null($media)) {

            if(!is_bool($media)) {
                $mediaUrlArr = explode("?",$media->getUrl());
                $filename = $mediaUrlArr[0];
                
                $image_name_array = explode("/",$filename);
                $image_name_array_2 = explode(".",$image_name_array[count($image_name_array)-1]);
                $image_name_end = $image_name_array_2[0];
                $extension = "jpg";
                if(count($image_name_array_2)>1) {
                    $image_name_counter = 0;
                    foreach($image_name_array_2 as $image_name_part) {
                        if($image_name_counter > 0 && $image_name_counter < count($image_name_array_2)-1) {
                            $image_name_end.= ".".$image_name_part;
                        }
                        else {
                            $extension = $image_name_part;
                        }
                        $image_name_counter++;
                    }
                }
                $image_name_end = str_replace("_".$media->getWidth()."x".$media->getWidth(),"",$image_name_end);

                $extension = strtolower($extension);

                if ($extension === 'svg') {
                    return $filename;
                }

                if($this->systemConfigService->get('WeedesignImages2WebP.config.webp')) {
                
                    if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') {
                        if($extension === 'png') {
                            if($this->systemConfigService->get('WeedesignImages2WebP.config.png')) {
                                return $this->generateMediaFiles->getThumbnail($filename,$media->getWidth(),$image_name_end,$extension,"thumbnail",true);
                            }
                        } else if($extension === 'jpg' || $extension === 'jpeg') {
                            if($this->systemConfigService->get('WeedesignImages2WebP.config.jpg')) {
                                return $this->generateMediaFiles->getThumbnail($filename,$media->getWidth(),$image_name_end,$extension,"thumbnail",true);
                            }
                        }
                    }

                } 

                return $filename;

            } else {
                return null;
            }

        } else {
            return null;
        }

    }

    public function encodeUrlNope($mediaUrl)
    {

        if ($mediaUrl === null) {
            return null;
        }
        if(is_string($mediaUrl)) {

            return $mediaUrl;

            $mediaUrlArr = explode("/",$mediaUrl);
            $image_name = str_replace("webp","",$mediaUrlArr[count($mediaUrlArr)-1]);

            $images_file = $this->cwd."/Data/files.json";
            
            if(file_exists($images_file)) {
                $images = json_decode(file_get_contents($images_file));
                if(count($images)>0) {
                    foreach($images as $image) {
                        if(strpos(str_replace("_","-",$image),$image_name)!==false||strpos($image,$image_name)!==false) {
                            return $image;
                        }
                    }
                }
            }
        }

        return $mediaUrl;
    }

    public function urlFallback(?string $mediaUrl): ?string {
        $urlInfo = parse_url($mediaUrl);
        
        $paths = \explode("/media"."/", $urlInfo['path']);
        $paths[0] .= '/media';

        $relativeImagePath = $paths[1];

        $relativeImagePathSegments = explode('/', $relativeImagePath);
        foreach ($relativeImagePathSegments as $index => $segment) {
            $relativeImagePathSegments[$index] = \rawurlencode($segment);
        }

        $paths[1] = implode('/', $relativeImagePathSegments);

        $path = implode('/', $paths);
        if (isset($urlInfo['query'])) {
            $path .= "?{$urlInfo['query']}";
        }

        $encodedPath = '';

        if (isset($urlInfo['scheme'])) {
            $encodedPath = "{$urlInfo['scheme']}://";
        }

        if (isset($urlInfo['host'])) {
            $encodedPath .= "{$urlInfo['host']}";
        }

        if (isset($urlInfo['port'])) {
            $encodedPath .= ":{$urlInfo['port']}";
        }

        return $encodedPath . $path;
    }

    private function createFallbackUrl(): string
    {
        $request = $this->requestStack->getMainRequest();
        if ($request) {
            $basePath = $request->getSchemeAndHttpHost() . $request->getBasePath();
        }
        if ($request) {
            return rtrim($basePath, '/');
        }
        return (string) EnvironmentHelper::getVariable('APP_URL');
    }

    private function normalizeBaseUrl(?string $baseUrl): ?string
    {
        if (!$baseUrl) {
            return null;
        }
        return rtrim($baseUrl, '/');
    }

    private function getBaseUrl(): string
    {
        if (!$this->baseUrl) {
            return $this->fallbackBaseUrl ?? $this->fallbackBaseUrl = $this->createFallbackUrl();
        }
        return $this->baseUrl;
    }

    public function setMaxImageSize()
    {
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

}
