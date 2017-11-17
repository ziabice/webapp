<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un contenitore di stili CSS.
 *
 * Permette di associare ad un insieme di stili una etichetta.
 * 
 * @author Luca 'Ziabice' Gambetta <l.gambetta@bluenine.it>
*/
class CSS {
	protected
		$styles = array(); 

	/**
	 * Inizializza l'oggetto
	 */
	public function __construct() {
		$this->styles = array();
	}
	
	/**
	 * Rimuove tutti gli stili definiti
	*/
	public function clear() {
		$this->styles = array();
	}
	
	/**
	 * Imposta uno stile associandolo ad una etichetta.
	 * 
	 * Nel caso l'etichetta esista già lo stile viene sovrascritto.
	 * 
	 * Uno stile è solo il contenuto dell'attributo 'class' o 'id' di un tag xhtml.
	 * Si può specificare tanto uno stile di classe (ossia per l'attributo 'class')
	 * quanto uno di id (attributo id).
	 * 
	 * Esempi:
	 * <code>
	 * $css->set('foo', 'first second third');
	 * </code>
	 * 
	 * Dà come output:
	 * 
	 * <code>
	 * <p class="first second third">
	 * </code>
	 * 
	 * Usando invece:
	 * <code>
	 * $css->set('foo', 'first', TRUE);
	 * </code>
	 * 
	 * Otteniamo:
	 * 
	 * <code>
	 * <p id="first">
	 * </code>
	 * 
	 * Nel caso di ID non ha senso passare più di un valore.
	 * 
	 * @param string $tag stringa con l'etichetta a cui associare lo stile
	 * @param string $styles stringa con gli stili (stili multipli separati da spazio)
	 * @param boolean $is_id TRUE lo stile è un ID univoco, FALSE è uno stile di classe
	*/
	public function set($tag, $styles, $is_id = FALSE) {
		if ($is_id) $this->setID($tag, $styles);
		else $this->setClass($tag, $styles);
	}

	/**
	 * Imposta gli stili CSS di classe per una certa etichetta.
	 *
	 * @param string $tag etichetta a cui associare lo stile
	 * @param string $styles stringa con gli stili (classi multiple separate da spazio)
	 */
	public function setClass($tag, $styles) {
		if (!array_key_exists($tag, $this->styles)) $this->styles[$tag] = array('', '');
		$this->styles[$tag][0] = $styles;
	}

	/**
	 * Aggiunge (appende in coda) gli stili CSS di classe per una certa etichetta.
	 *
	 * @param string $tag etichetta a cui associare lo stile
	 * @param string $styles stringa con gli stili (classi multiple separate da spazio)
	 */
	public function addStyleClass($tag, $styles) {
		if (!array_key_exists($tag, $this->styles)) $this->styles[$tag] = array('', '');
		$this->styles[$tag][0] .= $styles;
	}

	/**
	 * Imposta l'ID dell'elemento.
	 *
	 * @param string $tag etichetta a cui associare lo stile
	 * @param string $name nome dell'elemento (ID univoco)
	 */
	public function setID($tag, $name) {
		if (!array_key_exists($tag, $this->styles)) $this->styles[$tag] = array('', '');
		$this->styles[$tag][1] = $name;
	}

	/**
	 * Ritorna gli stili di classe.
	 * 
	 * @param string $tag etichetta associata allo stile
	 * @return string|boolean FALSE se non è stata definita l'etichetta, altrimenti una stringa con gli stili
	 */
	public function getClass($tag) {
		return (array_key_exists($tag, $this->styles) ? $this->styles[0] : FALSE);
	}

	/**
	 * Ritorna l'ID univoco dell'elemento
	 *
	 * @param string $tag etichetta associata allo stile
	 * @return string|boolean FALSE se non è stata definita l'etichetta, altrimenti una stringa con l'ID
	 */
	public function getID($tag) {
		return (array_key_exists($tag, $this->styles) ? $this->styles[1] : FALSE);
	}
	
	/**
	 * Rimuove se definita l'associazione tra stile ed etichetta.
	 * 
	 * @param string $tag stringa con l'etichetta dello stile
	*/
	public function del($tag) {
		if (array_key_exists($tag, $this->styles)) unset($this->styles[$tag]);
	}
	
	/**
	 * Ritorna gli stili di classe associati al tag.
	 * 
	 * Se uno stile non è definito ritorna una stringa vuota.
	 * 
	 * @param string $tag stringa con l'etichetta dello stile
	 * @return string stringa con gli stili (stringa vuota se non esiste uno stile di classe)
	*/
	public function get($tag) {
		return (array_key_exists($tag, $this->styles) ? $this->styles[$tag][0] : '');
	}
	
	/**
	 * Ritorna l'attributo CSS completo da utilizzare in un tag XHTML
	 * 
	 * Ritorna o una stringa ' class="..."' o ' id="..."' contenente lo stile definito per una certa etichetta.
	 * La stringa dell'attributo è preceduta da uno spazio.
	 * 
	 * @param string $tag stringa con l'etichetta dello stile
	 * @return string una stringa con l'attributo o una stringa vuota se l'attributo non esiste
	*/
	public function getAttr($tag) {
		$s = '';
		if (array_key_exists($tag, $this->styles)) {
			$s .= (empty($this->styles[$tag][0]) ? '':' class="'.strval($this->styles[$tag][0]).'" ');
			$s .= (empty($this->styles[$tag][1]) ? '':' id="'.strval($this->styles[$tag][1]).'" ');
		}
		return $s;
	}
	
	/**
	 * Ritorna l'attributo CSS come un array etichetta=valore, in cui
	 * l'etichetta è 'id' se lo stile si riferisce ad un id oppure 'class'
	 * se indica una classe.
	 *
	 * <code>
	 * $this->set('foo', 'mystyle');
	 *
	 * $out = $this->getArray('foo');
	 *
	 * $out è pari a:
	 *
	 * $out = array(
	 *  'class' => 'mystyle'
	 * );
	 * </code>
	 *
	 * @return array array con il tag CSS
	*/
	public function getArray($tag) {
		$out = array();
		if (array_key_exists($tag, $this->styles)) {
			if (!empty($this->styles[$tag][0])) $out['class'] = $this->styles[$tag][0];
			if (!empty($this->styles[$tag][1])) $out['id'] = $this->styles[$tag][1];
		}
		return $out;
	}
	
	/**
	 * Informa se lo stile sia di classe o di id.
	 * 
	 * Se lo stile non esiste ritorna FALSE
	 * 
	 * @param string $tag stringa con l'etichetta dello stile
	 * @return boolean TRUE se lo stile è di classe, FALSE se è di ID
	*/
	public function hasClass($tag) {
		if (array_key_exists($tag, $this->styles)) return !empty($this->styles[$tag][0]);
		return FALSE;
	}

	/**
	 * Informa se lo stile sia un id.
	 *
	 * Se lo stile non esiste ritorna FALSE
	 *
	 * @param string $tag stringa con l'etichetta dello stile
	 * @return boolean TRUE se lo stile è di classe, FALSE se è di ID
	*/
	public function hasID($tag) {
		if (array_key_exists($tag, $this->styles)) return !empty($this->styles[$tag][1]);
		return FALSE;
	}
	
	/**
	 * Informa se esista uno stile per una etichetta.
	 * 
	 * @param string $tag stringa con l'etichetta dello stile
	 * @return boolean TRUE se lo stile esite, FALSE altrimenti
	*/
	public function hasStyle($tag) {
		return array_key_exists($tag, $this->styles);
	}
	
}
