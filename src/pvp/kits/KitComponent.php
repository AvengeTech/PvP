<?php namespace pvp\kits;

use pvp\PvP;

use core\session\component\BaseComponent;

class KitComponent extends BaseComponent{

	public ?Kit $kit = null;

	public function getName() : string{
		return "kits";
	}

	public function getKit() : ?Kit{
		return $this->kit;
	}

	public function setKit(Kit|string $kit = null, bool $equip = true) : void{
		$kit = (!$kit instanceof Kit && $kit !== null) ? PvP::getInstance()->getKits()->getKit($kit) : $kit;
		$this->kit = $kit;
		if($equip && ($player = $this->getPlayer()) !== null){
			if($kit === null){
				$player->getArmorInventory()->clearAll();
				$player->getCursorInventory()->clearAll();
				$player->getInventory()->clearAll();
			}else{
				$kit->equip($player);
			}
		}
	}

	public function hasKit() : bool{
		return $this->kit !== null;
	}

}