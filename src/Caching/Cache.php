<?php declare(strict_types=1);

namespace h4kuna\Exchange\Caching;

use h4kuna\Exchange\Currency;
use h4kuna\Exchange\Driver;
use Nette\Utils;

class Cache implements ICache
{

	private const FILE_CURRENT = 'current';

	/** @var string */
	private $temp;

	/** @var Currency\ListRates[] */
	private $listRates;

	/** @var array */
	private $allowedCurrencies = [];

	/**
	 * int - unix time
	 * @var string|int
	 */
	private $refresh = '15:00';

	public function __construct(string $temp)
	{
		$this->temp = $temp;
	}

	public function loadListRate(Driver\ADriver $driver, \DateTime $date = null): Currency\ListRates
	{
		$file = $this->createFileInfo($driver, $date);
		if (isset($this->listRates[$file->getPathname()])) {
			return $this->listRates[$file->getPathname()];
		}
		return $this->listRates[$file->getPathname()] = $this->createListRate($driver, $file, $date);
	}

	public function flushCache(Driver\ADriver $driver, \DateTime $date = null): void
	{
		$file = $this->createFileInfo($driver, $date);
		$file->isFile() && unlink($file->getPathname());
	}

	public function invalidForce(Driver\ADriver $driver, \DateTime $date = null): void
	{
		$this->refresh = time() + Utils\DateTime::DAY;
		$file = $this->createFileInfo($driver, $date);
		$this->saveCurrencies($driver, $file, $date);
	}

	public function setAllowedCurrencies(array $allowedCurrencies): ICache
	{
		$this->allowedCurrencies = $allowedCurrencies;
		return $this;
	}

	public function setRefresh($hour): ICache
	{
		$this->refresh = $hour;
		return $this;
	}

	private function saveCurrencies(Driver\ADriver $driver, \SplFileInfo $file, \DateTime $date = null): Currency\ListRates
	{
		$listRates = $driver->download($date, $this->allowedCurrencies);

		file_put_contents(Utils\SafeStream::PROTOCOL . '://' . $file->getPathname(), serialize($listRates));
		if (self::isFileCurrent($file)) {
			touch($file->getPathname(), $this->getRefresh());
		}

		return $listRates;
	}

	private function createListRate(Driver\ADriver $driver, \SplFileInfo $file, \DateTime $date = null): Currency\ListRates
	{
		if ($this->isFileValid($file)) {
			Utils\FileSystem::createDir($file->getPath(), 0755);
			$handle = fopen(Utils\SafeStream::PROTOCOL . '://' . $this->temp . DIRECTORY_SEPARATOR . 'lock', 'w');

			if ($this->isFileValid($file)) {
				$listRate = $this->saveCurrencies($driver, $file, $date);
				fclose($handle);
				return $listRate;
			}
			fclose($handle);
		}

		return unserialize(file_get_contents($file->getPathname()));
	}

	private function isFileValid(\SplFileInfo $file): bool
	{
		return !$file->isFile() || (self::isFileCurrent($file) && $file->getMTime() < time());
	}

	private function getRefresh(): int
	{
		if (!is_int($this->refresh)) {
			$this->refresh = (int) (new \DateTime('today ' . $this->refresh))->format('U');
			if (time() >= $this->refresh) {
				$this->refresh += Utils\DateTime::DAY;
			}
		}
		return $this->refresh;
	}

	private function createFileInfo(Driver\ADriver $driver, \DateTime $date = null): \SplFileInfo
	{
		$filename = $date === null ? self::FILE_CURRENT : $date->format('Y-m-d');
		return new \SplFileInfo($this->temp . DIRECTORY_SEPARATOR . $driver->getName() . DIRECTORY_SEPARATOR . $filename);
	}

	private static function isFileCurrent(\SplFileInfo $file): bool
	{
		return $file->getFilename() === self::FILE_CURRENT;
	}

}
