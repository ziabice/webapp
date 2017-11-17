<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Classe base con lo stile per il disegno dei widget.
 * 
 * Verrà sempre istanziata una classe chiamata WidgetStyle, questa è solo una base.
 * 
 * Viene usata da BasePageLayout per fornire alle classi che ad essa si appoggiano per
 * fornire i vari stili.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class BaseWidgetStyle {
	
	/**
	 * Ritorna un gestore di CSS per il widget.
	 *
	 * @param mixed $view l'oggetto che richiede il gestore di CSS.
	 * @return CSS
	 */
	public function getCSS($view = NULL) {
		return new CSS();
	}
	
	/**
	 * Ritorna lo stile per il componente view delle form.
	 * 
	 * @param BaseFormView $view la view di cui si vuole lo stile
	 * @return FormViewStyle lo stile della form
	 */
	public function getFormViewStyle($view = NULL) {
		return new FormViewStyle();
	}
	
	/**
	 * Ritorna lo stile per il componente view del paginatore.
	 * 
	 * Quando viene passato un oggetto di tipo TabularPagerView, ritorna
	 * una istanza di {@link TabularPagerViewStyle}.
	 * 
	 * @param PagerView $view la view di cui si vuole lo stile
	 * @return TabularPagerViewStyle|PagerViewStyle lo stile del paginatore
	 */
	public function getPagerViewStyle($view = NULL) {
		if ($view instanceof TabularPagerView) return new TabularPagerViewStyle();
		return new PagerViewStyle();
	}

	/**
	 * Ritorna un pulsante per la form button factory.
	 *
	 * Viene usato da FormButtonFactory per creare un pulsante standard.
	 *
	 * @param integer $type il tipo di pulsante da creare
	 * @param array $params array associativo con eventuali parametri aggiuntivi
	 * @return FormButton
	 * @see FormButtonFactory
	 */
	public function getFormButtonFactoryButton($type, $params = array()) {
		$label = tr('Premi qui');
		$name = FormButtonFactory::getName($type);
		if (empty($name)) $name = 'frm_btn';
		$accel = '';
		$extra = '';
		$title = '';

		switch($type) {
			case FormButtonFactory::BTN_STOPEDIT: $label = tr('Interrompi!'); $accel ='s'; break;
			case FormButtonFactory::BTN_RESET: $label = tr('Annulla Modifiche'); $accel = 'a'; break;
			case FormButtonFactory::BTN_SEND: $label = tr('Invia!'); $accel ='i';  break;
			case FormButtonFactory::BTN_DELETE_ASK: $label = tr('Rimuovi...'); $accel = 'e';
			$extra = 'onclick="return window.confirm(\''.tr('Sei sicuro di voler procedere con la rimozione?').'\');"';break;
			case FormButtonFactory::BTN_DELETE: $label = tr('Rimuovi...'); $accel ='e'; break;
			case FormButtonFactory::BTN_PUBLISH: $label = tr('Pubblica!'); $accel ='p'; break;
			case FormButtonFactory::BTN_UNPUBLISH: $label = tr('Non pubblicare'); $accel ='u'; break;
			case FormButtonFactory::BTN_TRASH: $label = tr('Metti nel Cestino'); $accel ='t'; break;
			case FormButtonFactory::BTN_RESTORE: $label = tr('Ripristina'); $accel ='r'; break;
			case FormButtonFactory::BTN_PROCEED: $label = tr('Procedi...'); $accel ='p'; break;
			case FormButtonFactory::BTN_CANCEL: $label = tr('Interrompi l\'operazione...'); $accel ='s'; break;
			case FormButtonFactory::BTN_NEXT: $label = tr('Successivo >>'); $accel ='n'; break;
			case FormButtonFactory::BTN_PREVIOUS: $label = tr('<< Precedente'); $accel ='p'; break;
			case FormButtonFactory::BTN_SAVE: $label = tr('Salva...'); $accel ='s'; break;
			case FormButtonFactory::BTN_SAVE_CONTINUE: $label = tr('Salva e continua...'); $accel ='s'; break;
			case FormButtonFactory::BTN_DELETE_SELECTED: $label = tr('Rimuovi i selezionati...'); $accel ='d';break;
		}
		if ($type == FormButtonFactory::BTN_RESET) {
			return new FormResetButton($name, tr($label), $accel, tr($title), $extra);
		} else {
			return new FormButton($name, tr($label), $accel, tr($title), $extra);
		}
	}
}

