<?php declare(strict_types=1);

use Tester\Assert;
use TatraBankaApi\PaymentParticipant;

require __DIR__ . '/bootstrap.php';


$tb = new PaymentParticipant('John Doe', 'SK6807200002891987426353');

Assert::same('John Doe', $tb->getName());
Assert::same('SK6807200002891987426353', $tb->getIban());

Assert::exception(function () use ($tb) {
	new PaymentParticipant(str_repeat('John Doe', 100), 'SK6807200002891987426353');
}, '\TatraBankaApi\TatraBankaApiException', "Name is not in the correct format.");
Assert::exception(function () use ($tb) {
	new PaymentParticipant('John Doe', '123');
}, '\TatraBankaApi\TatraBankaApiException', "IBAN is not in the correct format.");
