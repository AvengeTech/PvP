<?php namespace pvp\games;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\games\command\{
	GameCommand,
	PracticeBot,
	TpCommas
};
use pvp\games\type\Game;

use core\utils\TextFormat;

class Games{

	public GameManager $gameManager;

	public function __construct(public PvP $plugin){
		$this->gameManager = new GameManager();

		$plugin->getServer()->getCommandMap()->register("games", new GameCommand($plugin, "game", "Games"));
		$plugin->getServer()->getCommandMap()->register("games", new PracticeBot($plugin, "pb", "pbj"));
		$plugin->getServer()->getCommandMap()->register("games", new TpCommas($plugin, "tpc", "pbj"));
	}

	public function tick() : void{
		$this->getGameManager()->tick();
	}

	public function getGameManager() : GameManager{
		return $this->gameManager;
	}

	public function close() : void{
		$this->getGameManager()->close();
	}

	public function onJoin(Player $player) : void{

	}

	public function onQuit(Player $player) : void{
		/** @var PvPPlayer $player */
		if(!$player->isLoaded()) return;
		$gs = $player->getGameSession()->getGame();
		if($gs->inGame()){
			$game = $gs->getGame();
			if($game->isSpectator($player)){
				$game->removeSpectator($player);
			}elseif($game->hasPlayer($player)){
				foreach($game->getViewers() as $viewer){
					$viewer->sendMessage(TextFormat::RI . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " has left the match.");
				}
				
				switch($game->getStatus()){
					case Game::GAME_WAITING:
					case Game::GAME_LOBBY_COUNTDOWN:
					case Game::GAME_END:
						$game->removePlayer($player, false, true);
						break;
					case Game::GAME_COUNTDOWN:
						if($game->getRound() === 1){
							$game->removePlayer($player, false, true);
							break;
						}
						$game->eliminate($player, false, true);
						break;
					default:
					case Game::GAME_START:
					case Game::GAME_DEATHMATCH_COUNTDOWN:
					case Game::GAME_DEATHMATCH:
						$game->eliminate($player, false, true);
						break;
				}
			}
		}
		//$this->getGameManager()->getQueue($player)?->removePlayer($player);
	}

}