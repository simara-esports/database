<?php

/**
 *
 * @author		Svaťa Šimara
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Reflection\DiscoveredReflection;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

class SqlBuilderMock extends SqlBuilder
{
	public function parseJoins(& $joins, & $query, $inner = FALSE)
	{
		parent::parseJoins($joins, $query);
	}
	public function buildQueryJoins(array $joins, $leftConditions = array())
	{
		return parent::buildQueryJoins($joins, $leftConditions);
	}
}

$reflection = new DiscoveredReflection($connection);

$authorSqlBuilder = new SqlBuilderMock('author', $connection, $reflection);

$joins = array();
$authorSqlBuilder->addAlias(':book(translator)', 'bok');
$authorSqlBuilder->addAlias(':book:book_tag', 'bok2');

Assert::exception(function() use ($authorSqlBuilder){
	$authorSqlBuilder->addAlias(':book', 'bok');
}, '\Nette\InvalidArgumentException');

Assert::exception(function() use ($authorSqlBuilder){
	$joins = array();
	$query = 'WHERE :bok.next_volume';
	$authorSqlBuilder->parseJoins($joins, $query);
}, '\Nette\InvalidArgumentException');

Assert::exception(function() use ($authorSqlBuilder){
	$authorSqlBuilder->addAlias('bok.book', 'bokxxx');
}, '\Nette\InvalidArgumentException');

$query = 'WHERE bok.next_volume IS NULL OR bok2.x IS NULL OR bok2.tag.x IS NULL';
$authorSqlBuilder->parseJoins($joins, $query);

$join = $authorSqlBuilder->buildQueryJoins($joins);
Assert::same(
	'LEFT JOIN book AS bok ON author.id = bok.translator_id LEFT JOIN book ON author.id = book.author_id'
	. ' LEFT JOIN book_tag AS bok2 ON book.id = bok2.book_id LEFT JOIN tag ON bok2.tag_id = tag.id',
	trim($join)
);



$bookJoins = array();
$bookSqlBuilder = new SqlBuilderMock('book', $connection, $reflection);
$bookSqlBuilder->addAlias('author', 'aut');
$bookSqlBuilder->addAlias('author:book(translator)', 'trans');
$bookQuery = "WHERE aut.name LIKE '%abc%' OR aut:book.id IS NOT NULL OR trans.id IS NOT NULL";
$bookSqlBuilder->parseJoins($bookJoins, $bookQuery);
$join = $authorSqlBuilder->buildQueryJoins($bookJoins);
var_dump($join);
Assert::same(
	'LEFT JOIN author AS aut ON book.author_id = aut.id'
	. ' LEFT JOIN book ON aut.id = book.author_id'
	. ' LEFT JOIN author ON book.author_id = author.id'
	. ' LEFT JOIN book AS trans ON author.id = trans.translator_id',
	trim($join)
);

