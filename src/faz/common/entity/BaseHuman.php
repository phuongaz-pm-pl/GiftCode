<?php

declare(strict_types=1);

namespace faz\common\entity;

use matze\pathfinder\result\PathResult;
use matze\pathfinder\setting\Settings;
use matze\pathfinder\type\AsyncPathfinder;
use pocketmine\block\Air;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\SlabType;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

abstract class BaseHuman extends Human {

    protected int $jumpTicks = 0;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
        parent::__construct($location, $skin, $nbt);
    }

    public function moveTo(Vector3 $vector3, ?\Closure $callback) : void {
        $settings = Settings::get()->setJumpHeight((int)$this->getJumpVelocity());
        $pathFinder = new AsyncPathfinder($settings, $this->getWorld());
        $pathFinder->findPath($this->getPosition()->asVector3(), $vector3, function(?PathResult $result) use ($callback, $vector3) {
            if($result instanceof PathResult) {
                foreach($result->getNodes() as $pos) {
                    $this->doMovement($pos);
                }
            }
            $callback($result);
        });
    }

    public function doMovement(Vector3 $targetPos) : void {
        $facing = $this->getHorizontalFacing();

        $location = $this->getLocation();

        $motion = $this->getMotion();

        $xDist = $targetPos->x - $location->x;
        $zDist = $targetPos->z - $location->z;
        $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;

        if ($yaw < 0) {
            $yaw += 360.0;
        }

        $this->setRotation($yaw, 0);

        $x = -1 * sin(deg2rad($yaw));
        $z = cos(deg2rad($yaw));
        $directionVector = (new Vector3($x, 0, $z))->normalize()->multiply(0.4);

        $motion->x = $directionVector->x;
        $motion->z = $directionVector->z;

        if ($this->isOnGround() && $this->isCollidedHorizontally && $this->jumpTicks <= 0) {

            $isFullJump = null;

            $block = $location->getWorld()->getBlock($location);

            $aboveBlock = $location->getWorld()->getBlock($location->add(0, 1, 0));

            $frontBlock = $location->getWorld()->getBlock($location->add(0, 0.5, 0)->getSide($facing));

            $secondFrontBlock = $location->getWorld()->getBlock($frontBlock->getPosition()->add(0, 1, 0));

            if ($block instanceof Air && $aboveBlock instanceof Air && !$frontBlock instanceof Air && $secondFrontBlock instanceof Air) {
                if (($frontBlock instanceof Slab && !$frontBlock->getSlabType()->equals(SlabType::TOP()) && !$frontBlock->getSlabType()->equals(SlabType::DOUBLE())) || ($frontBlock instanceof Stair && !$frontBlock->isUpsideDown() && $frontBlock->getFacing() === $facing)) {
                    $isFullJump = false;
                } else {
                    $isFullJump = true;
                }
            } elseif ($block instanceof Stair || $block instanceof Slab && $frontBlock instanceof Air && $secondFrontBlock instanceof Air && $aboveBlock instanceof Air) {
                $isFullJump = false;
            }

            if ($isFullJump !== null) {
                $motion->y = ($isFullJump ? 0.42 : 0.3) + $this->gravity;
                $this->jumpTicks = $isFullJump ? 5 : 2;
            }

            if ($motion->y > 0) {
                $motion->x /= 3;
                $motion->z /= 3;
            }
        }
        $this->setMotion($motion);
    }

    public function lookAtLocation(Location $location): array{
        $angle = atan2(
            $location->z - $this->getLocation()->z,
            $location->x - $this->getLocation()->x);
        $yaw = (($angle * 180) / M_PI) - 90;
        $angle = atan2((new Vector2($this->getLocation()->x, $this->getLocation()->z))->distance(new Vector2($location->x, $location->z)), $location->y - $this->getLocation()->y);
        $pitch = (($angle * 180) / M_PI) - 90;

        $this->setRotation($yaw, $pitch);

        return [$yaw, $pitch];
    }
}