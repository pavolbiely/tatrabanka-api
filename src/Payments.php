<?php declare(strict_types=1);

namespace TatraBankaApi;

class Payments extends TatraBankaApi
{
	/** @var string */
	protected $scope = 'payments';



	/**
	 * @param string
	 * @return mixed
	 */
	public function getPaymentStatus(string $orderId)
	{
		$this->validateParameterOrderId($orderId);
		return $this->sendApiRequest('payments/' . $orderId . '/status', 'GET');
	}



	/**
	 * @return mixed
	 */
	public function postPaymentSubmission()
	{
		return $this->sendApiRequest('payments/submission', 'POST');
	}



	/**
	 * @param \DateTime
	 * @return mixed
	 */
	public function postPaymentSba(\DateTime $dateTime)
	{
		$params = ['requestedExecutionDate' => $dateTime->format('Y-m-d')];
		return $this->sendApiRequest('payments/standard/sba', 'POST', $params, [], true);
	}



	/**
	 * @param \DateTime
	 * @return mixed
	 */
	public function postPaymentSbaEcomm(\DateTime $dateTime)
	{
		$params = ['requestedExecutionDate' => $dateTime->format('Y-m-d')];
		return $this->sendApiRequest('payments/ecomm/sba', 'POST', $params, [], true);
	}



	/**
	 * @param string
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterOrderId(string $orderId): void
	{
		if (!preg_match('~^[a-zA-Z0-9/\-?:().,\'\';+\s]{1,35}$~', $orderId)) {
			throw new TatraBankaApiException("Invalid orderId parameter '" . $orderId . "'.");
		}
	}
}
