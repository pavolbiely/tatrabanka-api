<?php declare(strict_types=1);

namespace TatraBankaApi;

class PaymentParticipant
{
	/** @var string */
	protected $name;

	/** @var string */
	protected $iban;



	/**
	 * @param string
	 * @param string
	 */
	public function __construct(string $name, string $iban)
	{
		$this->setName($name);
		$this->setIban($iban);
	}



	/**
	 * @param string
	 * @return self
	 */
	protected function setName(string $name): self
	{
		$this->validateParameterName($name);
		$this->name = $name;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}



	/**
	 * @param string
	 * @return self
	 */
	protected function setIban(string $iban): self
	{
		$this->validateParameterIban($iban);
		$this->iban = $iban;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getIban(): string
	{
		return $this->iban;
	}



	/**
	 * @param string
	 * @return void
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterName(string $name): void
	{
		if (!preg_match('~^[a-zA-Z0-9/\-?:().,\'\';+\s]{0,140}$~', $name)) {
			throw new TatraBankaApiException("Name is not in the correct format.");
		}
	}



	/**
	 * @param string
	 * @return void
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterIban(string $iban): void
	{
		if (!preg_match('~^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$~', $iban)) {
			throw new TatraBankaApiException("IBAN is not in the correct format.");
		}
	}
}
