<?php

declare(strict_types=1);
namespace sova\player\rank;

use sova\Loader;
use sova\player\SovaPlayer;

class Rank {
	const RANK_TAG = "%color% %player-name%";
	protected string $name;
	protected string $format;
	protected string $color;
	protected string $display;

	protected int $id;
	protected array $inheritance = [];
	protected array $permissions = [];

	public function __construct(string $name, string $color, string $display, string $format, int $id, array $permissions, array $inheritance) {
		$this->name = $name;
		$this->color = $color;
		$this->display = $display;
		$this->format = $format;
		$this->id = $id;
		$this->permissions = $permissions;
		$this->inheritance = $inheritance;
	}

	public function addInheritance(Rank $rank): void {
		if ($rank === $this) {
			throw new \InvalidArgumentException("Rank cannot inherit itself");
		}

		$permissions = $rank->getPermissions();
		$this->permissions = array_merge($this->permissions, $permissions);
	}

	public function bindPermissionsToPlayer(SovaPlayer $player): void {
		$effectivePermissions = $player->getEffectivePermissions();

		foreach ($effectivePermissions as $permission) {
			$attachment = $permission->getAttachment();
			if ($attachment === null) {
				continue;
			}

			$player->removeAttachment($attachment);
		}

		foreach ($this->getPermissions() as $permission) {
			$player->addAttachment(Loader::getInstance(), $permission, true);
		}
	}

	public function getFormat(): string {
		return $this->format;
	}

	public function getColor(): string {
		return $this->color;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getPermissions(): array {
		return $this->permissions;
	}

    public function getInheritance(): array {
        return $this->inheritance;
    }

    public function getDisplay(): string {
        return $this->display;
    }

    public function getName(): string {
        return $this->name;
    }
}