<?php

/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestisce la richiesta HTTP, inglobandola in un singleton.
 *
 * Le variabili di una richiesta (i parametri) sono sempre delle stringhe
 * o degli array di stringhe.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 **/
class Request {

	const
		HTTP_UNKNOW_REQUEST = 0,
		HTTP_NONE = 1, // Lo script viene eseguito via CLI
		HTTP_POST_REQUEST = 10, // Costante che indica una richiesta POST
		HTTP_GET_REQUEST = 20; // Costante che indica una richiesta GET

	protected
		$path_info = '', // Path info della richiesta
		$req_data = NULL, // conserva i dati ripuliti della richiesta
		$request_method = NULL,
		$port, // porta usata per la richiesta
		$host, // host che ha risposto alla richiesta
		$is_secure, // indica se la richiesta sia su un canale sicuro (crittato)
		$script_dir, // directory dello script
		$script, // script richiesto
		$server_ip, // indirizzo ip del server
		$protocol; // protocollo utilizzato (http o https)

	protected static
		$request = NULL; // Il Singleton

	protected function __construct() {
		if (strncasecmp(PHP_SAPI, 'cli', 3) == 0) {
			$this->request_method = self::HTTP_NONE;
			
			$this->port = NULL;
			$this->host = '';
			$this->is_secure = FALSE;
			$this->server_ip = '';
			$this->protocol = 'file';
			
			$this->req_data = array();
			foreach($_REQUEST as $k => $v) {
				if (get_magic_quotes_gpc()) {
					if (is_array($v)) {
						$this->req_data[$k] = $v;
						array_walk_recursive($this->req_data[$k], array($this, 'stripper'));
					} else {
						$this->req_data[$k] = stripslashes($v);
					}
				} else {
					$this->req_data[$k] = $v;
				}
			}
			
			// Ricava il path dello script lanciato
			$p = strrpos($_SERVER['SCRIPT_NAME'], '/');
			if ($p !== FALSE) {
				$this->script = substr($_SERVER['SCRIPT_NAME'], $p + 1);
				$this->script_dir = substr($_SERVER['SCRIPT_NAME'], 0, $p + 1);
			} else {
				$this->script = $_SERVER['SCRIPT_NAME'];
				$this->script_dir = '/';
			}
			
			if (array_key_exists('PATH_INFO', $_SERVER)) $this->path_info = $_SERVER['PATH_INFO'];
			else $this->path_info = '';
			
		} else {
			// Popola i parametri
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->request_method = self::HTTP_POST_REQUEST;
			} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$this->request_method = self::HTTP_GET_REQUEST;
			} else {
				$this->request_method = self::HTTP_UNKNOW_REQUEST;
			}

			// Prende sempre i valori da $_REQUEST, ma solo quelli
			// relativi a $_GET e $_POST
			$values = array_merge( array_keys($_GET), array_keys($_POST) );
			$this->req_data = array();
			foreach($values as $k) {
				if (get_magic_quotes_gpc()) {
					if (is_array($_REQUEST[$k])) {
						$this->req_data[$k] = $_REQUEST[$k];
						array_walk_recursive($this->req_data[$k], array($this, 'stripper'));
					} else {
						$this->req_data[$k] = stripslashes($_REQUEST[$k]);
					}
				} else {
					$this->req_data[$k] = $_REQUEST[$k];
				}
			}

			// Ricostruisce la query di richiesta di questo script
			$this->port = $_SERVER['SERVER_PORT'];
			$this->host = $_SERVER['SERVER_NAME'];
			$this->server_ip = $_SERVER['SERVER_ADDR'];

			// Ricava il path dello script lanciato
			$p = strrpos($_SERVER['SCRIPT_NAME'], '/');
			if ($p !== FALSE) {
				$this->script = substr($_SERVER['SCRIPT_NAME'], $p + 1);
				$this->script_dir = substr($_SERVER['SCRIPT_NAME'], 0, $p + 1);
			} else {
				$this->script = $_SERVER['SCRIPT_NAME'];
				$this->script_dir = '/';
			}

