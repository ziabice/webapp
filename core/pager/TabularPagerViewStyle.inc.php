<?php

/**
 * (c) 2012-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Delegate che disegna tabelle paginate.
 * 
 * Usarlo con classi TabularPagerView.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TabularPagerViewStyle extends PagerViewStyle {
	
	protected 
		/**
		 * @var array mappature stili-testate
		 */
		$header_style_mappings,
		/**
		 * @var array mappature stili-colonne
		 */
		$column_style_mappings,
		/**
		 * @var boolean TRUE ha il selettore JS, FALSE altrimenti
		 */
		$with_quick_selector;


	const
		// Costanti per gli stili CSS
		PAGER_HEADERS_ROW = 'table_headers',
		MAIN_TABLE = 'main_table'; // La tabella coi dati
	
	/**
	 * Inizializza l'oggetto.
	 * 
	 */
	public function __construct() {
		$this->header_style_mappings = array();
		$this->column_style_mappings = array();
		parent::__construct();
		$this->setQuickSelector(FALSE);
	}
	
	/**
	 * Imposta l'associazione tra un nome di testata ed uno stile.
	 * 
	 * Alla testata col nome indicato verrà applicato lo stile (se esistente)
	 * associato. Lo stile viene preso dal gestore di stile dell'oggetto.
	 * 
	 * @param string $name nome della colonna
	 * @param string $style stile CSS da usare
	 */
	public function setHeaderStyle($name, $style) {
		$this->header_style_mappings[$name] = $style;
	}
	
	/**
	 * Rimuove l'associazione tra un nome di testata ed uno stile.
	 * 
	 * @param string $name nome della colonna
	 */
	public function unsetHeaderStyle($name) {
		if (array_key_exists($name, $this->header_style_mappings)) unset( $this->header_style_mappings[$name] );
	}
	
	/**
	 * Imposta l'associazione tra un nome di colonna ed uno stile.
	 * 
	 * Alla colonna col nome indicato verrà applicato lo stile (se esistente)
	 * associato. Lo stile viene preso dal gestore di stile dell'oggetto.
	 * 
	 * @param string $name nome della colonna
	 * @param string $style stile CSS da usare
	 */
	public function setColumnStyle($name, $style) {
		$this->column_style_mappings[$name] = $style;
	}
	
	/**
	 * Rimuove l'associazione tra un nome di colonna ed uno stile.
	 * 
	 * @param string $name nome della colonna
	 */
	public function unsetColumnStyle($name) {
		if (array_key_exists($name, $this->column_style_mappings)) unset( $this->row_style_mappings[$name] );
	}
	
	/**
	 * Abilita/disabilita il selettore rapido.
	 * 
	 * Abilita o disabilita il selettore rapido (valido solo se c'è una colonna selettore) 
	 * che utilizza javascript.
	 * 
	 * @param boolean $enable TRUE abilita il selettore rapido, FALSE lo disabilita
	 */
	public function setQuickSelector($enable) {
		$this->with_quick_selector = $enable;
	}
	
	/**
	 * Mostra l'header.
	 * Aggiunge il tag di apertura della tabella e le intestazioni delle colonne.
	 *
	 * La tabella è inglobata nell'elemento block level che contiene il pager ed
	 * è preceduta dai link di navigazione.
	 *
	 * @see openTable
	 * @see columnHeaders
	 */
	public function showHeader() {
		parent::showHeader();
		$this->openTable();
		if ($this->pager_view->showHeaders()) $this->columnHeaders();
	}

	/**
	 * Mostra il footer
	 * Aggiunge il tag di chiusura della tabella
	 * @see closeTable
	 */
	public function showFooter() {
		$this->closeTable();
		parent::showFooter();
		if ($this->with_quick_selector) {
			echo '<script type="text/javascript">
		if (!PagerToggler) {
			var PagerToggler = new function() {
				this.checked = new Array();
				this.toggle = function(sel_name) {
					var e = document.getElementsByName(sel_name);
					if (e) {
						if (this.checked[sel_name] == null) this.checked[sel_name] = false;
						this.checked[sel_name] = !this.checked[sel_name];
						for (var i=0; i < e.length; i++) e[i].checked = this.checked[sel_name];
					}
				};
			};
		}
	</script>';
		}
	}
	
	/**
	 * Stampa il codice XHTML di apertura della tabella.
	 *
	 * Usa lo stile CSS individuato dalla costante MAIN_TABLE.
	 *
	 */
	public function openTable() {
		echo "<table".$this->getCSS()->getAttr(self::MAIN_TABLE).">\n";
	}

	/**
	 * Ritorna il tag di chiusura della tabella
	 * 
	 * @return string codice XHTML del tag di apertura
	 */
	public function closeTable() {
		echo "</table>\n";
	}
	
	/**
	 * Stampa le intestazioni delle colonne.
	 * 
	 * Stampa una riga di celle contenenti le intestazioni delle colonne.
	 * 
	 * @see openColumnHeaders
	 * @see closeColumnHeaders
	 * @see renderColumnHeader
	 */
	public function columnHeaders() {
		if (!$this->pager_view->getColumns()->isEmpty()) {
			$this->openColumnHeaders();
			
			foreach($this->pager_view->getColumns() as $column) {
				if ($column->isVisible()) {
					echo $this->renderColumnHeader($column);
				}
			}
			
			$this->closeColumnHeaders();
		}
	}
	
	/**
	 * Crea un link nella testata della colonna selettore che seleziona/deseleziona tutti gli elementi.
	 * @param TabularPagerViewColumn $column la colonna su cui operare
	 * */
	public function quickSelectorColumnHeader($column) {
		return "<a href=\"#\" onclick=\"PagerToggler.toggle('".$this->pager_view->getSelectorName()."[]'); return false;\" title=\"".tr("Seleziona/deseleziona tutti gli elementi.")."\">".tr("Sel.")."</a>";
	}
	
	/**
	 * Ritorna il codice XHTML per l'apertura di una riga di dati:
	 * aggiunge automaticamente gli stili per le righe pari/dispari.
	 * E per la prima/ultima riga
	 * @return string stringa col codice XHTML
	*/
	protected function openRow() {
		if ($this->getView()->isLastRow()) {
			echo "<tr".($this->getView()->isOdd() ? $this->getCSS()->getAttr(self::PAGER_LAST_ROW_ODD) : $this->getCSS()->getAttr(self::PAGER_LAST_ROW_EVEN)).">";
		} elseif ($this->getView()->isFirstRow()) {
			echo "<tr".$this->getCSS()->getAttr(self::PAGER_FIRST_ROW).">";
		} else {
			echo "<tr".($this->getView()->isOdd() ? $this->getCSS()->getAttr(self::PAGER_ODD_ITEM) : $this->getCSS()->getAttr(self::PAGER_EVEN_ITEM)).">";
		}
	}
	
	/**
	 * Stampa il codice XHTML di apertura della riga delle intestazioni colonna
	 * 
	 * @return string una stringa XHTML 
	 **/
	public function openColumnHeaders() {
		echo "<thead><tr".$this->getCSS()->getAttr(self::PAGER_HEADERS_ROW).">";
	}
	
	/**
	 * Ritorna il codice di chiusura della riga delle intestazioni colonna
	 * @return string una stringa XHTML 
	 * */
	public function closeColumnHeaders() {
		echo "</tr></thead>\n";
	}
	
	/**
	 * Ritorna il codice XHTML di chiusura di una riga
	 * @return string stringa col codice XHTML
	*/
	protected function closeRow() {
		echo "</tr>\n";
	}
	
	/**
	 * Ritorna il codice XHTML della colonna del selettore dati
	 * Usa lo stile PAGER_SELECTOR per la colonna
	 * @param TabularPagerViewColumn $column colonna da elaborare
	 * @return string il codice XHTML
	 * @see renderSelector
	 * */
	public function renderSelectorColumn($column) {
		return "<td".$this->getCSS()->getAttr(self::PAGER_SELECTOR).">".$this->renderSelector( $this->pager_view->getCurrentRow() )."</td>";
	}
	
	/**
	 * Ritorna il codice XHTML per un header di colonna
	 *
	 * Compone l'output di:
	 * - openHeaderColumn
	 * - renderColumnHeaderContent
	 * - closeHeaderColumn
	 *
	 * @param TabularPagerViewColumn $column colonna da elaborare
	 * @return string il codice XHTML di una colonna header
	 * @see openHeaderColumn
	 * @see renderColumnHeaderContent
	 * @see closeHeaderColumn
	 * */
	public function renderColumnHeader($column) {
		if ($this->pager_view->hasSelector() && $column->isSelectorColumn()) {
			if ($this->with_quick_selector) return $this->openHeaderColumn($column).$this->quickSelectorColumnHeader($column).$this->closeHeaderColumn($column);
			// else return $this->renderSelectorColumn($column);
		}
		return $this->openHeaderColumn($column).$this->renderColumnHeaderContent($column).$this->closeHeaderColumn($column);
	}
	
	/**
	 * Ritorna il tag di apertura dell'intestazione della colonna specificata.
	 * 
	 * Applica gli eventuali stili associati.
	 * 
	 * @param TabularPagerViewColumn $column la colonna da elaborare
	 * @return string codice XHTML 
	 * @see setHeaderStyle
	 * */
	public function openHeaderColumn($column) {
		return "<th".(array_key_exists($column->getName(), $this->header_style_mappings) ? $this->getCSS()->getAttr($this->header_style_mappings[ $column->getName() ]) : '').">";
	}
	
	/**
	 * Ritorna il tag di chiusura dell'intestazione della colonna specificata
	 *
	 * @param TabularPagerViewColumn $column la colonna da elaborare
	 * @return string codice XHTML 
	 * */
	public function closeHeaderColumn($column) {
		return "</th>";
	}
	
	/**
	 * Ritorna il contenuto della colonna header
	 *
	 * Se specificata utilizza una callback per generare il codice XHTML: questa
	 * callback accetta come parametro il nome della colonna di cui si vuole l'intestazione e 
	 * deve ritornare il codice XHTML per la testata.
	 * E' nella forma:
	 * string callback($column)
	 *
	 * @param TabularPagerViewColumn $column colonna di cui si vuole l'intestazione
	 * @return string il codice XHTML per l'intestazione di colonna
	 **/
	public function renderColumnHeaderContent($column) {
		if ($column->hasHeaderHandler()) {
			return call_user_func($column->getHeaderHandler(), $column);
		} else {
			return $column->getHeader();
		}
	}
	
	
	/**
	 * Ritorna il codice XHTML per la colonna specificata, compresivo del contenuto della stessa
	 *
	 * Compone l'output dei metodi:
	 * 
	 * - openColumn
	 * - renderColumnContent
	 * - closeColumn
	 *
	 * @param TabularPagerViewColumn $column colonna da elaborare
	 * @return string codice XHTML
	 * @see openColumn
	 * @see renderColumnContent
	 * @see closeColumn
	 **/
	public function renderColumn($column) {
		return $this->openColumn($column).$this->renderColumnContent($column).$this->closeColumn($column);
	}
	
	/**
	 * Restituisce il contenuto XHTML della colonna prendendolo dalla riga di dati corrente.
	 * 
	 * Suppone di funzionare all'interno di un ciclo di {@link PagerView::wallkRows}.
	 * 
	 * Usa la callback specificata nella colonna per creare il testo XHTML:
	 * 
	 * - Nel caso la callback sia una stringa: se la riga di dati corrente è un oggetto, verifica
	 * che esista un metodo col nome pari a quello indicato nel parametro $content_handler,
	 * se presente lo invoca, convertendo poi il valore di ritorno in XHTML. Questo approccio
	 * è usato per invocare direttamente un metodo getter. Se invece la riga di dati corrente è un
	 * array verifica se esista un campo col nome uguale a quello della callback specificata e se esiste
	 * usa il valore di tale campo (convertito in XHTML).
	 * 
	 * - nel caso invece sia una callback vera e propria: invoca la callback passandole come parametro la riga
	 * di dati corrente (che normalmente è un oggetto). Presume una callback del tipo:
	 * string callback($obj). Il valore ritornato viene usato direttamente (non viene convertito in XHTML)
	 * come valore della cella.
	 *
	 * @param TabularPagerViewColumn $column colonna da elaborare
	 * @return string codice XHTML del contenuto della colonna specificata
	 * @see PagerView::getCurrentRow
	 * @see PagerView::walkRows
	 * */
	public function renderColumnContent($column) {
		$row = $this->pager_view->getCurrentRow();
		$ch = $column->getContentHandler();
		if (is_object($row) && is_string($ch)) {
			if (method_exists($row, $ch) ) {
				return XHTML::toHTML( call_user_func(array($row, $ch)) );
			}
		}
		if (is_callable($ch)) {
			return call_user_func($ch, $row);
		}
		// Nel caso la riga di dati sia un array permette di accedere direttamente al valore di una cella
		if (is_array($row)) {
			if (array_key_exists($ch, $row)) return XHTML::toHTML( $row[$ch] );
		}
		return '';
	}
	
	/**
	 * Ritorna il tag di apertura della colonna specificata, per la riga di dati corrente.
	 * 
	 * Applica gli eventuali stili associati.
	 * 
	 * @param TabularPagerViewColumn $column colonna da elaborare
	 * @return string codice XHTML per la colonna
	 * @see setColumnStyle
	 * */
	public function openColumn($column) {
		return "<td".(array_key_exists($column->getName(), $this->column_style_mappings) ? $this->getCSS()->getAttr($this->column_style_mappings[$column->getName()]) : '').">";
	}
	
	/**
	 * Ritorna il tag di chiusura della colonna specificata, per la riga di dati corrente
	 * 
	 * @param TabularPagerViewColumn $column colonna da elaborare
	 * @return string codice XHTML per la colonna
	 **/
	public function closeColumn($column) {
		return "</td>";
	}
	
	
	/**
	 * Mostra un riga
	 *
	 * Apre la riga, disegna le colonna e chiude la riga
	 *
	 * @see openRow
	 * @see closeRow
	 * @see renderSelectorColumn
	 * @see renderColumn
	 * 
	 */
	public function showCurrentDataRow($row) {
		if (!$this->pager_view->getColumns()->isEmpty()) {
			$this->openRow();
			
			foreach($this->pager_view->getColumns() as $column) {
				if ($column->isVisible()) {
					if ($this->pager_view->hasSelector() && $column->isSelectorColumn()) echo $this->renderSelectorColumn($column);
					else echo $this->renderColumn($column);
				}
			}
			
			$this->closeRow();
		}
	}
	
	public function showBody() {
		echo "<tbody>\n";
		parent::showBody();
		echo "</tbody>\n";
	}
}

