<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pvp\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\item\PotionType;
use pocketmine\item\SplashPotion;
use pvp\entity\HealthPot;

class HealthPotItem extends SplashPotion
{

	public function __construct()
	{
		parent::__construct(new ItemIdentifier(ItemTypeIds::SPLASH_POTION), "Health Potion");
		$this->setType(PotionType::STRONG_HEALING());
	}

	protected function createEntity(Location $location, Player $thrower): Throwable
	{
		return new HealthPot($location, $thrower, $this->getType());
	}
}
