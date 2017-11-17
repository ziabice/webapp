<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Implementa l'interfaccia ObjectConnectInterface per connettere due
 * oggetti.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class ObjectConnectAdapter implements ObjectConnectInterface {
    protected
		$_changed = FALSE, // flag che indica se sia cambiato dall'ultimo controllo
		$_connected_object = NULL; // oggetto collegato

	/**
	 * Informa se questo oggetto sia cambiato.
	 * Una volta letta la proprietà viene riportata a FALSE.
	 * @return boolean TRUE se l'oggetto è cambiato, FALSE altrimenti
	 */
	public function isChanged() {
		if ($this->_changed) {
			$this->_changed = FALSE;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Specifica l'oggetto da connettere a questo, che verrà
	 * notificato delle modifiche nella proprietà.
	 *
	 * @param ObjectConnectInterface $obj l'oggetto destinatario delle notifiche
	 */
	public function connect(ObjectConnectInterface $obj) {
		$this->_connected_object = $obj;
	}

	/**
	 * Rimuove l'associazione con un oggetto
	 */
	public function disconnect() {
		$this->_connected_object = NULL;
	}

	/**
	 * Notifica all'oggetto collegato che l'istanza è stata cambiata
	 * @param object $sender oggetto che ha cambiato
	 */
	public function touch($sender = NULL) {
		$this->_changed = TRUE;
		if (is_object($this->_connected_object)) {
			$this->_connected_object->touch( is_null($sender) ? $this : $sender );
		}
	}
}

