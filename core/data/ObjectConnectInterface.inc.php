<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Permette di legare due oggetti in modo che le modifiche applicate ad uno
 * vengano segnalate a quello a cui è connesso.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
interface ObjectConnectInterface {

	/**
	 * Informa se questo oggetto sia cambiato.
	 * Una volta letta la proprietà viene riportata a FALSE.
	 * @return boolean TRUE se l'oggetto è cambiato, FALSE altrimenti
	 */
	public function isChanged();

    /**
	 * Specifica l'oggetto da connettere a questo, che verrà
	 * notificato delle modifiche nella proprietà.
	 *
	 * @param ObjectConnectInterface $obj l'oggetto destinatario delle notifiche
	 */
	public function connect(ObjectConnectInterface $obj);

	/**
	 * Rimuove l'associazione con un oggetto
	 */
	public function disconnect();

	/**
	 * Imposta il flag interno che l'oggetto è cambiato e notifica all'oggetto
	 * collegato mediante una chiamata a touch() questo cambiamento.
	 *
	 * @param object $sender chi ha generato il cambiamento, se NULL usa se stesso
	 */
	public function touch($sender = NULL);
}

