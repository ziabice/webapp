<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Mostra i dati paginati in una tabella
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class TabularPagerView extends PagerView {
	protected
		$show_headers = TRUE,
		/**
		 * @var HashMap le colonne definite
		 */
		$columns;

	/**
	 * Inizializza il View
	 *
	 * Utilizza initializeColunns per aggiungere le colonne di visualizzazione
	 *
	 * Permette di disegnare un selettore di elementi, che di solito è una checkbox
	 * che ha per valore l'ID degli elementi estratti dal Model.
	 *
	 * Permette di associare un gestore di stili CSS, nel caso non venisse passato,
	 * utilizza {@link setDefaultCSS} per inizializzarne uno di default.
	 *
	 * @param CSS $css indica il gestore di stili CSS
	 * @param boolean $with_selector TRUE mostra un selettore di elementi, FALSE non mostrare niente
	 * @param string $selector_name nome dell'eventuale selettore, ossia il nome della variabile HTTP
	 * @see initializeColumns
	*/
	public function __construct($with_selector = FALSE, $selector_name = '', $show_navigation_anchors = self::SHOW_BOTH_NAV_ANCHORS) {
		$this->columns = new HashMap();
		$this->show_headers = TRUE;
		parent::__construct($with_selector, $selector_name, $show_navigation_anchors);
		$this->initializeColumns();
	}
	
	/**
	 * Eseguito dal costruttore: aggiunge le colonne
	 * di visualizzazione. A tale scopo utilizzare il metodo addColumn
	 * */
	protected function initializeColumns() {}

	/**
	 * Mostra o nasconde le intestazioni delle tabella
	 * @param boolean $show_hide TRUE mostra le intestazioni, FALSE non mostrare
	 * */
	public function setHeadersVisibility($show_hide) {
		$this->show_headers = $show_hide;
	}
	
	/**
	 * Informa se debba mostrare o meno le intestazioni di tabella
	 * @return boolean TRUE mostra le intestazioni, FALSE non mostrare
	 * */
	public function showHeaders() {
		return $this->show_headers;
	}
	
	/**
	 * Ritorna una hashmap con le colonne definite.
	 * 
	 * 
	 * 
	 * @return HashMap le colonne definite
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * Aggiunge una colonna di visualizzazione, con intestazione
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
	*/
	public function addColumn($name, $header, $content_handler = NULL, $visible = TRUE, $header_handler = NULL, $is_selector_column = FALSE) {
		$this->columns->set($name, new TabularPagerViewColumn($name, $header, $content_handler, $is_selector_column, $visible, $header_handler) );
	}
	
	/**
	 * Aggiunge una colonna speciale di tipo selettore
	 * 
	 * Questa colonna conterrà il selettore dei dati.
	 *
	 * La stampa della colonna è gestita dal metodo renderSelectorColumn.
	 *
	 * @param string $name identificativo univoco della colonna
	 * @param string $header codice XHTML con la testata della colonna
	 * @param callback $header_handler callback che genera il contenuto della testata
	 * @see renderSelectorColumn
	 **/
	public function addSelectorColumn($name, $header, $header_handler = NULL, $visible = TRUE) {
		$this->addColumn($name, $header, NULL, $visible, $header_handler, TRUE);
	}
	
	/**
	 * Rimuove la colonna definita (se esiste)
	 * @param string $name string nome della colonna da rimuovere
	 * */
	public function delColumn($name) {
		if ($this->columns->hasKey($name)) $this->columns->del ($name);
	}
	
	/**
	 * Mostra/nasconde una colonna
	 * 
	 * @param string $name identificativo della colonna
	 * @param boolean $show TRUE mostra la colonna, FALSE la nasconde
	*/
	public function showColumn($name, $show) {
		if ($this->columns->hasKey($name)) $this->columns->get($name)->setVisibility((bool)$show);
	}
	
	
}
