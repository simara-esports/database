<?php

namespace Esports\Database;

use Esports\ModelException;
use Esports\PrimaryKeyException;
use Nette\Database\Connection;
use Nette\Object;
use PDOException;

/**
 * Description of DatabaseExceptionTranslator
 *
 * @author Svaťa
 */
class ExceptionTranslator extends Object {

    /**
     * Pripoji ke connection tento translator
     * @param Connection $connection
     */
    public static function connect(Connection $connection) {
		$self = new static;
        $connection->onQuery[] = $self->translate;
    }

    /**
     * Prelozi vyjimku
     * @param Connection $connection
     * @param \Exception $e
     * @throws ModelException
     */
    public function translate($connection, $e) {
        if ($e instanceof PDOException) {
			if ($e->getCode() == '23000' && isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
				throw new PrimaryKeyException($e->getMessage(), null, $e);
			}
			throw new ModelException($e->getMessage(), null, $e);
		}
    }
}
