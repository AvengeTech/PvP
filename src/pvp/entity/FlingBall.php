<?php namespace pvp\entity;

use pocketmine\entity\projectile\Snowball;
use pocketmine\event\entity\{
	ProjectileHitEvent,
	ProjectileHitEntityEvent
};

use pvp\PvP;

class FlingBall extends Snowball{

	protected function onHit(ProjectileHitEvent $event) : void{
		parent::onHit($event);

		if($this->getOwningEntity() !== null){
			if($event instanceof ProjectileHitEntityEvent){
				PvP::getInstance()->getEnchantments()->getCalls()->drag($event->getEntityHit(), $this->getOwningEntity(), 1);
			}else{
				PvP::getInstance()->getEnchantments()->getCalls()->repel($this->getOwningEntity(), $this, 1.5);
			}
		}
	}

}