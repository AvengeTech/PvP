<?php namespace pvp\enchantments\uis\aguide;

use pocketmine\player\Player;

use pvp\enchantments\effects\EffectClass;
use pvp\PvPPlayer;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class ShowGuideUi extends SimpleForm{

	public $effect;

	public function __construct(EffectClass $effect, bool $back = true){
		$this->effect = $effect;
		parent::__construct($effect->getName(),
			$effect->getRarityName() . " " . ($effect->getType() == 0 ? "Death" : "Mining") . " animation" . PHP_EOL . PHP_EOL . "Description: " . $effect->getDescription()
		);
		if($back) $this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		/** @var PvPPlayer $player */
		$player->showModal(new GuideSelectUi($player, $this->effect->getRarity()));
	}

}