<?php declare(strict_types=1);

use Tester\Assert;
use TatraBankaApi\Payments;
use TatraBankaApi\PaymentParticipant;
use TatraBankaApi\PaymentAmount;

require __DIR__ . '/bootstrap.php';

$tempDir = __DIR__ . '/../temp';

$debtor = new PaymentParticipant('John Doe', 'SK0511000000002600000054');
$creditor = new PaymentParticipant('John Doe', 'DE89370400440532013000');
$amount = new PaymentAmount(100.15);

$tb = new Payments('123', '456', 'http://example.org', $tempDir);
$tb->useSandbox(true);

Assert::exception(function () use ($tb) {
	$tb->getPaymentStatus('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
}, '\TatraBankaApi\TatraBankaApiException', "Invalid orderId parameter 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'.");

Assert::exception(function () use ($tb, $debtor, $creditor, $amount) {
	$tb->postPaymentSba('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $debtor, $creditor, $amount, new \DateTime('tomorrow'), new \DateTime('now'), '/VS123/SS456/KS0308', 'Test');
}, '\TatraBankaApi\TatraBankaApiException', "Invalid instruction identification parameter 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'.");

Assert::exception(function () use ($tb, $debtor, $creditor, $amount) {
	$tb->postPaymentSba('123', $debtor, $creditor, $amount, new \DateTime('tomorrow'), new \DateTime('now'), '/VS123/SS456/KS0308', str_repeat('a', 141));
}, '\TatraBankaApi\TatraBankaApiException', "Invalid remittance information parameter '" . str_repeat('a', 141) . "'.");

Assert::exception(function () use ($tb, $debtor, $creditor, $amount) {
	$tb->postPaymentSba('123', $debtor, $creditor, $amount, new \DateTime('tomorrow'), new \DateTime('now'), 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'Test');
}, '\TatraBankaApi\TatraBankaApiException', "Invalid end to end identification parameter 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'.");

Assert::exception(function () use ($tb, $debtor, $creditor, $amount) {
	$tb->postPaymentSbaEcomm('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $debtor, $creditor, $amount, new \DateTime('now'), '/VS123/SS456/KS0308', 'Test');
}, '\TatraBankaApi\TatraBankaApiException', "Invalid instruction identification parameter 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'.");

Assert::exception(function () use ($tb, $debtor, $creditor, $amount) {
	$tb->postPaymentSbaEcomm('123', $debtor, $creditor, $amount, new \DateTime('now'), '/VS123/SS456/KS0308', str_repeat('a', 141));
}, '\TatraBankaApi\TatraBankaApiException', "Invalid remittance information parameter '" . str_repeat('a', 141) . "'.");

Assert::exception(function () use ($tb, $debtor, $creditor, $amount) {
	$tb->postPaymentSbaEcomm('123', $debtor, $creditor, $amount, new \DateTime('now'), 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'Test');
}, '\TatraBankaApi\TatraBankaApiException', "Invalid end to end identification parameter 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'.");

