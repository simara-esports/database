<?php

namespace Esports\Database;

use Esports\ModelException;
use Esports\PrimaryKeyException;
use Esports\ConstraintViolationException;
use Nette\Database\Connection;
use Nette\Object;
use PDOException;

/**
 * Description of DatabaseExceptionTranslator
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
			$code = $e->getCode();
			$errorCode = $this->getErrorCode($e->errorInfo);

			if ($code == '23000' && $errorCode == 1062) {
				throw new PrimaryKeyException($e->getMessage(), null, $e);
			} else if ($code == '23000' && $errorCode == 1451) {
				throw new ConstraintViolationException($e->getMessage(), null, $e);
			}

			throw new ModelException($e->getMessage(), null, $e);
		}
	}

	/**
	 * Chybovy kod databazove serveru
	 * @param array
	 * @return int|null
	 */
	protected function getErrorCode($errorInfo) {
		if (!isset($errorInfo[1])) {
			return null;
		}

		return $errorInfo[1];
	}

}
