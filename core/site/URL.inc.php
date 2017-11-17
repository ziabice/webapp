<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Astrae una URL.
 *
 * Un oggetto URL mantiene un array associativo di parametri e valori, che verrà
 * usato per generare l'indirizzo completo.
 *
 * E' possibile agganciare una descrizione alla url.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class URL extends ObjectConnectAdapter {
	protected
		$path_info = '', // Path info
		$host = '', // URI dell'host a cui punta l'indirizzo
		$params = array(), // Parametri aggiuntivi da aggiungere all'URI, nella forma key => value 
		$title = '', // Testo del tooltip nella rappresentazione dell'ancora ipertestuale
		$label = '', // Etichetta nella rappresentazione dell'ancora ipertestuale
		$accesskey = '', // Tasto di accesso rapido nella rappresentazione dell'ancora ipertestuale
		$anchor = '', // Ancora a cui saltare
		$generator = NULL; // eventuale oggetto dal quale è stata generata questa URL

	/**
	 * Costruisce una URL
	 * 
	 * @param string $host L'host completo dell'url (proto://dir/dir/file.ext)
	 * @param string $anchor stringa con l'ancora a cui saltare (La parte #ancora di http://destinazione#ancora)
	*/
	public function __construct($host = '', $anchor = '') {
		$this->setAnchor($anchor);
		$this->setHost($host);
	}
	
	/**
	 * Factory method: crea un nuovo oggetto applicando anche una descrizione
	 * 
	 * @param string $host L'host completo dell'url (proto://dir/dir/file.ext)
	 * @param string $anchor stringa con l'ancora a cui saltare (La parte #ancora di http://destinazione#ancora)
	 * @param string $label etichetta (testo semplice, non HTML) da mostrare per l'URL
	 * @param string $title titolo (testo semplice, non HTML) da mostrare per l'URL
	 * @param char $accesskey eventuale tasto di accesso rapido per l'etichetta
	 * @return URL istanza di URL
	*/
	public static function newWithDescription($host = '', $anchor = '', $label = '', $title = '', $accesskey = '') {
		$u = new URL($host, $anchor);
		$u->describe($label, $title, $accesskey);
		return $u;
	}

	/**
	 * Verifica se l'URL generata sarà assoluta (comprende un host completo)
	 * 
	 * @return boolean TRUE se l'url generata è assoluta, FALSE se relativa
	*/
	public function isAbsolute() {
		return (strlen(trim($this->host)) > 0);
	}

	/**
	 * Descrive una URL
	 * 
	 * Permette di agganciare una descrizione ad una URL in modo che al momento
	 * della generazione dell'ancora HTML venga utilizzato tale testo
	 * 
	 * @param string $label etichetta (testo semplice, non HTML) da mostrare per l'URL
	 * @param string $title titolo (testo semplice, non HTML) da mostrare per l'URL
	 * @param char $accesskey eventuale tasto di accesso rapido per l'etichetta
	 **/
	public function describe($label = '', $title = '', $accesskey = '') {
		$this->label = $label;
		$this->title = $title;
		$this->accesskey = $accesskey;
		$this->touch();
	}

	/**
	 * Ritorna l'ancora impostata per questa URL
	 *
	 * @return string una stringa con l'ancora di testo
	*/
	public function getAnchor() {
		return $this->anchor;
	}
	
	/**
	 * Imposta l'ancora per questa URL
	 * 
	 * @param string $txt stringa con l'ancora
	*/
	public function setAnchor($txt) {
		$this->anchor = strval($txt);
		$this->touch();
	} 

	/**
	 * Ritorna l'etichetta (testo semplice, non xhtml) da mostrare per l'URL
	 * 
	 * @return string stringa con l'etichetta
	*/
	public function getLabel() { 
		return $this->label; 
	}
	
	/**
	 * Ritorna il titolo (testo semplice, non xhtml) da mostrare per l'URL (di solito in un tooltip)
	 * @return string stringa col titolo
	*/
	public function getTitle() { 
		return $this->title; 
	}
	
	/**
	 * Ritorna il tasto di accesso rapido da mostrare per l'URL
	 *
	 * @return char carattere che rappresenta il tasto
	*/
	public function getAccessKey() { 
		return $this->accesskey; 
	}
	
	/**
	 * Imposta il testo (un testo non html) da mostrare per l'URL.
	 * 
	 * @param string $txt una stringa di testo
	*/
	public function setLabel($txt) { 
		$this->label = $txt;
		$this->touch();
	}
	
	/**
	 * Imposta il titolo (un testo non html) da mostrare per l'URL.
	 * 
	 * E' il testo dei tooltip
	 *
	 * @param string $txt una stringa di testo
	*/
	public function setTitle($txt) { 
		$this->title = $txt;
		$this->touch();
	}
	
	/**
	 * Imposta il tasto di accesso rapido per l'URL
	 * @param char $key un carattere
	*/
	public function setAccessKey($key) { 
		$this->accesskey = $key;
		$this->touch();
	}
	
	/**
	 * Informa se l'oggetto ha un tasto di accesso rapido assegnato
	 *
	 * @return boolean TRUE se ha un tasto di accesso rapido associato, FALSE altrimenti
	*/
	public function hasAccessKey() {
		return (strlen($this->accesskey) > 0);
	}

	/**
	 * Esegue il parsing di una stringa contenete una url e
	 * ritorna il corrispondete oggetto URL
	 * 
	 * @param string $url stringa con la URL da cui costruire l'oggetto
	 * @param string $label etichetta (no xhtml) da mostrare
	 * @param string $title titolo (no xhmtl) da mostrare (nei tooltip)
	 * @param char $accesskey tasto di accesso rapido
	 * 
	 * @return URL l'istanza dell'oggetto URL creata o NULL se la stringa in ingresso non è valida
	 *
	 * FIXME: potrebbe essere implementata usando parse_url
	*/
	public static function newFromString($url, $label = '', $title = '', $accesskey = '') {
		$p = array();
		if (preg_match('#^((\w+)://)?([^/]+)/?(([^/]*/)*)(.*)#', $url, $p) != 0) {
			$u = new URL($p[1].$p[3].'/'.$p[4]);
			$res = array();
			if (preg_match('/^([^?]*)(\??(([^=]+=[^&]*\&?)+)+)?/', $p[6], $res) != 0) {
				$u->host .= $res[1]; // Aggiunge la risorsa
				if (array_key_exists(3, $res)) {
					$params = array();
					preg_match_all('/(([^=]+)=([^&]*))&?/', $res[3], $params);
					foreach($params[2] as $k => $pname) $u->set($pname, $params[3][$k]);
				}
			}
			$u->describe($label, $title, $accesskey);
			return $u;
		} else {
			return NULL;
		}
	}

	/**
	 * Aggiunge o imposta di nuovo il valore di un parametro della URL.
	 * 
	 * @param string $key parametro da impostare
	 * @param string|array $value valore (può essere anche un array monodimensionale)
	*/
	public function set($key, $value) {
		$this->params[$key] = $value;
		$this->touch();
	}

	/**
	 * Ritorna il valore associato al parametro della URL
	 * 
	 * @param string $key stringa col nome del parametro
	 * @return mixed NULL se la variabile non esiste, altrimenti il valore
	*/
	public function get($key) {
		if (array_key_exists($key, $this->params)) {
			return $this->params[$key];
		} else {
			return NULL;
		}
	}

	/**
	 * Aggiunge parametri ed il loro valore da un array.
	 *
	 * Dato un array di coppie parametro, valore, ad esempio:
	 * 
	 * <code>
	 * array( 'nome', 'Luca', 'eta', 20 );
	 * </code>
	 * 
	 * Aggiunge tali parametri alla richiesta generata nella URL:
	 * 
	 * http://host/pagina.php?nome=Luca&eta=20
	 * 
	 * @param array $data un array contenente chiavi e valori
	*/
	public function setMany($data) {
		$data = array_chunk($data, 2);
		foreach($data as $p) $this->set($p[0], $p[1]);
	}
	
	/**
	 * Rimuove il parametro dalla URL
	 * 
	 * @param string $key stringa contenente il parametro che si vuole rimuovere
	*/
	public function remove($key) {
		if (array_key_exists($key, $this->params)) {
			unset($this->params[$key]);
			$this->touch();
		}
	}

	/**
	 * Ripulisce l'oggetto di tutte le associazioni parametro = valore
	*/
	public function clear() {
		$this->params = array();
		$this->touch();
	}

	/**
	 * Imposta l'host (URI)
	 * 
	 * @param string $host una stringa con l'URI
	*/
	public function setHost($host) { 
		$this->host = $host;
		$this->touch();
	}
	
	/**
	 * Ritorna l'host (URI)
	 * 
	 * @return string una stringa con l'URI
	*/
	public function getHost() { 
		return $this->host; 
	}

	/**
	 * Ritorna l'oggetto che ha generato questa URL
	 * 
	 * @return object NULL o l'istanza dell'oggetto che ha generato questa URL
	*/
	public function getGenerator() { 
		return $this->generator; 
	}
	
	/**
	 * Imposta l'oggetto che ha generato questa URL
	 * 
	 * @param object $generator una istanza di classe
	*/
	public function setGenerator($generator) {
		$this->generator = $generator;
		$this->touch();
	}

	/**
	 * Ritorna l'array di associazioni parametro = valore
	 * @return array un array associativo che ha come chiave il parametro 
	*/
	public function getParams() {
		return $this->params;
	}
	
	/**
	 * Copia tutti i parametri della URL passata come parametro.
	 * 
	 * Eventualmente copia anche l'host.
	 * La copia è incrementale: i parametri vengono aggiunti a quelli già presenti.
	 * Nel caso ci siano parametri uguali, quelli dell'oggetto sorgente sovrascrivono quelli correnti.
	 * 
	 * @param URL $source istanza di URL da cui copiare
	 * @param boolean $copy_all TRUE copia anche le informazioni relative all'host, FALSE solo i parametri
	*/
	public function copy(URL $source, $copy_all = TRUE) {
		$this->params = array_merge($this->params, $source->getParams());
		if ($copy_all) {
			$this->host = $source->getHost();
			$this->anchor = $source->getAnchor();
			$this->path_info = $source->getPathInfo();
		}
		$this->touch();
	}
	
	/**
	 * Informa se nella URL sia presente il parametro indicato
	 * 
	 * @param string $name stringa col nome del parametro
	 * @return boolean TRUE se c'è il parametro, FALSE altrimenti
	*/
	public function hasParam($name) {
		return array_key_exists($name, $this->params);
	}
	
	/**
	 * Ritorna i parametri in maniera "flat", come se fosse una stringa HTML di
	 * coppie parametro=valore da utilizzare in una URL testuale.
	 * 
	 * @param boolean $as_html TRUE ritorna sostituendo i caratteri con entità HTML, FALSE ritorna la stringa così com'è
	 * @return string una stringa coi parametri
	*/
	public function getParamString($as_html = TRUE) {
		$out = array();
		foreach($this->params as $k => $v) {
			if (is_array($v)) {
				foreach($v as $vk => $vv) $out[] = urlencode($k).'['.urlencode($vk).']='.urlencode($vv);
			} else {
				$out[] = urlencode($k).'='.urlencode($v);
			}
		}
		return implode($out, $as_html ? '&amp;' : '&');
	}
	
	/**
	 * Imposta un path info da aggiungere alla URL.
	 * 
	 * @param string $path_info il path info da aggiungere
	 */
	public function setPathInfo($path_info) {
		$this->path_info = $path_info;
	}
	
	/**
	 * Ritorna il path info di questa URL
	 * 
	 * @return string una stringa col path info
	 */
	public function getPathInfo() {
		return $this->path_info;
	}
}
