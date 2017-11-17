<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * TODO: testing
 * 
 * Implementa un gestore di traduzioni basato su file di testo.
 *
 * Un file di testo per la traduzione deve chiamarsi "xx_YY.txt", dove xx_YY indica
 * la lingua della traduzione. Ad esempio per la lingua italiana il file si chiamerà:
 * 'it_IT.txt', per l'inglese 'en_GB.txt', l'americano 'en_US.txt' e così via.
 *
 * Il formato del file è quello CSV formato da solo due stringhe:
 * "originale","traduzione"
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class I18NManager_Text extends I18NManager {
    protected
		// cache delle traduzioni
		$trans_cache;

	public function __construct() {
		$this->trans_cache = array(
			'plugins' => array(),
			'application' => array(),
			'modules' => array()
		);
	}

	public function addModuleTrans($module_name, $path) {
		$path = FileUtils::slashPath($path);
		$path .= 'i18n'.DIRECTORY_SEPARATOR;
		$files = glob( $path . "[a-z][a-z]_[A-Z][A-Z].txt");
		if ($files === FALSE) return FALSE;
		foreach($files as $file) {
			$lang = basename($file, '.txt');
			$this->add_translations($file, $lang, 'modules', $module_name);
		}
		return TRUE;
	}

	public function discardModuleTrans($module) {
		foreach($this->trans_cache['modules'] as $lk => $lang) {
			foreach($this->trans_cache['modules'][$lk] as $mk => $module) {
				if ($mk == $module) unset($this->trans_cache['modules'][$lk][$mk]);
			}
		}

	}

	public function tr($str) {
		// Prova prima col modulo corrente
		if (array_key_exists($this->getLocale(), $this->trans_cache['modules'] )) {
			$module = WebApp::getInstance()->getCurrentModule();
			if (array_key_exists($module, $this->trans_cache['modules'][$this->getLocale()] )) {
				if ( array_key_exists($str, $this->trans_cache['modules'][$this->getLocale()][$module] ) ) {
					return $this->trans_cache['modules'][$this->getLocale()][$module][$str];
				}
			}
		}
		// Prova coi plugin e applicazioni
		if (array_key_exists($this->getLocale(), $this->trans_cache['plugins'] )) {
			if (array_key_exists($str, $this->trans_cache['plugins'][$this->getLocale()] )) {
				return $this->trans_cache['plugins'][$this->getLocale()][$str];
			}
		}
		// Prova con l'applicazione
		if (array_key_exists($this->getLocale(), $this->trans_cache['application'] )) {
			if (array_key_exists($str, $this->trans_cache['plugins'][$this->getLocale()] )) {
				return $this->trans_cache['plugins'][$this->getLocale()][$str];
			}
		}
		return $str;
	}

	public function addPluginTrans($plugin_name, $path) {
		$path = FileUtils::slashPath($path);
		$path .= 'i18n'.DIRECTORY_SEPARATOR;

		$files = glob( $path . "[a-z][a-z]_[A-Z][A-Z].txt");
		if ($files === FALSE) return FALSE;
		foreach($files as $file) {
			$lang = basename($file, '.txt');
			$this->add_translations($file, $lang, 'plugins');
		}
	}

	public function addApplicationTrans($appname, $path) {
		$path = FileUtils::slashPath($path);
		$path .= 'i18n'.DIRECTORY_SEPARATOR;

		$files = glob( $path . "[a-z][a-z]_[A-Z][A-Z].txt");
		if ($files === FALSE) return FALSE;
		foreach($files as $file) {
			$lang = basename($file, '.txt');
			$this->add_translations($file, $lang, 'application');
		}

	}

	private function add_translations($file_path, $lang, $cache_name, $element_name = NULL) {
		// Legge le traduzioni dal file CSV
		$fh = @fopen($file_path, 'r');
		if ($fh === FALSE) return FALSE;
		while (!feof($fh)) {
			$trans = @fgetcsv($fh);
			if ($trans !== FALSE) {
				// Crea se necessario il repository della lingua
				if (!array_key_exists($lang, $this->trans_cache[$cache_name])) $this->trans_cache[$cache_name][$lang] = array();

				// Aggiunge le traduzioni
				if (!is_null($element_name)) {
					if (!array_key_exists($element_name, $this->trans_cache[$cache_name][$lang])) $this->trans_cache[$cache_name][$lang][$element_name] = array();
					
					$this->trans_cache[$cache_name][$lang][$element_name][$trans[0]] = $trans[1];
					
				} else {
					$this->trans_cache[$cache_name][$lang][$trans[0]] = $trans[1];
				}
			}
		}
		@fclose($fh);

		
		return TRUE;
	}

	
}
