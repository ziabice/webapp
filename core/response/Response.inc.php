<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * 
 * Gestisce la risposta del Front Controller ad una richiesta 
 * 
 *
 * E' la risposta che il Front Controller produce in relazione ad una richiesta HTTP.
 * Può essere composta di soli header HTTP o di una pagina (contenuto) XHTML.
 * Permette inoltre di inviare file al client.
 * 
 * L'oggetto Response viene costruito dall'azione ({@link BaseAction}) attuale
 * e passato al front controller, che al termine dell'elaborazione lo esegue lanciando
 * il metodo Response::excute().
 * 
 * Una risposta è composta da:
 * Header HTTP
 * Cookies
 * Corpo della risposta
 * 
 * Il corpo della risposta non è sempre presente, ma quando c'è può essere del testo 
 * (di solito il codice XHTML che compone una pagina) o un file (a patto che gli 
 * header HTTP siano corretti).
 *
 * Per maggiori informazioni riguardo una risposta HTTP fare riferimento all' RFC 2616 (Hypertext Transfer Protocol - HTTP/1.1)
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class Response {

	// Tipi possibili di risposta
	const
		RESPONSE_NONE = 0, // Non manda nulla al browser
		RESPONSE_HEADERS = 1, // Invia solo gli header HTTP
		RESPONSE_COMPLETE = 2, // Invia una risposta completa header HTTP + dati (di solito testo XHTML)
		RESPONSE_PUTFILE = 3, // Invia un file prendendolo dal filesystem
		RESPONSE_PUTFILE_HANDLE = 4, // Invia un file già aperto
		RESPONSE_TEXTDATA = 10 // Invia una risposta completa header HTTP + dati testuali
		;

	protected
		$headers = array(),
		$is_header_only,
		$content = '', // Testo da inviare nella risposta
		$file_handle = NULL, // Handle del file già aperto da inviare all'utente
		$file_to_put = '', // Path del file da inviare all'utente
		$response_type = self::RESPONSE_NONE, // Tipo di risposta data al browser
		$http_status = '', // Stato richiesta HTTP
		$redirection = NULL, // URL a cui fare una redirezione
		$cookies = array(); // I cookies dell'applicazione

	/**
	 * Costruisce un nuovo gestore di risposta.
	 *
	 * La imposta automaticamente sul tipo RESPONSE_COMPLETE.
	 *
	 */
	public function __construct() {
		$this->is_header_only = FALSE;
		$this->cookies = array();
		$this->setResponseType(self::RESPONSE_COMPLETE);
	}

	/**
	 * Imposta il tipo di risposta
	 * 
	 * Sono disponibili i seguenti tipi di risposte:
	 * 
	 * RESPONSE_NONE - Non manda nulla al client
	 * RESPONSE_HEADERS - Invia solo gli header HTTP
	 * RESPONSE_COMPLETE - Invia una risposta completa di header HTTP + corpo (HTML)
	 * RESPONSE_PUTFILE - Invia un file prendendolo dal filesystem
	 * RESPONSE_TEXTDATA - Invia una risposta con header HTTP e dati testuali
	 * 
	 * @param integer $response_type intero corrispondente ad una delle costanti RESPONSE_*
	 *
	 **/
	public final function setResponseType($response_type) {
		$this->response_type = $response_type;
	}

	/**
	 * Ritorna il tipo di risposta da inviare al browser
	 *
	 * Sono disponibili i seguenti tipi di risposte:
	 *
	 * RESPONSE_NONE - Non manda nulla al client
	 * RESPONSE_HEADERS - Invia solo gli header HTTP
	 * RESPONSE_COMPLETE - Invia una risposta completa header HTTP + corpo
	 * RESPONSE_PUTFILE - Invia un file prendendolo dal filesystem
	 * RESPONSE_TEXTDATA - Invia una risposta con header HTTP e dati testuali
	 * 
	 * @return integer un intero con la risposta
	 **/
	public final function getResponseType() {
		return $this->response_type;
	}

	/**
	 * Invia al client gli header HTTP ed i cookie
	 *
	 * Invia al client gli header HTTP ed i Cookie attualmente impostati
	 * nella risposta.
	 *
	 * Nel caso di header HTTP di redirezione (header di tipo "Location") termina
	 * l'elaborazione del metodo
	 *
	 * @see sendCookies
	 */
	protected function sendHeaders() {
		if (!empty($this->http_status)) {
			header('HTTP/1.1 '.$this->http_status);
			header('Status: '.$this->http_status);
		}
		
		// Deve inviare prima i cookies
		$this->sendCookies();
		
		// Invia sempre prima gli header e poi completa a seconda dei casi
		foreach($this->headers as $hk => $hv) {
			header($this->prettyPrintHeader($hk).': '.$hv);
		}
		
		if (!empty($this->redirection)) {
			
			header('Location: '.$this->redirection);
			exit();
		} 
	}

	/**
	 * Invia i cookies impostati nella risposta al client.
	 * 
	 * I cookie vengono creati usando setcookie, per aggiungerne
	 * usare il metodo setCookie.
	 * 
	 * TODO: implementare la creazione di cookies completi
	 *
	 *
	 * @see setCookie
	 * @see sendHeaders
	 */
	protected function sendCookies() {
		// Ho dei cookies, li invia
		foreach($this->cookies as $c) {
			// setcookie($c['name'], $c['value'], $c['expire'], $c['path'], $c['domain'], $c['$secure'], $c['$httponly']);
			setcookie($c['name'], $c['value'], $c['expire'], $c['path'], $c['domain']);
		}
	}
	
	/**
	 * Invia il contenuto al browser.
	 *
	 * Invia il corpo del documento al browser: a seconda del tipo di risposta
	 * invia del testo o un file. Per una risposta corretta, occorre aver prima
	 * inviato gli header HTTP usando sendHeaders.
	 *
	 * Se il tipo di risposta è RESPONSE_COMPLETE invia la stringa
	 * di testo impostata con setContent; 
	 * se il tipo è RESPONSE_PUTFILE invia il file indicato con setFileToPut.
	 * se il tipo è RESPONSE_PUTFILE_HANDLE invia il file impostato con setFileHandle
	 *
	 * @see sendHeaders
	 * @see setContent
	 * @see setFileToPut
	 * @see setFileHandle
	 */
	protected function sendContent() {
		// Controlla se l'azione sia asincrona
		$is_async = FALSE;
		if (is_object(WebApp::getInstance()->getAction())) {
			if (WebApp::getInstance()->getAction()->isAsync()) {
				$is_async = TRUE;
			}
		}
		
		switch($this->response_type) {
			// Invia un file su disco
			case self::RESPONSE_PUTFILE:
				if (@file_exists($this->file_to_put) && @is_file($this->file_to_put) ) {
					$this->closeSession();
					@readfile($this->file_to_put);
				}
			break;
			// Invia un file già aperto
			case self::RESPONSE_PUTFILE_HANDLE: 
				if (@is_resource($this->file_handle)) {
					$this->closeSession();
					if (@rewind($this->file_handle)) {
						@fpassthru($this->file_handle);
					}
				}
			break;
			
			// Risposta completa o testuale (headers + dati)
			case self::RESPONSE_COMPLETE:
			case self::RESPONSE_TEXTDATA:
				if ($is_async) {
					WebApp::getInstance()->getAction()->asyncResponseHandler();
				} else {
					echo $this->content;
				}
			break;
			default:
				// Risposta solo header o sconosciuta, in ogni caso
				// se necessario usa l'azione asincrona
				if ($is_async) {
					WebApp::getInstance()->getAction()->asyncResponseHandler();
				}
		}
	}
	
	/**
	 * Chiude la sessione.
	 * Invia i necessari header e termina la scrittura dei dati di sessione.
	 * */
	public function closeSession() {
		session_cache_limiter("nocache");
		session_write_close();
	}
	
	/**
	 * Invia la risposta completa al browser
	 *
	 * Invia gli header ed il corpo che compongono la risposta.
	 *
	 * @see sendHeaders
	 * @see sendContent
	*/
	public function execute() {
		if ($this->response_type != self::RESPONSE_NONE) {
			$this->sendHeaders();
			
			// Termina la richiesta
			$this->sendContent();
		}
	}

	/**
	 * Ritorna una versione correttamente stampabile dell'header.
	 * 
	 * @param string $header una stringa lowercase dell'header richiesto
	 **/
	private function prettyPrintHeader($header) {
		switch ($header) {
			case 'content-md5': return 'Content-MD5';
			case 'etag': return 'ETag';
			case 'te': return 'TE';
			case 'www-authenticate': return 'WWW-Authenticate';
			default:
				return preg_replace_callback('/\-([a-z])/', function($m){ return '-'.strtoupper($m[1]); }, ucfirst($header) );
		}
	}

	/**
	 * Ritorna il nome del file da passare al browser
	 * 
	 * @return string una stringa col path completo del file
	 * @see setFileToPut
	*/
	public final function getFileToPut() {
		return $this->file_to_put;
	}

	/**
	 * Imposta il nome del file da inviare al client.
	 *
	 * Oltre a settare il nome inizializza tutti gli header HTTP necessari. Verifica
	 * l'esistenza del file per inviare i dati correntti.
	 *
	 * @param string $path_to_file percorso completo del file (sul server)
	 * @param string $outname nome del file che si propone al client
	 * @param string $mimetype tipo MIME del file
	 * @param string $outdesc descrizione del file
	 * @param boolean $inline TRUE invia inline, FALSE come allegato
	 * @return boolean TRUE se tutto ok, FALSE se il file non esiste o non è valido
	 */
	public final function setFileToPut($path_to_file, $outname = '', $mimetype = 'application/octet-stream', $outdesc = '', $inline = FALSE) {
		if (@file_exists($path_to_file) && @is_file($path_to_file)) {
			$this->file_to_put = $path_to_file;
			Logger::getInstance()->debug("Response::setFileToPut: serving: ".$path_to_file);
			if (isset($_SERVER["HTTPS"])) {
				$this->addRawHeader("Pragma: ");
				$this->addRawHeader("Cache-Control: ");
				$this->addRawHeader("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				$this->addRawHeader("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				$this->addRawHeader("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
				$this->addRawHeader("Cache-Control: post-check=0, pre-check=0", FALSE);
			} elseif ($inline) {
				$this->addRawHeader("Cache-control: private");
			} else {
				$this->addRawHeader("Cache-Control: no-cache, must-revalidate");
				$this->addRawHeader("Pragma: no-cache");
			}

			$this->addRawHeader("Content-Type: ".$mimetype);
			$this->addRawHeader("Content-Disposition:".($inline ? 'inline' : 'attachment')."; filename=\"".trim($outname)."\"");
			if(!empty($outdesc)) $this->addRawHeader("Content-Description: ".trim($outdesc));
			$this->addRawHeader("Content-Length: ".@filesize($path_to_file));
			$this->addRawHeader("Connection: close");
			return TRUE;
		} else {
			Logger::getInstance()->debug("Response::setFileToPut: invalid file: ".$path_to_file);
		}
		return FALSE;
	}
	
	/**
	 * Imposta il file handle del file già aperto da inviare al client.
	 *
	 * Inizializza tutti gli header HTTP necessari. 
	 *
	 * @param resource $file_handle handle del file
	 * @param string $outname nome del file che si propone al client
	 * @param string $mimetype tipo MIME del file
	 * @param string $outdesc descrizione del file
	 * @param boolean $inline TRUE invia inline, FALSE come allegato
	 * @return boolean TRUE se tutto ok, FALSE se non può operare
	 */
	public final function setFileHandle($file_handle, $outname = '', $mimetype = 'application/octet-stream', $outdesc = '', $inline = FALSE) {
		$stat = @fstat($file_handle);
		if ($stat === FALSE) {
			Logger::getInstance()->debug("Response::setFileHandle: stat failed.");
			return FALSE;
		}
		
		$this->file_handle = $file_handle;
		Logger::getInstance()->debug("Response: serving file using file handle.");
		if (isset($_SERVER["HTTPS"])) {
			$this->addRawHeader("Pragma: ");
			$this->addRawHeader("Cache-Control: ");
			$this->addRawHeader("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			$this->addRawHeader("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			$this->addRawHeader("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
			$this->addRawHeader("Cache-Control: post-check=0, pre-check=0", FALSE);
		} elseif ($inline) {
			$this->addRawHeader("Cache-control: private");
		} else {
			$this->addRawHeader("Cache-Control: no-cache, must-revalidate");
			$this->addRawHeader("Pragma: no-cache");
		}

		$this->addRawHeader("Content-Type: ".$mimetype);
		$this->addRawHeader("Content-Disposition:".($inline ? 'inline' : 'attachment')."; filename=\"".trim($outname)."\"");
		if(!empty($outdesc)) $this->addRawHeader("Content-Description: ".trim($outdesc));
		$this->addRawHeader("Content-Length: ".strval($stat['size']));
		$this->addRawHeader("Connection: close");
		return TRUE;
	}
	
	/**
	 * Ritorna il file handle associato.
	 * 
	 * @return resource NULL o il file handle impostato
	 */
	public function getFileHandle() {
		return $this->file_handle;
	}

	/**
	 * Imposta il file handle del file già aperto che si vuole inviare al client.
	 *
	 * Non imposta nessun header HTTP, né verifica la validità del file handle.
	 *
	 * @param resource $file_handle il file handle
	 */
	public function setFileHandle2($file_handle) {
		$this->file_handle = $file_handle;
	}

	/**
	 * Imposta il path completo di un file che si vuole inviare al client.
	 *
	 * Non imposta nessun header HTTP, né verifica l'esistenza del file.
	 *
	 * @param string $path il percorso completo del file
	 */
	public function setFileToPutPath($path) {
		$this->file_to_put = $path;
	}

	/**
	 * Inizializza l'oggetto come se fosse stato appena creato.
	 *
	 * Rimuove tutti gli header, i cookie e l'eventuale file da inviare
	 * e imposta il tipo della risposta a RESPONSE_COMPLETE.
	 **/
	public function clear() {
		$this->file_handle = NULL;
		$this->file_to_put = '';
		$this->content = '';

		$this->http_status = '';
		$this->headers = array();
		$this->is_header_only = FALSE;

		$this->setResponseType(self::RESPONSE_COMPLETE);
	}

	/**
	 * Aggiunge lo stato HTTP alla risposta.
	 * 
	 * Permette di specificare nella risposta uno stato HTTP. Utile ad esempio
	 * per inviare una risposta di tipo "404 Not Found" e gestirla via script.
	 * 
	 * @param integer $status un intero con il codice di stato
	 * @param string $text stringa col testo dello stato
	 **/
	public function setHTTPStatus($status, $text) {
		$this->http_status = strval($status).' '.$text;
	}

	/**
	 * Aggiunge un header HTTP alla risposta
	 * 
	 * Riferirsi agli RFC per il formato degli header.
	 * 
	 * @param string $header stringa con l'header completo
	 **/
	public function addRawHeader($header) {
		$p = array();

		if (preg_match('/^([a-z\-]+):\s*(.*)/i', $header, $p) == 1 ) {
			$this->headers[strtolower($p[1])] = $p[2];
		}
	}

	/**
	 * Aggiunge un header alla risposta
	 * 
	 * @param string $header stringa col nome dell'header
	 * @param string $value stringa col valore dell'header
	 *
	 **/
	public function addHeader($header, $value) {
		if (preg_match('/^[a-z\-]+/i', $header) == 1) {
			$this->headers[strtolower($header)] = $value;
		}
	}
	
	/**
	 * Rimuove un header dalla risposta
	 *
	 * @param string $header nome dell'header da rimuovere
	*/
	public function delHeader($header) {
		$header = strtolower($header);
		if (array_key_exists($header, $this->headers)) {
			unset($this->headers[$header]);
		}
		return FALSE;
	}

	/**
	 * Imposta il tipo MIME e l'encoding usati nel testo della risposta
	 * 
	 * Aggiunge un header di tipo "Content-Type" con il tipo MIME e encoding indicato.
	 * 
	 * @param string $type stringa col tipo MIME
	 * @param string $encoding stringa con l'encoding
	 **/
	public function setContentType($type = 'text/html', $encoding = 'utf-8') {
		$this->addHeader('content-type', $type.(strlen($encoding) > 0 ? '; charset='.$encoding : ''));
	}

	/**
	 * Imposta il contenuto testuale della risposta HTTP.
	 * 
	 * Questo testo viene inviato al client solo quando la risposta è di tipo RESPONSE_COMPLETE.
	 * 
	 * @param string $str stringa di testo con la risposta
	 * @see sendContent
	 * @see execute
	 **/
	public function setContent($str) {
		$this->content = $str;
	}

	/**
	 * Imposta gli header in modo che la richiesta sia un redirect HTTP.
	 * 
	 * Il tipo della risposta viene cambiato in RESPONSE_HEADERS.
	 * 
	 * @param string $location stringa con la URL di destinazione del redirect
	 **/
	public function redirect($location) {
		$this->setResponseType(self::RESPONSE_HEADERS);
		$this->redirection = $location;
		// $this->addRawHeader("Location: ".$location);
	}

	/**
	 * Imposta un cookie.
	 *
	 * Il cookie viene salvato internamente ed impostato solo al termine della risposta
	 * quando questa viene inviata al client.
	 *
	 * I parametri sono gli stessi della funzione PHP setcookie (vedi {@link http://www.php.net/manual/en/function.setcookie.php}).
	 *
	 * @param string $name nome del cookie
	 * @param string $value valore del cookie
	 * @param integer $expire timestamp unix della scadenza del cookie
	 * @param string $path percorso sul server nel quale il cookie sarà disponibile
	 * @param string $domain dominio nel quale il cookie è disponibile
	 * @param boolean $secure indica se debba essere trasmesso in modo sicuro (usando HTTPS)
	 * @param boolean $httponly indica se il cookie debba essere accessibile solo via HTTP
	 * @see sendCookies
	 * @see sendHeaders
	 */
	public function setCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE) {
		$this->cookies[$name] = array(
			'name' => $name,
			'value' => strval($value), 
			'expire' => $expire, 
			'path' => $path, 
			'domain' => $domain, 
			'secure' => (bool)$secure, 
			'httponly' => (bool)$httponly
		);
	}

	/**
	 * Rimuove un cookie dalla risposta.
	 * 
	 * Rimuove il cookie da quelli che si stanno per inviare, per rimuovere
	 * un cookie dalla 
	 * 
	 * @param $name nome del cookie da rimuovere
	 * @return TRUE se ha rimosso il cookie, FALSE altrimenti
	 *
	 **/
	public function unsetCookie($name) {
		if (array_key_exists($name, $this->cookies)) {
			unset($this->cookies[$name]);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Fa "scadere" e quindi cancellare il cookie indicato.
	 * 
	 * Con l'invio della risposta il cookie indicato verrà fatto scadere: per fare la sua
	 * data di scadenza viene posta ad un giorno indietro.
	 *
	 * @param string $name nome del cookie da far scadere
	 **/
	public function expireCookie($name) {
		$this->cookies[$name] = array(
			'name' => $name,
			'value' => '', 
			'expire' => time() - 3600, 
			'path' => '', 
			'domain' => '', 
			'secure' => FALSE, 
			'httponly' => FALSE
		);
	}

	/**
	 * Rimuove tutti i cookie dalla risposta.
	 *
	 * Non tocca i cookie della richiesta.
	 **/
	public function clearCookies() {
		$this->cookies = array();

	}

	/**
	 * Ritorna una nuova istanza di Response con una redirezione HTTP
	 *
	 * @param string $location stringa con la destinazione
	 * @return Response
 	 */
	public static function newRedirect($location) {
		$r = new Response();
		$r->redirect($location);
		return $r;
	}
	
	/**
	 * Imposta la risposta come stringa JSON.
	 * 
	 * Metodo di utilità che imposta la risposta per una stringa JSON.
	 */
	public function setJSONResponse() {
		$this->setContentType('application/json');
		$this->setResponseType(self::RESPONSE_TEXTDATA);
	}
	
}
