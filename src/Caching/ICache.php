<?php

namespace h4kuna\Exchange\Caching;

use h4kuna\Exchange\Driver;

interface ICache
{

	function loadListRate(Driver\ADriver $driver, \DateTime $date = null);

	function flushCache(Driver\ADriver $driver, \DateTime $date = null);

	function setAllowedCurrencies(array $allowed);

	function setRefresh($hour);

}
