<?php namespace pvp\games\ui;

use pocketmine\player\Player;

use pvp\games\type\Game;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class GameSpectateUi extends SimpleForm{

	public array $players = [];

	public function __construct(public Game $game){
		parent::__construct("Teleporter", "Tap a player below to teleport to them!");
		foreach($game->getPlayers() as $player){
			if(!$game->isRespawning($player->getPlayer())){
				$this->players[] = $player->getPlayer();
				$this->addButton(new Button($player->getPlayer()->getName()));
			}
		}
	}

	public function handle($response, Player $player){
		$game = $this->game->get();
		if($game === null || $game->isEnded()){
			$player->sendMessage(TextFormat::RI . "This game has already ended!");
			return;
		}

		$pl = $this->players[$response] ?? null;
		if($pl === null || !$game->hasPlayer($pl)){
			$player->sendMessage(TextFormat::RI . "This player is no longer in the game!");
			return;
		}

		$player->teleport($pl->getPosition());
		$player->sendMessage(TextFormat::GI . "Teleported to " . TextFormat::YELLOW . $pl->getName());
	}

}