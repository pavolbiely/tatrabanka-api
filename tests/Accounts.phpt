<?php declare(strict_types=1);

use Tester\Assert;
use TatraBankaApi\Accounts;

require __DIR__ . '/bootstrap.php';

$tempDir = __DIR__ . '/../temp';

Tester\Helpers::purge($tempDir);


$tempDir = __DIR__ . '/../temp';

$tb = new Accounts('123', '456', 'http://example.org', $tempDir);
$tb->useSandbox(true);

Assert::exception(function () use ($tb) {
	$tb->getToken();
}, '\TatraBankaApi\TatraBankaApiException', 'The access token is missing or expired');

Assert::same(false, $tb->isAuthorized());

$tb->setToken('abc', 'xyz', 3600, 'bearer', NULL);
$token = (array) $tb->getToken();
Assert::same([
	'access_token' => 'abc',
	'expires_in' => 3600,
	'token_type' => 'bearer',
	'scope' => NULL,
	'refresh_token' => 'xyz',
], $token);

Assert::true($tb->isAuthorized());
Assert::true($tb->isSandbox());

Assert::same('123', $tb->getClientId());
Assert::same('456', $tb->getClientSecret());
Assert::same('http://example.org', $tb->getRedirectUri());
Assert::same('https://api.tatrabanka.sk/sandbox/auth/oauth/v2/authorize?client_id=123&response_type=code&redirect_uri=http%3A%2F%2Fexample.org&scope=AISP', $tb->getAuthorizationUrl());
Assert::same($tempDir, $tb->getTempDir());

$tb->useSandbox(false);
Assert::false($tb->isSandbox());

Assert::exception(function () use ($tb) {
	$tb->getAccounts(0);
}, '\TatraBankaApi\TatraBankaApiException', "The page parameter must be within the range of 1 to 99999999.");
Assert::exception(function () use ($tb) {
	$tb->getAccounts(1000000000);
}, '\TatraBankaApi\TatraBankaApiException', "The page parameter must be within the range of 1 to 99999999.");

Assert::exception(function () use ($tb) {
	$tb->getAccounts(1, 9);
}, '\TatraBankaApi\TatraBankaApiException', "The pageSize parameter can only have values of 10, 20, 30, 40, 50, 60, 70, 80, 90, 100.");
Assert::exception(function () use ($tb) {
	$tb->getAccounts(1, 99);
}, '\TatraBankaApi\TatraBankaApiException', "The pageSize parameter can only have values of 10, 20, 30, 40, 50, 60, 70, 80, 90, 100.");

Assert::exception(function () use ($tb) {
	$tb->getAccounts(1, 10, -1);
}, '\TatraBankaApi\TatraBankaApiException', "The page parameter must be within the range of 0 to 99999999.");
Assert::exception(function () use ($tb) {
	$tb->getAccounts(1, 10, 1000000000);
}, '\TatraBankaApi\TatraBankaApiException', "The page parameter must be within the range of 0 to 99999999.");

Assert::exception(function () use ($tb) {
	$tb->getAccounts(1, 10, 1, 'xxx');
}, '\TatraBankaApi\TatraBankaApiException', "Sorting type 'xxx' not found.");

Assert::exception(function () use ($tb) {
	$tb->postAccountInfo('this_is_invalid_iban_string');
}, '\TatraBankaApi\TatraBankaApiException', "IBAN is not in the correct format.");

Assert::exception(function () use ($tb) {
	$tb->postTransactions('this_is_invalid_iban_string');
}, '\TatraBankaApi\TatraBankaApiException', "IBAN is not in the correct format.");

Assert::exception(function () use ($tb) {
	$tb->postTransactions('SK0511000000002600000054', 'FAKE');
}, '\TatraBankaApi\TatraBankaApiException', "Status 'FAKE' not found.");

Assert::exception(function () use ($tb) {
	$tb->postTransactions('SK0511000000002600000054', $tb::STATUS_ALL, new \DateTime('tomorrow'), new \DateTime('today'));
}, '\TatraBankaApi\TatraBankaApiException', "Date from must be older than date to.");

Assert::exception(function () use ($tb) {
	$tb->postTransactions('SK0511000000002600000054', $tb::STATUS_ALL, new \DateTime('today'), new \DateTime('today'), 0);
}, '\TatraBankaApi\TatraBankaApiException', "The page parameter must be within the range of 1 to 99999999.");

Assert::exception(function () use ($tb) {
	$tb->postTransactions('SK0511000000002600000054', $tb::STATUS_ALL, new \DateTime('today'), new \DateTime('today'), 1, 25);
}, '\TatraBankaApi\TatraBankaApiException', "The pageSize parameter can only have values of 10, 20, 30, 40, 50, 60, 70, 80, 90, 100.");




$tb = new Accounts('123', '456', 'http://example.org', $tempDir);
Assert::same(true, $tb->isAuthorized());

$tb = new Accounts('123', '456', 'http://example.org');
Assert::same(sys_get_temp_dir(), $tb->getTempDir());
