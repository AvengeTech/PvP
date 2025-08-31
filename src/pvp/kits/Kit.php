<?php namespace pvp\kits;

use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

use pvp\PvP;

class Kit{
	
	public function __construct(
		public string $name,
		public array $items = [],
		public array $armor = [],
		public string $icon = ""
	){}

	public function getName() : string{
		return $this->name;
	}

	public function getItems() : array{
		return $this->items;
	}

	public function getArmor() : array{
		return $this->armor;
	}
	
	public function getIcon() : string{
		return $this->icon;
	}

	public function equip(Player $player, bool $clear = true) : void{
		if($clear){
			$player->getArmorInventory()->clearAll();
			$player->getCursorInventory()->clearAll();
			$player->getInventory()->clearAll();
		}

		$ai = $player->getArmorInventory();
		foreach($this->getArmor() as $slot => $piece){
			if ($ai->getItem($slot)->getTypeId() === VanillaItems::AIR()->getTypeId()) {
				$ai->setItem($slot, $piece);
			}else{
				$player->getInventory()->addItem($piece);
			}
		}
		PvP::getInstance()->getEnchantments()->calculateCache($player);

		foreach($this->getItems() as $item){
			$player->getInventory()->addItem($item);
		}
	}

}