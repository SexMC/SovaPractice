<?php

declare(strict_types=1);
namespace sova\player\arena;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\Server;
use pocketmine\world\particle\BlockBreakParticle;
use sova\player\arena\form\FormFunctions;
use sova\player\arena\misc\MiscFunctions;
use sova\player\data\knockback\Knockback;
use sova\player\kit\ffa\NoDebuff;
use sova\player\kit\KitFactory;
use sova\player\SovaPlayer;
use sova\translation\Translation;

class Arena {
    public function __construct(
        protected string $name,
        protected int $maxPlayers,

        protected MiscFunctions $miscFunctions,
        protected FormFunctions $formFunctions,
    ){}


    public function listenForPlayerDeath(PlayerDeathEvent $event): void{
        /**@var SovaPlayer $player */
        $player = $event->getPlayer();
        $miscFunctions = $this->getMiscFunctions();
        $deathMessage = "";

        $cause = $player->getLastDamageCause();
        $kit = $miscFunctions->getKit();

        $event->setDrops([]);
        $event->setXpDropAmount(0);
        if(
            !$cause instanceof EntityDamageByEntityEvent ||
            !$cause->getDamager() instanceof SovaPlayer
        ){
            return;
        }
        /**@var SovaPlayer $attacker */
        $attacker = $cause->getDamager();

        if($kit instanceof NoDebuff){
            $deathMessage = Translation::translate("arena.nodebuff.death", [
                "victim" => $player->getName(),
                "killer" => $attacker->getName(),
                "killer_pots" => $attacker->getPotionCount(),
                "victim_pots" => $player->getPotionCount()
            ]);
        }

        $lightningPacket = new AddActorPacket();

        $lightningPacket->actorUniqueId = Entity::nextRuntimeId();
        $lightningPacket->actorRuntimeId = 1;
        $lightningPacket->position = $player->getPosition()->asVector3();
        $lightningPacket->type = "minecraft:lightning_bolt";
        $lightningPacket->yaw = $player->getLocation()->getYaw();
        $lightningPacket->syncedProperties = new PropertySyncData([], []);

        $soundPacket = PlaySoundPacket::create("ambient.weather.thunder", $player->getPosition()->x, $player->getPosition()->y, $player->getPosition()->z, 1, 1);

        Server::getInstance()->broadcastPackets($attacker->getWorld()->getPlayers(), [$lightningPacket, $soundPacket]);

        $player->setDeaths($player->getDeaths() + 1);
        $attacker->setKills($attacker->getKills() + 1);

        KitFactory::applyKit($attacker, $kit);
        $event->setDeathMessage($deathMessage);

    }

    public function listenForEntityDamageByEntity(EntityDamageByEntityEvent $event): void{
        $attacker = $event->getDamager();
        $entity = $event->getEntity();

        if(
            !$attacker instanceof SovaPlayer ||
            !$entity instanceof SovaPlayer
        ){
            return;
        }

        /**@var SovaPlayer $attacker */
        /**@var SovaPlayer $entity */


        $aHandler = $attacker->getCooldownHandler();
        $eHandler = $entity->getCooldownHandler();

        $aHandler->setTagged();
        $eHandler->setTagged();

        $entity->setLastHitPosition($attacker->getPosition());
    }

    public function getName(): string{
        return $this->name;
    }

    public function getKnockback(): Knockback{
        return $this->getMiscFunctions()->getKnockback();
    }

    public function getFormFunctions(): FormFunctions{
        return $this->formFunctions;
    }

    public function getMiscFunctions(): MiscFunctions{
        return $this->miscFunctions;
    }

    public function getPlayersCount(): int{
        return count($this->getMiscFunctions()->getWorld()->getPlayers());
    }

    public function getMaxPlayers(): int{
        return $this->maxPlayers;
    }

    public function isFull(): bool{
        return $this->getPlayersCount() >= $this->getMaxPlayers();
    }
}