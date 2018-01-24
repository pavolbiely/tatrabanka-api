<?php declare(strict_types=1);

namespace TatraBankaApi;

class Accounts extends TatraBankaApi
{
	const STATUS_ALL  = 'ALL';
	const STATUS_BOOK = 'BOOK';
	const STATUS_INFO = 'INFO';
	const STATUSES = [self::STATUS_ALL, self::STATUS_BOOK, self::STATUS_INFO];

	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	/** @var string */
	protected $scope = 'AISP';



	/**
	 * The operation provides the relevant data about bank customer's accounts in form of a list.
	 * @param int
	 * @param int
	 * @param int
	 * @param string
	 * @return mixed
	 */
	public function getAccounts(int $page = 1, int $pageSize = 50, int $pageCount = NULL, string $order = self::ORDER_DESC)
	{
		$this->validateParameterPage($page);
		$this->validateParameterPageSize($pageSize);
		$this->validateParameterPageCount($pageCount);
		$this->validateParameterOrder($order);

		return $this->sendApiRequest('accounts', 'GET', [
			'page' => $page,
			'pageSize' => $pageSize,
			'pageCount' => $pageCount,
			'order' => $order,
		]);
	}



	/**
	 * The operation provides the relevant data from a bank customer's account identified by IBAN.
	 * @param string
	 * @return mixed
	 */
	public function postAccountInfo(string $iban)
	{
		$this->validateParameterIban($iban);

		$params = ['iban' => $iban];
		return $this->sendApiRequest('accounts/information', 'POST', $params, [], true);
	}



	/**
	 * The list of financial transactions perfomed on a customer's bank account withing a date period.
	 * @param string
	 * @param string
	 * @param \DateTime
	 * @param \DateTime
	 * @param int
	 * @param int
	 * @return mixed
	 */
	public function postTransactions(string $iban, string $status = self::STATUS_ALL, \DateTime $dateFrom = NULL, \DateTime $dateTo = NULL, int $page = 1, int $pageSize = 50)
	{
		$this->validateParameterIban($iban);
		$this->validateParameterStatus($status);
		$this->validateParameterDateRange($dateFrom, $dateTo);
		$this->validateParameterPage($page);
		$this->validateParameterPageSize($pageSize);

		$params = [
			'iban' => $iban,
			'status' => $status,
			'page' => $page,
			'pageSize' => $pageSize,
		];

		if ($dateFrom !== NULL) {
			$params['dateFrom'] = $dateFrom->format('Y-m-d');
		}
		if ($dateTo !== NULL) {
			$params['dateTo'] = $dateTo->format('Y-m-d');
		}

		return $this->sendApiRequest('accounts/transactions', 'POST', $params, [], true);
	}



	/**
	 * @param string
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterStatus(string $status): void
	{
		if (!in_array($status, self::STATUSES)) {
			throw new TatraBankaApiException("Status '" . $status . "' not found.");
		}
	}



	/**
	 * @param \DateTime
	 * @param \DateTime
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterDateRange(\DateTime $dateFrom = NULL, \DateTime $dateTo = NULL): void
	{
		if ($dateTo !== NULL && $dateFrom !== NULL && $dateTo < $dateFrom) {
			throw new TatraBankaApiException("Date from must be older than date to.");
		}
	}



	/**
	 * @param string
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterOrder(string $order): void
	{
		if (!in_array($order, [self::ORDER_ASC, self::ORDER_DESC])) {
			throw new TatraBankaApiException("Sorting type '" . $order . "' not found.");
		}
	}
}
