<?php

namespace h4kuna\Exchange;

use h4kuna\Number;

class Filters
{

	/** @var Exchange */
	private $exchange;

	/** @var Currency\Formats */
	private $formats;

	/** @var Number\Tax */
	private $vat;

	public function __construct(Exchange $exchange, Currency\Formats $formats)
	{
		$this->exchange = $exchange;
		$this->formats = $formats;
	}

	public function setVat(Number\Tax $vat)
	{
		$this->vat = $vat;
	}

	public function change($number, $from = null, $to = null)
	{
		return $this->exchange->change($number, $from, $to);
	}

	public function changeTo($number, $to = null)
	{
		return $this->change($number, null, $to);
	}

	/**
	 * Count and format number.
	 * @param int|float $number
	 * @param string|null
	 * @param string $to output currency, null set actual
	 * @return string
	 */
	public function format($number, ?string $from = null, ?string $to = null): string
	{
		$data = $this->exchange->transfer($number, $from, $to);
		return $this->formats->getFormat($data[1]->code)->format($data[0], $data[1]->code);
	}

	/**
	 * @param float $number
	 * @param string $to
	 * @return string
	 */
	public function formatTo($number, string $to): string
	{
		return $this->format($number, null, $to);
	}

	/**
	 * @param float|int $number
	 * @return float
	 */
	public function vat($number)
	{
		return $this->vat->add($number);
	}

	/**
	 * @param float|int $number
	 * @param string|null $from
	 * @param string|null $to
	 * @return string
	 */
	public function formatVat($number, ?string $from = null, ?string $to = null): string
	{
		return $this->format($this->vat($number), $from, $to);
	}

	/**
	 * @param float|int $number
	 * @param string|null $to
	 * @return string
	 */
	public function formatVatTo($number, ?string $to): string
	{
		return $this->formatVat($number, null, $to);
	}

}
