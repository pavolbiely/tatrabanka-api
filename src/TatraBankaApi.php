<?php declare(strict_types=1);

namespace TatraBankaApi;

abstract class TatraBankaApi
{
	const API_URL = 'https://api.tatrabanka.sk';

	/** @var bool */
	protected $sandbox = false;

	/** @var string */
	protected $clientId;

	/** @var string */
	protected $clientSecret;

	/** @var string */
	protected $redirectUri;

	/** @var string */
	protected $scope;

	/** @var string */
	protected $tempDir;

	/** @var string */
	protected $token;

	/** @var int */
	protected $lastResponseCode;



	/**
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 */
	public function __construct(string $clientId, string $clientSecret, string $redirectUri, $tempDir = NULL)
	{
		$this->setClientId($clientId);
		$this->setClientSecret($clientSecret);
		$this->setRedirectUri($redirectUri);

		if ($tempDir === NULL) {
			$tempDir = sys_get_temp_dir();
		}
		$this->tempDir = rtrim($tempDir, '/');
	}



	/**
	 * @param bool
	 */
	public function useSandbox(bool $sandbox = true): self
	{
		$this->sandbox = $sandbox;
		return $this;
	}



	/**
	 * @return bool
	 */
	public function isSandbox(): bool
	{
		return $this->sandbox;
	}



	/**
	 * @param string
	 * @return self
	 */
	protected function setClientId(string $clientId): self
	{
		$this->clientId = $clientId;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getClientId(): string
	{
		return $this->clientId;
	}


	/**
	 * @param string
	 * @return self
	 */
	protected function setClientSecret(string $clientSecret): self
	{
		$this->clientSecret = $clientSecret;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getClientSecret(): string
	{
		return $this->clientSecret;
	}


	/**
	 * @param string
	 * @return self
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function setRedirectUri(string $url = NULL): self
	{
		if ($url === NULL || filter_var($url, FILTER_VALIDATE_URL)) {
			$this->redirectUri = $url;
		} else {
			throw new TatraBankaApiException('Invalid redirect URL format');
		}
		return $this;
	}


	/**
	 * @return string
	 */
	public function getRedirectUri(): string
	{
		return $this->redirectUri;
	}



	/**
	 * @return string
	 */
	public function getTempDir(): string
	{
		return $this->tempDir;
	}



	/**
	 * @return string
	 */
	public function getAuthorizationUrl(): string
	{
		return $this->getApiUrl() . '/auth/oauth/v2/authorize?' . http_build_query([
				'client_id' => $this->clientId,
				'response_type' => 'code',
				'redirect_uri' => $this->redirectUri,
				'scope' => $this->scope,
			]);
	}



	/**
	 * @return string
	 */
	protected function getApiUrl(): string
	{
		return self::API_URL . ($this->isSandbox() ? '/sandbox' : '');
	}



	/**
	 * @param string
	 * @param string
	 * @param int
	 * @param string
	 * @param string
	 * @return self
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	public function setToken(string $accessToken, string $refreshToken = NULL, int $expiresIn = 3600, string $tokenType = 'Bearer', string $scope = NULL): self
	{
		$token = (object) [
			'access_token'    => $accessToken,
			'expires_in'      => $expiresIn,
			'token_type'      => $tokenType,
			'scope'           => $scope,
			'refresh_token'   => $refreshToken,
		];
		if (@file_put_contents($this->getTokenFile(), serialize($token)) === false) {
			throw new TatraBankaApiException('Could not write to temp directory');
		}
		chmod($this->getTokenFile(), 0777);
		$this->token = (object) $token;
		return $this;
	}



	/**
	 * @return \stdClass
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	public function getToken(): \stdClass
	{
		if (!$this->isAuthorized()) {
			throw new TatraBankaApiException('The access token is missing or expired');
		}
		return $this->token;
	}



	/**
	 * @return string
	 */
	protected function getTokenFile(): string
	{
		return $this->tempDir . '/' . md5(implode('|', [static::class, $this->clientId, $this->clientSecret, $this->redirectUri]));
	}



	/**
	 * @return bool
	 */
	public function isAuthorized(): bool
	{
		if ($this->token === NULL && is_file($this->getTokenFile())) {
			$this->token = unserialize(file_get_contents($this->getTokenFile()));
		}

		return $this->token !== NULL;
	}



	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @param bool
	 * @return string
	 * @throws \TatraBankaApi\TatraBankaApiException
	 * @throws \RuntimeException
	 */
	protected function sendHttpRequest(string $url, string $type = 'GET', array $params = [], array $headers = [], bool $json = false)
	{
		if ($json) {
			$headers[] = 'Content-Type: application/json; charset=utf-8';
		} else {
			$headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
		}

		if ($type == 'GET' && !$json && count($params)) {
			$url = $url . '?' . http_build_query($params);
			$params = [];
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json ? json_encode($params) : http_build_query($params));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		if ($response === false) {
			throw new TatraBankaApiException(curl_error($ch), curl_errno($ch));
		}
		$this->lastResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $response;
	}




	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @param bool
	 * @return mixed
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function sendApiRequest(string $name, string $type, array $params = [], array $headers = [], $json = false)
	{
		$headers[] = 'Authorization: Bearer ' . $this->token->access_token;
		$headers[] = 'Request-ID: ' . static::generateUUID();
		//$headers[] = 'PSU-IP-Address: ' . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']);
		//$headers[] = 'PSU-Device-OS: ' . get_browser(null, true)['platform'] ?? NULL;
		//$headers[] = 'PSU-User-Agent: ' . $_SERVER['HTTP_USER_AGENT'];

		$response = $this->sendHttpRequest($this->getApiUrl() . '/api/v1/' . $name, $type, $params, $headers, $json);

		$data = json_decode($response);
		if (!$data) {
			throw new TatraBankaApiException('Unable to read JSON response');
		}

		if (isset($data->error)) {
			throw new TatraBankaApiException($data->error . (isset($data->error_description) ? ': ' . $data->error_description : NULL), $this->lastResponseCode);
		}

		return $data;
	}



	/**
	 * @return string
	 */
	protected static function generateUUID(): string
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}



	/**
	 * @param string
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterIban(string $iban): void
	{
		if (!preg_match('~^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$~', $iban)) {
			throw new TatraBankaApiException("IBAN is not in the correct format.");
		}
	}



	/**
	 * @param int
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterPage(int $page): void
	{
		if (!($page >= 1 && $page <= 99999999)) {
			throw new TatraBankaApiException("The page parameter must be within the range of 1 to 99999999.");
		}
	}



	/**
	 * @param int
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterPageCount(int $page): void
	{
		if (!($page >= 0 && $page <= 99999999)) {
			throw new TatraBankaApiException("The page parameter must be within the range of 0 to 99999999.");
		}
	}



	/**
	 * @param int
	 * @throws \TatraBankaApi\TatraBankaApiException
	 */
	protected function validateParameterPageSize(int $pageSize): void
	{
		$pageSteps = range(10, 100, 10);
		if (!in_array($pageSize, $pageSteps)) {
			throw new TatraBankaApiException("The pageSize parameter can only have values of " . implode(', ', $pageSteps) . ".");
		}
	}
}
