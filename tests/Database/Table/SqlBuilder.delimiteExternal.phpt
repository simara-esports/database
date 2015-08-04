<?php

/**
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Table\SqlBuilder;

$connectionOptions = [
	'driverClass' => 'Nette\Database\Drivers\ExternalMySqlDriver',
	'externalTables' => [
		[
		'name' => 'ext_db',
		'tables' => [
			'author', 'tag'
			],
		],
	],
];

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

test(function() use ($context){
		$builder = new \Nette\Database\Table\SqlBuilder('book', $context);
		$builder->addSelect('id');
		$builder->addWhere('id > ?', 5);
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `id` FROM `book` WHERE (`id` > ?)", $query);
	}
);

test(function() use ($context){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $context);
		$builder->addSelect('id');
		$builder->addWhere('id > ?', 5);
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `id` FROM `ext_db`.`author` WHERE (`id` > ?)", $query);
	}
);

test(function() use ($context){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $context);
		$builder->addSelect('id');
		$builder->addWhere(':book.id > ?', 5);
		$query = $builder->buildSelectQuery();
		Assert::same(
			  "SELECT `id` FROM `ext_db`.`author`"
			. " LEFT JOIN `book` ON `ext_db`.`author`.`id` = `book`.`author_id`"
			. " WHERE (`book`.`id` > ?)", $query);
	}
);

test(function() use ($context){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $context);
		$builder->addSelect('id');
		$builder->addWhere(':book.id > ?', 5);
		$builder->addWhere('author.id > ?', 5);
		$query = $builder->buildSelectQuery();
		Assert::same(
			  "SELECT `id` FROM `ext_db`.`author`"
			. " LEFT JOIN `book` ON `ext_db`.`author`.`id` = `book`.`author_id`"
			. " WHERE (`book`.`id` > ?) AND (`ext_db`.`author`.`id` > ?)", $query);
	}
);

test(function() use ($context){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->addWhere(':book.id > ?', 5);
		$builder->addWhere('author.id > ?', 5);
		$query = $builder->buildSelectQuery();
		Assert::same(
			  "SELECT `ext_db`.`author`.* FROM `ext_db`.`author`"
			. " LEFT JOIN `book` ON `ext_db`.`author`.`id` = `book`.`author_id`"
			. " WHERE (`book`.`id` > ?) AND (`ext_db`.`author`.`id` > ?)", $query);
	}
);

