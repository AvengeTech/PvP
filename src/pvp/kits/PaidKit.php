<?php namespace pvp\kits;

class PaidKit extends Kit{

	const CURRENCY_TECHITS = 0;
	//maybe multiple currencies? e.g. a currency that's only stored on a single session?

	public function __construct(
		string $name,
		array $items = [],
		array $armor = [],
		public int $price = 0,
		public int $currency = self::CURRENCY_TECHITS
	){
		parent::__construct($name, $items, $armor);
	}

	public function getPrice() : int{
		return $this->price;
	}

	public function getCurrency() : int{
		return $this->currency;
	}

	public function getCurrencyName() : string{
		return match($this->getCurrency()){
			self::CURRENCY_TECHITS => "techits",
			default => "moneys"
		};
	}

}