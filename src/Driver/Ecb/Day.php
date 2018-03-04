<?php

namespace h4kuna\Exchange\Driver\Ecb;

use GuzzleHttp;
use h4kuna\Exchange;

/**
 * @author Petr Poupě <pupe.dupe@gmail.com>
 */
class Day extends Exchange\Driver\ADriver
{

	const URL_DAY = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

	protected function loadFromSource(\DateTimeInterface $date = null): iterable
	{
		$request = new GuzzleHttp\Client;
		$data = $request->request('GET', $this->createUrlDay(self::URL_DAY, $date))->getBody();

		$xml = simplexml_load_string($data);

		// including EUR
		$eur = $xml->Cube->Cube->addChild("Cube");
		$eur->addAttribute('currency', 'EUR');
		$eur->addAttribute('rate', '1');
		$this->setDate('Y-m-d', (string) $xml->Cube->Cube->attributes()['time']);
		return $xml->Cube->Cube->Cube;
	}

	protected function createProperty($row): ?Exchange\Currency\Property
	{
		return new Exchange\Currency\Property([
			'code' => $row['currency'],
			'home' => $row['rate'],
			'foreign' => 1,
		]);
	}

	private function createUrlDay(string $url, \DateTimeInterface $date = null): string
	{
		if ($date) {
			throw new Exchange\DriverDoesNotSupport('Driver does not support history.');
		}
		return $url;
	}

}
