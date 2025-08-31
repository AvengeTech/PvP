<?php namespace pvp\tags\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use pvp\PvPPlayer;
use pvp\PvPSession;
use pvp\PvP;

use core\Core;
use core\user\User;
use core\utils\TextFormat;

class AddRandomTags extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.tier3");
		$this->setAliases(["art"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		/** @var PvPPlayer $sender */
		if($sender instanceof Player){
			if(!$sender->isTier3()){
				$sender->sendMessage(TextFormat::RI . "This command is for the owner only!");
				return;
			}
		}
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RI . "Usage: /addrandomtags <player> <amount>");
			return;
		}
		$name = array_shift($args);
		$amount = (int) array_shift($args);

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender, $amount) : void{
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			PvP::getInstance()->getSessionManager()->useSession($user, function(PvPSession $session) use($sender, $user, $amount) : void{
				$tags = PvP::getInstance()->getTags();
				$new = [];
				$tdh = $session->getTags()->getTagsNoHave();
				if(count($tdh) <= $amount){
					$new = $tdh;
					$total = count($new);
				}else{
					$total = 0;
					while($total < $amount){
						$tag = $tags->getRandomTag($tdh);
						if(!in_array($tag, $new)){
							$new[] = $tag;
							$total++;
						}
					}
				}
				foreach($new as $t){
					$session->getTags()->addTag($t);
				}
				if($user->validPlayer()){
					$user->getPlayer()->sendMessage(TextFormat::GI . "You just received " . TextFormat::GREEN . $total . TextFormat::GRAY . " new tags!");
				}else{
					$session->getTags()->saveAsync();
				}
				$sender->sendMessage(TextFormat::GI . "Gave " . $user->getGamertag() . " " . $total . " random tags!");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}