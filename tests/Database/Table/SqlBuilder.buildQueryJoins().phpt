<?php

/**
 * Test: Nette\Database\Table\SqlBuilder: parseJoins().
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test2.sql");


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
	public function buildLeftJoinConditions($allLeftJoinConditions) {
		return parent::buildLeftJoinConditions($allLeftJoinConditions);
	}
}

$sqlBuilder = new SqlBuilderMock('nUsers', $context);

$joins = array();
$leftJoins = array(':nusers_ntopics.topic.priorit.id IS NOT NULL', ':nusers_ntopics.topic.priorit.id = ?');
foreach($leftJoins as &$oneLeft){
	$sqlBuilder->parseJoins($joins, $oneLeft);
}
$leftConditions = $sqlBuilder->buildLeftJoinConditions($leftJoins);
$join = $sqlBuilder->buildQueryJoins($joins, $leftConditions);
Assert::same('priorit.id IS NOT NULL AND priorit.id = ?', $leftConditions['priorit']);

$tables = $connection->getSupplementalDriver()->getTables();
if (!in_array($tables[0]['name'], array('npriorities', 'ntopics', 'nusers', 'nusers_ntopics', 'nusers_ntopics_alt'), TRUE)) {
	Assert::same(
		'LEFT JOIN nUsers_nTopics nusers_ntopics ON nUsers.nUserId = nusers_ntopics.nUserId ' .
		'LEFT JOIN nTopics topic ON nusers_ntopics.nTopicId = topic.nTopicId ' .
		'LEFT JOIN nPriorities priorit ON topic.nPriorityId = priorit.nPriorityId AND (priorit.id IS NOT NULL AND priorit.id = ?)',
		trim($join)
	);
} else {
	Assert::same(
		'LEFT JOIN nusers_ntopics ON nUsers.nUserId = nusers_ntopics.nUserId ' .
		'LEFT JOIN ntopics topic ON nusers_ntopics.nTopicId = topic.nTopicId ' .
		'LEFT JOIN npriorities priorit ON topic.nPriorityId = priorit.nPriorityId AND (priorit.id IS NOT NULL AND priorit.id = ?)',
		trim($join)
	);
}



Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

$structure = new Nette\Database\Structure($connection, $cacheMemoryStorage);
$conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);
$context = new Nette\Database\Context($connection, $structure, $conventions, $cacheMemoryStorage);

$sqlBuilder = new SqlBuilderMock('author', $context);

$joins = array();
$leftJoin = ':book(translator).next_volume = ? OR :book(translator).next_volume IS NULL';
$sqlBuilder->parseJoins($joins, $leftJoin);
$leftConditions = $sqlBuilder->buildLeftJoinConditions(array($leftJoin));
$join = $sqlBuilder->buildQueryJoins($joins, $leftConditions);
Assert::same('book.next_volume = ? OR book.next_volume IS NULL', $leftConditions['book']);
Assert::same(
	'LEFT JOIN book ON author.id = book.translator_id AND (book.next_volume = ? OR book.next_volume IS NULL)',
	trim($join)
);



$sqlBuilder = new SqlBuilderMock('author', $context);

$joins = array();
$leftJoin = "5 IS NOT NULL OR 5 = 3";
$sqlBuilder->parseJoins($joins, $leftJoin);
$leftConditions = $sqlBuilder->buildLeftJoinConditions(array($leftJoin));
Assert::same(array(), $leftConditions);