			// Verifica il protocollo
			$this->protocol = 'http';
			$this->is_secure = FALSE;
			if (isset($_SERVER['HTTPS'])) {
				// Potrebbe essere una richiesta https
				if (strlen($_SERVER['HTTPS']) > 0) {
					// IIS setta il valore ad 'off' se NON si sta usando HTTPS
					if (strcasecmp($_SERVER['HTTPS'], 'off') != 0) {
						$this->protocol .= 's';
						$this->is_secure = TRUE;
					}
				}
			}

			if (array_key_exists('PATH_INFO', $_SERVER)) $this->path_info = $_SERVER['PATH_INFO'];
			else $this->path_info = '';
		}
	}

	/**
	 * Ritorna una stringa contenente il percorso fornito dal client.
	 *
	 * Dal manuale di PHP:
	 *
	 * Contains any client-provided pathname information trailing the actual script filename
	 * but preceding the query string, if available.
	 * For instance, if the current script was accessed via the URL
	 * http://www.example.com/php/path_info.php/some/stuff?foo=bar,
	 * then $_SERVER['PATH_INFO'] would contain /some/stuff.
	 *
	 * @return string il path
	 */
	public function getPathInfo() {
		return $this->path_info;
	}

	/**
	 * Imposta il path info.
	 *
	 * @param string $path_info il path info
	 * @see getPathInfo
	 */
	public function setPathInfo($path_info) {
		$this->path_info = strval($path_info);
	}

	/**
	 * Ritorna la porta della richiesta HTTP/HTTPS
	 * @return string una stringa con la porta, di solito '80'
	*/
	public function getPort() {
		return $this->port;
	}

	/**
	 * Ritorna l'indirizzo dell'host che ha gestito la richiesta
	 * @return string una stringa con l'host
	*/
	public function getHost() {
		return $this->host;
	}

	/**
	 * Informa se la richiesta sia avvenuta usando un protocollo sicuro
	 * @return boolean TRUE se la richiesta è sicura, FALSE altrimenti
	*/
	public function isSecure() {
		return $this->is_secure;
	}

	/**
	 * Ritorna il nome dello script che è stato indicato nella richiesta.
	 * 
	 * @return string una stringa col nome dello script/pagina
	*/
	public function getScript() {
		return $this->script;
	}
	
	/**
	 * Ritorna il percorso da cui viene lanciato lo script.
	 * 
	 * Il percorso è nella forma: "/path1/path2/" (inizia e termina
	 * con uno slash).
	 * 
	 * @return string il percorso dello script
	 */
	public function getScriptDir() {
		return $this->script_dir;
	}

	/**
	 * Ritorna l'indirizzo IP del server che ha gestito la richiesta.
	 * @return stringa una stringa con l'indirizzo ip (xxx.xxx.xxx.xxx)
	*/
	public function getServerIP() {
		return $this->server_ip;
	}

	/**
	 * Ritorna la stringa usata nella richiesta HTTP, senza i parametri
	 * @param boolean $with_script TRUE aggiunge anche lo script lanciato, FALSE solo l'indirizzo base
	 * @return string l'URL desunta dalla richiesta
	*/
	public function getRequestURI($with_script = TRUE) {
		return $this->protocol.'://'.$this->host.($this->port != '80' ? ":".$this->port : '').$this->script_dir.($with_script ? $this->script : '');
	}

	// Usata internamente da populateByRequestMethod
	private function stripper(&$item, $key) {
		$item = stripslashes(strval($item));
	}

	/**
	 * Ritorna il tipo di richiesta HTTP effettuato dall'utente,
	 * se POST o GET
	 *
	 * Il tipo di richiesta supportato è dato dalle costanti:
	 * HTTP_POST_REQUEST - Richiesta di tipo POST
	 * HTTP_GET_REQUEST - Richiesta di tipo GET
	 *
	 * @return integer tipo della richiesta
	*/
	public final function getMethod() {
		return $this->request_method;
	}

	/**
	 * Ritorna il valore del parametro
	 *
	 * Ritorna la stringa o l'array di stringhe associato al parametro, oppure
	 * NULL se il parametro non esiste.
	 *
	 * @param string $key una stringa con il parametro da estrarre
	 * @return string|array il valore del parametro o NULL se la chiave non esiste
	*/
	public function get($key) {
		if (array_key_exists($key, $this->req_data)) {
			return $this->req_data[$key];
		} else {
			return NULL;
		}
	}

	/**
	 *
	 * Imposta/modifica il valore di un parametro della richiesta.
	 *
	 * Tenere presente che {@link get} ritorna NULL per i parametri
	 * non impostati. Settare solo valori di tipo stringa o array
	 * di stringhe, come una richiesta HTTP normale
	 *
	 * @param string $key stringa col nome del parametro
	 * @param mixed $value valore da impostare per il parametro
	 */
	public function set($key, $value) {
		$this->req_data[$key] = $value;
	}

	/**
	 * Verifica se esista un parametro nella richiesta HTTP elaborata e ripulita
	 *
	 * @param string $key stringa col nome del parametro da verificare
	*/
	public function hasParameter($key) {
		return (array_key_exists($key, $this->req_data));
	}


	/**
	 * Ritorna tutti i dati della richiesta HTTP ripuliti.
	 *
	 * Ritorna un array associativo, parametro = valore.
	 *
	 * @return array un array di stringhe
	*/
	public function getAll() {
		return $this->req_data;
	}

	/**
	 * Ritorna il singleton per la richiesta
	 *
	 * @return Request il singleton di Request
	*/
	public static function getInstance() {
		if (!is_object(self::$request)) {
			self::$request = new Request();
		}
		return self::$request;
	}

	/**
	 * Ritorna tutti i parametri presenti nella richiesta HTTP.
	 *
	 * Ritorna solo la chiave, non il suo valore.
	 * @return array array di stringhe con le chiavi
	*/
	public final function getAllParameterKeys() {
		return array_keys($this->req_data);
	}

	/**
	 * Verifica se un cookie esista.
	 *
	 * @param string $name stringa col nome del cookie
	 * @return boolean TRUE se il cookie esiste, FALSE altrimenti
	*/
	public function hasCookie($name) {
		return array_key_exists($name, $_COOKIE);
	}

	/**
	 * Ritorna il valore di un cookie.
	 *
	 * @return mixed NULL se il cookie non esiste, altrimenti una stringa col valore del cookie
	*/
	public function getCookie($name) {
		if (array_key_exists($name, $_COOKIE)) {
			return $_COOKIE[$name];
		}
		return NULL;
	}

	/**
	 * Data la richiesta HTTP attuale genera una URL che ne è una replica quanto più fedele
	 *
	 * @return URL istanza di URL
	*/
	public function makeURL() {
		$u = new URL( $this->getRequestURI() );
		foreach($this->req_data as $k => $v) $u->set($k, $v);
		$u->setPathInfo($this->path_info);
		return $u;
	}

	/**
	 * Dato il metodo di una richiesta ritorna una rappresentazione testuale
	 *
	 * @param integer $method il valore di una delle costanti Request::HTTP_*_REQUEST
	 * @return string una stringa che descrive il metodo, stringa vuota se metodo sconosciuto
	*/
	public static function methodToString($method) {
		switch($method) {
			case self::HTTP_POST_REQUEST: return 'POST';
			case self::HTTP_GET_REQUEST: return 'GET';
			default:
				return '';
		}
	}

	/**
	 * Verifica se una Form sia stata attivata
	 *
	 * Verifica se la richiesta corrisponda al metodo di invio della form e se
	 * sia presente nella richiesta uno dei suoi attivatori.
	 *
	 * Un attivatore è una variabile col nome di uno dei pulsanti di submit.
	 *
	 * @param integer $method metodo di invio della form, può essere HTTP_GET_REQUEST o HTTP_POST_REQUEST
	 * @param array $activators array di stringhe con i nomi degli attivatori
	 * @return string FALSE se la form non è stata attivata, altrimenti una stringa col nome dell'attivatore.
	*/
	public function getFormActivator($method, $activators) {
		if ($this->request_method == $method) {
			$i = array_intersect($activators, array_keys($this->req_data));
			if (count($i) == 1) {
				return array_shift($i);
			}
		}
		return FALSE;
	}

	/**
	 * Informa se una form sia stata attivata
	 *
	 * @param integer $method metodo di invio della form, può essere HTTP_GET_REQUEST o HTTP_POST_REQUEST
	 * @param array $activators array di stringhe con i nomi degli attivatori
	 * @return boolean TRUE se la form è stata attivata, FALSE altrmenti
	 *
	 * @see getFormActivator
	 */
	public function formIsSubmitted($method, $activators) {
		return ($this->getFormActivator($method, $activators) !== FALSE);
	}
}

