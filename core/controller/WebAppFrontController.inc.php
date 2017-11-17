<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Front Controller per una applicazione.
 * Gestisce tutto il ciclo di vita dell'applicazione.
 * E' un singleton.
 *
 * Per funzionare correttamente è necessario che siano definite le seguenti costanti,
 * che indicano tutte path completi:
 * WEBAPP_BASE_PATH - directory base che contiene tutto il progetto (dove trovare file di configurazione ed i plugin)
 * WEBAPP_CORE_PATH - indica dove sia possibile trovare le classi core
 * WEBAPP_APPS_PATH - indica dove cercare le singole applicazioni
 * WEBAPP_LIB_PATH - percorso base delle librerie
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class WebAppFrontController {

	protected static
		$webapp_singleton = NULL; // Contiene il singleton dell'applicazione

	protected
		$webapp_view = NULL, // Istanza di oggetto che implementa WebAppViewInterface per gestire il View
		$plugins = array(), // elenco dei plugin presenti
		$appname = '', // Nome dell'applicazione
		$router = NULL, // Istanza di WebAppRouter
		$action = NULL, // Istanza di WebAppAction richiesta dall'utente
		$response = NULL, // Istanza di Response
		$request = NULL, // Istanza di Request
		$user = NULL, // Istanza di User
		$auth = NULL, // Istanza di Auth
		$session_manager = NULL, // Istanza di SessionManager
		$database_pool, // array con le connessioni aperte al DB
		$current_destination = NULL, // URL con la destinazione corrente 
		$current_module = '', // stringa col modulo richiesto attualemtne
		$current_action = '', // stringa con l'azione richiesta attualmente
		$app_params = NULL, // HashMap coi parametri dell'applicazione
		$language_manager = NULL, // BaseLanguageManager di gestione della lingua
		$current_running_action = NULL; // array con le informazioni relative all'azione eseguita (vedi loadAction)
	
	/**
	 * Costruisce il Front Controller.
	 * Al momento della costruzione inizializza solo la cache dei percorsi
	 * per l'autoinclusione delle classi
	 * @param $appname stringa col nome dell'applicazione
	*/
	protected function __construct() {
		require_once WEBAPP_CORE_PATH.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'WebAppFrontControllerHelper.inc.php';
	}

	public function  __destruct() {
		Logger::getInstance()->info("--- WebApp shutdown ---");
		Logger::getInstance()->shutdown();
	}

	/**
	 * Inizializza l'oggetto preparandolo all'esecuzione di una azione.
	 * Viene eseguito da {@link run}.
	 * 
	 * @see initializeWebApp
	 * @see bootstrap
	*/
	protected function initialize() {
		
		if ($this->loadConfig() == FALSE) {
			throw new Exception("WebApp: failed to load configuration files in ".$this->getConfigFilePath().".");
		}
		
		$this->initializeWebApp();
		
		$this->bootstrap();
	}
	
	/**
	 * Informa se l'applicazione sia eseguita da riga di comando.
	 * 
	 * @return boolean TRUE l'applicazione è eseguita da riga di comando, FALSE altrimenti
	 */
	public function isCLI() {
		return (strncasecmp(PHP_SAPI, 'cli', 3) == 0);
	}
	
	/**
	 * Inizializza l'ambiente per l'esecuzione di azioni.
	 * 
	 * Viene eseguita da {@link initialize} subito dopo aver letto la configurazione.
	 * 
	 * 
	 */
	protected function initializeWebApp() {
		/*
		Tutto ciò che è qui ha già bisogno dell'autoload delle classi
		*/
		$this->initializeLogger();
		$this->initializeRequest();
		$this->initializeResponse();
		$this->initializeDatabase();
		$this->initializeSecurityManager();
		$this->loadSessionHooks();
		$this->initializeSessionManager();
		$this->initializeLanguageManager();
		
		// Dopo aver inizializzato la sessione
		// inizializza il gestore di autenticazione
		// che può ricavare informazioni da essa
		$this->auth->initialize();
		$this->language_manager->initialize();
		
		$this->initializeRouting();
		
		$this->initializeView();
	}

	/**
	 * Eseguita dal processo di inizializzazione quando l'ambiente di esecuzione
	 * dell'applicazione è pronto.
	 *
	 * Viene eseguita da {@link initialize} quando sono stati inizializzati tutti
	 * i sottosistemi.
	 *
	 * @see initialize
	 */
	protected function bootstrap() {

	}

	/**
	 * Inizializza il gestore di registro azioni.
	 */
	protected function initializeLogger() {
		Logger::getInstance()->info("--- WebApp startup ---");
	}

	/**
	 * Crea il singleton {@link Request} con i parametri corretti
	*/
	protected function initializeRequest() {
		$this->request = Request::getInstance();
	}

	/**
	 * Inizializza il gestore della sicurezza. 
	 * Viene inizializzata la classe chiamata "Auth" sottoclasse di WebAppAuth,
	 * cercata nei path standard di ricerca.
	 *
	*/
	protected function initializeSecurityManager() {
		$this->auth = new Auth();
	}


	/**
	 * Ritorna il singleton con l'istanza di WebAppFrontController corrente.
	 * 
	 * Se l'istanza non c'è la crea, inizializzando la classe chiamata "WebApp".
	 * Viene perciò fornita una classe predefinita.
	 * 
	 * @return WebAppFrontController il front controller dell'applicazione
	*/
	public static function getInstance() {
		if (!is_object(self::$webapp_singleton)) {
			self::$webapp_singleton = new WebApp();
		}
		return self::$webapp_singleton;
	}

	/**
	 * Ritorna il nome dell'applicazione
	 * @return string una stringa col nome
	*/
	public function getName() {
		return $this->appname;
	}

	/**
	 * Ritorna il gestore dell'autenticazione/autorizzazione degli utenti
	 * 
	 * @return WebAppAuth
	*/
	public function getAuth() {
		return $this->auth;
	}

	/**
	 * Ritorna il percorso della directory di questa applicazione: ossia il percorso
	 * in cui verranno cercati i file che afferiscono all'applicazione corrente.
	 * 
	 * Il percorso ha come radice la costante WEBAPP_APPS_PATH che va definita prima
	 * di invocare il metodo.
	 * 
	 * Il percorso termina con il separatore di directory specifico della piattaforma in uso.
	 * 
	 * @return string una stringa col percorso
	*/
	public function getPath() {
		return WEBAPP_APPS_PATH.$this->appname.DIRECTORY_SEPARATOR;
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
	public function getPluginsPath() {
		return WEBAPP_BASE_PATH.'plugins'.DIRECTORY_SEPARATOR;
	}

	/**
	 * Include i file con la configurazione dell'applicazione.
	 * Il file si deve chiamare config.inc.php e deve essere posto sotto
	 * la directory config/, cercata sia sotto il path indicato dalla costante
	 * WEBAPP_BASE_PATH (che deve essere definita)
	 * che in quello dell'applicazione corrente (ricavato con {@link getPath}
	*/
	protected function loadConfig() {
		$files = array(
			WEBAPP_BASE_PATH.'config'.DIRECTORY_SEPARATOR.'config.inc.php',
			$this->getPath().'config'.DIRECTORY_SEPARATOR.'config.inc.php'
		);
		
		foreach($files as $f) {
			if ( @file_exists($f) ) {
				include $f;
			}
		}
		return TRUE;
	}
	
	/**
	 * Ritorna il percorso della directory dei dati, quella in cui vengono
	 * conservate le informazioni relative all'SQL o altri dati importati.
	 *
	 * Per ricavare il path utilizza come base il valore della costante WEBAPP_BASE_PATH
	 *
	 * In pratica la directory 'data/' sotto il path base.
	 *
	 * @return string una stringa col path
	*/
	public function getDataDirPath() {
		return WEBAPP_BASE_PATH.'data'.DIRECTORY_SEPARATOR;
	}

	/**
	 * Inizializza l'oggetto il routing.
	 * La classe deve chiamarsi Router ed essere una sottoclasse di WebAppRouter.
	 * Viene cercata nei path standard.
	 *
	*/
	protected function initializeRouting() {
		// Include se presente il router dell'applicazione
		$this->router = new Router($this);
	}

	/**
	 * Ritorna l'oggetto Router di questa applicazione
	 * @return WebAppRouter il router delle richieste
	*/
	public function getRouter() {
		return $this->router;
	}

	/**
	 * Esegue l'azione ricavandola dalla richiesta HTTP.
	 * 
	 * @see launchAction
	 */
	public function launchRequestedAction() {
		$this->launchAction($this->router->getInternalURL());
	}
	
	/**
	 * Lancia l'azione indicata.
	 *
	 * E' il metodo principale del front controller.
	 * 
	 * Viene di solito eseguita da {@link run()} o da {@see launchRequestedAction}.
	 * 
	 * @param string|URL $action_url stringa di destinazione o oggetto URL da cui ricavare l'azione da eseguire
	 * @see readyToLaunchAction
	 * @see onWebAppForwardException
	 * @see onWebAppRedirectException
	 * @see WebAppHTTPException
	*/
	public function launchAction($action_url) {
		
		if ($action_url	 instanceof URL) {
			$this->current_destination = clone $action_url;
		} else {
			$this->current_destination = $this->router->getURL($action_url);
		}
		
		// Cosa vogliamo fare ci arriva dalla richiesta
		// $this->current_destination = $this->router->getInternalURL(); 
		
		$this->current_module = '';
		$this->current_action = '';
		$anti_recursion = -100;
		

		do {
			$is_http_error_url = FALSE;
			$anti_recursion++;
			
			// Se la URL indica un errore HTTP, l'utente ha sempre i permessi
			// per visualizzarla (dopo averla ripulita)
			if ($this->router->isHTTPSTatusURL($this->current_destination)) {
				Logger::getInstance()->notice("WebAppFrontController: handling HTTP error code.");
				$is_http_error_url = TRUE;
			} else {
				// Verifica se l'utente abbia i giusti permessi di accesso
				if ( $this->auth->hasPermission($this->getUser(), $this->current_destination) == FALSE) {
					// Se non ha i permessi mostra una pagina con un messaggio
					// specifico...
					$this->current_destination = $this->auth->getNoPermissionURL($this->getUser(), $this->current_destination);
				}
			}
			

			// Prova a caricare in base alla destinazione attuale
			// Salva l'url con la destinazione corrente e aggiorna modulo e azione corrente in base ad essa
			$dest_ok = $this->router->getModuleAndActionFromURL($this->current_destination, $this->current_module, $this->current_action);
			$module_method = '';
			
			// Non ha un modulo o azione valido a cui saltare, mostra la pagina di errore
			if ($dest_ok === FALSE) {
				Logger::getInstance()->notice("WebAppFrontController: no action in request.");
				$this->current_destination = $this->router->getURL( $this->router->getHTTPStatusDestination( 404 ) );
				continue;
			}
			
			// Rimuove l'azione se l'URL indica un errore HTTP
			if ($is_http_error_url) {
				$this->current_action = '';
			}
			
			if ( $this->loadAction($this->current_module, $this->current_action, $module_method) ) {
				try {
					// Prima di eseguire, cancella gli elementi non
					// voluti dalla sessione
					// $this->session_manager->clearVolatile($this->current_module, empty($this->current_action) ? FALSE : $this->current_action );
					$this->session_manager->clearVolatile($this->current_module, $this->current_action );

					// Notifica che siamo pronti all'esecuzione
					$this->readyToLaunchAction();
					
					// Esegue l'azione
					Logger::getInstance()->info("Executing Action: ".$this->current_running_action['class'].'::'.$module_method." in ".$this->current_running_action['file']);
					
					if ($this->action->launch($module_method) === FALSE) {
						Logger::getInstance()->warning("Execution Failed (not found): ".$this->current_running_action['class'].'::'.$module_method." in ".$this->current_running_action['file']);
						// L'azione non è stata trovata, deve fare forward a 404 not found
						$this->forward404();
					} else {
						Logger::getInstance()->info("Terminated Action Execution: ".$this->current_running_action['class'].'::'.$module_method." in ".$this->current_running_action['file']);
					}
					break;
				}
				catch(WebAppForwardException $e) {
					if ($this->onWebAppForwardException($e)) break;
				}
				catch(WebAppRedirectException $e) {
					if ($this->onWebAppRedirectException($e)) break;
				}
				catch(WebAppHTTPException $e) {
					if ($this->onWebAppHTTPException($e)) break;
				}
				catch(WebAppActionLaunchException $e) {
					if ($this->onWebAppActionLaunchException($e)) break;
				}
			} else {
				// Un bel 404 not found
				Logger::getInstance()->notice("Action not found: ".$this->current_module.'/'.$this->current_action);
				$this->current_destination = $this->router->getURL( $this->router->getHTTPStatusDestination( 404 ) );
			}

			if ($anti_recursion > 0) {
				Logger::getInstance()->notice("Recursion invoking ".$this->current_module.'/'.$this->current_action);
				throw new Exception("WebApp: can't launch requested action (because of recursion).");
			}
		} while (1);
	}
	
	/**
	 * Eseguita quando il controller è pronto a lanciare una azione.
	 * 
	 * Viene eseguita giusto prima di eseguire {@see WebAppAction::launch) sull'azione
	 * corrente.
	 * 
	 * @see WebAppAction::webappIsReady
	 * @throws WebAppActionLaunchException
	 */
	protected function readyToLaunchAction() {
		$this->action->webappIsReady();
	}
	
	/**
	 * Ciclo di lancio di una azione: eseguita quando riceve un'eccezione di lancio azione.
	 * 
	 * @param WebAppActionLaunchException $e l'eccezione da gestire.
	 * @return boolean TRUE se deve interrompere il ciclo principale, FALSE se deve continuare l'elaborazione
	 */
	protected function onWebAppActionLaunchException(WebAppActionLaunchException $e) {
		Logger::getInstance()->error("Action Launch failed for ".$e->getModule()."/".$e->getAction());
		return TRUE;
	}
	
	/**
	 * Ciclo di lancio di una azione: eseguita quando riceve un'eccezione di forward.
	 * 
	 * Questo metodo viene eseguito durante il ciclo principale di 
	 * {@link launchAction} quando viene sollevata un'eccezione di forward.
	 * 
	 * Comportamento normale: annulla l'azione e aggiorna la destinazione corrente.
	 * Non interrompe l'esecuzione del ciclo.
	 * 
	 * @param WebAppForwardException $e l'eccezione da gestire
	 * @return boolean TRUE se deve interrompere il ciclo principale, FALSE se deve continuare l'elaborazione
	 * @see launchAction
	 */
	protected function onWebAppForwardException(WebAppForwardException $e) {
		Logger::getInstance()->notice("Execution forwarded to ".$e->getModule()."/".$e->getAction());
		// Hanno fatto forward, ricomincia il giro!
		$this->action = NULL;

		// Imposta l'azione dall'eccezione e ne fa il routing
		$this->router->updateURL($this->current_destination, $e->getModule(), $e->getAction());
		$this->router->applyRouting($this->current_destination);
		
		return FALSE;
	}
	
	/**
	 * Ciclo di lancio di una azione: eseguita quando riceve un'eccezione di redirezione HTTP.
	 * 
	 * Questo metodo viene eseguito durante il ciclo principale di 
	 * {@link launchAction} quando viene sollevata un'eccezione di redirezione HTTP.
	 * 
	 * Comportamento normale: imposta la risposta su una redirezione.
	 * Interrompe l'esecuzione del ciclo.
	 * 
	 * @param WebAppRedirectException $e l'eccezione da gestire
	 * @return boolean TRUE se deve interrompere il ciclo principale, FALSE se deve continuare l'elaborazione
	 * @see launchAction
	 */
	protected function onWebAppRedirectException(WebAppRedirectException $e) {
		// Esegue una redirezione HTTP

		// Imposta la risposta attuale come redirezione
		if ($e->isHTTPLocation()) {
			Logger::getInstance()->notice("Execution redirected to ".$e->getLocation());
			$this->getResponse()->redirect( $e->getLocation()  );
		} else {
			$u = $this->router->getURL( $e->getLocation() );
			Logger::getInstance()->notice("Execution redirected to ".$this->router->link($u));
			$this->getResponse()->redirect( $this->router->link($u)  );
		}
		return TRUE;
	}
	
	/**
	 * Ciclo di lancio di una azione: eseguita quando riceve un'eccezione di errore HTTP.
	 * 
	 * Questo metodo viene eseguito durante il ciclo principale di 
	 * {@link launchAction} quando viene sollevata un'eccezione di errore HTTP.
	 * 
	 * Comportamento normale: annulla l'azione, reimposta la destinazione corrente
	 * su una azione che possa gestire l'errore HTTP.
	 * Non interrompe l'esecuzione del ciclo.
	 * 
	 * @param WebAppRedirectException $e l'eccezione da gestire
	 * @return boolean TRUE se deve interrompere il ciclo principale, FALSE se deve continuare l'elaborazione
	 * @see launchAction
	 */
	protected function onWebAppHTTPException(WebAppHTTPException $e) {
		Logger::getInstance()->notice("HTTPException: ".$e->getCode());
		// Non interrompe l'azione, ma aggiorna in modo da mostrare
		// una pagina attinente
		$this->action = NULL;

		$this->current_destination = $this->router->getURL( $this->router->getHTTPStatusDestination( $e->getCode() ) );
		
		return FALSE;
	}

	/*
	 * Converte una stringa in CamelCase: sostituisce
	 * tutti gli '_' seguiti da una lettera nella lettera maiuscola.
	 * Esempio: my_example -> MyExample
	 *
	 * @param string $str stringa da convertire
	 * @return string la stringa convertita in camel case
	*/
	public function camelCase($str) {
		return preg_replace_callback('/([_\-]([a-zA-Z]))/', function($m) { return strtoupper($m[2]); }, ucfirst(strtolower($str)));
	}
	
	/**
	 * Include un file PHP per una azione e ne istanzia la classe che deve essere una
	 * sottoclasse di WebAppAction.
	 * Aggiunge i path di libreria del modulo ed inizializza l'oggetto. Successivamente
	 * è possibile accedere all'azione invocando {@link getAction}
	 *
	 * Il nome del metodo che si deve lanciare varia a seconda del nome del modulo e dell'azione.
	 *
	 * TODO: descrivere il meccanismo usato per ricavare il nome del metodo da lanciare
	 * 
	 * @param string $module nome del modulo che si vuole costruire
	 * @param string $action nome dell'azione che si vuole eseguire
	 * @param string $method metodo che si deve invocare per lanciare l'azione
	 * @return boolean TRUE se ha potuto includere il file, FALSE altrimenti
	*/
	protected function loadAction($module, $action, &$method) {
		$module = strtolower($module);
		$action = strtolower($action);
		$files = array();
		// Se non c'è azione, può solo includere il file principale dei moduli
		$module_cfg_path = $this->getModulePath($module).'config'.DIRECTORY_SEPARATOR;
		if ( empty($action) ) {
			// Una azione di un modulo
			$files[] = array(
				'file' => $this->getPath().'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'actions'.DIRECTORY_SEPARATOR.'actions.inc.php',
				'class' => $this->camelCase($module).'Actions',
				'config' => $module_cfg_path,
				'method' => 'execute'
			);
			
			// Un plugin, solo se autorizzato
			if (WebAppConfig::pluginModuleIsEnabled($module)) {
				$files[] = array(
					'file' => $this->getPluginsPath().$module.DIRECTORY_SEPARATOR.'actions'.DIRECTORY_SEPARATOR.'actions.inc.php',
					'class' => $this->camelCase($module).'Actions',
					'method' => 'execute',
					'config' => '',
					'is_plugin' => TRUE
				);
			}
			
			// Una azione di default
			$files[] = array(
				'file' => WEBAPP_CORE_PATH.'actions'.DIRECTORY_SEPARATOR.$module.'.inc.php',
				'class' => $this->camelCase($module).'Action',
				'method' => 'execute',
				'config' => $module_cfg_path
			);
		} else {
			// Prima possibilità: singola classe in un file
			$files[] = array(
				'file' => $this->getPath().'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'actions'.DIRECTORY_SEPARATOR.$action.'Action.inc.php',
				'class' => $this->camelCase($action).'Action',
				'method' => 'execute',
				'config' => $module_cfg_path
			);
			// Altrimenti un metodo di una classe 
			$files[] = array(
				'file' => $this->getPath().'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'actions'.DIRECTORY_SEPARATOR.'actions.inc.php',
				'class' => $this->camelCase($module).'Actions',
				'method' => 'execute'.$this->camelCase($action),
				'config' => $module_cfg_path
			);
			// Un plugin, solo se autorizzato
			if (WebAppConfig::pluginModuleIsEnabled($module)) {
				$files[] = array(
					'file' => $this->getPluginsPath().$module.DIRECTORY_SEPARATOR.'actions'.DIRECTORY_SEPARATOR.$action.'Action.inc.php',
					'class' => $this->camelCase($action).'Action',
					'method' => 'execute',
					'config' => '',
					'is_plugin' => TRUE
				);
				$files[] = array(
					'file' => $this->getPluginsPath().$module.DIRECTORY_SEPARATOR.'actions'.DIRECTORY_SEPARATOR.'actions.inc.php',
					'class' => $this->camelCase($module).'Actions',
					'method' => 'execute'.$this->camelCase($action),
					'config' => '',
					'is_plugin' => TRUE
				);
			}
			
			// Ultima possibilità, una classe builtin
			$files[] = array(
				'file' => WEBAPP_CORE_PATH.'actions'.DIRECTORY_SEPARATOR.$module.'.inc.php',
				'class' => $this->camelCase($module).'Actions',
				'method' => 'execute'.$this->camelCase($action),
				'config' => ''
			);
			$files[] = array(
				'file' => WEBAPP_CORE_PATH.'actions'.DIRECTORY_SEPARATOR.$module.'.inc.php',
				'class' => $this->camelCase($module).'Actions',
				'method' => 'execute',
				'config' => ''
			);
			$files[] = array(
				'file' => WEBAPP_CORE_PATH.'actions'.DIRECTORY_SEPARATOR.$module.'.inc.php',
				'class' => $this->camelCase($module).'Action',
				'method' => 'execute',
				'config' => ''
			);
		}
		foreach($files as $f) {
			$this->current_running_action = array();
			if (file_exists($f['file']) && is_file($f['file'])) {
				// Aggiunge già i path del modulo, così da permettere
				// il subclassing delle azioni
				if (!array_key_exists('is_plugin', $f)) {
					$this->addModulePaths($module);
					$this->addModuleTranslations($module);
				}
				Logger::getInstance()->debug('WebAppFrontController::loadAction: searching class '.$f['class'].' in file '.$f['file']);
				require_once $f['file'];
				Logger::getInstance()->debug('WebAppFrontController::loadAction: class '.$f['class'].(class_exists($f['class']) ? ' exists.' : ' not exists.'));
				Logger::getInstance()->debug('WebAppFrontController::loadAction: class '.$f['class'].(is_subclass_of($f['class'], 'WebAppAction') ? ' is subclass' : ' is not subclass').' of WebAppAction');
				if (class_exists($f['class']) && is_subclass_of($f['class'], 'WebAppAction') ) {
					Logger::getInstance()->debug('WebAppFrontController::loadAction: loaded class: '.$f['class']);
					// Aggiunge i percorsi di libreria del modulo
					// $this->addModulePaths($module);
					// legge la configurazione del modulo
					if (!empty($f['config']) && is_dir($f['config'])) {
						if (file_exists($f['config'].'config.inc.php')) include_once $f['config'].'config.inc.php';
					}
					$this->current_running_action = $f;
					// costruisce la classe
					$this->action = new $f['class'];
					$method = $f['method'];
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * Ritorna il percorso della directory principale di un modulo
	 * Ricava il percorso dal quello dell'applicazione corrente fornito da {@link getPath}
	 * @param string $module nome del modulo
	 * @return string una stringa col percorso
	*/
	public function getModulePath($module) {
		return $this->getPath().'modules'.DIRECTORY_SEPARATOR.strtolower($module).DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Ritorna il nome del modulo attualmente richiesto
	 * @return string col nome del modulo
	*/
	public function getCurrentModule() {
		return $this->current_module;
	}
	
	/**
	 * Ritorna il nome dell'azione richiesta
	 * @return string col nome dell'azione
	*/
	public function getCurrentAction() {
		return $this->current_action;
	}
	
	/**
	 * Informa se sia impostato il modulo attualmente richiesto.
	 * 
	 * @return boolean TRUE se è impostato, FALSE altrimenti
	 */
	public function hasCurrentModule() {
		return !empty($this->current_module);
	}
	
	/**
	* Informa se sia impostata l'azione attualmente richiesta.
	*
	* @return boolean TRUE se è impostata, FALSE altrimenti
	*/
	public function hasCurrentAction() {
		return !empty($this->current_action);
	}
	
	/**
	 * 
	 * Ritorna l'URL generata dal router dell'azione corrente.
	 * L'URL ritornata è completa e permette di accedere alla azione corrente una volta
	 * trasformata in ancora HTML.
	 *
	 * @return URL istanza di URL coi dati completi dell'azione
	*/
	public function getCurrentDestinationURL() {
		return clone $this->current_destination;
	}
	
	/**
	 * Ritorna una stringa con la destinazione corrente, ossia l'indirizzo
	 * interno del modulo/azione che sta gestendo l'operazione attuale.
	 * Ritorna una stringa nella forma:
	 * modulo/azione?param=value&param1=value=1...&paramX=valueX
	 *
	 * @return string una stringa con la destinazione
	*/
	public function getCurrentDestination() {
		return $this->router->URLtoDestination($this->current_destination, TRUE);
	}
	
	/**
	 * Informa se il modulo specificato sia quello corrente
	 * 
	 * @param string $module nome del modulo da verificare
	 * @return boolean TRUE se è il modulo corrente, FALSE altrimenti
	*/
	public function isCurrentModule($module) {
		return (strcmp($this->current_module, $module) == 0);
	}
	
	/**
	 * Informa se l'azione specificata sia quella corrente
	 * 
	 * @param string $action nome dell'azione da verificare
	 * @return boolean TRUE se è l'azione corrente, FALSE altrimenti
	*/
	public function isCurrentAction($action) {
		return (strcmp($this->current_action, $action) == 0);
	}

	/**
	 * 
	 * Imposta il gestore di risposta.
	 * 
	 * Imposta l'istanza di Response che gestirà la risposta: viene di solito invocata
	 * da WebAppAction prima di eseguire le operazioni.
	 * 
	 * @param Response $response il gestore di risposta
	*/
	public function setResponse(Response $response) {
		$this->response = $response;
	}

	/**
	 * Ritorna il gestore di risposta corrente
	 *
	 * Fornisce l'oggetto Response per elaborare la risposta
	 * 
	 * @return Response il gestore di risposta
	*/
	public function getResponse() {
		return $this->response;
	}
	
	/**
	 * Ritorna il gestore di richiesta HTTP corrente
	 *
	 * @return Request il singleton della richiesta
	*/
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * Informa se ci sia una istanza di Response registrata nell'applicazione
	 *
	 * @return boolean TRUE se l'istanza è presente, FALSE altrimenti
	*/
	public function hasResponse() {
		return is_object($this->response);
	}

	/**
	 * Esegue l'oggetto Response che invia l'output dell'azione al browser
	 * 
	 * Delega all'azione corrente di finalizzare la risposta attraverso il metodo
	 * {@link WebAppAction::finalizeResponse}, che consiste principalmente
	 * nel copiare il buffer di output nella risposta.
	 *
	 * 
	*/
	public function executeResponse() {
		// Lascia che l'azione corrente termini le operazioni
		$this->getAction()->finalizeResponse();

		// Esegue effettivamente la risposta
		$this->getResponse()->execute();
	}
	
	/**
	 * Ritorna una stringa col layout di pagina, facendolo generare all'istanza di PageLayout
	 * interna (ossia la layer View)
	 * 
	 * Invoca {@link PageLayout::render}
	 *
	 * @return string col codice xhtml
	 */
	public function getRenderedLayout() {
		return $this->getPageLayout()->render();
	}

	/**
	 * Ritorna l'istanza di PageLayout associata a questa applicazione.
	 * 
	 * L'istanza viene ricavata dal View correntemente in uso.
	 * 
	 * @return PageLayout una istanza di PageLayout
	*/
	public function getPageLayout() {
		return $this->webapp_view->getPageLayout();
	}
	
	/**
	 * Inizializza il View.
	 *
	 * Procede prima caricando un PageLayout personalizzato mediante  {@link loadPageLayout} e
	 * poi il fallback con {@link setDefaultPageLayout()}
	*/
	public function initializeView() {
		$this->webapp_view = new WebAppView();
	}
	
	/**
	 * Ritorna il gestore di View della WebApp.
	 * 
	 * @return WebAppViewInterface il gestore di view
	 */
	public function getView() {
		return $this->webapp_view;
	}
	
	/**
	 * Informa se sia stato impostato un gestore di layout di pagina.
	 *
	 * @return boolean TRUE se c'è il gestore, FALSE altrimenti
	*/
	public function hasPageLayout() {
		return $this->webapp_view->hasPageLayout();
	}

	/**
	 * Ritorna l'utente che sta usando l'applicazione
	 * L'utente anche se anonimo viene fornito dall'istanza di Auth
	 * @return BaseUser l'utente corrente
	*/
	public function getUser() {
		return $this->auth->getUser();
	}

	/**
	 *  Ritorna la connessione al database.
	 * Ogni connessione ha associata una etichetta (un nome): nel caso questo non venga fornito
	 * viene utilizzata la connessione marcata come default. 
	 * I dati relativi alla connessione vengono forniti da {@link WebAppConfig}
	 *
	 * Una volta creata l'istanza questa viene conservata (come se fosse un singleton) e riutilizzata
	 * nelle successive invocazioni per una stessa connessione.
	 *
	 * @return DatabaseDriver una istanza di DatabaseDriver con la connessione al db o NULL
	 * se non esiste una connessione
	*/
	public function getDatabase($connection_name = '') {
		if (empty($connection_name)) {
			if (WebAppConfig::hasDefaultDatabase()) {
				$connection_name = WebAppConfig::getDefaultDatabase();
			} else {
				return NULL;
			}
		} 
		
		if (array_key_exists($connection_name, $this->database_pool)) {
			return $this->database_pool[$connection_name];
		} else {
			$dsn = WebAppConfig::getDatabase($connection_name);
			if ($dsn !== FALSE) {
				try {
					$db = DatabaseDriverManager::getDatabaseDriver($dsn);
					if ($db !== FALSE) {
						$this->database_pool[$connection_name] = $db;
						return $db;
					}
				}
				catch(DBException $e) {
					die("Database initialization error.");
				}
			}
		}
		return NULL;
	}

	/**
	 * Esegue le operazioni necessarie per l'inzializzazione del
	 * pool di connessioni al database
	*/
	protected function initializeDatabase() {
		$this->database_pool = array();
	}
	
	/**
	 * Inizializza l'oggetto Response per gestire la risposta.
	 * 
	 * Crea l'oggetto Response
	 * 
	 * */
	protected function initializeResponse() {
		$this->response = new Response();
	}

	/**
	 * FIXME: cambiare togliendo i due parametri e mettendo invece una destinazione
	 *
	 * Esegue il forward della richiesta.
	 * Solleva una eccezione che viene intercettata da {@link launchAction}
	 * 
	 * @param $module stringa col modulo verso il quale andare
	 * @param $action stringa con l'azione da eseguire
	 *
	 * @throws WebAppForwardException
	*/
	public function forward($module, $action = '') {
		throw new WebAppForwardException($module, $action);
	}

	/**
	 * Esegue il forward della richiesta verso una pagina di errore HTTP 404.
	 * Solleva una eccezione che viene intercettata da {@link launchAction}
	 *
	 * @throws WebAppHTTPException
	*/
	public function forward404() {
		throw new WebAppHTTPException(404);
	}

	/**
	 * Ritorna l'istanza dell'azione correntemente eseguita
	 *
	 * @return WebAppAction l'istanza di WebAppAction correntemente in esecuzione
	*/
	public function getAction() {
		return $this->action;
	}

	/**
	 * Esegue una redirezione HTTP verso un indirizzo URL.
	 * 
	 * Il parametro può indicare sia una locazione esplicita, ossia
	 * 	essere una URL http://... che una destinazione interna (modulo/azione),
	 * nel secondo caso verrà prima applicato il routing
	 * 
	 * Solleva una eccezione che viene intercettata da {@link launchAction}
	 *
	 * @throws WebAppRedirectException
	*/
	public function redirect($location) {
		throw new WebAppRedirectException($location);
	}

	/**
	 * Esegue la richiesta
	 * 
	 * E' il metodo che va eseguito per processare e gestire una richeista HTTP: in pratica lancia
	 * l'applicazione.
	 *
	 * Esegue tutto il ciclo vitale: dall'acquisizione dei dati al dispatch alla produzione
	 * della risposta.
	 * 
	 * Il parametro $dont_execute controlla se si debbano eseguire l'azione e la risposta.
	 * 
	 * @param string $application_name stringa col nome dell'applicazione da eseguire
	 * @param boolean $dont_execute TRUE non lancia l'azione dalla URL e la risposta, FALSE esegue normalmente
	*/
	public function run($application_name, $dont_execute = FALSE) {
		$this->setAppName($application_name);
		
		// Inizializza il controller (legge la configurazione)
		$this->initialize();
		if (!$dont_execute) {
			// Esegue l'applicazione
			$this->launchRequestedAction();
			// Esegue la risposta
			$this->executeResponse();
		}
	}
	
	/**
	 * Imposta il nome dell'applicazione.
	 * 
	 * @param string $application_name il nome dell'applicazione
	 */
	protected function setAppName($application_name) {
		$this->appname = strval($application_name);
	}

	/**
	 * Ritorna l'encoding globale dei caratteri dell'applicazione, ricavandolo
	 * dal gestore di internazionalizzazione.
	 *
	 * @return string una stringa con l'encoding
	*/
	public function getEncoding() {
		return $this->language_manager->getEncoding();
	}
	
	/**
	 * Imposta l'encoding interno dei caratteri, mediante il gestore di internazionalizzazione
	 * l'encoding è una stringa tipo: utf-8, iso-8859-1
	 * @param string $encoding una stringa con l'encoding
	*/
	public function setEncoding($encoding) {
		$this->language_manager->setEncoding($encoding);
	}

	/**
	 * Inizializza il gestore di sessione.
	 * Ogni sessione viene posta in un realm che corrisponde al nome
	 * dell'applicazione.
	 * Se si vuole cambiare tale realm si deve impostare la variabile
	 * di configurazione WEBAPP_SESSION_REALM, in {@link WebAppConfig}.
	 *
	 * Cerca di istanziare la classe SessionManager, sottoclasse di BaseSessionManager.
	 *
	 **/
	protected function initializeSessionManager() {
		$realm = WebAppConfig::get('WEBAPP_SESSION_REALM');
		$this->session_manager = new SessionManager(empty($realm) ? $this->getName() : $realm);
	}

	/**
	 * Ritorna il gestore di sessione attuale
	 * @return BaseSessionManager una istanza di BaseSessionManager
	*/
	public function getSessionManager() {
		return $this->session_manager;
	}

	/**
	 * Si occupa di includere le dichiarazioni di oggetti necessarie prima
	 * di avviare la sessione
	 *
	 * Include il file 'session_hooks.inc.php' che dovrebbe contenere le dichiarazioni
	 * di classi necessarie prima di lanciare il gestore di sessione.
	 * 
	 * Il gestore standard di sessione di PHP richiede che per gli oggetti posti nella
	 * sessione sia presente la dichiarazione completa prima che la sessione
	 * venga inizializzata.
	*/
	protected function loadSessionHooks() {
		WebAppLoader::requireFile('session_hooks.inc.php');
	}

	/**
	 * Data una istanza di URL produce una stringa con la url, pronta per l'utilizzo.
	 * Proxy per {@link WebAppRouter::link}, ma invocata sul router corrente
	 * 
	 * @param $url l'istanza di URL da utilizzare
	 * @param $as_html boolean TRUE ritorna una stringa da usare come attributo in un tag xhtml, FALSE solo stringa
	 * @param $with_session boolean TRUE acclude il SID di sessione, FALSE non acclude il SID di sessione
	 * @param array $params array associativo con parametri addizionali
	 * @return string una stringa con la URL
	*/
	public function link(URL $url, $as_html = FALSE, $with_session = TRUE, $params = array()) {
		return $this->router->link($url, $as_html, $with_session, $params);
	}

	/**
	 * Data una destinazione, ritorna una stringa con la URL, usando il Router.
	 *
	 * @param string $destination la stringa con la destinazione
	 * @param boolean $as_html TRUE ritorna come HTML, FALSE altrimenti
	 * @param $with_session boolean TRUE acclude il SID di sessione, FALSE non acclude il SID di sessione
	 * @param array $params array associativo con parametri addizionali
	 * @return string la stringa con la url
	 */
	public function link_to($destination, $as_html = FALSE, $with_session = TRUE, $params = array()) {
		return $this->router->link($this->getURL($destination), $as_html, $with_session, $params);
	}
	
	
	/*
	 * Processa ricorsivamente una directory ritornando un array
	 * associativo composto dalle directory che trova
	*/
	private function scandirectory($path) {
		if (file_exists($path) && is_dir($path) ) {
			$out = array( $path );
			$dh = opendir($path);
			while ( ($fn = readdir($dh)) !== FALSE ) {
				if ($fn[0] != '.') {
					$newpath = $path.$fn;
					if ( is_dir($newpath) ) {
						$out = array_merge($out, $this->scandirectory($newpath.DIRECTORY_SEPARATOR));
					}
				}
				
			}
			closedir($dh);
			return $out;
		} else {
			return array();
		}
		
	}

	/**
	 * Ritorna tutti i plugin che ha trovato
	 *
	 * @return array un array di stringhe
	 */
	public function getPlugins() {
		return $this->plugins;
	}
	
	/**
	 * Aggiunge ai percorsi di inclusione quelli relativi all'applicazione corrente.
	 * Aggiunge anche i percorsi per le traduzioni.
	 *
	 * Usa la costante WEBAPP_APPS_PATH come base per i percorsi.
	*/
	protected function addApplicationPaths() {
		// Percorsi delle librerie
		WebAppLoader::addDirectory(WEBAPP_APPS_PATH.$this->appname.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR, TRUE);
	}
	
	/**
	 * Aggiunge ai percorsi di inclusione quelli relativi al modulo specificato.
	 * Usa {@link getPath} come base per i percorsi
	*/
	protected function addModulePaths($module) {
		WebAppLoader::addDirectory($this->getPath().'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR, TRUE);
	}

	/**
	 * Aggiunge al gestore di lingua corrente le traduzioni relative al modulo
	 * specificato
	 * @param string $module nome del modulo di cui aggiungere le traduzioni
	 */
	protected function addModuleTranslations($module) {
		$this->language_manager->getI18NManager()->addModuleTrans($module, $this->getPath().'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR);
	}

	/**
	 * Ritorna una URL data una destinazione.
	 * 
	 * Usa il router corrente.
	 * 
	 * Una stringa di destinazione ha la forma:
	 * modulo/azione?param1=value1&paramX=valueX...
	 *
	 * Proxy per {@link WebAppRouter::getURL}
	 * 
	 * @param string $destination una stringa di destinazione
	 * @return URL una istanza di URL
	*/
	public function getURL($destination) {
		return $this->router->getURL($destination);
	}

	// -------------- Gestione della lingua
	/**
	 * Carica il gestore di internazionalizzazioni, ma non lo inizializza.
	 *
	 * Istanzia la classe LanguageManager, che deve essere una
	 * sottoclasse di BaseLanguageManager.
	 *
	*/
	protected function initializeLanguageManager() {
		// Include il file corretto
		$this->language_manager = new LanguageManager();
	}
	
	/**
	 * Ritorna il gestore di internazionalizzazione.
	 * @return WebAppLanguageManager
	*/
	public function getLanguageManager() {
		return $this->language_manager;
	}
	
	/**
	 * Ritorna il gestore di traduzioni.
	 *
	 * Proxy per {@link BaseLanguageManager::getI18NManager}
	 *
	 * @return I18NManager una istanza di I18NManager
	*/
	public function getI18N() {
		return $this->language_manager->getI18NManager();
	}

	/**
	 * Ritorna la lingua correntemente impostata per questa applicazione
	 * La stringa è nel formato xx_YY ad esempio it_IT per l'italiano.
	 * 
	 * Proxy per {@link BaseLanguageManager::getLanguage}
	 * 
	 * @return string una stringa con la lingua
	*/
	public function getLanguage() {
		return $this->language_manager->getLanguage();
	}

	/**
	 * Imposta globalmente la lingua dell'applicazione
	 * 
	 * Proxy per {@link BaseLanguageManager::setLanguage}
	 * 
	 * @param string $lang stringa con la lingua
	 * @param boolean $set_cookie TRUE imposta anche il cookie per la lingua, FALSE solo la lingua corrente
	 *
	 */
	public function setLanguage($lang, $set_cookie = FALSE) {
		$this->language_manager->setLanguage($lang, $set_cookie);
	}
	
	// ---------------------- Parametri dell'applicazione
	/**
	 * Ritorna l'HashMap coi parametri dell'applicazione
	 * @return HashMap istanza di hashmap
	*/
	public function getParams() {
		if (!is_object($this->app_params)) $this->app_params = new HashMap();
		return $this->app_params;
	}
	
	/**
	 * Imposta un valore per un parametro dell'applicazione
	 * proxy per getParams()->set()
	 * @param string $param stringa col nome del parametro
	 * @param mixed $value valore del parametro
	*/
	public function setParam($param, $value) {
		$this->getParams()->set(strval($param), $value);
	}
	
	/**
	 * Ritorna il valore di un parametro dell'applicazione (se esiste)
	 * proxy per getParams()->get()
	 * 
	 * @param string $param stringa col nome del parametro
	 * @return mixed il valore del parametro o NULL
	*/
	public function getParam($param) {
		return $this->getParams()->get($param);
	}
	
	/**
	 * Verifica se esista un parametro dell'applicazione
	 * proxy per getParams()->hasKey()
	 * 
	 * @param string $param stringa col nome del parametro
	 * @return boolean TRUE se il parametro esiste, FALSE altrimenti
	*/
	public function hasParam($param) {
		return $this->getParams()->hasKey($param);
	}
}
