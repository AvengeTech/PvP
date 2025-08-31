<?php namespace pvp\kits;

use pvp\PvP;

class KitLibrary{
	
	public array $kits = [];
	public function __construct(
		array $kits,
		public string $defaultKit = ""
	){
		foreach($kits as $kit){
			if(!$kit instanceof Kit){
				$kit = PvP::getInstance()->getKits()->getKit($kit);
				if($kit === null) continue;
			}
			$this->kits[strtolower($kit->getName())] = $kit;
		}
	}

	public function getKits() : array{
		return $this->kits;
	}
	
	public function getKit(string $name = "") : ?Kit{
		if($name === "" && count($this->kits) === 1){
			return current($this->kits) ?? null;
		}else{
			return $this->kits[strtolower($name)] ?? null;
		}
	}
	
	public function getDefaultKitName() : string{
		return $this->defaultKit;
	}
	
	public function getDefaultKit() : ?Kit{
		return $this->getKit($this->getDefaultKitName());
	}

	public function getRandomKit() : Kit{
		$kits = array_values($this->getKits());
		if(count($kits) === 1) return $this->getKit();
		return $kits[mt_rand(0, count($kits) - 1)] ?? null;
	}

}