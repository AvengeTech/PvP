<?php

namespace pvp\games\entity;


class Dummy extends Bot
{

	public function doTechnical()
	{
	}

	public function postAttack(): void
	{
	}

	public function constructor(): void
	{
		$this->setDummy(true);
	}
}
