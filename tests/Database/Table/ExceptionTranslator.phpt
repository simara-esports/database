<?php

/**
 * Test: Esports\Database\ExceptionTranslator: translate exception
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

if ($driverName !== "mysql") {
	Tester\Environment::skip("This test is only for MySQL");
}

test(function() use ($context) {
	$connection = $context->getConnection();
	$translator = new \Esports\Database\ExceptionTranslator;
	$translator->connect($connection);

	Assert::exception(function () use ($context) {
		$context
			->table('author')
			->delete();
	}, '\Esports\ConstraintViolationException');
});

test(function() use ($context) {
	$connection = $context->getConnection();
	$translator = new \Esports\Database\ExceptionTranslator;
	$translator->connect($connection);

	Assert::exception(function () use ($context) {
		$context
			->table('book')
			->insert([
				'id' => 1,
				'author_id' => 11,
				'translator_id' => null,
				'title' => 'test'
			]);
	}, '\Esports\PrimaryKeyException');
});