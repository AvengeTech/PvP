<?php namespace pvp\arenas\ui;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\kits\PaidKit;
use pvp\PvPPlayer;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ArenaSelectUi extends SimpleForm{

	public array $arenas = [];

	public function __construct(PvPPlayer $player, public bool $spectate = false){
		parent::__construct(($spectate ? "Spectate " : "") . "Arenas", "Select the arena you'd like to " . ($spectate ? "spectate" : "warp to") . "!");
		if(!$spectate){
			$this->addButton(new Button("Spectate"));
		}
		$arenas = PvP::getInstance()->getArenas();
		foreach($arenas->getArenas() as $arena){
			if(!$arena->isLocked() || $player->isStaff()){
				$this->addButton(new Button(TextFormat::DARK_GREEN . $arena->getName() . PHP_EOL . TextFormat::DARK_GRAY . count($arena->getPlayers()) . " playing", "url", $arena->getIcon()));
				$this->arenas[] = $arena;
			}
		}
		if($spectate){
			$this->addButton(new Button("Go back"));
		}
	}

	public function handle($response, Player $player){
		/** @var PvPPlayer $player */
		if(!$this->spectate && $response === 0){
			$player->showModal(new ArenaSelectUi($player, true));
			return;
		}
		if($this->spectate && $response === count($this->arenas)){
			$player->showModal(new ArenaSelectUi($player));
			return;
		}
		$arena = $this->arenas[$response - ($this->spectate ? 0 : 1)] ?? null;
		if($arena !== null){
			if($player->getGameSession()->getGame()->inGame()){
				$player->sendMessage(TextFormat::RI . "You can't teleport to an arena while in a game!");
				return;
			}
			if($arena->isLocked()){
				$player->sendMessage(TextFormat::RI . "The arena you are trying to access is locked!");
				return;
			}
			
			if(!$this->spectate){
				if(($kl = $arena->getKitLibrary()) === null || count($kl->getKits()) == 0){
					$arena->teleportTo($player);
				}elseif(count($kl->getKits()) === 1){
					if(($kit = $kl->getKit()) instanceof PaidKit){
						$player->showModal(new ArenaKitSelectUi($player, $arena));
					}else{
						$player->setKit($kit);
						$arena->teleportTo($player);
					}
				}else{
					$player->showModal(new ArenaKitSelectUi($player, $arena));
				}
			}else{
				$arena->teleportTo($player, true);
			}
		}
	}
}