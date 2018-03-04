<?php declare(strict_types=1);

namespace h4kuna\Exchange;

final class Utils
{

	private function __construct() {}

	/**
	 * Stroke replace by point
	 */
	public static function stroke2point(string $str): string
	{
		return trim(str_replace(',', '.', $str));
	}

}
