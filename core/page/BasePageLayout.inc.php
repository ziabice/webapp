<?php
/**
 * (c) 2012-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/** 
 * Gestisce la presentazione di una pagina attraverso il template in un file.
 *
 * Funziona con un gestore di View che implementi {@link WebAppViewInterface}. In particolare
 * con la classe {@link WebAppView}.
 *
 * Un oggetto BaseAction contiene tanti buffer di output a cui è associata
 * una tag (etichetta testuale). Questo oggetto non fa altro che leggere
 * un file di template e popolarlo con il contenuto di tali buffer.
 *
 * Il file di template deve contenere il codice PHP opportuno, ad esempio:
 *
 * <head>
 * <title><?php put_page_title(); ?></title>
 * <?php put_page_encoding(); ?>
 * </head>
 * <body>
 * <?php put_page_body(); ?>
 * <div><?php $this->putText('tag_per_il_div') ?></div>
 * </body>
 *
 * Questo file viene incluso ed elaborato da {@link renderTemplate}.
 *
 *
 * E' possibile controllare la visibilità delle parti del layout utilizzando l'opportuno
 * tag e il metodo {@link PageLayout::setVisibility}. Ogni tag è visibile di default.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * 
 * @see WebAppViewInterface
 * @see WebAppView
*/
class BasePageLayout  {
	protected
		/**
		 * @var string nome del tag principale
		 */
		$body_tag,
		/**
		 * @var BaseWidgetStyle lo stile per i widget
		 */
		$widget_style,
		/**
		 * @var string MIME type for this layout
		 */
		$page_mime, 
		/**
		 * @var string encoding for this layout
		 */
		$encoding = '', 
		/**
		 * @var string full path of the file to be included as template
		 */
		$template_filename = '',
		/**
		 * @var string the page title
		 */
		$page_title = '',
		/**
		 * @var array contains visibility for layout zones
		 */
		$zones = array(); 

	const
	// Some usual character encodings
	ENCODING_UTF8 = 'utf-8',
	ENCODING_LATIN1 = 'iso-8859-1',
	ENCODING_LATIN15 = 'iso-8859-15';

	/**
	 * Inizializza l'oggetto con le informazioni base.
	 *
	 * Per completare l'inizializzatione, va invocata anche {@link initialize}.
	 *
	 * Sono definite diverse costanti per l'encoding: conviene comunque usare sempre 
	 * l'encoding ENCODING_UTF8, per avere l'UTF-8.
	 *
	 * ENCODING_UTF8 - testo in UTF-8
	 * ENCODING_LATIN1 - testo Latin 1 (iso 8859-1)
	 * ENCODING_LATIN15 - testo Latin 15 (iso 8859-15)
	 * 
	 * Imposta come tag per il corpo del documento quello pari 
	 * alla costante WebAppAction::MAIN_OUTPUT_BUFFER.
	 * 
	 * @param string $encoding stringa con l'encoding utilizzato
	 * @param string $template_filename nome dell'eventuale file da includere
	 * @param string $mime_tyle stringa con il tipo MIME del layout
	 * */
	public function __construct($encoding = self::ENCODING_UTF8, $template_filename = '', $mime_type = 'text/html') {
		$this->setTemplateFilename($template_filename);
		$this->setEncoding($encoding);
		$this->setPageMIME($mime_type);
		$this->setBodyTag( WebAppAction::MAIN_OUTPUT_BUFFER );
		$this->initializeWdigetStyle();
	}

	/**
	 * Metodo per inizializzare l'oggetto.
	 * 
	 * Viene eseguito da WebAppView dopo che la classe è stata istanziata.
	 * 
	 */
	public function initialize() {
		
	}
	
	/**
	 * Inizializza lo stile di disegno dei widget.
	 * 
	 * Viene eseguito dal costruttore.
	 * 
	 * Viene invocata una classe chiamata "WidgetStyle" derivata da {@link BaseWidgetStyle}.
	 * @see getStyle
	 */
	public function initializeWdigetStyle() {
		$this->widget_style = new WidgetStyle();
	}

	/**
	 * Ritorna l'encoding di pagina
	 * @return string una stringa con l'encoding dei dati
	*/
	public function getEncoding() {
		return $this->encoding;
	}

	/**
	 * Imposta l'encoding dei dati di pagina
	 *
	 * @param string $encoding una stringa xhtml con la codifica dei dati di pagina, default self::ENCODING_UTF8
	*/
	public function setEncoding($encoding = self::ENCODING_UTF8) {
		$this->encoding = $encoding;
	}

	/**
	 * Ritorna la stringa con i tag META da inserire nell'head di pagina, secondo l'encoding dei dati
	 * impostato nell'istanza.
	 *
	 * @return string una stringa col codice XHTML
	 **/
	public function getXHTMLEncondig() {
		return '<meta http-equiv="Content-Type" content="text/html; charset='.$this->getEncoding().'" />';
	}

	/**
	 * Imposta il titolo della pagina xhtml
	 * @param string $title stringa xhtml con il titolo
	 */
	public function setPageTitle($title) {
		$this->page_title = strval($title);
	}

	/**
	 * Ritorna il titolo della pagina
	 *
	 * @return string una stringa xhtml col titolo della pagina
	 */
	public function getPageTitle() {
		return $this->page_title;
	}

