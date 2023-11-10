<?php

declare(strict_types=1);

namespace faz\common\utils;

use GdImage;

class Image {

    public static function dropSize($imageFile, $newWidth, $newHeight, $crop = false) {
        list($originalWidth, $originalHeight) = getimagesize($imageFile);
        $aspectRatio = $originalWidth / $originalHeight;

        if ($crop) {
            if ($originalWidth > $originalHeight) {
                $originalWidth = ceil($originalWidth - ($originalWidth * abs($aspectRatio - $newWidth / $newHeight)));
            } else {
                $originalHeight = ceil($originalHeight - ($originalHeight * abs($aspectRatio - $newWidth / $newHeight)));
            }
        } else {
            if ($newWidth / $newHeight > $aspectRatio) {
                $newWidth = $newHeight * $aspectRatio;
            } else {
                $newHeight = $newWidth / $aspectRatio;
            }
        }

        $sourceImage = imagecreatefrompng($imageFile);
        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);

        imagecolortransparent($destinationImage, imagecolorallocatealpha($destinationImage, 0, 0, 0, 127));
        imagealphablending($destinationImage, false);
        imagesavealpha($destinationImage, true);

        imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        return $destinationImage;
    }


}