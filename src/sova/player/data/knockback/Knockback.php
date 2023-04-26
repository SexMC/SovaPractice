<?php

declare(strict_types=1);
namespace sova\player\data\knockback;

class Knockback {
	public function __construct(
		protected float $horizontal,
		protected float $vertical,

		protected float $maxKB,
		protected float $heightLimitKB,

		protected bool $criticals,
		protected bool $heightLimiter
	) {
	}

	public function getHorizontal(): float {
		return $this->horizontal;
	}

	public function getVertical(): float {
		return $this->vertical;
	}

	public function getMaxKB(): float {
		return $this->maxKB;
	}

	public function getHeightLimitKB(): float {
		return $this->heightLimitKB;
	}

	public function isCriticals(): bool {
		return $this->criticals;
	}

	public function isHeightLimiter(): bool {
		return $this->heightLimiter;
	}

    public static function parse(array $data): Knockback{
        return new Knockback(
            $data["horizontal"],
            $data["vertical"],
            $data["maxKB"],
            $data["heightLimitKB"],
            $data["criticals"],
            $data["heightLimiter"]
        );
    }
}