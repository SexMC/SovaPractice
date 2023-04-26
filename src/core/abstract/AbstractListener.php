<?php

declare(strict_types=1);
namespace core\abstract;

use pocketmine\event\Listener;
use sova\Loader;

class AbstractListener implements Listener {
	public function __construct(protected Loader $plugin) {
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function getPlugin(): Loader {
		return $this->plugin;
	}

	public function getServer() {
		return $this->getPlugin()->getServer();
	}
}