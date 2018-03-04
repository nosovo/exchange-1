<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Cnb;

use DateTime;
use GuzzleHttp;
use h4kuna\Exchange;

class Day extends Exchange\Driver\ADriver
{

	/**
	 * Url where download rating
	 */
	private const URL_DAY = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
	private const URL_DAY_OTHER = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';

	/**
	 * Load data from remote source.
	 * @param \DateTimeInterface|null $date
	 * @return string[]
	 */
	protected function loadFromSource(\DateTimeInterface $date = null): iterable
	{
		$request = new GuzzleHttp\Client();
		$data = $request->request('GET', $this->createUrl(self::URL_DAY, $date))->getBody()->getContents();
		$list = explode("\n", Exchange\Utils::stroke2point($data));
		$list[1] = 'Česká Republika|koruna|1|CZK|1';

		$this->setDate('d.m.Y', explode(' ', $list[0])[0]);
		unset($list[0]);

		return $list;
	}

	/**
	 * @param string $row
	 * @return Property
	 */
	protected function createProperty($row): ?Exchange\Currency\Property
	{
		$currency = explode('|', $row);
		return new Property([
			'country' => $currency[0],
			'currency' => $currency[1],
			'foreign' => $currency[2],
			'code' => $currency[3],
			'home' => $currency[4],
		]);
	}

	private function createUrl(string $url, \DateTimeInterface $date = null): string
	{
		if ($date === null) {
			return $url;
		}
		return $url . '?date=' . urlencode($date->format('d.m.Y'));
	}

}
