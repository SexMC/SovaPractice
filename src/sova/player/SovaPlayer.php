<?php

declare(strict_types=1);

namespace sova\player;

use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use sova\item\lobby\ArenaItem;
use sova\Loader;
use sova\player\data\cooldowns\CooldownTrait;
use sova\player\data\knockback\KnockbackTrait;
use sova\player\data\scoreboard\ScoreboardTrait;
use sova\player\data\SessionizedData;
use sova\player\data\stats\StatsTrait;
use sova\player\rank\Rank;

class SovaPlayer extends Player
{
    use SessionizedData;
    use KnockbackTrait;
    use StatsTrait;
    use ScoreboardTrait;
    use CooldownTrait;

    protected Loader $plugin;

    public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag)
    {
        parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);
        $this->setPlugin(Loader::getInstance());

        $this->load();
        $this->initKnockback();
    }

    public function isInLobby(): bool
    {
        if ($this->getFFA() === null) {
            return true;
        }
        return false;
    }

    public function isVanished() : bool {
        return $this->isVanished;
    }

    public function toggleVanish() : void {
        $this->isVanished = !$this->isVanished;
    }

    public function isFrozen() : bool {
        return $this->isFrozen;
    }

    public function toggleFreeze() : void {
        $this->isFrozen = !$this->isFrozen;

        if ($this->isFrozen) {
            $this->setImmobile(true);
            $this->sendMessage(TextFormat::RED . "You have been frozen by a staff member!");
            return;
        }
        $this->setImmobile(false);
        $this->sendMessage(TextFormat::GREEN . "You have been unfrozen by a staff member!");
    }
    public function attack(EntityDamageEvent $source): void
    {
        $knockback = $this->getKnockback();

        if ($knockback->isCriticals() === false and $source->getModifier($source::MODIFIER_CRITICAL) > 0) {
            $source->setModifier(0, $source::MODIFIER_CRITICAL);
        }
        parent::attack($source);
    }

    public function initialize(): void
    {
        $this->getInventory()->clearAll();
        $this->getArmorInventory()->clearAll();
        $this->getEffects()->clear();
        $this->getCursorInventory()->clearAll();

        $this->setHealth($this->getMaxHealth());
        $this->getHungerManager()->setFood($this->getHungerManager()->getMaxFood());
        $this->getXpManager()->setXpAndProgress(0, 0);

        $this->giveLobbyItems();
        $this->setRankTag();
        $this->setFFA(null);

        $this->teleport($this->getPlugin()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
    }

    public function giveLobbyItems(): void
    {
        $inventory = $this->getInventory();

        $inventory->setContents([
            0 => new ArenaItem(), 
        ]);
    }

    protected function onHitGround(): ?float
    {
        $fallBlockPos = $this->location->floor();
        $fallBlock = $this->getWorld()->getBlock($fallBlockPos);
        if (count($fallBlock->getCollisionBoxes()) === 0) {
            $fallBlockPos = $fallBlockPos->down();
            $fallBlock = $this->getWorld()->getBlock($fallBlockPos);
        }
        $newVerticalVelocity = $fallBlock->onEntityLand($this);

        $damage = $this->calculateFallDamage($this->fallDistance);
        if ($damage > 0) {
            $ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
            $this->attack($ev);
        }

        return $newVerticalVelocity;
    }

    public function calculateFallDamage(float $fallDistance): float
    {
        return 0;
    }

    public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null): bool
    {
        $t = parent::teleport($pos, $yaw, $pitch);
        $this->broadcastMotion();
        $this->broadcastMovement(true);
        return $t;
    }

    protected function broadcastMovement(bool $teleport = false): void
    {
        $this->server->broadcastPackets($this->hasSpawned, [MoveActorAbsolutePacket::create(
            $this->id,
            $this->getOffsetPosition($this->location),
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw,
            (
                ($teleport ? MoveActorAbsolutePacket::FLAG_TELEPORT : 0) |
                ($this->onGround ? MoveActorAbsolutePacket::FLAG_GROUND : 0)
            )
        )]);
    }

    public function setRankTag(): void
    {
        $nickname = $this->getNickname() !== null ? $this->getNickname() : $this->getName();
        $rank = $this->getRank();

        $this->setNameTag(
            str_replace([
                '%player-name%',
                '%color%'
            ], [
                $nickname, $rank->getColor()
            ], Rank::RANK_TAG)
        );
    }

    public function getPotionCount(): int
    {
        $inventory = $this->getInventory();
        $count = 0;

        array_map(function (Item $item) use (&$count): void {
            if ($item->getId() === ItemIds::SPLASH_POTION) {
                $count++;
            }
        }, $inventory->getContents());
        return $count;
    }
}