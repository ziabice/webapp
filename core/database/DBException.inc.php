<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Eccezione generica generata dall'interazione col database
*/
class DBException extends Exception {

	public function __construct ($message, $code = 0) {
		parent::__construct($message, $code);
		Logger::getInstance()->debug("DBException raised CODE: ".strval($code)." MESSAGE: ".$message);
	}
}
