<?php

/**
 * Test: Nette\Database\Table: tryDelimite.
 * @dataProvider? ../databases.ini
 * @phpVersion 5.3.2 due to ReflectionMethod::setAccessible
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection


$sqlBuilder = new Nette\Database\Table\SqlBuilder('book', $context);
$tryDelimite = $sqlBuilder->getReflection()->getMethod('tryDelimite');
$tryDelimite->setAccessible(TRUE);

Assert::same(reformat('[hello]'), $tryDelimite->invoke($sqlBuilder, 'hello'));
Assert::same(reformat(' [hello] '), $tryDelimite->invoke($sqlBuilder, ' hello '));
Assert::same(reformat('HELLO'), $tryDelimite->invoke($sqlBuilder, 'HELLO'));
Assert::same(reformat('[HellO]'), $tryDelimite->invoke($sqlBuilder, 'HellO'));
Assert::same(reformat('[hello].[world]'), $tryDelimite->invoke($sqlBuilder, 'hello.world'));
Assert::same(reformat('[hello] [world]'), $tryDelimite->invoke($sqlBuilder, 'hello world'));
Assert::same(reformat('HELLO([world])'), $tryDelimite->invoke($sqlBuilder, 'HELLO(world)'));
Assert::same(reformat('hello([world])'), $tryDelimite->invoke($sqlBuilder, 'hello(world)'));
Assert::same('[hello]', $tryDelimite->invoke($sqlBuilder, '[hello]'));
