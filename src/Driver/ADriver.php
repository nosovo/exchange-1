<?php

namespace h4kuna\Exchange\Driver;

use h4kuna\Exchange;

/**
 * Download currency from server.
 */
abstract class ADriver
{

	/** @var \DateTimeInterface */
	private $date;

	/**
	 * Download data from remote source and save.
	 */
	public function download(\DateTimeInterface $date = null, array $allowedCurrencies = []): Exchange\Currency\ListRates
	{
		$allowedCurrencies = array_flip($allowedCurrencies);
		$source = $this->loadFromSource($date);
		$currencies = new Exchange\Currency\ListRates($this->getDate());
		foreach ($source as $row) {
			if (!$row) {
				continue;
			}
			$property = $this->createProperty($row);

			if (!$property || !$property->rate || ($allowedCurrencies !== [] && !isset($allowedCurrencies[$property->code]))) {
				continue;
			}
			$currencies->addProperty($property);
		}
		$currencies->getFirst(); // check if is not empty
		return $currencies;
	}

	protected function setDate(string $format, $value)
	{
		$this->date = \DateTime::createFromFormat($format, $value);
		$this->date->setTime(0, 0, 0);
	}

	public function getName(): string
	{
		return strtolower(str_replace('\\', '.', static::class));
	}

	public function getDate(): \DateTimeInterface
	{
		return $this->date;
	}

	/**
	 * Load data from source for iterator.
	 */
	abstract protected function loadFromSource(\DateTimeInterface $date = null): iterable;

	/**
	 * Modify data before save to cache.
	 */
	abstract protected function createProperty($row): ?Exchange\Currency\Property;

}
