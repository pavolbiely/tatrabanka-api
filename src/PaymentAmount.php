<?php declare(strict_types=1);

namespace TatraBankaApi;

class PaymentAmount
{
	/** @var float */
	protected $value;

	/** @var string */
	protected $currency;



	/**
	 * @param string
	 * @param string Alphabetic codes from ISO 4712.
	 */
	public function __construct(float $value, string $currency = 'EUR')
	{
		$this->setValue($value);
		$this->setCurrency($currency);
	}



	/**
	 * @param float
	 * @return self
	 */
	protected function setValue(float $value): self
	{
		$this->validateParameterValue($value);
		$this->value = $value;
		return $this;
	}



	/**
	 * @return float
	 */
	public function getValue(): float
	{
		return $this->value;
	}



	/**
	 * @param string
	 * @return self
	 */
	protected function setCurrency(string $currency): self
	{
		$this->validateParameterCurrency($currency);
		$this->currency = $currency;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}



	/**
	 * @param float
	 * @return void
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterValue(float $value): void
	{
		$value = round($value, 2);

		if (!($value >= 0.0 && $value <= 9999999999.99)) {
			throw new TatraBankaApiException("The value parameter must be within the range of 0.0 to 9999999999.99.");
		}
	}



	/**
	 * @param string
	 * @return void
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterCurrency(string $currency): void
	{
		if (!preg_match('~^[A-Z]{3}$~', $currency)) {
			throw new TatraBankaApiException("Currency is not in the correct format. An alphabetic code within ISO 4712 must be used.");
		}
	}
}
