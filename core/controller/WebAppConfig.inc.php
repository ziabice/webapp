<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * A global configuration registry.
 * 
 * Keeps the global application configuration:
 * - Application Database Connections
 * - Application Config Parameters
 * - Language Settings
 * 
 * Maintains an association between a label and a configuration value.
 * 
 * It's a singleton, but all the method are static so it can be used
 * without formal initialization (which is done automatically by 
 * getInstance method).
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class WebAppConfig {
	
	protected static
		$config_instance = NULL;

	protected
		/**
		 * @var HashMap the database connection pool
		 * */
		$database_pool,
		
		/**
		 * @var string the default database connection name
		 * */
		$default_database,
		
		/**
		 * @var HashMap the configuration parameters hashmap
		 * */
		$params,
		
		/**
		 * @var array enabled plugins list
		 * */
		$plugin_modules;

	/**
	 * Initialize the object
	 * */
	protected function __construct() {
		$this->database_pool = new HashMap();
		$this->default_database = '';
		$this->params = new HashMap();
		$this->plugin_modules = array();
	}
	
	/**
	 * Returns the singleton
	 * 
	 * @return WebAppConfig the config singleton
	 * */
	public static function getInstance() {
		if (!is_object(WebAppConfig::$config_instance)) {
			WebAppConfig::$config_instance = new WebAppConfig();
			
			WebAppConfig::$config_instance->setDefaultLanguage( 'en_GB' );
			WebAppConfig::$config_instance->setAvailableLanguages( array( 'en_GB' => 'English' ) );
		}
		
		return WebAppConfig::$config_instance;
	}
	
	// ------------------------- Internationalization
	
	/**
	 * Sets the application default language.
	 * 
	 * You must use an ISO string, ie:
	 * it_IT,
	 * en_GB,
	 * en_US
	 * 
	 * Alternatively you can set the 'WEBAPP_DEFAULT_LANGUAGE' key value.
	 * 
	 * @param string $lang language string
	*/
	public static function setDefaultLanguage($lang) {
		WebAppConfig::getInstance()->params->set('WEBAPP_DEFAULT_LANGUAGE', $lang);
	}
	
	/**
	 * Gets applicaiton default language.
	 * 
	 * Returns an ISO string in the form xx_YY, ie:
	 * it_IT,
	 * en_GB,
	 * en_US
	 * 
	 * Alternatively you can get the value of the configuration key named 'WEBAPP_DEFAULT_LANGUAGE'
	 * 
	 * @return string the language string
	*/
	public static function getDefaultLanguage() {
		return WebAppConfig::getInstance()->params->getValue('WEBAPP_DEFAULT_LANGUAGE');
	}
	
	/**
	 * Gets the list of available site languages.
	 * 
	 * Returns an associative array:
	 * 
	 * array(
	 * 	'xx_YY' => 'Language string',
	 * 	...
	 * )
	 *  
	 * Where xx_YY is the ISO language string, and 'Language String' is the name 
	 * of the language: this string should be translated when presented to the 
	 * frontend (if necessary, of course).
	 * 
	 * Internally it gets the value of the 'WEBAPP_AVAILABLE_LANGUAGES' configuration key.
	 * 
	 * @return array associative array with available languages
	 **/
	public static function getAvailableLanguages() {
		return WebAppConfig::getInstance()->params->getValue('WEBAPP_AVAILABLE_LANGUAGES');
	}
	
	/**
	 * Sets the site available languages list.
	 * 
	 * The parameter is an associative array:
	 * 
	 * array(
	 * 	'xx_YY' => 'Language string',
	 * 	...
	 * )
	 *  
	 * Where xx_YY is the ISO language string, and 'Language String' is the name 
	 * of the language: this string should be translated when presented to the 
	 * frontend (if necessary, of course).
	 * 
	 * Internally it sets the value of the 'WEBAPP_AVAILABLE_LANGUAGES' configuration key.
	 * 
	 * @param array $languages associative array with available languages
	 */
	public static function setAvailableLanguages($languages) {
		WebAppConfig::getInstance()->params->set('WEBAPP_AVAILABLE_LANGUAGES', $languages);
	}

	// -------------------------- Database configuration
	/**
	 * Adds a database connection to the application.
	 * 
	 * Every connection is uniquely labeled. You can set a default connection,
	 * that will be returned by {@link WebAppConfig::getDefaultDatabase}.
	 * 
	 * @param string $name connection name
	 * @param string $dsn the connection DSN string
	 * @param boolean $is_default TRUE if it's the default connection, FALSE otherwise
	*/
	public static function addDatabase($name, $dsn, $is_default = FALSE) {
		WebAppConfig::getInstance()->set($name, $dsn);
		if ($is_default) WebAppConfig::getInstance()->setDefaultDatabase($name);
	}
	
	/**
	 * Sets the default database connection name
	 * 
	 * @param string $name the connection name
	 * */
	public static function setDefaultDatabase($name) {
		WebAppConfig::getInstance()->default_database = strval($name);
	}
	
	/**
	 * Tells if we have set up a default database connection
	 * 
	 * @return boolean TRUE if we have a default database connection, FALSE otherwise
	*/
	public static function hasDefaultDatabase() {
		return !empty(WebAppConfig::getInstance()->default_database);
	}
	
	/**
	 * Gets the database DSN connection string.
	 * 
	 * Returns FALSE if the wanted connection doesn't exists.
	 * 
	 * @param string $name connection name
	 * @return string|boolean the wanted DSN string or FALSE if not exists
	*/
	public static function getDatabase($name) {
		if (WebAppConfig::getInstance()->database_pool->hasKey($name)) return self::$database_pool->get($name);
		return FALSE;
	}
	
	/**
	 * Returns the default database connection name.
	 * 
	 * It may returns an empty string if the default conenction name is not set.
	 * 
	 * @return string the default database connection name
	*/
	public static function getDefaultDatabase() {
		return WebAppConfig::getInstance()->default_database;
	}
	
	// --------------------------- Configuration parameters
	/**
	 * Get the configuration parameters hash map
	 *
	 * @return HashMap the hash map
	*/
	public static function getParams() {
		return WebAppConfig::getInstance()->params;
	}
	
	/**
	 * Set a configuration parameter.
	 * 
	 * @param string $param parameter name
	 * @param mixed $value value
	*/
	public static function set($param, $value) {
		WebAppConfig::getInstance()->params->set($param, $value);
	}
	
	/**
	 * Get a configuration parameter value.
	 * 
	 * If the configuration key doesn't exists return NULL. To ensure
	 * that the configuration key really exists use 
	 * the {@link WebAppConfig:: has} method.
	 * 
	 * @param string $param parameter name
	 * @return mixed the parameter associated value
	*/
	public static function get($param) {
		return WebAppConfig::getInstance()->params->getValue($param);
	}
	
	/**
	 * Finds whether the given configuration parameter key is defined.
	 * 
	 * @param string $param the parameter name
	 * @return boolean TRUE if the parameter exists, FALSE otherwise
	 * */
	public static function has($param) {
		return WebAppConfig::getInstance()->params->hasKey($param);
	}
	
	// --------------------------- Application Plugin Management
	/**
	 * Enable a module provided by a plugin.
	 * 
	 * When enabled, a module exposes his actions to the fronted so 
	 * you can execute them.
	 * 
	 * @param string|array $module string or array of string with plugin names
	*/
	public static function enablePluginModule($plugin_name) {
		WebAppConfig::getInstance()->plugin_modules = array_merge(WebAppConfig::getInstance()->plugin_module, is_array($plugin_name) ? $plugin_name : array($plugin_name) );
	}
	
	/**
	 * Finds whether a module provided by a plugin is enabled
	 * 
	 * @return boolean TRUE if the module is enabled, FALSE otherwise
	*/
	public static function pluginModuleIsEnabled($plugin_name) {
		return in_array( strval($plugin_name), WebAppConfig::getInstance()->plugin_modules );
	}
}
