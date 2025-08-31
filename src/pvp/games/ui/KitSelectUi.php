<?php namespace pvp\games\ui;

use pocketmine\player\Player;

use pvp\PvPPlayer;
use pvp\kits\PaidKit;
use pvp\games\type\Game;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class KitSelectUi extends SimpleForm{

	public array $kits = [];

	public function __construct(Player $player, public Game $game){
		parent::__construct("Kit Select", "Select the kit you'd like to use for this game!");
		foreach(($kl = $game->getSettings()->getKitLibrary())->getKits() as $kit){
			$this->kits[] = $kit;
			$this->addButton(new Button($kit->getName() . ($kit instanceof PaidKit ? PHP_EOL . $kit->getPrice() . " " . $kit->getCurrencyName() : "")));
		}
	}

	public function handle($response, Player $player) {
		/** @var PvPPlayer $player */
		$game = $this->game->get();
		if($game === null){
			$player->sendMessage(TextFormat::RI . "This game has ended!");
			return;
		}
		$kit = $this->kits[$response] ?? null;
		if($kit === null) return;
		if(
			$game->isStarted() ||
			(!is_null($game->getHandler()->getAvailableGameBy($game)) && $game->getStatus() === Game::GAME_COUNTDOWN)
		){
			$player->sendMessage(TextFormat::RI . "Kit select has already ended!");
			return;
		}
		$player->getGameSession()->getGame()->setKit($kit);
		if(is_null($game->getHandler()->getAvailableGameBy($game))){
			$kit->equip($player, true);
		}
		$player->sendMessage(TextFormat::GI . "You selected the " . TextFormat::YELLOW . $kit->getName() . TextFormat::GRAY . " kit!");
	}
    
}