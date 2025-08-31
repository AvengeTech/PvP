<?php namespace pvp\tags;

use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;

use pvp\PvP;

use core\utils\NewSaveableSession;

// Whole lotta nothin here shane

class Session extends NewSaveableSession{

	public $active = null;
	public $tags = [];

	public function load() : void{
		parent::load();

		$db = PvP::getInstance()->getDatabase("here");
		$xuid = $this->getXuid();

		$stmt = $db->prepare("SELECT * FROM tags_data WHERE xuid=?");
		$stmt->bind_param("i", $xuid);
		$stmt->bind_result($x, $active, $tags);
		if($stmt->execute()){
			$stmt->fetch();
		}
		$stmt->close();

		if($x === null) return;

		$this->active = PvP::getInstance()->getTags()->getTag($active);
		$tags = explode(",", $tags);
		foreach($tags as $tag){
			$tagc = PvP::getInstance()->getTags()->getTag($tag);
			if($tagc !== null){
				$this->tags[$tagc->getName()] = $tagc;
			}
		}
	}

	public function getActiveTag() : ?Tag{
		if(!$this->isLoaded()) $this->load();
		return $this->active;
	}

	public function setActiveTag(?Tag $tag = null) : void{
		if(!$this->isLoaded()) $this->load();
		if($tag instanceof Tag) $tag = clone $tag;

		$this->active = $tag;

		$player = $this->getPlayer();
		if($player instanceof Player){
			$player->updateNametag();
			$player->updateChatFormat();
		}
	}

	public function getTagsNoHave() : array{
		$no = [];
		$tags = PvP::getInstance()->getTags()->getTags();
		foreach($tags as $tag){
			if(!$this->hasTag($tag) && !$tag->isDisabled())
				$no[] = $tag;
		}
		return $no;
	}

	public function getTags() : array{
		if(!$this->isLoaded()) $this->load();
		return $this->tags;
	}

	public function hasTag(Tag $tag) : bool{
		if(!$this->isLoaded()) $this->load();
		return isset($this->tags[$tag->getName()]);
	}

	public function addTag(Tag $tag) : void{
		if(!$this->isLoaded()) $this->load();
		$this->tags[$tag->getName()] = clone $tag;
	}

	public function removeTag(Tag $tag) : void{
		if(!$this->isLoaded()) $this->load();
		unset($this->tags[$tag->getName()]);
	}

	public function save() : void{
		$db = PvP::getInstance()->getDatabase("here");
		$xuid = $this->getXuid();
		$active = ($this->getActiveTag() === null ? "" : $this->getActiveTag()->getName());
		$tags = $this->getTags();
		$tl = [];
		foreach($tags as $name => $tag){
			$tl[] = $name;
		}
		$tags = implode(",", $tl);

		$stmt = $db->prepare("INSERT INTO tags_data(xuid, active, tags) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE active=VALUES(active), tags=VALUES(tags)");
		$stmt->bind_param("iss", $xuid, $active, $tags);
		if($stmt->execute()){
			$stmt->fetch();
		}
		$stmt->close();
	}
}