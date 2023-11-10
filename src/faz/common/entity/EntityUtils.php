<?php

declare(strict_types=1);

namespace faz\common\entity;

use faz\common\utils\Image;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;

class EntityUtils {

    public static function parseSkin(string $texture, string $geometry, string $path, ?string $capData = "") : ?Skin {
        $texturePath = $path . $texture . ".png";
        $img = imagecreatefrompng($texturePath);
        $bytes = '';
        $imageSize = @getimagesize($texturePath);

        if ($imageSize === false || $imageSize[0] !== 64 || $imageSize[1] !== 64) {
            $skinImage = Image::dropSize($texturePath, 64, 64);
            imagepng($skinImage, $texturePath);
            return self::parseSkin($texture, $geometry, $path);
        }

        $l = $imageSize[1];

        for ($y = 0; $y < $l; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $a = ((~($rgba >> 24)) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        imagedestroy($img);
        $geometryPath = $path . $geometry . ".json";

        try {
            return new Skin("Standard_CustomSlim", $bytes, $capData, "geometry." . $geometry , file_get_contents($geometryPath));
        } catch (InvalidSkinException) {
            return null;
        }
    }

}