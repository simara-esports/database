<?php

/**
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Conventions\DiscoveredConventions;
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

test(function() use ($context){
	$authorSqlBuilder = new SqlBuilderMock('author', $context);
	$authorSqlBuilder->addAlias(':book(translator)', 'bok');

	Assert::exception(function() use ($authorSqlBuilder){
		$authorSqlBuilder->addAlias(':book', 'bok');
	}, '\Nette\InvalidArgumentException');

	Assert::exception(function() use ($authorSqlBuilder){
		$joins = array();
		$query = 'WHERE :bok.next_volume';
		$authorSqlBuilder->parseJoins($joins, $query);
	}, '\Nette\InvalidArgumentException');

});

test(function() use ($context){
	$authorSqlBuilder = new SqlBuilderMock('author', $context);
	$joins = array();
	$authorSqlBuilder->addAlias(':book(translator)', 'bok');
	$authorSqlBuilder->addAlias(':book:book_tag', 'bok2');

	$query = 'WHERE bok.next_volume IS NULL OR bok2.x IS NULL OR bok2.tag.x IS NULL';
	$authorSqlBuilder->parseJoins($joins, $query);

	$join = $authorSqlBuilder->buildQueryJoins($joins);
	Assert::same(
		'LEFT JOIN book bok ON author.id = bok.translator_id LEFT JOIN book ON author.id = book.author_id'
		. ' LEFT JOIN book_tag bok2 ON book.id = bok2.book_id LEFT JOIN tag ON bok2.tag_id = tag.id',
		trim($join)
	);
});


test(function() use ($context){
	$bookJoins = array();
	$bookSqlBuilder = new SqlBuilderMock('book', $context);
	$bookSqlBuilder->addAlias('author', 'aut');
	$bookSqlBuilder->addAlias('author:book(translator)', 'trans');
	$bookQuery = "WHERE aut.name LIKE '%abc%' OR aut:book.id IS NOT NULL OR trans.id IS NOT NULL";
	$bookSqlBuilder->parseJoins($bookJoins, $bookQuery);
	$join = $bookSqlBuilder->buildQueryJoins($bookJoins);
	Assert::same(
		'LEFT JOIN author aut ON book.author_id = aut.id'
		. ' LEFT JOIN book ON aut.id = book.author_id'
		. ' LEFT JOIN author ON book.author_id = author.id'
		. ' LEFT JOIN book trans ON author.id = trans.translator_id',
		trim($join)
	);
});

test(function() use ($context){
	$bookJoins = array();

	$bookSqlBuilder = new SqlBuilderMock('book', $context);
	$bookSqlBuilder->addAlias(':book_tag.tag:book_tag_alt.book', 'book2');

	$bookQuery = "WHERE book2.id IS NOT NULL";
	$bookSqlBuilder->parseJoins($bookJoins, $bookQuery);

	$join = $bookSqlBuilder->buildQueryJoins($bookJoins);

	Assert::same(
		'LEFT JOIN book_tag ON book.id = book_tag.book_id'
		. ' LEFT JOIN tag ON book_tag.tag_id = tag.id'
		. ' LEFT JOIN book_tag_alt ON tag.id = book_tag_alt.tag_id'
		. ' LEFT JOIN book book2 ON book_tag_alt.book_id = book2.id',
		trim($join)
	);
});

test(function() use ($context){
	$bookJoins = array();

	$bookSqlBuilder = new SqlBuilderMock('book', $context);
	$bookSqlBuilder->addAlias(':book_tag.tag', 'tagAlias');
	$bookSqlBuilder->addAlias(':book_tag.tag', 'tag2Alias');
	$bookSqlBuilder->addAlias('tagAlias:book_tag_alt', 'btaAlias');
	$bookSqlBuilder->addAlias('tag2Alias:book_tag_alt', 'bta2Alias');
	$bookSqlBuilder->addAlias('btaAlias.book', 'bookAlias');
	$bookSqlBuilder->addAlias('bta2Alias.book', 'book2Alias');

	$bookQuery = "WHERE btaAlias.statte='public' AND bta2Alias.state='private' AND (bookAlias.id IS NOT NULL OR book2Alias.id IS NOT NULL)";
	$bookSqlBuilder->parseJoins($bookJoins, $bookQuery);

	$join = $bookSqlBuilder->buildQueryJoins($bookJoins);

	Assert::same(
		'LEFT JOIN book_tag ON book.id = book_tag.book_id'
			. ' LEFT JOIN tag tagAlias ON book_tag.tag_id = tagAlias.id'
			. ' LEFT JOIN book_tag_alt btaAlias ON tagAlias.id = btaAlias.tag_id'
			. ' LEFT JOIN tag tag2Alias ON book_tag.tag_id = tag2Alias.id'
			. ' LEFT JOIN book_tag_alt bta2Alias ON tag2Alias.id = bta2Alias.tag_id'
			. ' LEFT JOIN book bookAlias ON btaAlias.book_id = bookAlias.id'
			. ' LEFT JOIN book book2Alias ON bta2Alias.book_id = book2Alias.id',
		trim($join)
	);
});

test(function() use ($context){
	$bookJoins = array();

	$bookSqlBuilder = new SqlBuilderMock('book', $context);
	$bookSqlBuilder->addAlias(':book_tag', 'btAlias');
	$bookSqlBuilder->addAlias('btAlias.tag:book_tag_alt', 'btaAlias');
	$bookSqlBuilder->addAlias('btaAlias.book', 'bookAlias');

	$bookQuery = "WHERE btaAlias.statte='public' AND bookAlias.id IS NOT NULL";
	$bookSqlBuilder->parseJoins($bookJoins, $bookQuery);

	$join = $bookSqlBuilder->buildQueryJoins($bookJoins);

	Assert::same(
		'LEFT JOIN book_tag btAlias ON book.id = btAlias.book_id'
			. ' LEFT JOIN tag ON btAlias.tag_id = tag.id'
			. ' LEFT JOIN book_tag_alt btaAlias ON tag.id = btaAlias.tag_id'
			. ' LEFT JOIN book bookAlias ON btaAlias.book_id = bookAlias.id',
		trim($join)
	);
});