	/**
	 * Ritorna il tipo MIME della pagina.
	 * 
	 * @return string una stringa col tipo MIME
	 */
	public function getPageMIME() {
		return 'text/html';
	}
	
	/**
	 * Imposta il tipo MIME della pagina.
	 * 
	 * @param string $mime_type il tipo MIME
	 */
	public function setPageMIME($mime_type) {
		$this->page_mime = $mime_type;
	}

	/**
	 * Imposta la visibilità di una zona del layout.
	 *
	 * @param string $tag nome della zona del layout
	 * @param boolean $visibility TRUE se la zona è visibile, FALSE altrimenti
	 */
	public function setVisibility($tag, $visibility) {
		$this->zones[$tag] = $visibility;
	}

	/**
	 * Informa se una zona del layout sia visibile o meno.
	 * 
	 * Una zona è sempre visibile se non settata diversamente, quindi anche per
	 * zone non assegnate o inesistenti viene ritornato TRUE.
	 *
	 * @param string $tag nome della zona del layout
	 * @return boolean TRUE se la zona è visibile, FALSE altrimenti
	 */
	public function isVisible($tag) {
		if (array_key_exists($tag, $this->zones)) return $this->zones[$tag];
		return TRUE;
	}

	/**
	 * Informa se una zona del layout esista.
	 *
	 * Verifica se una zona del layout con flag di visibilità col tag indicato
	 * sia stata definita.
	 *
	 * @param string $tag nome della zona
	 * @return boolean TRUE se la zona esiste, FALSE altrimenti
	 */
	public function tagExists($tag) {
		return array_key_exists($tag, $this->zones);
	}

	/**
	 * Effettua il rendering della pagina.
	 *
	 * @return string una stringa con il codice elaborato della pagina.
	 */
	public function render() {
		return $this->renderTemplate();
	}

	/**
	 * Include il file di template, lo elabora e ne ritorna il testo.
	 *
	 * @return string il testo del template popolato
	 *
	 * @see setTemplateFilename
	 */
	public function renderTemplate() {
		if (!empty($this->template_filename)) {
			ob_start();
			include $this->template_filename;
			return ob_get_clean();
		}
		
		return '';
	}

	/**
	 * Imposta il nome del file template da caricare.
	 * 
	 * Il nome deve essere un path assoluto.
	 *
	 * @param string $filename il nome del file
	 */
	public function setTemplateFilename($filename) {
		$this->template_filename = $filename;
	}
	
	/**
	 * Ritorna lo stile per il disegno dei widget.
	 * 
	 * @return BaseWidgetStyle
	 */
	public function getWidgetStyle() {
		return $this->widget_style;
	}
	
	/**
	 *
	 * Stampa il testo del tag indicato, se il buffer è visibile.
	 *
	 * Il testo viene preso dal buffer di output dell'azione attuale.
	 *
	 * @param string $tag nome del tag
	 */
	public function putText($tag) {
		if ($this->isVisible($tag)) echo WebApp::getInstance()->getAction()->getText($tag);
	}
	
	/**
	 * Verifica se ci sia del testo per il tag specificato.
	 * 
	 * Controlla che nell'azione corrente sia presente il buffer indicato e che
	 * non sia vuoto.
	 * 
	 * @param string $tag il tag da verificare
	 * @return boolean TRUE se il tag esiste e è non vuoto, FALSE altrimenti
	 */
	public function hasText($tag) {
		$a = WebApp::getInstance()->getAction();
		if ($a->hasOutputBuffer($tag)) {
			return !$a->getOutputBuffer($tag)->isEmpty();
		}
		return FALSE;
	}
	
	/**
	 * Ritorna il tag che rappresenta il corpo principale del documento
	 * 
	 * @return string una stringa col tag
	 * */
	public function getBodyTag() {
		return $this->body_tag;
	}
	
	/**
	 * Imposta il tag che rappresenta il corpo principale del documento
	 * 
	 * @param string $tag nome del tag
	 * */
	public function setBodyTag($tag) {
		$this->body_tag = $tag;
	}
	
	/**
	* Include un file di template.
	* 
	* Il file viene cercato nei seguenti percorsi:
	* 
	* 	<modulocorrente>/view/NOMEFILE
	*  <applicazione_corrente>/view/NOMEFILE
	* 
	* @param string $filename nome del file da includere
	* @return boolean TRUE se il file è stato trovato e incluso, FALSE altrimenti
	*/
   public function includeFile($filename) {
	   $wa = WebApp::getInstance();
	   $paths = array();
	   if ($wa->hasCurrentModule()) {
		   $paths[] = $wa->getModulePath( $wa->getCurrentModule() ).'view'.DIRECTORY_SEPARATOR.$filename;
	   }
	   $paths[] = $wa->getPath().'view'.DIRECTORY_SEPARATOR.$filename;
	   $paths[] = WEBAPP_BASE_PATH.'view'.DIRECTORY_SEPARATOR.$filename;

	   foreach($paths as $p) {
		   if (@file_exists($p)) {
			   include($p);
			   return TRUE;
		   }
	   }
	   return FALSE;
   }
	
}

