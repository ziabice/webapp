<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Delegate per il disegno di un oggetto PagerView
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class PagerViewStyle {
	
	const
	// Costanti da utilizzare per definire lo stile
	PAGER_CONTAINER = 'pager_container', // DIV che racchiude il pager

	PAGER_SELECTOR = 'pager_selector', // Stile del selettore
	PAGER_TOP_NAVIGATION = 'pager_top_nav',
	PAGER_BOTTOM_NAVIGATION = 'pager_bottom_nav',
	
	// Stili per le ancore coi numeri di pagina
	PAGER_ITEM = 'pager_item', // Elemento normale
	PAGER_TOP_ITEM = 'pager_top_item', // Elemento normale navigatore superiore
	PAGER_BOTTOM_ITEM = 'pager_bottom_item', // Elemento normale navigatore inferiore
	PAGER_SEL_ITEM = 'pager_sel_item', // Elemento correntemente selezionato (pagina corrente)
	PAGER_TOP_SEL_ITEM = 'pager_top_sel_item', // Elemento correntemente selezionato (navigatore superiore)  	
	PAGER_BOTTOM_SEL_ITEM = 'pager_bottom_sel_item', // Elemento correntemente selezionato (navigatore inferiore)
	
	
	PAGER_ODD_ITEM = 'pager_odd', // Elemento dispari
	PAGER_EVEN_ITEM = 'pager_even', // Elemento pari
	PAGER_FIRST_ROW = 'pager_first', // Prima riga
	PAGER_LAST_ROW = 'pager_last',  // Ultima riga
	PAGER_LAST_ROW_EVEN = 'pager_last_even', // Ultima riga pari
	PAGER_LAST_ROW_ODD = 'pager_last_odd'; // Ultima riga dispari
	
	protected
			/**
			 * @var array testo da stampare per i vari stati
			 */
			$status_txt = array(),
			/**
			 * @var PagerView il view 
			 */
			$pager_view,
			/**
			 * @var CSS gli stili CSS
			 */
			$css = NULL;


	public function __construct() {
		$this->setDefaults();
	}
	
	/**
	 * Imposta i valori di base per questo view.
	 * 
	 * Inizializza i CSS.
	 * Imposta il testo di default per i vari stati.
	 */
	public function setDefaults() {
		if (WebApp::getInstance()->hasPageLayout()) {
			$this->setCSS(WebApp::getInstance()->getPageLayout()->getWidgetStyle()->getCSS($this));
		} else {
			$this->setCSS(new CSS());
		}
		
		$this->status_txt[PagerView::STATUS_IDLE] =  XHTML::p( tr('Immettere dei criteri di ricerca.') ).PHP_EOL;
		$this->status_txt[PagerView::STATUS_MODEL_ERROR] = XHTML::p( tr('Impossibile visualizzare i dati a causa di un errore di accesso al database.')  ).PHP_EOL;
		$this->status_txt[PagerView::STATUS_SHOW_MODEL_DATA] = '';
		$this->status_txt[PagerView::STATUS_NO_MODEL_DATA] = XHTML::p( tr('Non ci sono dati da poter visualizzare.') ).PHP_EOL;
		$this->status_txt[PagerView::STATUS_WRONG_PAGE] = XHTML::p( tr('Impossibile visualizzare i dati richiesti.') ).PHP_EOL;
		$this->status_txt[PagerView::STATUS_INPUT_ERROR] = XHTML::p( tr('I dati immessi non sono validi, impossibile procedere.') ).PHP_EOL;
		$this->status_txt[PagerView::STATUS_POST_PROCESS_ERROR] = XHTML::p( tr('Impossibile visualizzare i dati a causa di un errore di elaborazione.') ).PHP_EOL;
	}
	
	/**
	 * Ritorna il testo associato ad uno stato.
	 * 
	 * Il testo (una stringa XHTML) verrà mostrato quando verrà (dal PagerView) invocato il metodo
	 * relativo ad uno stato.
	 * Gli stati vengono indicati usando le costanti definite nella classe PagerView:
	 * 
	 * 	PagerView::STATUS_IDLE - il pager è dormiente
	 * 	PagerView::STATUS_MODEL_ERROR - Il Model associato al PagerController è in uno stato di errore
	 * 	PagerView::STATUS_SHOW_MODEL_DATA - Mostra i dati del Model (non viene utilizzato, avendo un metodo apposito)
	 * 	PagerView::STATUS_NO_MODEL_DATA - Il Model non ha dati da visualizzare
	 * 	PagerView::STATUS_WRONG_PAGE - si sta cercando di visualizzare una pagina non corretta
	 * 	PagerView::STATUS_INPUT_ERROR - i dati della richiesta HTTP non sono validi
	 * 	PagerView::STATUS_POST_PROCESS_ERROR - I dati ci sono, ma si è verificato un errore prima di poterli utilizzare
	 * 
	 * Se non è stato definito un testo per lo stato viene ritornato FALSE.
	 * 
	 * @param integer $status stato da leggere
	 * @return string una stringa XHTML o il valore booleano FALSE
	 * */
	public function getStatusText($status) {
		return (array_key_exists($status, $this->status_txt) ? $this->status_txt[$status] : FALSE);
	}
	
	/**
	 * Imposta il testo per uno stato del PagerView.
	 * 
	 * Il testo è di solito una stringa XHTML.
	 * Per le costanti di stato riferirsi alla documentazione di {@link getStatusText}.
	 * 
	 * @param integer $status stato da impostare
	 * @param string $text testo da impostare
	 * 
	 * @see getStatusText
	 */
	public function setStatusText($status, $text) {
		$this->status_txt[$status] = $text;
	}
	
	/**
	 * Imposta il gestore di stili CSS.
	 * 
	 * Sono definite le seguenti costanti per le varie zone del pager:
	 * 
	 * PAGER_CONTAINER - il DIV che racchiude il pager
	 * PAGER_SELECTOR - Stile del selettore
	 * PAGER_TOP_NAVIGATION - div del navigatore superiore
	 * PAGER_BOTTOM_NAVIGATION - div del navigatore inferiore
	 * 
	 * Stili delle ancore ipertestuali di navigazione:
	 * 
	 * PAGER_ITEM - Elemento normale
	 * PAGER_TOP_ITEM - Elemento normale navigatore superiore
	 * PAGER_BOTTOM_ITEM - Elemento normale navigatore inferiore
	 * PAGER_SEL_ITEM - Elemento correntemente selezionato (pagina corrente)
	 * PAGER_TOP_SEL_ITEM - Elemento correntemente selezionato (navigatore superiore)  	
	 * PAGER_BOTTOM_SEL_ITEM - Elemento correntemente selezionato (navigatore inferiore) 
	 * 
	 * Stili per le righe di dati:
	 * 
	 * PAGER_ODD_ITEM - Elemento dispari
	 * PAGER_EVEN_ITEM - Elemento pari
	 * PAGER_FIRST_ROW - Prima riga
	 * PAGER_LAST_ROW - Ultima riga
	 * PAGER_LAST_ROW_EVEN - Ultima riga pari
	 * PAGER_LAST_ROW_ODD - Ultima riga dispari
	 * 
	 * @param CSS $css il gestore di stili
	 * @return CSS l'istanza passata come parametro
	 * */
	public function setCSS(CSS $css) {
		$this->css = $css;
		return $css;
	}
	
	/**
	 * Ritorna il gestore di stili associato all'oggetto
	 * 
	 * @return CSS una istanza di CSS
	*/
	public function getCSS() {
		return $this->css;
	}
	
	/**
	 * Ritorna il view associato.
	 * 
	 * @return PagerView il view
	 */
	public function getView() {
		return $this->pager_view;
	}
	
	/**
	 * Imposta il view.
	 * 
	 * @param PagerView2 $v il view
	 * @return PagerView2
	 */
	public function setView(PagerView $v) {
		$this->pager_view = $v;
		return $v;
	}
	
	
	/**
	 * Stampa il tag di apertura del pager
	 * 
	 * Di solito apre un tag div ed usa come stile CSS quello associato alla costante PAGER_CONTAINER
	 * 
	*/
	public function openPager() {
		echo "<div",$this->css->getAttr(self::PAGER_CONTAINER),">\n";
	}

	/**
	 * Stampa il tag di chiusura del Pager
	 **/
	public function closePager() {
		echo "</div>\n";
	}
	
	/**
	 * Ritorna una stringa con i link per la navigazione
	 *
	 * Utilizza gli stili CSS impostati con {@link setCSS}
	 * @param boolean $top_bottom TRUE navigazione superiore, FALSE navigazioe inferiore
	 * @return una stringa col codice xhtml
	 * @see getNavigationPages
	 * @see openNavigation
	 * @see closeNavigation
	 * @see getPrevNextNavigationAnchors
	 */
	public function makeNavigation($top_bottom) {
		$p = $this->pager_view->getController()->getPagerDestinations();
		
		if (count($p['pages']) > 0) {
			
			$out = $this->openNavigation($top_bottom);			
			if (!is_null($p['prev'])) $out .= $this->getPrevNextNavigation($p['prev_dest'], $p['prev'], FALSE, $top_bottom).' ';
			
			$out .= $this->getNavigationPages($p['pages'], $p['current_id'], $top_bottom);
			if (!is_null($p['next'])) $out .= ' '.$this->getPrevNextNavigation($p['next_dest'], $p['next'], TRUE, $top_bottom);
			
			$out .= $this->closeNavigation($top_bottom);
			return $out;
		} else {
			return '';
		}
	}
	
	/**
	 * Ritorna la stringa XHTML con le ancore per andare avanti/indietro tra le pagine.
	 *  
	 * @param string $destination destinazione da usare
	 * @param integer $page_num numero di pagina
	 * @param boolean $prev_next TRUE pagina successiva, FALSE pagina precedente
	 * @param boolean $top_bottom TRUE navigazione superiore, FALSE navigazioe inferiore
	 */
	public function getPrevNextNavigation($destination, $page_num, $prev_next, $top_bottom) {
		return ($prev_next ? ' ' : '').$this->getPrevNextNavigationAnchors($destination, $page_num, $prev_next, $top_bottom).($prev_next ? '' : ' ');
 	}
	
	/**
	 * Fornisce una stringa XHTML con le ancore per le pagine.
	 * 
	 * Il parametro $pages è l'array $p['pages'] ottenuto da:
	 * $p = $this->controller->getPagerDestinations();
	 *  
	 * 
	 * @param array $pages le pagine
	 * @param integer $current_page_id ID della pagina corrente
	 * @param boolean $top_bottom TRUE navigazione superiore, FALSE navigazioe inferiore
	 * @return string il codice XHTML
	 * @see getNavigationPageAnchor
	 */
	public function getNavigationPages($pages, $current_page_id, $top_bottom) {
		$pagine = array();
		foreach($pages as $pag) {
			$pagine[] = $this->getNavigationPageAnchor($pag['url_dest'], $pag['id'], $pag['id'] == $current_page_id, $top_bottom);
		}
		return implode($pagine, ' | ');
	}
	
	/**
	 * Ritorna l'ancora di navigazione per una pagina specifica
	 * 
	 * @param string $destination destinazione da usare
	 * @param integer $page_num numero della pagina
	 * @param boolean $is_current_page TRUE è la pagina corrente, FALSE altrimenti
	 * @param boolean $top_bottom TRUE navigazione superiore, FALSE navigazioe inferiore
	 * @see getNavigationPages
	 */
	public function getNavigationPageAnchor($destination, $page_num, $is_current_page, $top_bottom) {
		$pl = $this->getNavigationPageLabel($page_num);
		
		return XHTML::a( WebApp::getInstance()->link_to($destination, TRUE), $pl['label'], $pl['title'], 
				($is_current_page ? $this->css->getArray($top_bottom ? self::PAGER_TOP_SEL_ITEM : self::PAGER_BOTTOM_SEL_ITEM) : $this->css->getArray($top_bottom ? self::PAGER_TOP_ITEM : self::PAGER_BOTTOM_ITEM) )
				);
		
	}
	
	/**
	 * Ritorna l'ancora di navigazione per le pagine successiva e precedente.
	 * 
	 * @param string $destination destinazione da usare
	 * @param integer $page_num numero di pagina
	 * @param boolean $prev_next TRUE pagina successiva, FALSE pagina precedente
	 * @param boolean $top_bottom TRUE navigazione superiore, FALSE navigazioe inferiore
	 */
	public function getPrevNextNavigationAnchors($destination, $page_num,  $prev_next, $top_bottom) {
		$l = $this->getNavigationLabels();
		if ($prev_next) {
			$label = $l['next_page_label'];
			$title = $l['next_page_title'];
		} else {
			$label = $l['prev_page_label'];
			$title = $l['prev_page_title'];
		}
		
		return XHTML::a( WebApp::getInstance()->link_to($destination, TRUE), $label, $title );
	}
	
	/**
	 * Ritorna il codice XHTML di apertura della zona di navigazione tra le pagine
	 * 
	 * @param boolean $top_bottom TRUE navigazione superiore, FALSE navigazioe inferiore
	 * @return string codice XHTML
	 */
	public function openNavigation($top_bottom) {
		return "<div".$this->css->getAttr($top_bottom ? self::PAGER_TOP_NAVIGATION : self::PAGER_BOTTOM_NAVIGATION).">\n";
	}

	/**
	 * Ritorna il codice XHTML di chiusura della zona di navigazione tra le pagine.
	 * 
	 * @param boolean $top_bottom TRUE navigazione superiore, FALSE navigazioe inferiore
	 * @return string codice XHTML
	 */
	public function closeNavigation($top_bottom) {
		return "</div>\n";
	}
	
	/**
	 * Ritorna il testo da utilizzare per generare le ancore di navigazione.
	 * 
	 * Ritorna un array associativo con i seguenti elementi:
	 * 
	 * array(
	 * 		'prev_page_label' => testo XHTML dell'ancora per la pagina precedente,
	 * 		'prev_page_title' => testo XHTML del titolo dell'ancora per la pagina precedente,
	 * 		'next_page_label' => testo XHTML dell'ancora per la pagina successiva,
	 * 		'next_page_title' => testo XHTML del titolo dell'ancora per la pagina successiva
	 * 	);
	 * 
	 * @return array array associativo col testo
	 * @see makeNavigation
	 */
	public function getNavigationLabels() {
		return array(
				'prev_page_label' => '&laquo;',
				'prev_page_title' => tr('Pagina precedente'),
				'next_page_label' => '&raquo;',
				'next_page_title' => tr('Pagina successiva')
			);
	}
	
	/**
	 * Ritorna il testao da utilizzare per generare le ancore per la navigazione tra le pagine.
	 * 
	 * Ritorna un array associativo:
	 * array(
	 * 	'label' - testo XHTML dell'ancora per la pagina
	 * 	'title' - testo XHTML del titolo dell'ancora per la pagina
	 * )
	 * 
	 * @param integer $page_num numero della pagina
	 * @return array un array associativo col testo
	 * @see makeNavigation
	 */
	public function getNavigationPageLabel($page_num) {
		return array(
				'label' => strval($page_num),
				'title' => sprintf(tr('Vai alla pagina %d'), $page_num)
				);
	}
	
	/**
	 * Ritorna il selettore standard degli elementi, una checkbox.
	 * 
	 * @param mixed $row una riga di dati
	 * @return string il codice XHTML del selettore
	 * @see PagerView::getRowSelectorValue
	*/
	public function renderSelector($row) {
		return "<input type=\"checkbox\" name=\"".$this->getView()->getSelectorName()."[]\" value=\"".XHTML::toHTML(strval($this->getView()->getRowSelectorValue($row)))."\" />";
	}
	
	/**
	 * Stampa un messaggio di errore quando lo stato del pager è STATUS_POST_PROCESS_ERROR
	 *
	 */
	public function showPostProcessError() {
		echo $this->status_txt[PagerView::STATUS_POST_PROCESS_ERROR];
	}
	
	
	/**
	 * Stampa il pager quando non ci sono dati.
	 * 
	 * Lo stato del PagerView è STATUS_NO_MODEL_DATA.
	 * 
	 **/
	public function showNoData() {
		echo $this->status_txt[PagerView::STATUS_NO_MODEL_DATA];
	}

	/**
	 * Stampa il pager quando c'è un errore relativo al model
	 * 
	 * Lo stato del PagerView è STATUS_MODEL_ERROR.
	 **/
	public function showModelError() {
		echo $this->status_txt[PagerView::STATUS_MODEL_ERROR];
	}

	/**
	 * Stampa il pager quando c'è un errore di richiesta di pagina non valida.
	 * 
	 * Lo stato del PagerView è STATUS_WRONG_PAGE.
	 */
	public function showWrongPageError() {
		echo $this->status_txt[PagerView::STATUS_WRONG_PAGE];
	}

	/**
	 * Stampa il pager quando ci sono errori in input nella richiesta HTTP.
	 * 
	 * Lo stato del PagerView è STATUS_INPUT_ERROR.
	 **/
	public function showInputError() {
		echo $this->status_txt[PagerView::STATUS_INPUT_ERROR];
	}

	/**
	 * Stampa il pager in attesa di input.
	 * 
	 * Lo stato del PagerView è STATUS_IDLE.
	 **/
	public function showIdle() {
		echo $this->status_txt[PagerView::STATUS_IDLE];
	}
	
	/**
	 * Invocata prima di visualizzare il pager.
	 * 
	 * Viene invocata da {@link show} prima del disegno del pager.
	 * Verificare lo stato del pager usando {@link getStatus}.
	 * */
	public function preShow() {
	}
	
	/**
	 * Invocata dopo aver visualizzato il pager.
	 * 
	 * Viene invocata da {@link show} dopo aver disegnato il pager.
	 * Verificare lo stato del pager usando {@link getStatus}.
	 * */
	public function postShow() {
	}
	
	
	/**
	 * Effettua il disegno del pager quando ci sono dei dati.
	 *
	 * Prima viene mostrata una testata, poi il corpo e quindi un footer.
	 * Il corpo di solito disegna i dati.
	 * 
	 * Viene invocata quando lo stato del PagerView è STATUS_SHOW_MODEL_DATA.
	 * 
	 * @see showHeader
	 * @see showBody
	 * @see showFooter
	 */
	public function showPager() {
		$this->showHeader();
		$this->showBody();
		$this->showFooter();
	}
	
	/**
	 * Stampa la parte superiore (testata) del pager
	 * 
	 * Apre il pager invocando {@link openPager} quindi mostra
	 * i link di navigazione con usando {@link showNavigationLinks}
	 **/
	public function showHeader() {
		$this->openPager();
		if ($this->pager_view->getShowNavigationLinks() & PagerView::SHOW_TOP_NAV_ANCHORS) $this->showNavigationLinks(TRUE);
	}

	/**
	 * Mostra i link di navigazione
	 * @param  boolean $show_top TRUE mostra i link di navigazione superiori, FALSE mostra quelli inferiori
	*/
	public function showNavigationLinks($show_top) {
		echo $this->makeNavigation($show_top);
	}

	/**
	 * Stampa la parte inferiore del Pager
	 * 
	 * Stampa prima i link di chiusura con {@link showNavigationLinks}, quindi 
	 * chiude il pager usando @link {@link closePager}
	*/
	public function showFooter() {
		if ($this->pager_view->getShowNavigationLinks() & PagerView::SHOW_BOTTOM_NAV_ANCHORS) $this->showNavigationLinks(FALSE);
		$this->closePager();
	}

	/**
	 * Stampa i dati forniti dal pager
	 *
	 * Utilizza walkRows e showCurrentDataRow, in modo da poter
	 * capire l'ordine della riga corrente.
	 * 
	 *
	 */
	public function showBody() {
		$this->getView()->walkRows( array($this, 'showCurrentDataRow') );
	}
	
	/**
	 * Mostra la riga di dati corrente.
	 * 
	 * Valida solo all'interno di un ciclo di {@link PagerView::walkRows}.
	 *
	 */
	public function showCurrentDataRow($row) {
		echo "Row: ", ($this->pager_view->isOdd() ? 'Odd' : 'Even'), $this->pager_view->getGlobalIndex(), ' - ', $this->pager_view->getIndex(),"<br/>\n";
	}
}

