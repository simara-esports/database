<?php

namespace Nette\Database\Drivers;

/**
 * MySqlDriver supports external tables
 *
 * @author Svaťa
 */
class ExternalMySqlDriver extends MySqlDriver {
	
	/**
	 * External tables in form "name" => ["table1", "table2", ...]
	 * @var array
	 */
	private $externalTables = [];
	
	/**
	 * Table name regexp definition
	 * @var string
	 */
	private $tableRegex = "[a-z_][a-z0-9_]*";
	
	public function __construct($connection, $options) {
		parent::__construct($connection, $options);
		if(isset($options['externalTables'])){
			$this->fillExternal($options['externalTables']);
		}
	}
	
	/**
	 * Fills external tables by configuration
	 * @param array $tables
	 */
	private function fillExternal($tables) {
		foreach($tables as $table){
			$this->externalTables[$table['name']] = $table['tables'];
		}
	}
	
	/**
	 * Prefixes external table (if possible)
	 * @param string $table
	 * @return string|NULL
	 */
	private function prefixExternal($table) {
		foreach($this->externalTables as $name => $tables){
			if(in_array($table, $tables)){
				return "`$name`.`$table`";
			}
		}
		return "`$table`";
	}
	
	/**
	 * Delimite fully qualified columns like `table`.`column`
	 * @param string $sql
	 * @return string
	 */
	private function delimiteColumns($sql) {
		return preg_replace_callback('~(^|[^.])`('.$this->tableRegex.')`\.~i', function($m) {
			return $m[1].$this->prefixExternal($m[2]).".";
		}, $sql);
	}
	
	/**
	 * Delimite table nasmes like `table`
	 * only in section given by $startKeyword and $endKeywords
	 * @param string $sql
	 * @param string $startKeyword
	 * @param array $endKeywords
	 * @return string
	 */
	private function delimiteTables($sql, $startKeyword, $endKeywords) {
		$endRegex = implode('|', $endKeywords);
		return preg_replace_callback("~$startKeyword(((?!$endRegex).)*)~s", function($m) use ($startKeyword){
			return $startKeyword . preg_replace_callback('~`('.$this->tableRegex.')`~', function($m){
				return $this->prefixExternal($m[1]);
			}, $m[1]);
		}, $sql);
	}
	
	/**
	 * Delimite external tables
	 * @param string $sql
	 * @return string
	 */
	public function delimiteExternal($sql) {
		
		$sql = $this->delimiteColumns($sql);
		
		$sql = $this->delimiteTables($sql, 'FROM', ['JOIN', 'WHERE', 'ORDER BY', 'GROUP BY']);
		$sql = $this->delimiteTables($sql, 'JOIN', ['ON', 'AS']);
		
		return $sql;
	}
}
