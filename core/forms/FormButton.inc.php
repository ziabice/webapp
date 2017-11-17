<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un pulsante da aggiungere ad un modulo xhtml.
 * 
 * Il pulsante è una astrazione che associa ad un bottone di submit
 * altre caratteristiche.
 * 
 * I pulsanti vanno aggiunti ad un oggetto Form usando {@link Form::addButton}.
 * 
 * Di solito il pulsante viene visualizzato da FormView in una bottoniera
 * a fondo modulo, ma potrebbe andare dovunque.
 * Prima di poter essere visualizzato deve essere associato ad una istanza di FormView
 * (usando {@link FormButton::setFormView}) che gli fornisce le funzioni di visualizzazione.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class FormButton {
	protected
		 $name = '', $label = '', 
		 $accel = '', $extra = '', 
		 $title = '',

		$form_view = NULL, // Istanza di FormView a cui appartiene
		$form = NULL; // Istanza di Form a cui appartiene

	/**
	 * @param string $name stringa col nome del pulsante
	 * @param string $label stringa XHTML con l'etichetta presentata all'utente
	 * @param string $accel carattere con l'acceleratore di tastiera
	 * @param string $title stringa (non xhtml) con il testo di aiuto per il pulsante
	 * @param string $extra stringa col codice xhtml per gli attributi aggiuntivi di questo pulsante
	*/
	public function __construct($name, $label = '', $accel = '', $title = '', $extra = '') {
		$this->name = $name;
		$this->label = $label;
		$this->accel = $accel;
		$this->title = $title;
		$this->extra = $extra;
	}

	/**
	 * Ritorna il nome del controllo xhtml
	 *
	 * @return string una stringa col nome
	*/
	public function getName() { 
		return $this->name; 
	}
	
	/**
	 * Ritorna l'etichetta usata per il pulsante
	 * @return string una stringa con il testo
	*/
	public function getLabel() { 
		return $this->label; 
	}
	
	/**
	 * Ritorna il tasto di accesso rapido per il pulsante
	 * @return string un carattere con l'acceleratore di tastiera
	*/
	public function getAccessKey() {
		return $this->accel; 
	}
	
	/**
	 * Ritorna il titolo del controllo (il tooltip mostrato quando 
	 * il mouse passa sul controllo)
	 * 
	 * @return string una stringa col testo
	*/
	public function getTitle() { 
		return $this->title; 
	}

	/**
	 * Restituisce una rappresentazione xhtml del controllo.
	 * 
	 * Per ottenere tale rappresentazione viene utilizzato l'oggetto FormView
	 * associato: occorre perciò aver effettuato una chiamata a {@link setFormView}.
	 * 
	 * L'associazione viene fatta automaticamente dall'oggetto Form in cui il pulsante
	 * è stato inserito.
	 *
	 * @return string una stringa col codice xhtml
	*/
	public function render() {
		/*
		return $this->form_view->submit(
			htmlspecialchars($this->getName()),
			XHTML::toHTML($this->getLabel()),
			FALSE,
			$this->form_view->accessibility(XHTML::toHTML($this->getTitle()), '', $this->getAccessKey()).$this->extra
		);*/
		return $this->form_view->button(
			$this->getName(),
			'',
			$this->getLabel(),
			BaseFormView::BUTTON_TYPE_SUBMIT,
			$this->form_view->accessibility(XHTML::toHTML($this->getTitle()), '', $this->getAccessKey()).$this->extra
		);
	}

	/**
	 * Ritorna uno o più stringhe da utilizzare per il controllo dell'attivazione.
	 * @return mixed stringa o array di stringhe con gli attivatori
	*/
	public function getActivator() {
		return $this->name;
	}

	/**
	 * Imposta  la stringa con gli attributi extra da usare con il pulsante.
	 *
	 * E' una stringa del tipo ' tabindex="0" class="foo"'
	 *
	 * @param string $txt gli attributi extra
	*/
	public function addExtra($txt) {
		$this->extra .= $txt;
	}

	/**
	 * Invocata quando si associa ad una Form
	*/
	protected function onFormAttach() {
	}

	/**
	 * Invocata quando si associa un FormView (di solito prima del rendering)
	*/
	protected function onFormViewAttach() {
	}

	/**
	 * Ritorna l'istanza di Form a cui il bottone è associato
	 *
	 * @return Form una istanza di Form
	*/
	public function getForm() {
		return $this->form;
	}

	/**
	 * Ritorna l'istanza di FormView a cui il bottone è associato
	 * 
	 * @return FormView una istanza di FormView
	*/
	public function getFormView() {
		return $this->form_view;
	}
	
	/**
	 * Associa una {@link Form}.
	 * 
	 * Viene invocata dalla Form stessa nel metodo {@linkk Form::addButton}.
	 * Invoca {@link onFormAttach}
	 *
	 * @param Form $form l'istanza di Form da associare
	 * @return Form l'istanza di Form
	*/
	public function setForm(Form $form) {
		$this->form = $form;
		$this->onFormAttach();
		return $form;
	}
	
	/**
	 * Associa una BaseFormView.
	 * 
	 * Viene invocata al momento del rendering.
	 * 
	 * @param BaseFormView $formview l'istanza di FormView da associare
	 * @return FormView l'istanza di FormView
	*/
	public function setFormView(BaseFormView $formview) {
		$this->form_view = $formview;
		$this->onFormViewAttach();
		return $formview;
	}
}

