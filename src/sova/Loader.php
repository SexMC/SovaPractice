<?php

declare(strict_types=1);
namespace sova;

use CortexPE\Commando\PacketHooker;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use sova\command\PingCommand;
use sova\command\RankCommand;
use sova\command\RekitCommand;
use sova\command\SpawnCommand;
use sova\command\VanishCommand;
use sova\database\Database;
use sova\entity\PearlEntity;
use sova\entity\SplashPotion;
use sova\item\game\EnderPearlItem;
use sova\item\game\SplashPotionItem;
use sova\player\arena\ArenaFactory;
use sova\player\kit\KitFactory;
use sova\player\rank\RankFactory;
use sova\translation\Translation;

class Loader extends PluginBase
{
    static protected ?Loader $instance = null;
    protected RankFactory $rankFactory;
    protected ArenaFactory $arenaFactory;
    protected Database $database;

    protected function onLoad(): void{
        self::$instance = $this;
        $this->saveResource("ranks.yml");
        $this->saveResource("ffas.yml");
        $this->saveResource("database.yml");
        $this->saveResource("knockback.yml");
        $this->saveResource("messages.yml");

        foreach (glob($this->getServer()->getDataPath() . "worlds/*") as $world) {
            $world = str_replace($this->getServer()->getDataPath() . "worlds/", "", $world);
            $worldManager = $this->getServer()->getWorldManager();

            if (!$worldManager->isWorldLoaded($world)) $worldManager->loadWorld($world);

            foreach ($worldManager->getWorlds() as $world) $world->stopTime();
        }
        $this->getServer()->getNetwork()->setName("§l§5Sova Practice");
    }

	protected function onEnable(): void {
        PacketHooker::register($this);

        $this->registerItems();
        $this->registerEntities();

        KitFactory::init($this);
        Translation::init($this);
        $this->registerHandlers();
        $this->initManagers();
        $this->registerCommands();
	}

	protected function initManagers(): void {
		$this->rankFactory = new RankFactory($this);
        $this->arenaFactory = new ArenaFactory($this);
		$this->database = new Database($this);
	}

    public function registerCommands(): void{
        $map = $this->getServer()->getCommandMap();

        $map->registerAll("Sova", [
            new RankCommand($this),
            new SpawnCommand($this),
            new RekitCommand($this),
            new PingCommand($this),
            new VanishCommand($this)
        ]);
    }

	private function registerHandlers(): void {
		new PracticeHandler($this);
	}

    protected function registerItems(): void{
        ItemFactory::getInstance()->register(new EnderPearlItem(), true);
        foreach (PotionType::getAll() as $potionType) {
            ItemFactory::getInstance()->register(new SplashPotionItem($potionType), true);
        }
    }

    protected function registerEntities(): void{
        EntityFactory::getInstance()->register(PearlEntity::class, static function (World $world, CompoundTag $nbt): PearlEntity {
            return new PearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityLegacyIds::ENDER_PEARL);

        EntityFactory::getInstance()->register(SplashPotion::class, static function (World $world, CompoundTag $nbt): SplashPotion {
            $potionType = PotionTypeIdMap::getInstance()->fromId($nbt->getShort('PotionId', PotionTypeIds::WATER));

            if ($potionType === null) {
                throw new SavedDataLoadingException;
            }
            return new SplashPotion(EntityDataHelper::parseLocation($nbt, $world), null, $potionType, $nbt);

        }, ['ThrownPotion', 'minecraft:potion', 'thrownpotion'], EntityLegacyIds::SPLASH_POTION);
    }

	public function getRankFactory(): RankFactory {
		return $this->rankFactory;
	}

    public function getArenaFactory(): ArenaFactory {
        return $this->arenaFactory;
    }

    public function getDatabase(): Database {
		return $this->database;
	}

	public static function getInstance(): Loader {
		return self::$instance;
	}
}