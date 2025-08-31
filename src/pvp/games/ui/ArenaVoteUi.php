<?php namespace pvp\games\ui;

use pocketmine\player\Player;

use pvp\games\type\Game;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ArenaVoteUi extends SimpleForm{

	public array $arenas = [];

	public function __construct(Player $player, public Game $game){
		parent::__construct("Map Vote", "Select the map you'd like to use for this game!");
		foreach(($av = $game->getArenaVote())->getArenas() as $arena){
			$this->arenas[] = $arena;
			$this->addButton(new Button($arena->getName() . PHP_EOL . $av->getVoteTotal($arena) . " votes"));
		}
	}

	public function handle($response, Player $player){
		$game = $this->game->get();
		if($game === null){
			$player->sendMessage(TextFormat::RI . "This game has ended!");
			return;
		}
		$arena = $this->arenas[$response] ?? null;
		if($arena === null) return;
		if($game->getStatus() > Game::GAME_LOBBY_COUNTDOWN){
			$player->sendMessage(TextFormat::RI . "Map vote has already ended!");
			return;
		}
		$game->getArenaVote()->vote($player, $arena);
		$player->sendMessage(TextFormat::GI . "You voted for the " . TextFormat::AQUA . $arena->getName() . TextFormat::GRAY . " map!");
	}

}