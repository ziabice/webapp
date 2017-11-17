<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

// Versione
define('WEBAPP_CORE_VERSION', '1.3.9'); 

/*
  Definizioni base per i percorsi, basta aver definito la costante WEBAPP_BASE_PATH,
  di solito in config.inc.php, che indica il percorso base di tutta l'applicazione,
  ricordarsi di fare terminare il path definito in WEBAPP_BASE_PATH con un separatore di path
*/

if (defined('WEBAPP_BASE_PATH')) {
	// Percorso base del framework
	if (!defined('WEBAPP_CORE_PATH')) define('WEBAPP_CORE_PATH', WEBAPP_BASE_PATH.'core'.DIRECTORY_SEPARATOR);

	// Percorso base delle applicazioni
	if (!defined('WEBAPP_APPS_PATH')) define('WEBAPP_APPS_PATH', WEBAPP_BASE_PATH.'apps'.DIRECTORY_SEPARATOR);


	// Percorso base delle librerie e del model
	if (!defined('WEBAPP_LIB_PATH')) define('WEBAPP_LIB_PATH', WEBAPP_BASE_PATH.'lib'.DIRECTORY_SEPARATOR);
}


