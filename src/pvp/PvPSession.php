<?php namespace pvp;

use pocketmine\player\Player;

use pvp\{
	arenas\ArenaComponent,
	combat\CombatComponent,
	games\GameComponent,
	hotbar\HotbarComponent,
	kits\KitComponent,
	levels\LevelsComponent,
	tags\TagsComponent,
	techits\TechitsComponent
};
use pvp\settings\PvPSettings;

use core\session\{
	PlayerSession,
	SessionManager
};
use core\settings\SettingsComponent;
use core\user\User;
use core\utils\Version;

class PvPSession extends PlayerSession{

	public function __construct(SessionManager $sessionManager, Player|User $user){
		parent::__construct($sessionManager, $user);

		$this->addComponent(new ArenaComponent($this));
		$this->addComponent(new CombatComponent($this));
		$this->addComponent(new GameComponent($this));
		$this->addComponent(new HotbarComponent($this));
		$this->addComponent(new KitComponent($this));
		$this->addComponent(new LevelsComponent($this));
		$this->addComponent(new TagsComponent($this));
		$this->addComponent(new TechitsComponent($this));

		$this->addComponent(new SettingsComponent(
			$this,
			Version::fromString(PvPSettings::VERSION),
			PvPSettings::DEFAULT_SETTINGS,
			PvPSettings::SETTING_UPDATES
		));
	}

	public function getArenas() : ArenaComponent{
		return $this->getComponent("arenas");
	}
	
	public function getCombat() : CombatComponent{
		return $this->getComponent("combat");
	}

	public function getGame() : GameComponent{
		return $this->getComponent("game");
	}

	public function getHotbar() : HotbarComponent{
		return $this->getComponent("hotbar");
	}

	public function getKits() : KitComponent{
		return $this->getComponent("kits");
	}

	public function getLevels() : LevelsComponent{
		return $this->getComponent("levels");
	}

	public function getTags() : TagsComponent{
		return $this->getComponent("tags");
	}

	public function getTechits() : TechitsComponent{
		return $this->getComponent("techits");
	}

	public function getSettings() : SettingsComponent{
		return $this->getComponent("settings");
	}

	public function finishAsyncLoad() : void{
		parent::finishLoadAsync();
		echo "PvP session finished loading!", PHP_EOL;
	}

	public function finishAsyncSave() : void{
		parent::finishSaveAsync();
		echo "PvP session finished saving async!", PHP_EOL;
	}

}