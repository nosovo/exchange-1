<?php

namespace h4kuna\Exchange;

abstract class ExchangeException extends \Exception {}

class DriverDoesNotSupport extends \RuntimeException {}

class InvalidArgumentException extends \InvalidArgumentException {}

class UnknownCurrencyException extends InvalidArgumentException {}

class FrozenMethodException extends InvalidArgumentException {}

class EmptyExchangeRateException extends \LogicException {}
