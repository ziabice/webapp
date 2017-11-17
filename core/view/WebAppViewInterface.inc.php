<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Fornisce il View alla WebApp corrente.
 * 
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
 */
interface WebAppViewInterface {
	
	/**
	 * Inizializza il view.
	 * 
	 * @return boolean TRUE se l'inizializzazione è riuscita, FALSE altrimenti
	 */
	public function initialize();
	
	/**
	 * Fornisce l'istanza di PageLayout che gestisce il View.
	 * 
	 * @return PageLayout l'oggetto che gestisce il View.
	 */
	public function getPageLayout();
	
	/**
	* Informa se sia stato impostato un gestore di layout di pagina.
	*
	* @return boolean TRUE se c'è il gestore, FALSE altrimenti
	*/
	public function hasPageLayout();
	
	/**
	 * Imposta un gestore di layout di pagina per questo View,
	 *
	 * @param BasePageLayout $page_layout il gestore di layout
	 *
	 * @return BasePageLayout l'oggetto aggiunto
	 */
	public function setPageLayout(BasePageLayout $page_layout);

	/**
	 * Azzera la configurazione del View, rendendo necessario lanciare
	 * di nuovo {@link initialize} per avere un PageLayout su cui operare.
	 */
	public function reset();
	
	/**
	 * Ritorna (e controlla) il percorso di un file di View.
	 * 
	 * Se il nome è esplicitato viene ritornato il file in un percorso standard,
	 * altrimenti viene ritornato il percorso di un file convenzionale basato 
	 * sull'azione corrente.
	 * 
	 * Poste le variabili:
	 * %modulocorrente% - nome del modulo corrente
	 * %azionecorrente% - nome dell'azione corrente
	 * %pathapplicazione% - percorso dell'applicazione corrente
	 * %nomefile% - nome completo del file
	 * 
	 * I percorsi standard sono:
	 *
	 * %modulocorrente%/view/%nomefile%
	 * %pathapplicazione%/view/%nomefile%
	 *
	 * Mentre i path convenzionali:
	 *
	 * %modulocorrente%/view/%azionecorrente%.html|.php|.phtml
	 * %modulocorrente%/view/%modulocorrente%.html|.php|.phtml
	 * %modulocorrente%/view/layout.html|.php|.phtml
	 * %pathapplicazione%/view/layout.html|.php|.phtml
	 *
	 * Verifica se il file richiesto esista, se non esiste o il percorso 
	 * non è raggiungibile ritorna FALSE.
	 *
	 * @param string $filename stringa vuota o col nome del file da cercare
	 * @return string|boolean con il percorso o FALSE se il file non esiste
	 * */
	public function getViewPath($filename = '');
}

