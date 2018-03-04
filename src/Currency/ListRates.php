<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange;

class ListRates implements \ArrayAccess, \Iterator
{

	/** @var \DateTimeInterface */
	private $date;

	/** @var Property[] */
	private $currencies = [];

	public function __construct(\DateTimeInterface $date)
	{
		$this->date = $date;
	}

	public function addProperty(Property $property)
	{
		$this->currencies[$property->code] = $property;
	}

	/**
	 * @return Property[]
	 */
	public function getCurrencies(): array
	{
		return $this->currencies;
	}

	public function getFirst(): Property
	{
		if ($this->currencies === []) {
			throw new Exchange\EmptyExchangeRateException();
		}
		reset($this->currencies);
		return current($this->currencies);
	}

	public function getDate(): \DateTimeInterface
	{
		return $this->date;
	}

	public function offsetExists($offset): bool
	{
		return isset($this->currencies[$offset]);
	}

	public function offsetGet($offset): Property
	{
		return $this->currencies[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new Exchange\FrozenMethodException;
	}

	public function offsetUnset($offset)
	{
		throw new Exchange\FrozenMethodException;
	}

	public function current(): Property
	{
		return current($this->currencies);
	}

	public function next()
	{
		next($this->currencies);
	}

	public function key(): string
	{
		return key($this->currencies);
	}

	public function valid(): bool
	{
		return isset($this->currencies[$this->key()]);
	}

	public function rewind()
	{
		reset($this->currencies);
	}

}
