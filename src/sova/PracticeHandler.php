<?php

declare(strict_types=1);
namespace sova;

use core\abstract\AbstractListener;
use Exception;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Location;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\utils\TextFormat;
use sova\player\arena\event\ArenaTeleportEvent;
use sova\player\kit\KitFactory;
use sova\player\rank\event\RankAlterEvent;
use sova\player\SovaPlayer;
use sova\player\SovaPlayer as Player;
use sova\translation\Translation;
use function Symfony\Component\String\b;

class PracticeHandler extends AbstractListener {
    public const CHEMISTRY_PACK_ID = "0fba4063-dba1-4281-9b89-ff9390653530";
	/**
	 * @param PlayerCreationEvent $event
	 * @return void
	 * @priority LOWEST
	 */
	public function listenForPlayerCreation(PlayerCreationEvent $event): void {
		$event->setPlayerClass(Player::class);
	}

	/**
	 * @priority LOWEST
	 *
	 * @param PlayerLoginEvent $event
	 * @return void
	 * @throws Exception
	 */
	public function listenForPlayerLogin(PlayerLoginEvent $event): void {
		$player = $event->getPlayer();
		if (!( $player instanceof Player )) {
			throw new Exception("Player is not an instance of SovaPlayer!");
		}
	}

	/**
	 * @param PlayerChatEvent $event
	 * @return void
	 * @igornoreCancelled true
	 */
	public function listenForPlayerChat(PlayerChatEvent $event): void {
		/** @var Player $player */
		$player = $event->getPlayer();
		$message = $event->getMessage();

		$rank = $player->getRank();
		$name = $player->getNickname() !== null ? $player->getNickname() : $player->getName();

		$event->setFormat(
			str_replace([
				"%player-name%",
				"%message%",
			], [
				$name,
				$message,
			], $rank->getFormat())
		);
	}

    /**
     * @param ArenaTeleportEvent $event
     * @return void
     * @igornoreCancelled true
     */
    public function listenForArenaTeleport(ArenaTeleportEvent $event): void{
        /** @var Player $player */
        $player = $event->getPlayer();
        $arena = $event->getArena();

        if($arena->isFull()){
            return;
        }

        $miscFunctions = $arena->getMiscFunctions();
        $world = $miscFunctions->getWorld();

        if($world === null){
            return;
        }

        $kit = $miscFunctions->getKit();
        $knockback = $arena->getKnockback();

        $player->teleport(Location::fromObject($world->getSpawnLocation(), $world));
        $player->setKnockback($knockback);
        $player->setFFA($arena);

        KitFactory::applyKit($player, $kit);
    }

