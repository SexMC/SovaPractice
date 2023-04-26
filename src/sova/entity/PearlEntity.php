<?php

namespace sova\entity;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
class PearlEntity extends EnderPearl{

    protected $gravity = 0.065;
    protected $drag = 0.0085;

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null){
        parent::__construct($location, $shootingEntity, $nbt);
    }

    public function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
        if($blockHit->getId() === BlockLegacyIds::BARRIER){
            $this->flagForDespawn();
        }
        parent::onHitBlock($blockHit, $hitResult);
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        if($this->isCollided || $this->isCollidedHorizontally || $this->isCollidedVertically){
            $this->flagForDespawn();
        }
        return parent::entityBaseTick($tickDiff);
    }

    public function canCollideWith(Entity $entity) : bool{
        return parent::canCollideWith($entity);
    }

    protected function onHit(ProjectileHitEvent $event) : void {
        $owner = $this->getOwningEntity();

        if ($owner instanceof Player && $owner->isAlive()) {
            if ($owner->getWorld()->getId() === $this->getWorld()->getId()) {
                $owner->teleport($event->getRayTraceResult()->getHitVector());

                $this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle());
                $this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound());

                $owner->getNetworkSession()->syncMovement($this->getPosition());
            }
        }

        if ($event instanceof ProjectileHitEntityEvent) $owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_CUSTOM, 0));
    }
}