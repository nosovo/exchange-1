<?php

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange;
use h4kuna\Number;

class Formats
{

	/** @var Number\NumberFormatFactory */
	private $numberFormatFactory;

	/** @var Number\UnitFormatState[] */
	private $formats;

	/** @var array */
	private $rawFormats;

	/** @var Number\UnitFormatState */
	private $default;

	public function __construct(Number\NumberFormatFactory $numberFormatFactory)
	{
		$this->numberFormatFactory = $numberFormatFactory;
	}

	public function setDefaultFormat($setup)
	{
		if (is_array($setup)) {
			$setup = $this->numberFormatFactory->createUnit($setup);
		} else if (!$setup instanceof Number\UnitFormatState) {
			throw new Exchange\InvalidArgumentException('$setup must be array or ' . Number\UnitFormatState::class);
		}

		$this->default = $setup;
	}

	public function addFormat($code, array $setup)
	{
		$code = strtoupper($code);
		$this->rawFormats[$code] = $setup;
		unset($this->formats[$code]);
	}

	public function getFormat($code)
	{
		if (isset($this->formats[$code])) {
			return $this->formats[$code];
		} else if (isset($this->rawFormats[$code])) {
			if (isset($this->rawFormats[$code]['unit'])) {
				$format = $this->numberFormatFactory->createUnitPersistent(null, $this->rawFormats[$code]);
			} else {
				$format = $this->numberFormatFactory->createUnit($this->rawFormats[$code]);
			}
			$this->formats[$code] = $format;
			unset($this->rawFormats[$code]);
		} else {
			$this->formats[$code] = $this->getDefaultFormat();
		}
		return $this->formats[$code];
	}

	private function getDefaultFormat()
	{
		if ($this->default === null) {
			$this->default = $this->numberFormatFactory->createUnit();
		}
		return $this->default;
	}

}
