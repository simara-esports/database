<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Database;



require __DIR__ . '/../bootstrap.php';



try {
	$connection = new Database\Connection('mysql:host=localhost', 'root');
} catch (PDOException $e) {
	TestHelpers::skip('Requires corretly configured mysql connection and "nette_test" database.');
}

flock($lock = fopen(TEMP_DIR . '/../lock', 'w'), LOCK_EX);

Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test.sql');
