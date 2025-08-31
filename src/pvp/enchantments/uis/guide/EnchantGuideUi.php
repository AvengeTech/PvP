<?php namespace pvp\enchantments\uis\guide;

use pocketmine\player\Player;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use pvp\PvPPlayer;

class EnchantGuideUi extends SimpleForm{

	public function __construct(Player $player) {
		/** @var PvPPlayer $player */
		parent::__construct("Enchantment Guide", "Select a rarity to see all enchantments that belong to it!");

		$this->addButton(new Button("Common"));
		$this->addButton(new Button("Uncommon"));
		$this->addButton(new Button("Rare"));
		$this->addButton(new Button("Legendary"));
		$this->addButton(new Button("Divine"));
	}

	public function handle($response, Player $player) {
		/** @var PvPPlayer $player */
		$player->showModal(new GuideSelectUi($player, $response + 1));
	}

}