<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Metodi utili con i file
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class FileUtils {
	
	/**
	 * Ritorna il tipo MIME effettivo di un file, utilizzando se necessario
	 * il sistema operativo.
	 *
	 * Se non riesce a trovare un tipo MIME utilizza una chiamata di sistema
	 * all'utility "file", a meno che non sia forzata la non invocazione
	 * 
	 * @param string $filename path completo del file di cui si vuole il tipo MIME
	 * @param boolean $use_file se TRUE: nel caso l'utilizzo di funzioni PHP non dia risultati, invoca "file", FALSE non fare nulla
	 * @return string una stringa col tipo MIME, se non riesce a trovare un tipo di solito ritorna "application/octet-stream"
	*/
	public static function getMIME($filename, $use_file = TRUE) {
		if (empty($filename)) return '';
		if (@is_dir($filename)) return 'directory';
        
		// potrebbe essere una immagine
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ( in_array(MIME::getMIME($ext), MIME::getWebImageMIME() ) && @is_file($filename) ) {
			$img = getimagesize($filename);
			if ($img !== FALSE) return $img['mime'];
		}
		// Per Flash fa una verifica a mano
		$buffer = @file_get_contents($filename, FILE_BINARY, NULL, 0, 10);
		if ($buffer !== FALSE) {
			// Verifica se sia una presentazione
			if (strncmp($buffer, 'FWS', 3) == 0 || strncmp($buffer, 'CWS', 3) == 0 ) return 'application/x-shockwave-flash';
			if (strncmp($buffer, 'FLV', 3) == 0) return 'video/x-flv';
		}
		
		if (function_exists('finfo_open')) {
			$finfo = @finfo_open(FILEINFO_MIME);
			if ($finfo !== FALSE) {
				$mimetype = @finfo_file($finfo, $filename);
				@finfo_close($finfo);
				if ($mimetype !== FALSE) return substr($mimetype, 0, strcspn($mimetype, '; '));
			}
        }
		
		// Prova usando la shell
		if ($use_file) {
			$m = @exec('file -ib '.escapeshellarg($filename));
			if ($m !== FALSE) {
				return trim(substr($m, 0, strcspn($m, '; ')));
			}
		}
		
		return 'application/octet-stream';
	}
	
	/**
	 * Dato un insieme di directory in cui cercare ritorna il path
	 * completo in cui un file dal nome specificato è stato trovato
	 *
	 * @param string $filename stringa col nome di file
	 * @param array $paths array di stringhe coi path
	 * @return string FALSE se non ha trovato il file, altrimenti una stringa col percorso
	 */
	public static function searchFilePath($filename, $paths) {
		$found = FALSE;
		if (strlen($filename) > 0) {
			foreach($paths as $p) {
				if (@file_exists($p.$filename) && @is_file($p.$filename)) {
					$found = $p.$filename;
					break;
				}
			}
		}
		return $found;
	}
	
	const
		KILOBYTE = 1024,
		MEGABYTE = 1048576,
		GIGABYTE = 1073741824;
	
	/**
	 * Data la dimensione del file in byte, ritorna una stringa
	 * con la dimensione formattata usando l'unità di misura del
	 * kilobyte (NB 1kb = 1024 byte).
	 * 
	 * Postpone l'unità di misura:
	 * B per i btye
	 * Gb per i gigabyte
	 * Mb per i megabyte
	 * Kb per i kilobyte
	 *
	 *
	 * @param integer $filesize dimensione del file
	 * @return string stringa con la dimensione formattata
	*/
	public static function getPrettyFilesize($filesize) {
		if ($filesize < self::KILOBYTE) return strval($filesize).' B';
		if ($filesize >= self::GIGABYTE) return strval((int) ($filesize / self::GIGABYTE)).' Gb';
		if ($filesize >= self::MEGABYTE) return strval((int) ($filesize / self::MEGABYTE)).' Mb';
		if ($filesize >= self::KILOBYTE) return strval((int)($filesize / self::KILOBYTE)).' Kb';
	}
	
	/**
	 * Dato un nome in ingresso lo rende quanto più possibile "neutro",
	 * riducendo tutti i caratteri in minuscolo, togliendo le accentate,
	 * rimuovendo gli spazi multipli e sostituendoli con '_'.
	 * 
	 * @param string $filename nome del file da ripulire
	 * @return string stringa con il nome ripulito.
	*/
	public static function normalizeName($filename) {
	
		/* Evita un bug di php 4.3.11 su IE, trasforma un path DOS in uno unix */
		$filename = strtr($filename, '\\', '/');
		$filename = basename($filename);
	   
		return preg_replace('/[^\w\d\.\-]/', '', strtolower(strtr(preg_replace('/(\s)(\s+)?/', '_', trim($filename)), array('%20' => '_'))));
	}
	
	/**
	 * Verifica se un percorso contenga lo slash finale
	 * e nel caso lo aggiunge
	 *
	 * @param string $path path da verificare
	 * @return string la stringa del path completata dallo slash
	*/
	public static function slashPath($path) {
		$path = trim($path);
		if (empty($path)) return DIRECTORY_SEPARATOR;
		if ($path[ strlen($path) - 1 ] != '/' && $path[ strlen($path) - 1 ] != '\\' ) $path .= DIRECTORY_SEPARATOR;
		return $path;
	}
	
	/**
	 * Crea un file unico in una directory.
	 * Il file viene allocato, vuoto.
	 * 
	 * Il nome viene generato postponendo un numero al nome originale:
	 * mio_file.ext -> mio_file_1.ext
	 * mio_file.ext -> mio_file_2.ext
	 * 
	 * Ritorna il nome del file (senza path).
	 * 
	 * @param string $path directory in cui creare il file
	 * @param string $filename nome del file da creare
	 * @param integer $perms permessi di accesso con cui creare il file
	 * @param integer $retry_limit quante volte deve ritentare la creazione prima di abbandonare
	 * @return string la stringa col nome del file oppure FALSE se non è stato possibile operare
	*/
	public static function allocateUniqueFile($path, $filename, $perms = 0755, $retry_limit = 100) {
		$path = self::slashPath($path);
		if (!file_exists($path) || !is_dir($path)) return FALSE;
		
		// Crea un file temporaneo
		$tempfile = tempnam($path, 'FOO');
		if ($tempfile === FALSE) return FALSE;
		if (@chmod($tempfile, $perms) == FALSE) {
			@unlink($tempfile);
			return FALSE;
		}
		
		// Prova prima ad allocare direttamente il file
		if (!file_exists($path.$filename)) {
			if (@rename($tempfile, $path.$filename) == TRUE) {
				@chmod($tempfile, $perms);
				return $filename;
			} 
		}
		// Il file esiste, prova e riprova, ma a tutto c'è un limite... ;)
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if (!empty($ext)) $ext = '.'.$ext;
		$name = basename($filename, $ext);
		
		for($n = 0; $n < $retry_limit; $n++) {
			if (!file_exists($path.$name.'_'.strval($n).$ext)) {
				if (@rename($tempfile, $path.$name.'_'.strval($n).$ext) == TRUE) {
					@chmod($tempfile, $perms);
					return $name.'_'.strval($n).$ext;
				}
			}
		}
		
		// pietà di me, ho fallito...
		return FALSE;
	}
	
	/**
	 * Dato un nome di file, lo scompone in nome ed estensione
	 * Ritorna un array composto da nome ed estensione
	 * @return array array con nome ed estensione
	*/
	public static function splitFilename($filename) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if (!empty($ext)) $ext = '.'.$ext;
		$name = basename($filename, $ext);
		return array($name, $ext);	
	}
	
	/**
	 * Verifica che il path passato esita e sia un file
	 * @param string $path path da verificare
	 * @return boolean TRUE se è un file, FALSE altrmenti
	*/
	public static function isFile($path) {
		return (@file_exists($path) && @is_file($path));
	}

	/**
	 * Legge un file nel formato ini
	 *
	 * Utilizza il formato definito qui {@link http://en.wikipedia.org/wiki/INI_file}
	 * @param string $filename stringa col nome del file da leggere
	 * @param string $readsections se TRUE ritorna un array multidimensionale con le varie sotto sezioni
	 * @return array un array associativo con i valori, FALSE se non riesce a leggere il file
	 **/
	public static function read_ini_file($filename, $readsections = FALSE) {
		$ini = @file($filename);
		if ($ini === FALSE) return FALSE;
		$out = array();
		$section = '';
		foreach($ini as $i) {
			$m = array();
			if (preg_match('/^\s*;/', $i) != 0) {
				continue;
			} elseif (preg_match('/^\[(\w*)\]/', $i, $m) != 0) {
				if ($readsections) {
					$out[$m[1]] = array();
					$section = $m[1];
				} else {
					continue;
				}
			} elseif(preg_match('/^(\w+)\s*=(.*)/', $i, $m)) {
				if ($readsections) {
					$out[$section][$m[1]] = trim($m[2]);
				} else {
					$out[$m[1]] = trim($m[2]);
				}
			}
		}
		return $out;
	}
	
}
