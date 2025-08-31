<?php namespace pvp\games\ui;

use pocketmine\player\Player;

use pvp\PvPPlayer;
use pvp\games\GameManager;
use pvp\games\type\PracticeGame;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ActiveGamesUi extends SimpleForm{

	public array $games = [];

	public function __construct(public bool $practice){
		parent::__construct("Active " . ($practice ? "Practice " : "") . "Games", "Select a game below to spectate it!");
		foreach(GameManager::getInstance()->getGameHandlers() as $handler){
			if($practice === $handler->getBaseGame() instanceof PracticeGame){
				foreach($handler->getGames() as $game){
					if($game->isStarted()){
						$this->games[] = $game;
						$this->addButton(new Button($game->getName() . " " . $game->getId() . PHP_EOL . "Status: " . $game->getStatusName()));
					}
				}
			}
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var PvPPlayer $player */
		$game = $this->games[$response] ?? null;
		if($game !== null){
			$game = $game->get();
			if($game === null){
				$player->sendMessage(TextFormat::RI . "This game no longer exists.");
				return;
			}
			$game->addSpectator($player);
			$player->sendMessage(TextFormat::GI . "Now spectating " . TextFormat::YELLOW . $game->getName() . " " . $game->getId());
			return;
		}
		$player->showModal(new GameSelectUi($player, $this->practice));
	}

}