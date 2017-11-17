<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * La classe che gestisce l'inclusione dei file nell'ambiente
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class WebAppLoader {

	protected static
		$is_initialized = FALSE,
		$plugins = array(), // I plugin trovati nell'applicazione
		$path_cache = array(); // La cache coi percorsi

	/**
	 * Ritorna la cache dei percorsi per la ricerca delle classi: un array di stringhe
	 * coi path completi
	 * @return array un array di stringhe
	*/
	public static function getPathCache() {
		return self::$path_cache;
	}

	/**
	 * Dato un nome di file 'nomefile.inc.php' o 'nomefile.php' lo cerca nelle
	 * directory relative alla librerie dell'applicazione e lo include.
	 * 
	 * L'inclusione avviene usando require_once
	 * 
	 * @param string $lib_file stringa col nome del file da includere
	 * @return boolean TRUE se è tutto ok, FALSE altrimenti
	*/
	public static function requireFile($lib_file) {
		foreach( self::$path_cache as $path ) {
			if (file_exists($path.$lib_file)) {
				require_once $path.$lib_file;
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Aggiunge un percorso o un array di percorsi alla lista di percorsi
	 * in cui cercare i file sorgente.
	 *
	 * Aggiunge in coda.
	 *
	 * @param string|array $path percorso da aggiungere
	 */
	public static function appendPath($path) {
		if (is_string($path)) self::$path_cache[] = $path;
		elseif (is_array($path)) self::$path_cache = array_merge(self::$path_cache, $path);
	}

	/*
	 * Processa ricorsivamente una directory ritornando un array
	 * associativo composto dalle directory che trova.
	 *
	 * @param string $path percorso in cui cercare
	 * @return array array di stringhe coi percorsi trovati
	 */
	private static function scandirectory($path) {
		if (file_exists($path) && is_dir($path) ) {
			$out = array();
			if (($dh = @opendir($path)) !== FALSE) {
				$out[] =  $path ;
				while ( ($fn = readdir($dh)) !== FALSE ) {
					if ($fn[0] != '.') {
						$newpath = $path.$fn;
						if ( is_dir($newpath) ) {
							$out = array_merge($out, self::scandirectory($newpath.DIRECTORY_SEPARATOR));
						}
					}
	
				}
				closedir($dh);
			}
			return $out;
		} else {
			return array();
		}

	}

	/**
	 * Inizializza i path di inclusione dei sorgenti dell'applicazione, quelli dei
	 * plugin e quelli per il gestore di traduzioni.
	 *
	 * Per costruire i path usa come base le costanti WEBAPP_LIB_PATH, WEBAPP_CORE_PATH
	 *
	*/
	public static function initializePaths() {
		self::$path_cache = array();

		// trova i plugin
		self::searchPlugins();

		self::$path_cache = array_merge(
			self::scandirectory(WEBAPP_LIB_PATH),
			
			self::$plugins,
			
			self::scandirectory(WEBAPP_CORE_PATH) );

		WebAppLoader::$is_initialized = TRUE;
	}

	/**
	 * Compone un elenco dei plugin installati
	 */
	protected static function searchPlugins() {
		self::$plugins = array();
		
		$p_path = array();
		
		$basepath = self::getPluginsPath();
		if (($dh = @opendir($basepath)) !== FALSE) {
			while ( ($fn = readdir($dh)) !== FALSE ) {
				if ($fn[0] != '.') {
					if (is_dir($basepath.$fn)) {
						self::$plugins = array_merge(self::$plugins, self::scandirectory( self::getPluginsPath().$fn.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR ));
					}
				}
			}
			closedir($dh);
		}
	}

	/**
	 * Ritorna il percorso principale in cui cercare i plugin
	 *
	 * Il percorso ha come radice WEBAPP_BASE_PATH che deve essere definita
	 * prima di utilizzare il metodo
	 *
	 * Il percorso termina con il separatore di directory specifico della piattaforma in uso.
	 *
	 * @return string stringa col percorso.
	*/
	public static function getPluginsPath() {
		return WEBAPP_BASE_PATH.'plugins'.DIRECTORY_SEPARATOR;
	}

	/**
	 * Informa se la classe sia inizializzata correttamente.
	 * 
	 * @return boolean TRUE se i percorsi sono inzializzati, FALSE altrmenti
	 */
	public static function isInitialized() {
		return self::$is_initialized;
	}

	/**
	 * Appende ricorsivamente i parcorsi di una directory
	 * @param string $path percorsi da aggiungere
	 * @param boolean $insert TRUE aggiunge in testa, FALSE appende in coda
	 */
	public static function addDirectory($path, $insert = FALSE) {
		if ($insert) {
			self::$path_cache = array_merge(self::scandirectory($path), self::$path_cache);
		} else {
			self::$path_cache = array_merge(self::$path_cache, self::scandirectory($path));
		}
	}

	/**
	 * Esegue l'applicazione indicata.
	 *
	 * Cerca se esiste di caricare il front controller specificato dall'applicazione: deve
	 * chiamarsi WebApp e dericare da WebAppFrontController. Cerca prima nella
	 * sottodirectory lib/ dell'applicazione e poi nei path.
	 * 
	 * É possibile non lanciare l'applicazione, ma solo creare l'ambiente adatto 
	 * alla sua esecuzione passando TRUE come paramentro $dont_execute.
	 * In questo modo non verrà cercata l'azione da eseguire e non verrà composta
	 * una risposta.
	 *
	 * @param string $application_name nome dell'applicazione da lanciare
	 * @param boolean $dont_execute TRUE non lancia l'azione dalla URL e la risposta, FALSE esegue normalmente
	 */
	public static function launchApplication($application_name, $dont_execute = FALSE) {
		self::initialize();
		self::addDirectory(WEBAPP_APPS_PATH.strval($application_name).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR, TRUE);
		WebApp::getInstance()->run($application_name, $dont_execute);
	}
	
	/**
	 * Registra la callback per il caricamento automatico delle classi. 
	 */
	public static function registerAutoloader() {
		if (!self::isInitialized()) {
			spl_autoload_register( array('WebAppLoader', 'loadClass') );
		}
	}
	
	/**
	 * Metodo usato dall'autoloader per caricare una classe.
	 * 
	 * @param string $class_name il nome della classe da caricare
	 */
	public static function loadClass($class_name) {
		self::initialize();
		self::requireFile($class_name.'.inc.php');
	}
	
	/**
	 * Inizializza l'oggetto.
	 * 
	 * Registra la callback per l'autoloader e inizializza i percorsi di ricerca.
	 * 
	 * Se l'oggetto è già inizializzato non opera.
	 */
	public static function initialize() {
		if (!self::isInitialized()) {
			self::registerAutoloader();
			self::initializePaths();
		}
	}
}

