<?php

namespace App\Services;

use App\Factories\ResponseErrorFactory;

use App\Validators\ImageValidator;

use App\Responses\ResponseError;

class ImageService
{
    private $responseErrorFactory;
    private $imageValidator;

    public function __construct(
        ResponseErrorFactory $responseErrorFactory,
        ImageValidator $imageValidator
    )
    {
        $this->responseErrorFactory = $responseErrorFactory;
        $this->imageValidator = $imageValidator;
    }

    public function convertBase64ToWebp(string $base64Image): string|ResponseError
    {
        try{
            if(str_contains(haystack: $base64Image, needle: 'data:image/webp;base64,')){
                return $base64Image;
            }

            $imageData = base64_decode(string: preg_replace(pattern: '/^data:image\/\w+;base64,/', replacement: '', subject: $base64Image));
            $sourceImage = imagecreatefromstring(data: $imageData);

            imagealphablending(image: $sourceImage, enable: false);
            imagesavealpha(image: $sourceImage, enable: true);

            ob_start();
            imagepng(image: $sourceImage, file: null, quality: 9);
            $webpData = ob_get_clean();

            return 'data:image/webp;base64,' . base64_encode(string: $webpData);

        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function resizeImage(string $base64, string $fileName, string $targetPath): bool|ResponseError
    {
        try{
            $sizes = [
                '1024'=> 1024,
                '512' => 512,
                '256' => 256,
                '128' => 128,
            ];

            $this->imageValidator->isValide(base64Image: $base64);
            $base64 = $this->convertBase64ToWebp(base64Image: $base64);

            $avatarBase64 = str_replace(search: 'data:image/webp;base64,', replace: '', subject: $base64);
            $imageData = base64_decode(string: $avatarBase64);

            $sourceImage = imagecreatefromstring(data : $imageData);
            $sourceWidth = imagesx(image: $sourceImage);
            $sourceHeight = imagesy(image: $sourceImage);

            foreach($sizes as $sizeName => $size){
                $sizeDirectory = rtrim(string: $targetPath, characters: '/') . '/' . $sizeName;
                if(!file_exists(filename: $sizeDirectory)){
                    mkdir(directory: $sizeDirectory, permissions: 0777, recursive: true);
                }

                $ratio = min($size / $sourceWidth, $size / $sourceHeight);
                $newWidth = (int)($sourceWidth * $ratio);
                $newHeight = (int)($sourceHeight * $ratio);

                $newImage = imagecreatetruecolor(width: $newWidth, height: $newHeight);

                imagealphablending(image: $newImage, enable: false);
                imagesavealpha(image: $newImage, enable: true);
                
                imagecopyresampled(
                    dst_image: $newImage,
                    src_image: $sourceImage,
                    dst_x: 0,
                    dst_y: 0,
                    src_x: 0,
                    src_y: 0,
                    dst_width: $newWidth,
                    dst_height: $newHeight,
                    src_width: $sourceWidth,
                    src_height: $sourceHeight
                );

                $outputPath = rtrim(string: $sizeDirectory, characters: '/') . '/' . $fileName;
                imagewebp(image: $newImage, file: $outputPath, quality: 80);
            }
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}