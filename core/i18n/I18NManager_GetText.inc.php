<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * TODO: testing
 *
 * Gestisce le traduzioni usando GNU gettext.
 *
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class I18NManager_GetText extends I18NManager {

	protected
		$codeset,
		$domains, $module_domain;

	/**
	 * Permette di specificare un codeset da utilizzare: il default 'UTF8'
	 * permette di gestire le traduzioni in questa codifica.
	 *
	 * @param string $codeset codeset da usare per il locale
	 */
	public function __construct($codeset = 'UTF-8') {
		$this->domains = array();
		$this->module_domain = '';
		$this->codeset = $codeset;
		parent::__construct();
	}

	public function setLocale($locale) {
		parent::setLocale($locale);
		setlocale(LC_MESSAGES, $locale.(empty($this->codeset) ? '' : '.'.$this->codeset));
	}

	public function tr($str) {
		$module = WebApp::getInstance()->getCurrentModule();
		$s = dgettext($module, $str);
		// Le stringhe sono uguali, prova con gli domain
		if (strcmp($s, $str) == 0) {
			// verifica i plugin
			foreach($this->domains as $d) {
				$s = dgettext($d, $str);
				if (strcmp($s, $str) != 0) return $s;
			}
			// verifica sull'applicazione
			$s = dgettext('application', $str);
			if (strcmp($s, $str) != 0) return $s;
		}
		return $str;
	}


	public function addApplicationTrans($appname, $path) {
		$path = FileUtils::slashPath($path);
		$path .= 'i18n'.DIRECTORY_SEPARATOR;
		bind_textdomain_codeset('application', $this->codeset);
		bindtextdomain('application', $path);
	}


	public function addPluginTrans($plugin_name, $path) {
		$path = FileUtils::slashPath($path);
		$path .= 'i18n'.DIRECTORY_SEPARATOR;

		bind_textdomain_codeset('plugin_'.$plugin_name, $this->codeset);
		bindtextdomain('plugin_'.$plugin_name, $path);
		$this->domains[] = 'plugin_'.$plugin_name;
	}


	public function addModuleTrans($module_name, $path) {
		$path = FileUtils::slashPath($path);
		$path .= 'i18n'.DIRECTORY_SEPARATOR;
		
		bind_textdomain_codeset($module_name, $this->codeset);
		bindtextdomain($module_name, $path);
		$this->module_domain = $module_name;
	}
	
}

