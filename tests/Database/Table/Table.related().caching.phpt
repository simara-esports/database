<?php

/**
 * Test: Nette\Database\Table: Shared related data caching.
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


test(function () use ($context) {
	$books = $context->table('book');
	foreach ($books as $book) {
		foreach ($book->related('book_tag') as $bookTag) {
			$bookTag->tag;
		}
	}

	$tags = array();
	foreach ($books as $book) {
		foreach ($book->related('book_tag_alt') as $bookTag) {
			$tags[] = $bookTag->tag->name;
		}
	}

	Assert::same(array(
		'PHP',
		'MySQL',
		'JavaScript',
		'Neon',
	), $tags);
});


test(function () use ($context) {
	$authors = $context->table('author')->where('id', 11);
	$books = array();
	foreach ($authors as $author) {
		foreach ($author->related('book')->where('translator_id', NULL) as $book) {
			foreach ($book->related('book_tag') as $bookTag) {
				$books[] = $bookTag->tag->name;
			}
		}
	}
	Assert::same(array('JavaScript'), $books);

	foreach ($authors as $author) {
		foreach ($author->related('book')->where('NOT translator_id', NULL) as $book) {
			foreach ($book->related('book_tag')->order('tag_id') as $bookTag) {
				$books[] = $bookTag->tag->name;
			}
		}
	}
	Assert::same(array('JavaScript', 'PHP', 'MySQL'), $books);
});


test(function () use ($context) {
	$context->query('UPDATE book SET translator_id = 12 WHERE id = 2');
	$author = $context->table('author')->get(11);

	foreach ($author->related('book')->limit(1) as $book) {
		$book->ref('author', 'translator_id')->name;
	}

	$translators = array();
	foreach ($author->related('book')->limit(2) as $book) {
		$translators[] = $book->ref('author', 'translator_id')->name;
	}
	sort($translators);

	Assert::same(array(
		'David Grudl',
		'Jakub Vrana',
	), $translators);
});



test(function () use ($context) { // cache can't be affected by inner query!
	$author = $context->table('author')->get(11);
	$secondBookTagRels = NULL;
	foreach ($author->related('book')->order('id') as $book) {
		if (!isset($secondBookTagRels)) {
			$bookFromAnotherSelection = $author->related('book')->where('id', $book->id)->fetch();
			$bookFromAnotherSelection->related('book_tag')->fetchPairs('id');
			$secondBookTagRels = array();
		} else {
			foreach ($book->related('book_tag') as $bookTagRel) {
				$secondBookTagRels[] = $bookTagRel->tag->name;
			}
		}
	}
	Assert::same(array('JavaScript'), $secondBookTagRels);
});
