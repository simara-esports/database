<?php

/**
 *
 * @author     Svaťa Šimara
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


test(function() use ($context) {
	$sql = $context
		->table('book')
		->forceIndex('use_this_index')
		->select('book.*')
		->getSql();
	Assert::same(reformat(
		'SELECT [book].* FROM [book] '
		. 'FORCE INDEX (`use_this_index`)'), $sql);
});


test(function() use ($context) {
	$sql = $context
		->table('book')
		->where('id IS NOT NULL')
		->forceIndex('thisIndex')
		->select('book.*')
		->getSql();
	Assert::same(reformat(
		'SELECT [book].* FROM [book] '
		. 'FORCE INDEX (`thisIndex`) WHERE (`id` IS NOT NULL)'), $sql);
});

