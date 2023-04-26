<?php

declare(strict_types=1);
namespace sova\player\rank;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Colors;
use sova\Loader;

class RankFactory {
	/** @var Rank[] */
	private array $ranks = [];

	public function __construct(protected Loader $plugin) {
		$config = new Config($this->getLoader()->getDataFolder() . 'ranks.yml', Config::YAML);
		foreach ($config->get('ranks') as $rankName => $rankData) {
			try {
				$this->ranks[$rankName] = new Rank(
					$rankName,
					Colors::colorize($rankData['color']),
					Colors::colorize($rankData['display']),
					Colors::colorize($rankData['format']),
					$rankData['id'],
					$rankData['permissions'],
					[],
				);

				$inheritance = $rankData['inheritance'] ?? [];
				foreach ($inheritance as $inheritanceName) {
					$inheritanceRank = $this->getRank($inheritanceName);
					if ($inheritanceRank === null) {
						throw new \Exception("Rank $inheritanceName not found");
					}
					$this->ranks[$rankName]->addInheritance($inheritanceRank);
				}
			} catch (\Exception $exception) {
				$this->getLoader()->getLogger()->error($exception->getMessage());
			}
		}
	}

	public function getRank(string $name): ?Rank {
		return $this->ranks[$name] ?? null;
	}

	public function getRankById(int $id): ?Rank {
		foreach ($this->ranks as $rank) {
			if ($rank->getId() === $id) {
				return $rank;
			}
		}
		return null;
	}

    public function getRanks(): array {
        return $this->ranks;
    }

	public function getLoader(): Loader {
		return $this->plugin;
	}

	public function getDefaultRank(): Rank {
		return array_values($this->ranks)[0];
	}
}