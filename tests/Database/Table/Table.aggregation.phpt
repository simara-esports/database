<?php

/**
 * Test: Nette\Database\Table: Aggregation functions.
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

$prepareAggregation = Nette\Database\Table\Selection::getReflection()->getMethod('prepareAggregation');
$prepareAggregation->setAccessible(true);

test(function () use ($context) {
	$count = $context->table('book')->count('*');  // SELECT COUNT(*) FROM `book`
	Assert::same(4, $count);
});


test(function () use ($context) {
	$tags = array();
	foreach ($context->table('book') as $book) {  // SELECT * FROM `book`
		$count = $book->related('book_tag')->count('*');  // SELECT COUNT(*), `book_id` FROM `book_tag` WHERE (`book_tag`.`book_id` IN (1, 2, 3, 4)) GROUP BY `book_id`
		$tags[$book->title] = $count;
	}

	Assert::same(array(
		'1001 tipu a triku pro PHP' => 2,
		'JUSH' => 1,
		'Nette' => 1,
		'Dibi' => 2,
	), $tags);
});


test(function () use ($context) {
	$authors = $context->table('author')->where(':book.translator_id IS NOT NULL')->group('author.id');  // SELECT `author`.* FROM `author` INNER JOIN `book` ON `author`.`id` = `book`.`author_id` WHERE (`book`.`translator_id` IS NOT NULL) GROUP BY `author`.`id`
	Assert::count(2, $authors);
	Assert::same(2, $authors->count('DISTINCT author.id'));  // SELECT COUNT(DISTINCT author.id) FROM `author` INNER JOIN `book` ON `author`.`id` = `book`.`author_id` WHERE (`book`.`translator_id` IS NOT NULL)
});

test(function() use ($context, $prepareAggregation){
	$table = $context->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->alias(':book_tag.tag', 't')
		->where('book.name LIKE', 'PHP')
		->left('pp.value > ?', 0)
		->left(':book_tag.tag_id IS NOT NULL');
	$table->removeLefts();
	$selection = $prepareAggregation->invoke($table, 'COUNT(*)');
	$sql = $selection->getSql();
	Assert::same(reformat(
		'SELECT COUNT(*) FROM [book]'
		. ' WHERE ([book].[name] LIKE ?)'), $sql);
});

test(function() use ($context, $prepareAggregation){
	$table = $context->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->alias(':book_tag.tag', 't')
		->where('author.name LIKE', 'ja')
		->left('pp.value > ?', 0)
		->left(':book_tag.tag_id IS NOT NULL');
	$table->removeLefts();
	$selection = $prepareAggregation->invoke($table, 'COUNT(*)');
	$sql = $selection->getSql();
	Assert::same(reformat(
		'SELECT COUNT(*) FROM [book]'
		. ' LEFT JOIN [author] ON [book].[author_id] = [author].[id]'
		. ' WHERE ([author].[name] LIKE ?)'), $sql);
});

