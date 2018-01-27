<?php declare(strict_types=1);

use Tester\Assert;
use TatraBankaApi\PaymentAmount;

require __DIR__ . '/bootstrap.php';


$tb = new PaymentAmount(10.0, 'EUR');

Assert::same(10.0, $tb->getValue());
Assert::same('EUR', $tb->getCurrency());

Assert::exception(function () use ($tb) {
	new PaymentAmount(-1);
}, '\TatraBankaApi\TatraBankaApiException', "The value parameter must be within the range of 0.0 to 9999999999.99.");
Assert::exception(function () use ($tb) {
	new PaymentAmount(9999999999.999);
}, '\TatraBankaApi\TatraBankaApiException', "The value parameter must be within the range of 0.0 to 9999999999.99.");
Assert::exception(function () use ($tb) {
	new PaymentAmount(10.0, 'nonsense');
}, '\TatraBankaApi\TatraBankaApiException', "Currency is not in the correct format. An alphabetic code within ISO 4712 must be used.");
