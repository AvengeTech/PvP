<?php namespace pvp\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\Snowball;
use pocketmine\player\Player;

use pvp\entity\FlingBall as FlingBallEntity;

class FlingBall extends Snowball{

	protected function createEntity(Location $location, Player $thrower) : Throwable{
		return new FlingBallEntity($location, $thrower);
	}
	
}