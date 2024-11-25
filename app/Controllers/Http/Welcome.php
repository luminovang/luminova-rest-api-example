<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Http;

use \Luminova\Base\BaseViewController;
use \Luminova\Attributes\Prefix;
use \Luminova\Attributes\Route;
use \App\Controllers\Errors\ViewErrors;
use \Luminova\Storages\FileDelivery;
use \Luminova\Exceptions\AppException;
use \Exception;

#[Prefix(
    pattern: '/(?!api).*', // Prevent this controller from handling APIs request prefixes.
    onError: [ViewErrors::class, 'onWebError'] // Define error handler for this controller methods.
)]
class Welcome extends BaseViewController 
{
    /**
     * Expiration time for cached files (1 year).
     * 
     * @var int $expiration Expiration time in seconds.
     */
    private int $expiration = 365 * 24 * 60 * 60;

    /**
     * Index page controller method.
     * 
     * @return int Return status code STATUS_SUCCESS or STATUS_ERROR.
     */
    #[Route('/', methods: ['GET'])]
    public function home(): int
    {
        return $this->view('index');
    }

    /**
     * Output private store images on browser (https://localhost/project/public/cdn/foo.png).
     * It also supports resizing image width and height as well as the image quality.
     * 
     * @param string $filename The image filename reference (e.g, `foo.png`).
     * 
     * @return int Return status code STATUS_SUCCESS or STATUS_ERROR.
     * @todo Add middle way authentication to secure image access.
     *      Add check to verify if image is valid.
     */
    #[Route('/cdn/([a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)+)', methods: ['GET'])]
    public function imageCdn(string $filename): int 
    {
        $filename = escape($filename);
        $width = (int) strict($this->request->getGet('width', '0'), 'int');
        $height = (int) strict($this->request->getGet('height', '0'), 'int');
        $cdn = FileDelivery::storage('posts');
       
        try{
            if($width > 0 || $height > 0){
                $quality = strict($this->request->getGet('quality', '100'), 'int');
                $ratio = (bool) strict($this->request->getGet('ratio', '1'), 'int');
                
                if($cdn->outputImage($filename, $this->expiration, [
                    'width' => $width,
                    'height' => $height,
                    'quality' => $quality,
                    'ratio' => $ratio
                ], ['Vary' => ''])){
                    return STATUS_SUCCESS;
                }
            }

            if($cdn->output($filename, $this->expiration, ['Vary' => ''])){
                return STATUS_SUCCESS;
            }

        }catch(AppException|Exception){}

        return STATUS_ERROR;
    }
}