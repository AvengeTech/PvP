<?php namespace pvp\arenas\ui;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\arenas\Arena;
use pvp\kits\PaidKit;
use pvp\PvPPlayer;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ArenaKitSelectUi extends SimpleForm{

	public array $kits = [];

	public function __construct(Player $player, public Arena $arena){
		parent::__construct($arena->getName(), "Select a kit you'd like to equip");
		$arenas = PvP::getInstance()->getArenas();
		foreach($arena->getKitLibrary()->getKits() as $kit){
			$this->addButton(new Button(TextFormat::AQUA . $kit->getName() . PHP_EOL . TextFormat::GRAY . count($arena->getPlayers()) . " playing", "url", $kit->getIcon()));
			$this->kits[] = $kit;
		}
		$this->addButton(new Button("No kit"));
	}

	public function handle($response, Player $player){
		/** @var PvPPlayer $player */
		if($player->getGameSession()->getGame()->inGame()){
			$player->sendMessage(TextFormat::RI . "You can't teleport to an arena while in a game!");
			return;
		}
		if($this->arena->isLocked()){
			$player->sendMessage(TextFormat::RI . "The arena you are trying to access is locked!");
			return;
		}
		$kit = $this->kits[$response] ?? null;
		if($kit !== null){
			if($kit instanceof PaidKit){
				//extra shiiiii
			}else{
				$player->setKit($kit);
			}
		}
		$this->arena->teleportTo($player);
	}
}