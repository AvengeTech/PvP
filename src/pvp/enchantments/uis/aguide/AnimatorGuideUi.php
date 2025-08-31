<?php namespace pvp\enchantments\uis\aguide;

use pocketmine\player\Player;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use pvp\PvPPlayer;

class AnimatorGuideUi extends SimpleForm{

	public function __construct(Player $player){
		parent::__construct("Animator Guide", "Select a rarity to see all Animators that belong to it!");

		$this->addButton(new Button("Common"));
		$this->addButton(new Button("Uncommon"));
		$this->addButton(new Button("Rare"));
		$this->addButton(new Button("Legendary"));
	}

	public function handle($response, Player $player){
		/** @var PvPPlayer $player */
		$player->showModal(new GuideSelectUi($player, $response + 1));
	}

}