<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace OCA\Scanner\Sane;


use OCA\Scanner\Sane\Exception\InvalidArgumentException;
use OCA\Scanner\Sane\Param\ListScanParam;
use OCA\Scanner\Sane\Param\ScanParameterFactory;
use OCA\Scanner\Sane\Param\ScanParamList;
use OCA\Scanner\Sane\Param\ScanParamListInterface;

class SaneBackend {

	/**
	 * @var array
	 */
	private $params;
	/**
	 * @var string
	 */
	private $id;

	public function __construct(string $id, ScanParamListInterface $params) {

		$this->params = $params;
		$this->id = $id;
	}

	/**
	 * @param string $id
	 * @return SaneBackend
	 * @throws InvalidArgumentException
	 */
	public static function fromShell(string $id): self {
		exec("scanimage -A -d {$id}", $result);
		$result = implode("\n", $result);
		return self::fromShellOutput($result);

	}

	/**
	 * @param string $shellOutput
	 * @return SaneBackend
	 * @throws InvalidArgumentException
	 */
	public static function fromShellOutput(string $shellOutput): self {
		preg_match('/`(.+)\'/', $shellOutput, $idMatch);
		$id = $idMatch[1];
		if (empty($id)) {
			throw new InvalidArgumentException('ID could not be determined from input string: ' . $shellOutput);
		}
		preg_match_all('/\s+--?(\S+)\s(\S*)\s+\[(\S*?)\].*\n(.+)\n/', $shellOutput, $matches);
		list(, $parameterNames, $options, $defaults, $descriptions) = $matches;
		$params = [];
		$factory = new ScanParameterFactory();
		array_walk($parameterNames, function ($name, $idx) use (&$params, $options, $descriptions, $defaults, $factory) {

			/**
			 * Remove some bogus params that are either deprecated or nonsensical.
			 * Let's hope this won't lead to collisions between backends...
			 */
			$blacklist = [
				'jpeg-quality'
			];
			if (\in_array($name, $blacklist, true)) {
				return;
			}
			$params[] = $factory->create($name, $descriptions[$idx], $options[$idx], $defaults[$idx]);
		});
		$paramList = new ScanParamList($params);

		return new self((string)$id, $paramList);
	}

	public function acceptsParamValue(string $paramName, string $value): bool {
		if (!$this->acceptsParam($paramName)) {
			return false;
		}
		return $this->params->get($paramName)->accepts($value);
	}

	public function acceptsParam(string $paramName): bool {
		return $this->params->contains($paramName);
	}

	public function id(): string {
		return $this->id;
	}

	public function toArray(): array {
		return ['id' => $this->id, 'params' => $this->params->toArray()];
	}

	public function getClosestAvailableResolution(int $desired): int {
		if (!$this->acceptsParam('resolution')) {
			//TODO exception?
			return 0;
		}
		$desiredResolution = 30;

		$param = $this->params->get('resolution');
		$options = $param->options();
		if ($param instanceof ListScanParam) {
			foreach ($options as $option) {
				if ($option > $desiredResolution) {
					return (int)$option;
				}
			}
		}

		$min = $options[0];
		$chosenResolution = ($min > $desiredResolution) ? $min : $desiredResolution;

		return (int)$chosenResolution;
	}
}
