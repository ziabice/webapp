<?php

/**
 * (c) 2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Funzioni di utilità varie.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class WebAppUtils {
	
	/**
	 * Crea una stringa casuale della lunghezza indicata.
	 * 
	 * La stringa contiene solo numeri e lettere (maiuscole e minuscole e solo ASCII),
	 * senza ripetizioni tra lettere consecutive.
	 * 
	 * @return string una stringa casuale
	 */
	public static function makeRandomHash($max_length = 32) {
		if ($max_length < 1) return FALSE;
		$letters = array_map('chr', array_merge(range(48, 57), range(65, 90), range(97, 122)));
		$max_letters = count($letters) - 1;
		$hash = $letters[ mt_rand(0, $max_letters) ];
		$idx = 1;
		while(strlen($hash) < $max_length) {
			$chr = $letters[ mt_rand(0, $max_letters) ];
			if ($hash[$idx - 1] != $chr) {
				$idx++;
				$hash .= $chr;
			}
		}
		return $hash;
	}
}

