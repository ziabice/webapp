<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestione del routing delle risposte: data una richiesta HTTP estrae
 * il modulo e l'azione richiesta, fornendoli all'applicazione.
 *
 * E' usato anche per traslare certi indirizzi in altri.
 *
 * Una destinazione viene prima trasformata in un oggetto URL e poi
 * quest'ultimo trasformato in una stringa di testo con l'URL (stringa che verrà
 * usata nel codice XHTML).
 * Il processo che porta ad una stringa con la URL partendo dalla destinazione
 * è il seguente:
 *  - Viene invocata {@link WebAppRouter::getURL} per trasformare una destinazione in un oggetto {@link URL}
 *  - Il metodo {@link WebAppRouter::getURL} invoca come passo finale {@link WebAppRouter::applyRouting} che eseguirà eventuali cambiamenti sull'istanza di URL
 *  - l'oggetto {@link URL} così creato viene passato come parametro a {@link WebAppRouter::link}, che ne restituirà una versione testuale
 *
 * Quindi ad esempio per creare un sito in cui tutte le URL sono elaborate
 * mediante mod_rewrite di Apache, bisogna ridefinire il metodo {@link WebAppRouter::link}.
 * Per invece trasformare le URL generate (ad esempio rimuovendo dei parametri o cambiandone alcuni)
 * bisogna ridefinire {@link WebAppRouter::applyRouting}. Ad esempio in un sito in produzione
 * si possono cambiare tutti i richiami alla destinazione 'foo/action' in 'bar/action' senza
 * riscrivere il codice, ma semplicemente manipolando il modulo in {@link WebAppRouter::applyRouting}.
 *
 *
 * E' possibile anche eseuire l'operazione inversa, ossia passare da una URL
 * espressa in una richiesta, in un'altra. Per fare ciò basta
 * ridefinire {@link WebAppRouter::applyRequestedDestinationRouting}.
 *
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class WebAppRouter {
	protected
		/**
		 * @var string nome del parametro nella richiesta che contiene il modulo
		 */
		$request_module_name = 'module',
		/**
		 * @var string nome del parametro nella richiesta che contiene il modulo
		 */
		$request_action_name = 'action',
		$internal_url, // Oggetto URL che contiene la destinazione interna (routed) 
		$external_url, // Oggetto URL che contiene la destinazione richiesta via HTTP (non routed)
		$webapp;

	/**
	 * Inizializza con il front controller attuale
	 *
	 * @param WebAppFrontController $webapp il front controller col quale interagire
	 */
	public function __construct($webapp) {
		$this->webapp = $webapp;
		
		$this->initialize();
	}
	
	/**
	 * 
	 * Elabora la richiesta HTTP producendo da essa una URL interna.
	 * 
	 * Viene invocata dal costruttore.
	 * 
	 * @see applyRouting
	 * @see getInternalURL
	 * @see getExternalURL
	 */
	protected function initialize() {
		$this->initializeCoreURLs();
		$this->applyRouting($this->internal_url);
	}
	
	/**
	 * Inizializza le URL interne ed esterne.
	 * @see getInternalURL
	 * @see getExternalURL
	 */
	protected function initializeCoreURLs() {
		$this->external_url = Request::getInstance()->makeURL();
		$this->internal_url = clone $this->external_url;
	}
	
	/**
	 * 
	 * Ritorna la URL alla destinazione interna.
	 * 
	 * A questa URL è già stato applicato il ruoting.
	 * 
	 * Viene inizializzata da {@link initialize} al momento della costruzione
	 * dell'istanza.
	 * 
	 * @return URL l'oggetto con la destinazione interna
	 * @see initialize
	 */
	public function getInternalURL() {
		return clone $this->internal_url;
	}
	
	/**
	 * 
	 * Ritorna la URL alla destinazione interna, non routed.
	 * 
	 * E' la URL generata direttamente dalla richiesta.
	 * 
	 * Viene inizializzata da {@link initialize} al momento della costruzione
	 * dell'istanza.
	 * 
	 * @return URL l'oggetto con la destinazione interna senza routing
	 * @see initialize
	 */	
	public function getExternalURL() {
		return clone $this->external_url;
	}
	
	/**
	 * Effettua il parsing di una destinazione, ritornando un hashmap.
	 * 
	 * Non applica nessun routing, effettua solo il parsing semplice.
	 * 
	 * Una destinazione ha la forma:
	 * modulo/azione?paramX=valueX&paramX=valueX#anchor
	 *
	 * modulo e azione devono iniziare per una lettera minuscola ed essere composti da numeri, lettere minuscole e _
	 * 
	 * parametri: una associazione parametro=valore, si possono specificare anche array:
	 * param[]=val1&param[]=val2, dà: 'param' => array('val1', 'val2')
	 * 
	 * Anche array con indice:
	 * param[10]=val1&param[stringa]=val2, dà: 'param' => array('10' => 'val1', 'stringa' => 'val2')
	 * 
	 * Le chiavi dell'array di ritorno sono:
	 * 'module' => stringa col modulo
	 * 'action' => stringa con l'azione
	 * 'params' => array con i parametri
	 * 
	 * l'array dei parametri è un array associativo parametro=valore:
	 * 'nomeparametro' => valore
	 * 'nomeparametro1' => array di valori
	 * 
	 * @return array|boolean ritorna un array se tutto ok, FALSE se la destinazione è errata
	*/
	public function parseDestination($destination) {
		if (preg_match('/^([a-z][a-z0-9_]*)(\/(?P<action>([a-z][a-z0-9_]*))((\?(?P<params1>[^#]*)(#(?P<anchor1>[^#]+))?|#(?P<anchor2>[^#]+))?)|((\?(?P<params2>[^#]*)(#(?P<anchor3>[^#]+))?|#(?P<anchor4>[^#]+))?))?$/', $destination, $params) != 0) {
			$out = array(
				'referer' => '',
				'anchor' => '',
				'module' => $params[1],
				'action' => (array_key_exists('action', $params) ? $params['action'] : ''),
				'params' => array()
			);
			$parametri = '';
			foreach(array('params1', 'params2') as $p) {
				if (isset($params[$p])) {
					if (!empty($params[$p])) $parametri = $params[$p];
				}
			}
			preg_match_all('/([^=&]+)=([^&]*)/', $parametri, $par, PREG_SET_ORDER);
			// cerca gli eventuali parametri array
			$arrs = array();
			foreach($par as $idx => $p) {
				if (preg_match('/^([^\]]+)\[(.*)?\]/',$p[1], $arr) == 1) {
					if (!array_key_exists($arr[1], $arrs)) $arrs[$arr[1]] = array();
					if (empty($arr[2])) $arrs[$arr[1]][] = $p[2];
					else $arrs[$arr[1]][$arr[2]] = $p[2];
				} else {
					$out['params'][$p[1]] = $p[2];
				}
			}
			$out['params'] = array_merge($out['params'], $arrs);
			// ripulisce l'array, decodificando i caratteri
			$this->urldecode_arr($out['params']);
			
			foreach( array('anchor1','anchor2','anchor3','anchor4') as $a ) {
				if (isset($params[$a])) {
					if (!empty($params[$a])) $out['anchor'] = $params[$a];
				}
			}
			return $out;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Applica urldecode ad un array, ricorsivamente
	*/
	private function urldecode_arr(&$arr) {
		foreach($arr as $k => $v) {
			if (is_array($v)) $this->urldecode_arr($arr[$k]);
			else $arr[$k] = urldecode($v);
		}
	}
	
	/**
	 * Data una destinazione ritorna una istanza di URL per raggiungerla.
	 * 
	 * Ritorna una URL anche se la destinazione è sconosciuta.
	 * La stringa di destinazione deve essere una accettata da parseDestination().
	 * 
	 * Applica il routing.
	 * 
	 * @param string $destination una stringa di destinazione interna
	 * @return URL una istanza di URL o NULL se la destinazione non è valida
	*/
	public function getURL($destination) {
		$dest = $this->parseDestination($destination);
		if ($dest !== FALSE) {
			$u = $this->getBaseURL();
			$this->applyRoutingToDestination($dest, $u);
		} else {
			$u = NULL;
		}
		return $u;
	}
	
	/**
	 * Verifica se una destinazione sia formalmente valida: non verifica
	 * se essa esista realmente, ma solo se la sua sintassi sia corretta.
	 *
	 * @param string $destination una stringa con la destinazione
	 * @return boolean TRUE se la sintassi è corretta, FALSE altrimenti
	*/
	public function checkDestination($destination) {
		return ($this->parseDestination(strval($destination)) !== FALSE);
	}
	
	/**
	 * 
	 * Ritorna l'URL di base utilizzata per creare le URL da una destinazione.
	 * 
	 * Ricava dalla richiesta (oggetto {@link Request}) l'URI completo di script
	 * da usare come base.
	 *
	 * Usata da {@link getURL).
	 *
	 * @return URL una istanza di URL
	*/
	public function getBaseURL() {
		return new URL( Request::getInstance()->getRequestURI() );
	}
	
	/**
	 * Salva i parametri di destinazione in un oggetto URL
	 * I dati in ingresso sono quelli generati da {@link parseDestination}
	 * 
	 * @param array $dest_arr dati di destinazione, come ritornati da {@link parseDestination}
	 * @param URL $url istanza di url su cui applicare i dati
	*/
	public function saveDestinationToURL($dest_arr, URL $url) {
		$url->set( $this->getRequestModuleName(), $dest_arr['module'] );
		if (!empty($dest_arr['action'])) $url->set( $this->getRequestActionName(), $dest_arr['action'] );
		foreach($dest_arr['params'] as $k => $v) $url->set($k, $v);
		$url->setAnchor($dest_arr['anchor']);
	}
	
	/**
	 * Filtra la destinazione applicando la tabella di routing alla URL risultante.
	 * 
	 * Di default non cambia niente, ricopia solo i dati di destinazione e i
	 * parametri nella URL, usando \ref saveDestinationToURL.
	 * 
	 * @param $destination_arr array generato da parseDestination()
	 * @param $url URL istanza di URL a cui applicare il routing
	*/
	public function applyRoutingToDestination($destination_arr, URL $url) {
		$this->saveDestinationToURL($destination_arr, $url);
		$this->applyRouting($url);
	}
	
	/**
	 * Applica il routing ad una URL.
	 * 
	 * @param URL $url istanza di URL a cui applicare il routing
	 * */
	public function applyRouting(URL $url) {
		if (!$this->hasModule($url)) {
			$url->copy($this->getDefaultURL(), TRUE);
		}
	}
	
	/**
	 * Informa se la URL contenga l'indicazione del modulo
	 * 
	 * @param URL $url istanza di Url da verificare
	 * @return boolean TRUE se il modulo è indicato, FALSE altrimenti
	*/
	public function hasModule(URL $url) {
		return $url->hasParam( $this->getRequestModuleName() );
	}
	
	/**
	 * Informa se la URL contenga l'indicazione dell'azione
	 *
	 * @param URL $url istanza di Url da verificare
	 * @return boolean TRUE se l'azione è indicata, FALSE altrimenti
	*/
	public function hasAction(URL $url) {
		return $url->hasParam( $this->getRequestActionName() );
	}
	
	/**
	 * Verifica se una istanza di URL sia valida, ossia se contenga
	 * l'indicazione di modulo ed eventualmente azione da eseguire
	 * 
	 * @param URL $url istanza di URL da verificare
	 * @return boolean TRUE se l'URL è valida (formalmente), FALSE altrimenti
	*/
	public function isValidURL($url) {
		if ($url instanceof URL) {
			if ($this->checkModuleName( $url->get( $this->getRequestModuleName() ) )) {
				if ( $this->hasAction($url) ) {
					$a = $url->get( $this->getRequestActionName() );
					if (empty($a)) return TRUE; 
					else return $this->checkActionName( $url->get( $this->getRequestActionName() ) );
				} else {
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * 
	 * Data una URL ne estrae le indicazioni di modulo e azione
	 * e le salva nelle variabili indicate.
	 *
	 * 
	 * @param URL $url istanza di URL da controllare
	 * @param string $module conterrà il modulo
	 * @param string $action conterrà l'azione
	 * @return boolean FALSE se l'url non è valida, TRUE altrimenti
	*/
	public function getModuleAndActionFromURL($url, &$module, &$action) {
		$module = '';
		$action = '';
		if ( $this->isValidURL($url) ) {
			$module = $url->get( $this->getRequestModuleName() );
			$action = $url->get( $this->getRequestActionName() );
			if (is_null($action)) $action = '';
			return TRUE;
		} 
		return FALSE;
	}
	
	/**
	 * Ritorna il nome della variabile HTTP che contiene il modulo da eseguire
	 * @return string nome della variabile nella richiesta HTTP che contiene il modulo
	*/
	public function getRequestModuleName() {
		return $this->request_module_name;
	}
	
	/**
	 * Imposta il nome del parametro della richiesta HTTP che contiene il modulo da eseguire
	 * @param string $name nome del parametro
	 */
	public function setRequestModuleName($name) {
		$this->request_module_name = strval($name);
	}
	
	/**
	 * Ritorna il nome della variabile HTTP che contiene l'azione da eseguire
	 * @return string nome della variabile nella richiesta HTTP che contiene l'azione
	*/
	public function getRequestActionName() {
		return $this->request_action_name;
	}
	
	/**
	 * Imposta il nome del parametro della richiesta HTTP che contiene l'azione da eseguire
	 * @param string $name nome del parametro
	 */
	public function setRequestActionName($name) {
		$this->request_action_name = strval($name);
	}
	
	/**
	 * Verifica se il nome fornito sia formalmente un modulo corretto.
	 * 
	 * Il nome di modulo deve cominciare per lettera minuscola e proseguire con numeri, lettere minuscole e _
	 * 
	 * @param string $module stringa col nome del modulo
	 * @return boolean TRUE se il valore fornito è corretto, FALSE altrimenti
	*/
	public function checkModuleName($module) {
		return (preg_match('/^[a-z][a-z0-9_]*[^_]$/', $module) == 1);
	}
	
	/**
	 * Verifica se il nome fornito sia formalmente una azione corretta.
	 * 
	 * Il nome di azione deve cominciare per lettera minuscola e proseguire con numeri, lettere minuscole e _
	 * 
	 * @param string $action stringa col nome dell'azione
	 * @return boolean TRUE se il valore fornito è corretto, FALSE altrimenti
	*/
	public function checkActionName($action) {
		return (preg_match('/^[a-z][a-z0-9_]*[^_]$/', $action) == 1);
	}
	
	/**
	 * Data una URL ne aggiorna modulo e azione, verifica che i valori
	 * siano corretti.
	 * 
	 * Non applica il routing.
	 * 
	 * @param URL $url istanza di URL da aggiornare
	 * @param string $module stringa col modulo
	 * @param string $action stringa con l'azione (se viene fornita una stringa vuota non aggiorna l'azione)
	 * @return boolean TRUE se tutto ok, FALSE se uno dei parametri non è valido
	*/
	public function updateURL(URL $url, $module, $action = '') {
		if ($this->checkModuleName($module)) {
			if (!empty($action)) {
				if (!$this->checkActionName($action)) return FALSE;
			}
			$url->set( $this->getRequestModuleName(), $module );
			$url->set( $this->getRequestActionName(), $action );
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Data una istanza di URL produce una stringa con la url, pronta per l'utilizzo
	 * tipicamente nel View.
	 * 
	 * Permette di generare già una URL pronta per una stirnga XHTML, convertendo
	 * le entità (ad esempio & in &amp;)
	 * 
	 * Questo metodo va modificato se ad esempio si vogliono fornire delle url
	 * formate in modo particolare quando si usa il rewriting del web server.
	 * Ad esempio per passare da http://foo/index.php?action=view&get_id=23 a
	 * qualcosa tipo http://foo/view/23
	 * 
	 * É possibile passare parametri extra per la creazione della URL usando
	 * il parametro $extra_params, che è un array associativo.
	 * 
	 * @param URL $url l'istanza di URL da utilizzare
	 * @param boolean $as_html TRUE ritorna una stringa da usare come attributo in un tag xhtml, FALSE solo stringa
	 * @param boolean $with_session TRUE acclude il SID di sessione, FALSE non acclude il SID di sessione
	 * @param array $extra_params array associativo con parametri addizionali
	 * @return string la stringa con la URL
	*/
	public function link(URL $url, $as_html = FALSE, $with_session = TRUE, $extra_params = array()) {
		if ($with_session) WebApp::getInstance()->getSessionManager()->toURL($url);
		$h = $url->getHost();
		$params = $url->getParamString($as_html);
		if (!empty($params)) $params = '?'.$params;
		if ($as_html) $h = htmlentities($h);
		$anchor = $url->getAnchor();
		if (!empty($anchor)) $anchor = '#'.$anchor;
		$pi = $url->getPathInfo();
		if (!empty($pi)) {
			if ($pi[0] != '/') $pi = '/'.$pi;
			if ($as_html) $pi = htmlentities($pi);
		}
		return $h.$pi.$params.$anchor;
	}
	
	/**
	 * Ritorna una URL per accedere alle pagine di stato (di errore) HTTP.
	 * 
	 * Sono pagine che hanno come modulo 'error_XXX', dove XXX è il codice
	 * di stato/errore HTTP.
	 * 
	 * @param integer $http_status_code codice di errore HTTP
	 * @return URL istanza di URL
	*/
	public function getHTTPStatusURL($http_status_code) {
		return $this->getURL($this->getHTTPStatusDestination($http_status_code));
	}
	
	/**
	 * Ritorna una destinazione per accedere alle pagine di stato (di errore) HTTP.
	 * Sono pagine che hanno come modulo 'error_XXX', dove XXX è il codice
	 * di stato/errore HTTP.
	 * 
	 * @param integer $http_status_code codice di errore HTTP
	 * @return string una stringa con la destinazione
	*/
	public function getHTTPStatusDestination($http_status_code) {
		return 'error_'.strval($http_status_code);
	}
	
	/**
	 * Verifica se la URL indichi uno stato (di errore) HTTP.
	 * 
	 * @param URL $url la url da verificare
	 * @return boolean TRUE se la URL indica uno stato HTTP, FALSE altrimenti
	 */
	public function isHTTPSTatusURL(URL $url) {
		return (preg_match('/^error_\d{3}$/', $url->get($this->getRequestModuleName())) == 1);
	}
	
	/**
	 * Ritorna la destinazione di default quando l'utente non indica nessun modulo.
	 * 
	 * Viene usata dal front controller per indirizzare la navigazione verso una
	 * determinata pagina. Ad esempio se l'utente richiama http://foo/index.php
	 * si dovrebbe fare in modo da puntare alla destinazione interna 'home', 
	 * per andare direttamente al modulo 'home' che sarà la home page del sito.
	 * 
	 * Normalmente punta alla pagina di errore 404 non trovato.
	 *
	 * @return string una stringa con la destinazione di default
	*/
	public function getDefaultDestination() {
		return $this->getHTTPStatusDestination(404);
	}
	
	/**
	 * Ritorna la URL di default: è la url che viene mostrata quando nella richiesta
	 * HTTP non è specificato un modulo.
	 * 
	 * Viene applicato il routing in quanto usa {@link getURL}.
	 * 
	 * Utilizza {@link getDefaultDestination} per conoscere la destinazione
	 * @return URL istanza di URL
	 * @see getURL
	 * @see getDefaultDestination
	*/
	public function getDefaultURL() {
		return $this->getURL( $this->getDefaultDestination() );
	}
	
	/**
	 * Data una URL (validata) ritorna la stringa con la destinazione.
	 * 
	 * Alla URL non viene applicato il routing.
	 * 
	 * @param URL $url istanza di URL su cui operare
	 * @param boolean $without_session TRUE non includere gli ID della sessione, FALSE includi la sessione
	 * @return string|boolean una stringa con la destinazione o FALSE se l'url non è valida,
	*/
	public function URLtoDestination(URL $url, $without_session = TRUE) {
		if ( $this->isValidURL($url) ) {
			$url2 = clone $url;
			if (is_object($this->webapp)) $this->webapp->getSessionManager()->removeFromURL( $url2 );
			
			$dest = '';
			$module = '';
			$action = '';
			$this->getModuleAndActionFromURL($url, $module, $action);
			if (!empty($action)) $action = '/'.$action;
			$dest .= $module.$action;
			
			$url2->remove( $this->getRequestModuleName() );
			$url2->remove( $this->getRequestActionName() );
			// Mette i parametri, esclude modulo e azione
			$params = $url2->getParamString(FALSE);
			if (!empty($params)) $params = '?'.$params;
			$dest .= $params;
			$anchor = $url->getAnchor();
			if (!empty($anchor)) $dest .= '#'.$anchor;
			return $dest;
		}
		return FALSE;
	}
	
	/**
	 * Dato un oggetto URL ritorna una stringa URL da usare come azione in una Form.
	 * 
	 * Applica il ruoting alla URL in ingresso.
	 * 
	 * @param URL $url l'oggetto URL su cui operare
	 * @return string la stringa con la URL
	 */
	public function getFormAction(URL $url, $as_html = TRUE) {
		$u = clone $url;
		$this->applyRouting($u);
		if ($as_html) return htmlspecialchars($u->getHost());
		else $u->getHost();
	}
}

