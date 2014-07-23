<?php

/**
 *
 * @author		Svaťa Šimara
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");

$reflection = new DiscoveredConventions($structure);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`)", $query);
	}
);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->addWhere('id', 5);
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`) WHERE (`id` = ?)", $query);
	}
);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->addOrder('id');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`) ORDER BY `id`", $query);
	}
);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->setGroup('id');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`) GROUP BY `id`", $query);
	}
);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->setGroup('id');
		$builder->setHaving('id > 5');
		$builder->setForceIndex('nameOfTheForceIndex');
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`) GROUP BY `id` HAVING `id` > 5", $query);
	}
);


test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex, nameOfAnothorOne');
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`, `nameOfAnothorOne`)", $query);
	}
);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->setForceIndex('nameOfTheForceIndex,nameOfAnothorOne');
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`,`nameOfAnothorOne`)", $query);
	}
);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		$builder->setForceIndex(['nameOfTheForceIndex', 'nameOfAnothorOne']);
		$query = $builder->buildSelectQuery();
		Assert::same("SELECT `author`.* FROM `author` FORCE INDEX (`nameOfTheForceIndex`, `nameOfAnothorOne`)", $query);
	}
);

test(function() use ($connection, $reflection){
		$builder = new \Nette\Database\Table\SqlBuilder('author', $connection, $reflection);
		$builder->addSelect('author.*');
		Assert::exception(function() use ($builder){
			$builder->setForceIndex([]);
		}, '\Nette\InvalidArgumentException');
		
		Assert::exception(function() use ($builder){
			$builder->setForceIndex('');
		}, '\Nette\InvalidArgumentException');
		
		Assert::exception(function() use ($builder){
			$builder->setForceIndex(null);
		}, '\Nette\InvalidArgumentException');
	}
);
