<?php

/**
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex])"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->addWhere('id', 5);
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex]) WHERE ([id] = ?)"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->addOrder('id');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex]) ORDER BY [id]"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setGroup('id');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex]) GROUP BY [id]"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setGroup('id');
		$builder->setHaving('id > 5');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex]) GROUP BY [id] HAVING [id] > 5"), $query);
	}
);


test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex, nameOfAnothorOne');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex], [nameOfAnothorOne])"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex,nameOfAnothorOne');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex],[nameOfAnothorOne])"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setForceIndex(['nameOfTheForceIndex', 'nameOfAnothorOne']);
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author] FORCE INDEX ([nameOfTheForceIndex], [nameOfAnothorOne])"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex');
		$builder->setForceIndex(null);
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author]"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex');
		$builder->setForceIndex('');
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author]"), $query);
	}
);

test(function() use ($context){
		$builder = new SqlBuilder('author', $context);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex');
		$builder->setForceIndex([]);
		$query = $builder->buildSelectQuery();
		Assert::same(reformat("SELECT [author].* FROM [author]"), $query);
	}
);

