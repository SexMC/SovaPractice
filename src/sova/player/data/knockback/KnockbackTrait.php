<?php

declare(strict_types=1);
namespace sova\player\data\knockback;

trait KnockbackTrait {
	protected Knockback $knockback;

	public function getKnockback(): Knockback {
		return $this->knockback;
	}

	public function setKnockback(Knockback $knockback): void {
		$this->knockback = $knockback;
	}

	protected function initKnockback(): void {
		$this->knockback = new Knockback(0.4, 0.4, 0.4, 0.4, true, true);
	}

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4) : void{
        $knockback = $this->getKnockback();

        $horizontal = $knockback->getHorizontal();
        $vertical = $knockback->getVertical();
        $max = $knockback->getMaxKB();
        $hLimiterEnabled = $knockback->isHeightLimiter();
        $hLimiterKnockback = $knockback->getHeightLimitKB();

        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }

        if($hLimiterEnabled && !$this->isOnGround() && ($position = $this->getLastHitPosition()) !== null){
            if(($dist = $this->getPosition()->getY() - $position->getY()) >= $max){
                $vertical -= $dist * $hLimiterKnockback;
            }
        }
        if(mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()){
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $horizontal;
            $motion->y += $vertical;
            $motion->z += $z * $f * $horizontal;

            if($motion->y > $vertical){
                $motion->y = $vertical;
            }
            $this->setMotion($motion);
        }
    }
}