<?php namespace pvp\techits;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\item\ItemFactory;

use pvp\PvP;
use pvp\techits\commands\{
	AddTechits,
	SetTechits,
	TopTechits,
	MyTechits,
	TechitNote
};
use pvp\{
	PvPPlayer,
	pvp\techits\item\TechitNote as TechitNoteItem
};

use core\Core;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\user\User;

class Techits{

	const TYPE_SERVER = 0;
	const TYPE_DATABASE = 1;

	public function __construct(public PvP $plugin){
		$plugin->getServer()->getCommandMap()->registerAll("techits", [
			new AddTechits($plugin, "addtechits", "Give player techits"),
			new SetTechits($plugin, "settechits", "Set player techits"),
			new TopTechits($plugin, "toptechits", "Top techits"),
			new MyTechits($plugin, "mytechits", "Check your techits"),
			//new TechitNote($plugin, "techitnote", "Create a techit note"),
		]);

		//ItemFactory::getInstance()->register(new TechitNoteItem(), true);
	}

	public function getTop(int $page = 1, int $per = 10, int $type = self::TYPE_DATABASE, ?\Closure $closure = null) : void{
		$array = [];
		if($type == self::TYPE_SERVER){
			/** @var PvPPlayer $player */
			foreach(Server::getInstance()->getOnlinePlayers() as $player){
				if($player->hasGameSession()) $array[$player->getName()] = $player->getGameSession()->getTechits()->getTechits();
			}
			arsort($array);
			$array = array_slice($array, max(0, ($per * ($page - 1)) - 1), $per);
			if($closure !== null) $closure($array);
		}else{
			$start = max(0, ($page - 1) * $per);

			PvP::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("top_techits", new MySqlQuery("main",
				"SELECT * FROM techits ORDER BY techits DESC LIMIT ?, ?",
				[$start, $per]
			)), function(MySqlRequest $request) use($closure) : void{
				$result = $request->getQuery()->getResult()->getRows();
				$array = [];
				$users = [];
				foreach($result as $row){
					$array[$row["xuid"]] = $row["techits"];
					$users[] = $row["xuid"];
				}
				Core::getInstance()->getUserPool()->useUsers($users, function(array $users) use($closure, $array) : void{
					$newArray = [];
					foreach($array as $xuid => $techits){
						$newArray[(array_shift($users))->getGamertag()] = $techits;
					}
					if($closure !== null) $closure($newArray);
				});
			});
		}
	}

}