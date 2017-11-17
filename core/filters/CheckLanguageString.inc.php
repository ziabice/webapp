<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che una stringa indichi una lingua valida: ossia una lingua
 * tra quelle disponibili per l'applicazione corrente.
 * La stringa in ingresso deve essere nella forma xx_YY, es: it_IT
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 **/
class CheckLanguageString extends InputFilter {

	protected function checkValue($value) {
		return in_array(strval($value), array_keys(WebAppConfig::getAvailableLanguages() ));
	}
}
