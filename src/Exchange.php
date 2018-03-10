<?php

namespace h4kuna\Exchange;

use DateTime;

/**
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 */
class Exchange implements \ArrayAccess, \IteratorAggregate
{

	/** @var Caching\ICache */
	private $cache;

	/** @var Currency\ListRates */
	private $listRates;

	/**
	 * Default currency "from" input
	 * @var Currency\Property
	 */
	private $default;

	/**
	 * Display currency "to" output
	 * @var Currency\Property
	 */
	private $output;

	/** @var float[] */
	private $tempRates;

	public function __construct(Caching\ICache $cache)
	{
		$this->cache = $cache;
	}

	public function getDefault(): Currency\Property
	{
		if ($this->default === null) {
			$this->default = $this->getListRates()->getFirst();
		}
		return $this->default;
	}

	public function getOutput(): Currency\Property
	{
		if ($this->output === null) {
			$this->output = $this->getDefault();
		}
		return $this->output;
	}

	/**
	 * Set default "from" currency.
	 * @param string $code
	 */
	public function setDefault(string $code)
	{
		$this->default = $this->offsetGet($code);
	}

	public function setDriver(Driver\ADriver $driver = null, DateTime $date = null)
	{
		if ($driver === null) {
			$driver = new Driver\Cnb\Day();
		}
		$this->listRates = $this->cache->loadListRate($driver, $date);
		if ($this->default) {
			$this->setDefault($this->default->code);
		}
		if ($this->output) {
			$this->setOutput($this->output->code);
		}
		return $this;
	}

	/**
	 * Set currency "to".
	 */
	public function setOutput(string $code): Currency\Property
	{
		return $this->output = $this->offsetGet($code);
	}

	/**
	 * Transfer number by exchange rate.
	 * @param float|int $price number
	 * @param string|null
	 * @param string $to
	 * @return float|int
	 */
	public function change($price, ?string $from = null, ?string $to = null)
	{
		return $this->transfer($price, $from, $to)[0];
	}

	/**
	 * @param int|float $price
	 * @param string|null $from
	 * @param string|null $to
	 * @return array
	 */
	public function transfer($price, ?string $from, ?string $to)
	{
		$to = $to === null ? $this->getOutput() : $this->offsetGet($to);
		if (((float) $price) === 0.0) {
			return [0, $to];
		}

		$from = $from === null ? $this->getDefault() : $this->offsetGet($from);

		if ($to !== $from) {
			$toRate = isset($this->tempRates[$to->code]) ? $this->tempRates[$to->code] : $to->rate;
			$fromRate = isset($this->tempRates[$from->code]) ? $this->tempRates[$from->code] : $from->rate;
			$price *= $fromRate / $toRate;
		}

		return [$price, $to];
	}

	/**
	 * Add history rate for rating
	 * @param string $code
	 * @param float $rate
	 * @return self
	 */
	public function addRate(string $code, $rate)
	{
		$property = $this->offsetGet($code);
		$this->tempRates[$property->code] = $rate;
		return $this;
	}

	/**
	 * Remove history rating
	 * @param string $code
	 * @return self
	 */
	public function removeRate(string $code)
	{
		$property = $this->offsetGet($code);
		unset($this->tempRates[$property->code]);
		return $this;
	}

	/**
	 * Load currency property.
	 * @param string|Currency\Property $index
	 * @return Currency\Property
	 */
	public function offsetGet($index)
	{
		$index = strtoupper($index);
		if ($this->getListRates()->offsetExists($index)) {
			return $this->getListRates()->offsetGet($index);
		}
		throw new UnknownCurrencyException('Undefined currency code: "' . $index . '".');
	}

	public function offsetExists($offset)
	{
		return $this->getListRates()->offsetExists(strtoupper($offset));
	}

	public function offsetSet($offset, $value)
	{
		return $this->getListRates()->offsetSet($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->getListRates()->offsetUnset($offset);
	}

	public function getIterator()
	{
		return $this->getListRates();
	}

	protected function getListRates(): Currency\ListRates
	{
		if ($this->listRates === null) {
			$this->setDriver();
		}
		return $this->listRates;
	}

}
