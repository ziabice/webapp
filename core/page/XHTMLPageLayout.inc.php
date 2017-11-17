<?php

/**
 * (c) 2012-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Page layout basato su XHTML
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class XHTMLPageLayout extends BasePageLayout {
	protected
		/**
		 * @var string DOCTYPE del documento
		 */
		$doctype = '', 
		/**
		 * @var array array con le URI dei file javascript da caricare
		 */
		$javascript = array(),
		/**
		 * @var array array con le URI dei file CSS da caricare
		 */
		$css = array(); 
	
	const
	// Nomi di alcuni tag comuni ad ogni pagina
	TAG_XML_PROLOG = 'xml', // Prologo XML della pagina
	TAG_DOCTYPE = 'doctype', // Doctype del documento
	TAG_HTML = 'html', // Html: non ci dovrebbe mai essere bisogno di referenziarlo
	TAG_HEAD = 'head', // parte HEAD della pagina
	TAG_BODY = 'body', // parte BODY del documento
	TAG_CSS = 'css', // codice per i CSS
	TAG_JAVASCRIPT = 'javascript'; // codice javascript

	/**
	 * Crea l'oggetto, imposta l'encoding ed azzera tutti i Javascript e CSS aggiuntivi.
	 *
	 *
	 * Per completare l'inizializzatione, va invocata anche {@link initialize}.
	 *
	 * Sono definite diverse costanti con l'encoding: conviene comunque usare sempre l'encoding ENCODING_UTF8, per
	 * avere l'UTF-8.
	 *
	 * ENCODING_UTF8 - testo in UTF-8
	 * ENCODING_LATIN1 - testo Latin 1 (iso 8859-1)
	 * ENCODING_LATIN15 - testo Latin 15 (iso 8859-15)
	 *
	 * @param string $template_filename nome dell'eventuale file XHTML da utilizzare al posto di quelli standard
	 * @param string $encoding stringa con l'encoding utilizzato, non c'è motivo nel 2008 per passare qualcosa di diverso da self::ENCODING_UTF8
	 * @see initialize().
	*/
	public function __construct($encoding = self::ENCODING_UTF8, $template_filename = '') {
		parent::__construct($encoding, $template_filename, 'text/html');
		$this->setDoctype('');
		$this->clearJavascript();
		$this->clearCSS();
	}
	
	/**
	 * Ritorna il codice xhtml che verrà utilizzato per caricare i file Javascript
	 * aggiunti con addJavascript.
	 *
	 * @return array un array di stringhe col codice xhtml per i sorgenti javascript
	 * @see addJavascript
	*/
	public function getJavascript() {
		$s = array();
		foreach($this->javascript as $j) {
			$s[] = "<script type=\"text/javascript\" src=\"{$j[0]}\" {$j[1]}></script>";
		}
		return $s;
	}

	/**
	 * Ritorna il codice xhtml che verrà utilizzato per caricare i file CSS
	 * aggiunti con addCSS
	 * @return string un array di stringhe col codice xhtml
	 * @see addCSS
	*/
	public function getCSS() {
		$s = array();
		foreach($this->css as $c) {
			$s[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$c[0]}\" ".(empty($c[1]) ? '' : ' media="'.$c[1].'"')." {$c[2]}/>";
		}
		return $s;
	}

	/**
	 * Ritorna la stringa DOCTYPE associata a questo layout di pagina
	 *
	 * @return string la stringa DOCTYPE
	*/
	public function getDoctype() {
		return $this->doctype;
	}

	/**
	 * Imposta il doctype di questo documento
	 *
	 * @param string $doctype una stringa che indichi il doctype per il documento
	*/
	public function setDoctype($doctype) {
		$this->doctype = $doctype;
	}
	
		// Gestiscono in maniera "pulita" parti accessorie di una pagina
	/**
	 * Aggiunge un file Javascript alla pagina
	 * 
	 * @param string $tag una stringa con lo mnemonico del tag (per seguenti accessi)
	 * @param string $js_url stringa con la url del file
	 * @param string $extra stringa con eventuali attributi extra per il tag xhtml generato
	*/
	public function addJavascript($tag, $js_url, $extra = '') {
		$this->javascript[$tag] = array(htmlentities($js_url), $extra);
	}

	/**
	 * Rimuove un file javascript dalla pagina
	 *
	 * Il file deve essere stato aggiunto usando addJavascript
	 *
	 * @param string $tag una stringa con lo mnemonico del tag
	 * @return boolean TRUE se ha rimosso, FALSE altrimenti
	 * @see addJavascript
	*/
	public function delJavascript($tag) {
		if (array_key_exists($tag, $this->javascript)) {
			unset($this->javascript[$tag]);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Rimuove tutti i file Javascript aggiuntivi da caricare
	*/
	public function clearJavascript() {
		$this->javascript = array();
	}

	/**
	 * Aggiunge un file CSS alla pagina
	 * 
	 * @param string $tag una stringa con lo mnemonico del tag (per seguenti accessi)
	 * @param string $css_url stringa con la url del file
	 * @param string $media stringa con il media per il CSS
	 * @param string $extra stringa con eventuali attributi extra per il tag xhtml
	*/
	public function addCSS($tag, $css_url, $media = 'screen', $extra = '') {
		$this->css[$tag] = array(htmlentities($css_url), $media, $extra, FALSE);
	}

	/**
	 * Rimuove un file javascript dalla pagina
	 *
	 * @param string $tag una stringa con lo mnemonico del tag
	 * @see addCSS
	*/
	public function delCSS($tag) {
		if (array_key_exists($tag, $this->css)) {
			unset($this->css[$tag]);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Rimuove tutti i file CSS aggiuntivi
	*/
	public function clearCSS() {
		$this->css = array();
	}
	
	/**
	 * Effettua il prerendering della pagina.
	 *
	 * Pone nel buffer di output il codice XHTML per i CSS e quello per i javascript.
	 *
	 * Il codice XHTML per i CSS viene messo nel tag indicato da self::TAG_CSS, mentre
	 * il codice XHTML per i javascript nel tag indicato da self::TAG_JAVASCRIPT.
	 */
	protected function prerendering() {
		// Mette i CSS
		WebApp::getInstance()->getAction()->write_to(self::TAG_CSS, implode($this->getCSS(), "\n"));

		// Mette il codice javascript
		WebApp::getInstance()->getAction()->insert_into(self::TAG_JAVASCRIPT, implode($this->getJavascript(), "\n") );
	}

	/**
	 * Effettua il rendering della pagina html.
	 *
	 * @return string una stringa con la pagina xhtml
	 * @see prerendering
	*/
	public function render() {
		$this->prerendering();

		return $this->renderTemplate();
	}
}

