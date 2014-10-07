<?php

namespace Nette\Database\Drivers;

/**
 * MySqlDriver supports external tables
 *
 * @author Svaťa
 */
class ExternalMySqlDriver extends MySqlDriver {
	
	private $externalTables = [];
	
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
	 * Finds DB name that should be prefix for table name
	 * @param string $table
	 * @return string|NULL
	 */
	private function getExternalPrefix($table) {
		foreach($this->externalTables as $name => $tables){
			if(in_array($table, $tables)){
				return "`$name`.";
			}
		}
		return NULL;
	}
	
	/**
	 * Detects if select contains only simple columns
	 * @param string $s
	 * @return bool
	 */
	private function isSimpleSelect($s) {
		return preg_match('~^SELECT[^.]*FROM~', $s);
	}
	
	/**
	 * Delimite one string
	 * @param string $s
	 * @return string
	 */
	private function delimiteString($s) {
		return preg_replace_callback('~(^|[^.])`([a-z_][a-z0-9_]*)`~i', function($m) {
			return $m[1].$this->getExternalPrefix($m[2])."`$m[2]`";
		}, $s);
	}
	
	/**
	 * Delimite only one table in FROM clausule
	 * @param type $s
	 * @return type
	 */
	private function delimiteSimple($s) {
		preg_match('~FROM `([^`]+)`~', $s, $m);
		return str_replace("`$m[1]`", $this->getExternalPrefix($m[1])."`$m[1]`", $s);
	}
	
	public function delimiteExternal($s) {
		if($s === null){
			return null;
		}
		
		if($this->isSimpleSelect($s)){
			return $this->delimiteSimple($s);
		}
		
		return $this->delimiteString($s);
	}
}
