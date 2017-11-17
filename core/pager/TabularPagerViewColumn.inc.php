<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una colonna di dati di un TabularPagerView.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TabularPagerViewColumn {
	public
		/**
		 * @var boolean indica se la colonna è di tipo "selettore"
		 */
		$is_selector_column,
		/**
		 * @var string nome della colonna
		 */
		$name, 
		/**
		 * @var string etichetta della colonna (nella testata)
		 */
		$header, 
		/**
		 * @var callback callback da usare per presentare i dati della colonna
		 */
		$content_handler = NULL, 
		/**
		 * @var boolean flag di visibilità della colonna
		 */
		$visible = TRUE, 
		/**
		 * @var callback callback da usare per presentare l'etichetta della colonna
		 */
		$header_handler = NULL;
	
	/**
	 * Crea una colonna di visualizzazione, con intestazione
	 *
	 * Ad ogni colonna corrisponde una cella di dati.
	 *
	 * Invece del testo esplicito è possibile specificare una callback che verrà
	 * invocata per generare il contenuto delle colonne e delle rispettive intestazioni.
	 *
	 * Callback dei contenuti di cella:
	 * Viene gestita in modo particolare: può essere o una stringa o
	 * una callback vera e propria.
	 * - Nel caso sia una stringa: se la riga di dati corrente è un oggetto, verifica
	 * che esista un metodo col nome pari a quello indicato nel parametro $content_handler,
	 * se presente lo invoca, convertendo poi il valore di ritorno in XHTML. Questo approccio
	 * è usato per invocare direttamente un metodo getter.
	 * - nel caso invece sia una callback: invoca la callback passandole come parametro la riga
	 * di dati corrente (che normalmente è un oggetto). Presume una callback del tipo:
	 * string callback($obj). Il valore ritornato viene usato direttamente (non viene convertito in XHTML)
	 * come valore della cella.
	 *
	 *
	 * Callback degli header:
	 * E' una callback che deve ritornare una stringa (XHTML) con l'intestazione e accetta
	 * come parametro il nome della colonna che si sta per disegnare:
	 * string callback($header_name)
	 *
	 *
	 * @param string $name identificativo univoco della colonna
	 * @param string $header codice XHTML con la testata della colonna
	 * @param string|callback $content_handler callback che genera il contenuto della colonna
	 * @param boolean $visible TRUE la colonna è visibile, FALSE altrimenti
	 * @param callback $header_handler callback che genera il contenuto della testata
	 * @param boolean $is_selector_column 
	*/
	public function __construct($name, $header, $content_handler = NULL, $is_selector_column = FALSE, $visible = TRUE, $header_handler = NULL) {
		$this->name = $name;
		$this->header = $header;
		$this->content_handler = $content_handler;
		$this->visible = $visible;
		$this->header_handler = $header_handler;
		$this->is_selector_column = $is_selector_column;
	}
	
	/**
	 * Ritorna il nome della colonna.
	 * 
	 * @return string il nome della colonna
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Ritorna il codice XHTML dell'intestazione.
	 * 
	 * @return string il codice XHTML
	 */
	public function getHeader() {
		return $this->header;
	}
	
	/**
	 * Imposta il codice XHTML dell'intestazione.
	 * 
	 * @param string $header il codice XHTML dell'intestazione
	 */
	public function setHeader($header) {
		$this->header = $header;
	}
	
	/**
	 * Ritorna la callback che stampa il contenuto della colonna.
	 * 
	 * @return callback la callback
	 */
	public function getContentHandler() {
		return $this->content_handler;
	}
	
	/**
	 * Informa se sia stato assegnata una callback per la stampa dei contenuti.
	 * 
	 * @return boolean TRUE se ha una callback per la stampa dei contenuti, FALSE altrimenti
	 */
	public function hasContentHandler() {
		return !is_null($this->content_handler);
	}
	
	/**
	 * Imposta la callback di stampa del contenuto della colonna.
	 * 
	 * Il parametro può essere:
	 * stringa: 
	 * - se la riga di dati è un array associativo, utilizza tale chiave per estrarre il valore da cui
	 * generare il codice XHTML della cella
	 * - se la riga di dati è un oggetto ed esiste un metodo con il nome della stringa, invoca tale
	 * metodo ed usa il valore di ritorno per generare il codice XHTML col contenuto della cella
	 * 
	 * callback:
	 * Se viene passata una callback nella forma:
	 * 
	 * string callback($row)
	 * 
	 * allora utilizza il valore ritornato per generare il codice XHTML del contenuto della cella.
	 * 
	 * 
	 * @param string|callback $content_handler il generatore dei contenuti della cella
	 */
	public function setContentHandler($content_handler) {
		$this->content_handler = $content_handler;
	}
	
	/**
	 * Informa se questa colonna sia una speciale di tipo selettore di contenuti.
	 * 
	 * @return boolean TRUE se la colonna è un selettore di contenuti, FALSE altrimenti
	 */
	public function isSelectorColumn() {
		return $this->is_selector_column;
	}
	
	/**
	 * Informa se la colonna sia visibile.
	 * 
	 * @return boolean TRUE se la colonna è visibile, FALSE altrimenti
	 */
	public function isVisible() {
		return $this->visible;
	}
	
	/**
	 * Imposta la visibilità della colonna.
	 * 
	 * @param boolean $is_visible TRUE la colonna è visibile, FALSE la colonna non è visibile
	 */
	public function setVisibility($is_visible) {
		$this->visible = $is_visible;
	}
	
	
	/**
	 * Ritorna la callback che stampa il contenuto dell'a colonna'intestazione.
	 * 
	 * @return callback la callback
	 */
	public function getHeaderHandler() {
		return $this->header_handler;
	}
	
	/**
	 * Informa se sia stato assegnata una callback per la stampa dei contenuti dell'intestazione.
	 * 
	 * @return boolean TRUE se ha una callback per la stampa dei contenuti, FALSE altrimenti
	 */
	public function hasHeaderHandler() {
		return !is_null($this->header_handler);
	}
	
	/**
	 * Imposta la callback per la stampa dell'intestazione.
	 * 
	 * La forma della callback è:
	 * 
	 * string callback($column)
	 * 
	 * deve ritornare il codice XHTML per l'intestazione
	 * 
	 * @param callback $header_handler 
	 */
	public function setHeaderHandler($header_handler) {
		$this->header_handler = $header_handler;
	}
	
}

