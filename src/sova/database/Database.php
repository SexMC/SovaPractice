<?php

declare(strict_types=1);
namespace sova\database;

use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use sova\Loader;

class Database implements Statements {
	protected DataConnector $db;

	public function __construct(protected Loader $plugin) {
		$config = new Config($this->getPlugin()->getDataFolder() . 'database.yml', Config::YAML);

		$this->setDB(libasynql::create($this->getPlugin(), $config->get('database'), self::PSFS ?? []));
		$this->initializeTables();
	}

	public function initializeTables(): void {
		$this->getMysqlHandler()->executeGeneric(self::INIT_STATS);
	}

	protected function setDB(DataConnector $db): void {
		$this->db = $db;
	}

	public function getMysqlHandler(): DataConnector {
		return $this->db;
	}

	public function getPlugin(): Loader {
		return $this->plugin;
	}
}