    public function listenForRankAlter(RankAlterEvent $event): void{
        /** @var Player $player */
        $player = $event->getPlayer();

        $rank = $event->getRank();

        if($player->getRank() === $rank){
            return;
        }
        $player->setRank($rank);
        $player->setRankId($rank->getId());
        $player->setRankTag();
        $rank->bindPermissionsToPlayer($player);

        if($event->shouldSave()){
            $player->saveData();
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function listenForPlayerQuit(PlayerQuitEvent $event): void {
        /** @var Player $player */
        $player = $event->getPlayer();

        $player->saveData();

        $event->setQuitMessage(Translation::translate("quit-message", ["player" => $player->getName()]));
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function listenForPlayerJoin(PlayerJoinEvent $event): void {
        /** @var Player $player */
        $player = $event->getPlayer();
        $server = $player->getServer();

        $default = $server->getWorldManager()->getDefaultWorld();
        $player->teleport(Location::fromObject($default->getSpawnLocation(), $default));

        $player->initialize();

        $player->sendMessage(Translation::translate("info-message"));
        $event->setJoinMessage(Translation::translate("join-message", ["player" => $player->getName()]));
    }

    public function listenForPlayerRespawn(PlayerRespawnEvent $event): void{
        /** @var Player $player */
        $player = $event->getPlayer();

        $player->initialize();

    }

    public function listenForEntityDamage(EntityDamageEvent $event): void{
        if($event->getCause() === EntityDamageEvent::CAUSE_FALL){
            $event->cancel();
        }
        $entity = $event->getEntity();

        if ($event instanceof EntityDamageByEntityEvent) {
            $player = $event->getDamager();

            if ($entity instanceof SovaPlayer && $player instanceof SovaPlayer) {
                if ($player->getInventory()->getItemInHand()->getName() === TextFormat::RED . "Freeze Block" . TextFormat::GRAY . " (Hit Player)") {
                    $event->cancel();
                    $entity->toggleFreeze();
                }
            }
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     * @igornoreCancelled true
     */
    public function listenForEntityAttack(EntityDamageByEntityEvent $event): void{
        /** @var Player $entity */
        $entity = $event->getEntity();

        if($entity->isInLobby()){
            $event->cancel();
        }
        $arena = $entity->getFFA();
        if($arena === null){
            return;
        }
        $arena->listenForEntityDamageByEntity($event);

    }

    public function listenForPlayerDeath(PlayerDeathEvent $event): void{
        /** @var Player $player */
        $player = $event->getPlayer();

        if($player->isInLobby()){
            return;
        }

        $arena = $player->getFFA();
        if($arena === null){
            return;
        }
        $arena->listenForPlayerDeath($event);
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     */
    public function listenForPacketRecieve(DataPacketReceiveEvent $event): void{
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if ($player === null) {
            return;
        }

        switch ($packet->pid()) {
            case LevelSoundEventPacket::NETWORK_ID:
                /** @var LevelSoundEventPacket $packet */
                $sound = $packet->sound;

                if(in_array($sound,
                    [LevelSoundEvent::ATTACK_NODAMAGE,
                        LevelSoundEvent::ATTACK_STRONG
                    ])){
                    $player->broadcastAnimation(new ArmSwingAnimation($player));

                }
                break;
        }
    }

    /**
     * @param DataPacketSendEvent $event
     * @return void
     */
    public function listenForDataPacketSend(DataPacketSendEvent $event): void{
        $flag = false;
        $packets = $event->getPackets();
        foreach($packets as $key => $packet){
            if($packet->pid() === LevelSoundEventPacket::NETWORK_ID && $packet instanceof LevelSoundEventPacket && ($packet->sound === LevelSoundEvent::ATTACK || $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE || $packet->sound === LevelSoundEvent::ATTACK_STRONG)){
                $flag = true;
                unset($packets[$key]);
            }
        }
        if($flag){
            $event->cancel();
            if(count($packets) > 0){
                $recipients = $event->getTargets();
                $broadcasters = [];
                $broadcasterTargets = [];
                foreach($recipients as $recipient){
                    $broadcaster = $recipient->getBroadcaster();
                    $broadcasters[spl_object_id($broadcaster)] = $broadcaster;
                    $broadcasterTargets[spl_object_id($broadcaster)][] = $recipient;
                }
                foreach($broadcasters as $broadcaster){
                    $broadcaster->broadcastPackets($broadcasterTargets[spl_object_id($broadcaster)], $packets);
                }
            }
        }
        if ($packets instanceof ResourcePackStackPacket) {
            $resourcePackStack = $packet->resourcePackStack;
            foreach ($resourcePackStack as $index => $resourcePack) {
                if ($resourcePack->getPackId() == self::CHEMISTRY_PACK_ID) {
                    unset($resourcePackStack[$index]);
                }
            }
        }
    }

    public function listenForPlayerDropItem(PlayerDropItemEvent $event): void{
        $event->cancel();
    }

    public function listenForLeavesDecay(LeavesDecayEvent $event): void{
        $event->cancel();
    }

    public function listenForHungerExhaustion(PlayerExhaustEvent $event): void{
        $event->cancel();
    }

    public function listenForBlockSpread(BlockSpreadEvent $event): void{
        $event->cancel();
    }

    public function listenForBlockBreak(BlockBreakEvent $event): void{
        $event->cancel();
    }
    /**
     * @priority LOWEST
     */
    public function onSwitching(EntityDamageByEntityEvent $event){
        if($event->getModifier(EntityDamageEvent::MODIFIER_PREVIOUS_DAMAGE_COOLDOWN) < 0){
            $event->cancel();
        }
    }
    public function listenForBlockPlace(BlockPlaceEvent $event): void{
        $event->cancel();
    }

}