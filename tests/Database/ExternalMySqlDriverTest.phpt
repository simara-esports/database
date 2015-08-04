<?php
/**
 * Test: Nette\Database\Connection: reflection
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


class ExternalMySqlDriverTest extends Tester\TestCase {

	/**
	 *
	 * @var \Nette\Database\Connection
	 */
	private $connection;

	private $sqls = [
"SELECT `post`.*, IFNULL(get_post_title(`post`.`id`, `post`.`rotate_title`), `post`.`name`) AS
`nameTitle`, IF(`post`.`photo_id` IS NOT NULL, (
SELECT `photo`.`url`
FROM `photo`
WHERE `photo`.`id`=`post`.`photo_id`), IF(`post`.`file_id` IS NOT NULL, (
SELECT CONCAT('/static/files/', `file`.`path`, `file`.`filename`, \".\", `file`.`extension`) AS `url`
FROM `file`
WHERE `file`.`id`=`post`.`file_id`), NULL)) AS `photoUrl`
FROM `post`
WHERE (`post`.`post_type_id` = 2) AND (`post`.`post_state_id` = 3) AND (`post`.`publish_date` <=
NOW()) AND (`post`.`unpublish_date` IS NULL OR (`post`.`unpublish_date` IS NOT NULL AND
`post`.`unpublish_date` >= NOW())) AND (`post`.`topstory` IS NOT NULL)
ORDER BY `post`.`topstory` ASC, `post`.`publish_date` DESC
LIMIT 1",

"SELECT `league`.*, `stats_league_logo`.`path` `logo_path`
FROM `post_2_stats_league`
LEFT JOIN `stats_league` AS `league` ON `post_2_stats_league`.`stats_league_id` = `league`.`id`
LEFT JOIN `stats_league_logo` ON `league`.`id` = `stats_league_logo`.`stats_league_id` AND
(`stats_league_logo`.`id` = (
SELECT `id`
FROM `stats_league_logo` `nameTable`
WHERE `nameTable`.`stats_league_id` = `stats_league_logo`.`stats_league_id`
ORDER BY IFNULL(NOW() BETWEEN `nameTable`.`from` AND `nameTable`.`to`, 0) DESC, (`nameTable`.`from`
IS NULL AND NOW() <= `nameTable`.`to`) DESC, (`nameTable`.`to` IS NULL AND NOW() >=
`nameTable`.`from`) DESC
LIMIT 1))
WHERE (`post_id` = '3')",

"SELECT `photo` FROM `stats_league`",
"SELECT `photo` FROM `post`",
"SELECT `photo` FROM `stats_league` WHERE `photo` = ''",
"SELECT `photo` FROM `stats_league` WHERE `photo` = '' ORDER BY `photo`",
"SELECT `photo` FROM `stats_league` ORDER BY `photo`",
"SELECT `photo` FROM `stats_league` WHERE `photo` = '' ORDER BY `photo` GROUP BY `photo`",
"SELECT `photo` FROM `stats_league` GROUP BY `photo`",
"SELECT `photo` FROM `stats_league` WHERE (`stats_league`.`post_type_id` = 2) AND (`photo` = '2-liga') LIMIT 1",

"SELECT `stats_competition`.*
FROM `stats_competition`
LEFT JOIN `stats_category` AS `category` ON
`stats_competition`.`stats_category_id` = `category`.`id`
LEFT JOIN `stats_league` AS `league` ON `stats_competition`.`league_id` = `league`.`id`
WHERE (`season` = 2014) AND (`league_id` = 3) AND (`category`.`type` = 'base') AND (`virtual` = 0)
ORDER BY `season` DESC, IFNULL(`league`.`rank`, 9223372036854775807), `priority` DESC LIMIT 1",

"UPDATE `photo` SET `name`='xxx' WHERE (`photo`.`id` = 1)",
"INSERT INTO `photo` VALUES (1, 2)",
"INSERT INTO `photo` (`id`, `season`) VALUES (1, 2)",
"INSERT INTO `photo` SELECT `id`, `season` FROM `photo`",

		];

	private $sqlsExternal = [
"SELECT `post`.*, IFNULL(get_post_title(`post`.`id`, `post`.`rotate_title`), `post`.`name`) AS
`nameTitle`, IF(`post`.`photo_id` IS NOT NULL, (
SELECT `ext_db`.`photo`.`url`
FROM `ext_db`.`photo`
WHERE `ext_db`.`photo`.`id`=`post`.`photo_id`), IF(`post`.`file_id` IS NOT NULL, (
SELECT CONCAT('/static/files/', `ext_db`.`file`.`path`, `ext_db`.`file`.`filename`, \".\", `ext_db`.`file`.`extension`) AS `url`
FROM `ext_db`.`file`
WHERE `ext_db`.`file`.`id`=`post`.`file_id`), NULL)) AS `photoUrl`
FROM `post`
WHERE (`post`.`post_type_id` = 2) AND (`post`.`post_state_id` = 3) AND (`post`.`publish_date` <=
NOW()) AND (`post`.`unpublish_date` IS NULL OR (`post`.`unpublish_date` IS NOT NULL AND
`post`.`unpublish_date` >= NOW())) AND (`post`.`topstory` IS NOT NULL)
ORDER BY `post`.`topstory` ASC, `post`.`publish_date` DESC
LIMIT 1",

"SELECT `league`.*, `ext_db`.`stats_league_logo`.`path` `logo_path`
FROM `post_2_stats_league`
LEFT JOIN `ext_db`.`stats_league` AS `league` ON `post_2_stats_league`.`stats_league_id` = `league`.`id`
LEFT JOIN `ext_db`.`stats_league_logo` ON `league`.`id` = `ext_db`.`stats_league_logo`.`stats_league_id` AND
(`ext_db`.`stats_league_logo`.`id` = (
SELECT `id`
FROM `ext_db`.`stats_league_logo` `nameTable`
WHERE `nameTable`.`stats_league_id` = `ext_db`.`stats_league_logo`.`stats_league_id`
ORDER BY IFNULL(NOW() BETWEEN `nameTable`.`from` AND `nameTable`.`to`, 0) DESC, (`nameTable`.`from`
IS NULL AND NOW() <= `nameTable`.`to`) DESC, (`nameTable`.`to` IS NULL AND NOW() >=
`nameTable`.`from`) DESC
LIMIT 1))
WHERE (`post_id` = '3')",

"SELECT `photo` FROM `ext_db`.`stats_league`",
"SELECT `photo` FROM `post`",
"SELECT `photo` FROM `ext_db`.`stats_league` WHERE `photo` = ''",
"SELECT `photo` FROM `ext_db`.`stats_league` WHERE `photo` = '' ORDER BY `photo`",
"SELECT `photo` FROM `ext_db`.`stats_league` ORDER BY `photo`",
"SELECT `photo` FROM `ext_db`.`stats_league` WHERE `photo` = '' ORDER BY `photo` GROUP BY `photo`",
"SELECT `photo` FROM `ext_db`.`stats_league` GROUP BY `photo`",
"SELECT `photo` FROM `ext_db`.`stats_league` WHERE (`ext_db`.`stats_league`.`post_type_id` = 2) AND (`photo` = '2-liga') LIMIT 1",

"SELECT `ext_db`.`stats_competition`.*
FROM `ext_db`.`stats_competition`
LEFT JOIN `ext_db`.`stats_category` AS `category` ON
`ext_db`.`stats_competition`.`stats_category_id` = `category`.`id`
LEFT JOIN `ext_db`.`stats_league` AS `league` ON `ext_db`.`stats_competition`.`league_id` = `league`.`id`
WHERE (`season` = 2014) AND (`league_id` = 3) AND (`category`.`type` = 'base') AND (`virtual` = 0)
ORDER BY `season` DESC, IFNULL(`league`.`rank`, 9223372036854775807), `priority` DESC LIMIT 1",

"UPDATE `ext_db`.`photo` SET `name`='xxx' WHERE (`ext_db`.`photo`.`id` = 1)",
"INSERT INTO `ext_db`.`photo` VALUES (1, 2)",
"INSERT INTO `ext_db`.`photo` (`id`, `season`) VALUES (1, 2)",
"INSERT INTO `ext_db`.`photo` SELECT `id`, `season` FROM `ext_db`.`photo`",
		];

	function __construct($connection) {
		$this->connection = $connection;
	}

	/**
	 * @return \Nette\Database\Drivers\ExternalMySqlDriver
	 */
	private function createEmptyDriver() {
		return new Nette\Database\Drivers\ExternalMySqlDriver($this->connection, []);
	}

	public function testDelimiteSqlEmpty() {
		foreach($this->sqls as $sql){
			Assert::same($sql, $this->createEmptyDriver()->delimiteExternal($sql));
		}
	}

	/**
	 * @return \Nette\Database\Drivers\ExternalMySqlDriver
	 */
	private function createDriver() {
		return new Nette\Database\Drivers\ExternalMySqlDriver($this->connection, [
			'externalTables' => [
					[
					'name' => 'ext_db',
					'tables' => [
						'photo', 'file', 'stats_league', 'stats_league_logo',
						'stats_competition', 'stats_category', 'stats_league',
						'season'
					],
				],
			],
		]);
	}

	public function testDelimiteSql() {
		foreach($this->sqls as $counter => $sql){
			Assert::same($this->sqlsExternal[$counter], $this->createDriver()->delimiteExternal($sql));
		}
	}

}

if ($driverName !== "mysql") {
	Tester\Environment::skip("This test is only for MySQL");
}

id(new ExternalMySqlDriverTest($connection))->run();
