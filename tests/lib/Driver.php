<?php

namespace h4kuna\Exchange\Test;

use h4kuna\Exchange\Currency\Property;

class Driver extends \h4kuna\Exchange\Driver\ADriver
{

	protected function loadFromSource(\DateTimeInterface $date = null): iterable
	{
		$myDate = $date;
		if ($myDate === null) {
			$myDate = new \DateTime;
		}
		$this->setDate('Y-m-d', $myDate->format('Y-m-d'));

		return [
			[
				'home' => $date ? 26 : 25,
				'foreign' => 1,
				'code' => 'EUR',
			],
			[
				'home' => $date ? 19 : 20,
				'foreign' => 1,
				'code' => 'USD',
			],
			[
				'home' => 1,
				'foreign' => 1,
				'code' => 'CZK',
			],
		];
	}

	protected function createProperty($row): ?Property
	{
		return new Property($row);
	}
}
