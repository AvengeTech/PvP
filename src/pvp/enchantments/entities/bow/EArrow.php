<?php namespace pvp\enchantments\entities\bow;

use pocketmine\entity\projectile\Arrow;

class EArrow extends Arrow{

	public function canSaveWithChunk() : bool{
		return false;
	}
}