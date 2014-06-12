<?php

/** *
 * @author     Svaťa
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");
$reflection = new DiscoveredConventions($structure);


test(function() use ($connection, $reflection) { 
	$sqlBuilder = new SqlBuilder('author', $connection, $reflection);
	$sqlBuilder->addLeft(':book.name LIKE ?', 'some book');
	$sqlBuilder->addSelect('author.id, author.name');
	$sql = reformat('SELECT [author].[id], [author].[name] FROM [author] LEFT JOIN `book` ON `author`.`id` = `book`.`author_id` AND (`book`.[name] LIKE ?)');
	Assert::same($sql, $sqlBuilder->buildSelectQuery());
	Assert::same($sql, $sqlBuilder->buildSelectQuery());
});