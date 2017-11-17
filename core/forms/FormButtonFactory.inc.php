<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
/**
	Pulsanti standardizzati nelle Form XHTML
	(c) 2008 by Luca Gambetta <l.gambetta@bluenine.it>
*/

/**
 * Fornisce pulsanti standard ad una applicazione.
 *
 * Il tipo di pulsante viene deciso dalle costanti:
 *
 * BTN_STOPEDIT - pulsante di interruzione di editazione
 * BTN_RESET - pulsante di reset
 * BTN_SEND - pulsante di invio modulo
 * BTN_DELETE - pulsante di rimozione
 * BTN_PUBLISH - pulsante per l'azione "Pubblica"
 * BTN_UNPUBLISH - pulsante per l'azione "Non Pubblicare"
 * BTN_TRASH - pulsante di "Sposta nel cestino"
 * BTN_RESTORE - pulsante di "Ripristina"
 * BTN_DELETE_ASK - pulsante di rimozione con conferma via javascript
 * BTN_PROCEED - pulsante per l'azione "procedi"
 * BTN_CANCEL - pulsante per l'azione "Interrompi l'operazione"
 * BTN_NEXT - pulsante per l'azione "Successivo" (vai all'elemento successivo)
 * BTN_PREVIOUS - pulsante per l'azione "Precedente" (vai all'elemento precedente)
 * BTN_SAVE - pulsante per l'azione "Salva"
 * BTN_SAVE_CONTINUE - pulsante per l'azione "Salva e continua"
 * BTN_DELETE_SELECTED - pulsante per l'azione "Rimuovi i selezionati"
 *
 *
*/
class FormButtonFactory {
	const
		BTN_STOPEDIT = 1, // mostra pulsante di interruzione di editazione
		BTN_RESET = 2, // mostra pulsante di reset
		BTN_SEND = 4, // mostra pulsante di invia
		BTN_DELETE = 6, // mostra pulsante di rimozione
		BTN_PUBLISH = 8, // mostra pulsante di "Pubblica"
		BTN_UNPUBLISH = 10, // mostra pulsante di "Non Pubblicare"
		BTN_TRASH = 12, // mostra pulsante di "Sposta nel cestino"
		BTN_RESTORE = 14, // mostra pulsante di "Ripristina"
		BTN_DELETE_ASK = 16, // mostra pulsante di rimozione con conferma via javascript
		BTN_PROCEED = 18, // mostra pulsante di "procedi"
		BTN_CANCEL = 20, // mostra pulsante di "Interrompi l'operazione"
		BTN_NEXT = 22, // mostra pulsante di "Successivo >>"
		BTN_PREVIOUS = 24, // mostra pulsante di "<< Precedente"
		BTN_SAVE = 26, // mostra pulsante di "Salva"
		BTN_SAVE_CONTINUE = 28, // mostra pulsante di "Salva e continua"
		BTN_DELETE_SELECTED = 30; // mostra pulsante di "Rimuovi i selezionati"

	protected static
		$button_registry = array(
			self::BTN_STOPEDIT => 'frm_btn_stopedit',
			self::BTN_RESET => 'frm_btn_reset',
			self::BTN_SEND => 'frm_btn_send',
			self::BTN_DELETE => 'frm_btn_delete',
			self::BTN_DELETE_ASK => 'frm_btn_delete',
			self::BTN_PUBLISH => 'frm_btn_publish',
			self::BTN_UNPUBLISH => 'frm_btn_unpublish',
			self::BTN_TRASH => 'frm_btn_trash',
			self::BTN_RESTORE => 'frm_btn_restore',
			self::BTN_PROCEED => 'frm_btn_proceed',
			self::BTN_CANCEL => 'frm_btn_stop',
			self::BTN_NEXT => 'frm_btn_next',
			self::BTN_PREVIOUS => 'frm_btn_previous',
			self::BTN_SAVE => 'frm_btn_save',
			self::BTN_SAVE_CONTINUE => 'frm_btn_save_continue',
			self::BTN_DELETE_SELECTED => 'frm_btn_delete_sel'
		);


	/**
	 * Ritorna un bottone standard
	 * 
	 * L'etichetta dei pulsanti è:
	 * 
	 * BTN_STOPEDIT - "Interrompi!"
	 * BTN_RESET - "Annulla Modifiche"
	 * BTN_SEND - "Invia!"
	 * BTN_DELETE - "Rimuovi..."
	 * BTN_PUBLISH - "Pubblica!"
	 * BTN_UNPUBLISH - "Non pubblicare"
	 * BTN_TRASH - "Metti nel Cestino"
	 * BTN_RESTORE - "Ripristina"
	 * BTN_DELETE_ASK - "Rimuovi..."
	 * BTN_PROCEED - "Procedi..."
	 * BTN_CANCEL - "Interrompi l'operazione"
	 * BTN_NEXT - "Successivo >>"
	 * BTN_PREVIOUS - "<< Precedente"
	 * BTN_SAVE - "Salva..."
	 * BTN_SAVE_CONTINUE - "Salva e continua..."
	 * BTN_DELETE_SELECTED - "Rimuovi i selezionati..."
	 * 
	 * Cerca sempre di tradurre l'etichetta nella lingua corrente.
	 * 
	 * Per generare il nome utilizza {@link FormButtonFactory::getName()}.
	 * 
	 * Possono essere passati dei parametri utili alla costruzione
	 * del pulsante in un array associativo.
	 * 
	 * La creazione dei pulsanti viene delegata all'oggetto PageLayout corrente.
	 * 
	 * @param integer $type costante per il tipo di pulsante
	 * @param array $params parametri da usare per costruire il pulsante
	 * @return FormButton il pulsante richiesto
	*/
	public static function getButton($type, $params = array()) {
		return WebApp::getInstance()->getPageLayout()->getWidgetStyle()->getFormButtonFactoryButton($type, $params);
	}

	/**
	 * Ritorna il nome standard del pulsante.
	 *
	 * Ritorna i seguenti nomi:
	 *
	 *
	 * @param integer $type tipo del pulsante di cui si vuole il nome
	 * @return string una stringa col nome, vuota se il tipo di pulsante è sconosciuto.
	 */
	public static function getName($type) {
		if (array_key_exists($type, self::$button_registry)) return self::$button_registry[$type];
		else return '';
	}

	/**
	 * Dato il nome di un pulsante, ritorna il suo tipo
	 *
	 * @param string $name nome del pulsante
	 * @return integer valore delle costanti dei pulsanti o 0 se il pulsante è sconosciuto
	 */
	public static function getButtonTypeFromName($name) {
		$k = array_search($name, self::$button_registry);
		if ($k !== FALSE) return $k;
		else return 0;
	}
	
	/**
	 * Verifica se una form sia stata attivata da un pulsante specifico
	 * 
	 * @param Form $form la Form da controllare
	 * @param integer $button_type una delle costanti di tipo pulsante
	 * @return boolean TRUE se è stata attivata dal pulsante, FALSE altrimenti
	*/
	public static function isActivatedBy(Form $form, $button_type) {
		return $form->getActivator() == self::getName($button_type);
	}
	
	/**
	 * Verifica se nella richiesta HTTP sia presente il pulsante specificato.
	 *
	 * @param integer $button_type una delle costanti di tipo pulsante
	 */
	public static function requestHasActivator($button_type) {
		return Request::getInstance()->hasParameter(self::getButtonName($button_type));
	}
}
