<?php

declare(strict_types=1);
namespace sova\player\arena;

use pocketmine\utils\Config;
use pocketmine\world\World;
use sova\Loader;
use sova\player\arena\form\FormFunctions;
use sova\player\arena\misc\MiscFunctions;
use sova\player\kit\KitFactory;

class ArenaFactory{

    /** @var Arena[] */
    protected array $arenas = [];
    public function __construct(private Loader $plugin){
        $config = new Config($this->plugin->getDataFolder() . "ffas.yml", Config::YAML);
        foreach ($config->get("ffas") as $name => $data) {
            try {
                $formData = $data["form"] ?? [];
                $formFunctions = new FormFunctions($formData["icon"]);

                $otherData = $data["other"] ?? [];
                $miscFunctions = new MiscFunctions(KitFactory::get($otherData["kit"]), $this->getWorldFromString($otherData["world"]));

                $maxPlayers = $otherData["max"] ?? 10;

                $this->arenas[$name] = new Arena(
                    $name,
                    $maxPlayers,
                    $miscFunctions,
                    $formFunctions
                );
            } catch (\Exception $exception) {
                $this->getPlugin()->getLogger()->error("Error while loading arena $name: " . $exception->getMessage());
            }
        }
    }
    public function getWorldFromString(string $world): ?World{
        $plugin = $this->getPlugin();

        return $plugin->getServer()->getWorldManager()->getWorldByName($world);
    }

    public function getArena(string $name): ?Arena{
        return $this->arenas[$name] ?? null;
    }

    public function getArenas(): array{
        return $this->arenas;
    }

    public function getPlugin(): Loader{
        return $this->plugin;
    }
}