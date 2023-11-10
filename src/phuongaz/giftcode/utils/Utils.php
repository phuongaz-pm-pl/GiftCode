<?php

declare(strict_types=1);

namespace phuongaz\giftcode\utils;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use pocketmine\player\Player;

class Utils {
    public static function getExpireTime(string $duration): string {
        $now = new \DateTime();
        $endTime = clone $now;

        $pattern = '/(\d+)\s*d(?:\s*(\d+)\s*h)?(?:\s*(\d+)\s*m)?(?:\s*(\d+)\s*s)?/';

        preg_match($pattern, $duration, $matches);

        $days = isset($matches[1]) ? (int)$matches[1] : 0;
        $hours = isset($matches[2]) ? (int)$matches[2] : 0;
        $minutes = isset($matches[3]) ? (int)$matches[3] : 0;
        $seconds = isset($matches[4]) ? (int)$matches[4] : 0;

        $interval = new \DateInterval("P{$days}DT{$hours}H{$minutes}M{$seconds}S");
        $endTime->add($interval);

        // Y-m-d H:i:s
        return $endTime->format("Y-m-d H:i:s");
    }

    public static function randomString(string $startWith = "", $length = 10): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $startWith . $randomString;
    }

    public static function serializeItem(Item $item) :string
    {
        return base64_encode(serialize($item));
    }

    public static function unserializeItem(string $item) :Item
    {
        return unserialize(base64_decode($item));
    }

    public static function sendToast(Player $player, string $title, string $message) :void
    {
        $packet = ToastRequestPacket::create($title, $message);
        $player->getNetworkSession()->sendDataPacket($packet);
    }

}