<?php

declare(strict_types=1);
namespace sova\player\data;

use pocketmine\world\Position;
use sova\Loader;
use pocketmine\utils\TextFormat as C;
use sova\player\arena\Arena;
use sova\player\data\cooldowns\CooldownHandler;
use sova\player\data\scoreboard\ScoreboardHandler;
use sova\player\rank\Rank;

trait SessionizedData {
	protected ?Arena $ffa = null;
	protected ?Rank $rank = null;
    protected ?ScoreboardHandler $scoreboard = null;
    protected ?CooldownHandler $cooldown = null;
	protected ?string $nickname = null;
    protected ?Position $lastHitPosition = null;

    protected bool $isVanished = false;

    protected bool $isFrozen = false;

	public function setRank(?Rank $rank): void {
		$this->rank = $rank;
	}

	public function getRank(): ?Rank {
		return $this->rank;
	}

	public function setFFA(?Arena $ffa): void {
		$this->ffa = $ffa;
	}

	public function getFFA(): ?Arena {
		return $this->ffa;
	}

	public function getPlugin(): Loader {
		return $this->plugin;
	}

	public function setPlugin(Loader $plugin): void {
		$this->plugin = $plugin;
	}

	public function setNickname(?string $nickname): void {
		$this->nickname = $nickname;
	}

	public function getNickname(): ?string {
		return $this->nickname;
	}

    public function setLastHitPosition(?Position $lastHitPosition): void {
        $this->lastHitPosition = $lastHitPosition;
    }

    public function getLastHitPosition(): ?Position {
        return $this->lastHitPosition;
    }

    public function setScoreboard(?ScoreboardHandler $scoreboard): void {
        $this->scoreboard = $scoreboard;
    }

    public function getScoreboard(): ?ScoreboardHandler {
        return $this->scoreboard;
    }

    public function setCooldownHandler(?CooldownHandler $cooldown): void {
        $this->cooldown = $cooldown;
    }

    public function getCooldownHandler(): ?CooldownHandler {
        return $this->cooldown;
    }

    public function initScoreboard(): void {
        $this->setScoreboard(new ScoreboardHandler($this, C::colorize("&r&5&lSova Practice")));
    }

    public function initCooldownHandler(): void {
        $this->setCooldownHandler(new CooldownHandler($this));
    }
}