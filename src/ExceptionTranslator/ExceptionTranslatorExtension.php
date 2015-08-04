<?php

namespace Esports\Database;

use Nette;

/**
 * Rozsireni pro ExceptionTranslator
 */
class ExceptionTranslatorExtension extends Nette\DI\CompilerExtension {

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('database.default');
		$engine->addSetup('Esports\Database\ExceptionTranslator::connect(?)', array('@Nette\Database\Connection'));
	}
}
