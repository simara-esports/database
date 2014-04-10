<?php

/** *
 * @author     Svaťa
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Reflection\DiscoveredReflection;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

test(function() use ($connection, $reflection) { 
	$sqlBuilder = new SqlBuilder('author', $connection, $reflection);
	$sqlBuilder->addWhere('author.id = (SELECT !book.author_id FROM book LIMIT 1)');
	Assert::same(reformat('SELECT * FROM [author] WHERE ([author].[id] = (SELECT [book].[author_id] FROM [book] LIMIT 1))'), $sqlBuilder->buildSelectQuery());
});

test(function() use ($connection, $reflection) { 
	$sqlBuilder = new SqlBuilder('author', $connection, $reflection);
	$sqlBuilder->addWhere('(SELECT !t.id FROM tag AS t WHERE !t.id = :book:book_tag.tag.id LIMIT 1) IS NOT NULL');
	Assert::same(reformat('SELECT [author].* FROM `author` '
		. 'LEFT JOIN `book` ON `author`.`id` = `book`.`author_id` '
		. 'LEFT JOIN `book_tag` ON `book`.`id` = `book_tag`.`book_id` '
		. 'LEFT JOIN `tag` ON `book_tag`.`tag_id` = `tag`.`id` '
		. 'WHERE ((SELECT `t`.`id` FROM `tag` AS `t` WHERE `t`.`id` = `tag`.`id` LIMIT 1) IS NOT NULL)'), $sqlBuilder->buildSelectQuery());
});

test(function() use ($connection, $reflection) { 
	$sqlBuilder = new SqlBuilder('author', $connection, $reflection);
	$sqlBuilder->addLeft(':book.id IS NULL OR (SELECT !t.id FROM tag AS t WHERE !t.id = :book:book_tag.tag.id LIMIT 1) IS NOT NULL');
	Assert::same(reformat('SELECT [author].* FROM `author` '
		. 'LEFT JOIN `book` ON `author`.`id` = `book`.`author_id` '
		. 'AND (`book`.`id` IS NULL OR (SELECT `t`.`id` FROM `tag` AS `t` WHERE `t`.`id` = `tag`.`id` LIMIT 1) IS NOT NULL) '
		. 'LEFT JOIN `book_tag` ON `book`.`id` = `book_tag`.`book_id` '
		. 'LEFT JOIN `tag` ON `book_tag`.`tag_id` = `tag`.`id`'), $sqlBuilder->buildSelectQuery());
});

test(function() use ($connection, $reflection) { 
	$sqlBuilder = new SqlBuilder('author', $connection, $reflection);
	$sqlBuilder->addLeft(':book:book_tag.tag.id IN (SELECT !t.id FROM tag AS t WHERE !t.id ORDER BY !t.name, !t.id)');
	Assert::same(reformat('SELECT [author].* FROM `author` '
		. 'LEFT JOIN `book` ON `author`.`id` = `book`.`author_id` '
		. 'LEFT JOIN `book_tag` ON `book`.`id` = `book_tag`.`book_id` '
		. 'LEFT JOIN `tag` ON `book_tag`.`tag_id` = `tag`.`id` '
		. 'AND (`tag`.`id` IN (SELECT `t`.`id` FROM `tag` AS `t` WHERE `t`.`id` ORDER BY `t`.`name`, `t`.`id`))'), $sqlBuilder->buildSelectQuery());
});