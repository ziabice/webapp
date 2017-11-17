<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/*
 * Fuzioni di utilità per il front controller.
 * */

/**
 * Ritorna la traduzione per una stringa
 *
 * @param string $txt stringa per la quale si vuole la traduzione
 * @return string una stringa con la traduzione, se non disponibile ritorna $txt
*/
function tr($txt) {
	return WebApp::getInstance()->getI18N()->tr($txt);
}

/**
 * Aggiunge un messaggio del tipo specificato al registro delle azioni.
 *
 * @param string $message
 * @param integer $level
 */
function log_message($message, $level) {
	Logger::getInstance()->log($message, $level);
}
 
