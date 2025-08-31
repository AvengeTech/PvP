<?php namespace pvp\hotbar;

use pocketmine\player\Player;

use pvp\hotbar\utils\HotbarHandler;

use core\session\component\BaseComponent;

class HotbarComponent extends BaseComponent{

	public int $clickDelay = 0;

	public ?HotbarHandler $hotbar = null;

	public function getName() : string{
		return "hotbar";
	}

	public function tick() : void{
		$player = $this->getPlayer();
		if(
			$player !== null &&
			$player->isConnected() &&
			$this->hasHotbar() &&
			$this->getHotbar()->ticks()
		){
			$this->getHotbar()->tick($player);
		}
	}

	public function setClicked() : void{
		$this->clickDelay = time();
	}

	public function canClick() : bool{
		return $this->clickDelay !== time();
	}

	public function getHotbar() : ?HotbarHandler{
		return $this->hotbar;
	}

	public function hasHotbar() : bool{
		return $this->getHotbar() !== null;
	}

	public function setHotbar(?HotbarHandler $hotbar = null, bool $clear = true) : void{
		$old = $this->getHotbar();
		$this->hotbar = $hotbar;
		if(($player = $this->getPlayer()) instanceof Player){
			if($hotbar !== null){
				$hotbar->setup($player, $clear);
			}else{
				if($clear){
					$player->getArmorInventory()->clearAll();
					$player->getCursorInventory()->clearAll();
					$player->getInventory()->clearAll();
				}
			}
		}
	}

}