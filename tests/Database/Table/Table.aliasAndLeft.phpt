<?php

/**
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


test(function() use ($context) {
	$sql = $context
		->table('book')
		->alias('translator', 'trans')
		->left('trans.name', 'Geek')
		->select('book.*')
		->getSql();
	Assert::same(reformat(
		'SELECT [book].* FROM [book] '
		. 'LEFT JOIN [author] AS [trans] ON [book].[translator_id] = [trans].[id] AND ([trans].[name] = ?)'), $sql);
});

test(function() use ($context){
	$sql = $context
		->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->select('book.*, pp.value')
		->getSql();
	Assert::same(reformat(
		'SELECT [book].*, [pp].[value]'
		. ' FROM [book]'
		. ' LEFT JOIN [product_price] AS [pp] ON [book].[id] = [pp].[book_id] AND ([pp].[active] = ?)'), $sql);
});

test(function() use ($context){
	$sql = $context
		->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->select('book.*, :product_price.value')//tricky
		->getSql();
	Assert::same(reformat(
		'SELECT [book].*, [product_price].[value]'
		. ' FROM [book]'
		. ' LEFT JOIN [product_price] AS [pp] ON [book].[id] = [pp].[book_id] AND ([pp].[active] = ?)'
		. ' LEFT JOIN [product_price] ON [book].[id] = [product_price].[book_id]'), $sql);
});

test(function() use ($context){
	$sql = $context->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->alias(':book_tag.tag', 't')
		->where('t.name LIKE', 'PHP')
		->left('pp.value > ?', 0)
		->left(':book_tag.tag_id IS NOT NULL')
		->select('book.*, pp.value')->getSql();
	Assert::same(reformat(
		'SELECT [book].*, [pp].[value] FROM [book]'
		. ' LEFT JOIN [book_tag] ON [book].[id] = [book_tag].[book_id] AND ([book_tag].[tag_id] IS NOT NULL)'
		. ' LEFT JOIN [tag] AS [t] ON [book_tag].[tag_id] = [t].[id]'
		. ' LEFT JOIN [product_price] AS [pp] ON [book].[id] = [pp].[book_id] AND ([pp].[active] = ? AND [pp].[value] > ?)'
		. ' WHERE ([t].[name] LIKE ?)'), $sql);
});

test(function() use ($context){
	$count = $context
		->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->select('book.*, pp.value')
		->count('book.id');
	Assert::same(4, $count);
});

test(function() use ($context){
	$sql = $context->table('book')
		->left(':product_price.active', 1)
		->left(':book_tag.tag_id IS NOT NULL OR :product_price.active IS NOT NULL')
		->select('book.*, :product_price.value')->getSql();

	Assert::same(reformat('SELECT [book].*, [product_price].[value] FROM [book]'
		. ' LEFT JOIN [product_price] ON [book].[id] = [product_price].[book_id] AND ([product_price].[active] = ?)'
		. ' LEFT JOIN [book_tag] ON [book].[id] = [book_tag].[book_id] AND ([book_tag].[tag_id] IS NOT NULL OR [product_price].[active] IS NOT NULL)'), $sql);
});