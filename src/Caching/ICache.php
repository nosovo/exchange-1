<?php declare(strict_types=1);

namespace h4kuna\Exchange\Caching;

use h4kuna\Exchange\Currency\ListRates;
use h4kuna\Exchange\Driver;
use Nette\Utils\DateTime;

interface ICache
{

	function loadListRate(Driver\ADriver $driver, \DateTime $date = null): ListRates;

	function flushCache(Driver\ADriver $driver, \DateTime $date = null): void;

	/**
	 * @param string[] $allowed
	 * @return ICache
	 */
	function setAllowedCurrencies(array $allowed): ICache;

	/**
	 * @param string|DateTime $hour
	 * @return ICache
	 */
	function setRefresh($hour): ICache;

}
