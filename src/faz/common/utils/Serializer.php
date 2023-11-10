<?php

declare(strict_types=1);

namespace faz\common\utils;

use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;

class Serializer {

    private LittleEndianNbtSerializer $serializer;

    public function __construct() {
        $this->serializer = new LittleEndianNbtSerializer();
    }

    public function encodeItems(array $itemData) : string {
        $itemsDataRaw = array_map(function(Item $item) {
            return base64_encode($this->serializer->write(new TreeRoot($item->nbtSerialize())));
        }, $itemData);
        return json_encode($itemsDataRaw);
    }

    public function decodeItems(string $dataRaw) : array {
        $dataRaw = json_decode($dataRaw, true);
        return array_map(function(string $data) {
            return Item::nbtDeserialize($this->serializer->read(base64_decode($data))->mustGetCompoundTag());
        }, $dataRaw);
    }
}