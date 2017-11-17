<?php

/**
 * (c) 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 * */

/**
 * Invia una email usando un server SMTP
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 **/
class SimpleSMTPMail {

	public
		$errno,
		$error,
		$from, 
		$to, 
		$subject,
		$body;


	/**
	 * Imposta l'oggetto dell'email.
	 * 
	 * @param string $subject stringa con l'oggetto
	 **/
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	/**
	* Imposta il mittente.
	*
	* Deve essere nel formato "user@hostname"
	*
	* @param string $from il mittente
	*/
	public function setFrom($from) {
		$this->from = $from;
	}
	
	/**
	* Imposta il destinatario.
	*
	* Deve essere nel formato "user@hostname"
	*
	* @param string $to il destinatario
	*/
	public function setTo($to) {
		$this->to = $to;
	}
	
	/**
	* Imposta il corpo del messaggio.
	*
	* Deve essere solo testo.
	*
	* @param string $body il corpo del messaggio
	*/
	public function setBody($body) {
		$this->body = $body;
	}
	
	/**
	 * Ritorna il codice dell'errore SMTP.
	 * 
	 * @return integer il codice di errore 
	 */
	public function getErrno() {
		return $this->errno;
	}
	
	/**
	 * Ritorna il messaggio del server SMTP.
	 * 
	 * Può contenere un messaggio di errore, in base al codice di errore.
	 * 
	 * @return string il messaggio di errore
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * Invia il messaggio di posta.
	 * 
	 * Bisogna aver prima composto il messaggio.
	 * 
	 * I codici di errore si possono ottenere usando {@link getErrno} e {@link getError}.
	 * 
	 * @param string $hostname host del server di posta
	 * @param integer $port porta su cui è in ascolto il server (di solito 25)
	 * @param boolean $with_auth TRUE il server utilizza l'autenticazione
	 * @param string $username username in caso di server con autenticazione
	 * @param string $password password in caso di server con autenticazione
	 * @return boolean TRUE se il messaggio è stato inviato, FALSE altrimenti
	 */
	public function send($hostname, $port = 25, $with_auth = FALSE, $username = NULL, $password = NULL) {
		// Si connette al server
		$this->errno = 0;
		$this->error = "";
		$status = FALSE;
		
		$conn = fsockopen($hostname, $port, $this->errno, $this->error);
		if ($conn !== FALSE) {
			$this->readLine($conn, $this->errno, $this->error);
			if ($this->errno === "220") {
				// if (fwrite($conn, 'HELO '.$_SERVER['SERVER_ADDR']."\r\n" ) !== FALSE) {
				if (fwrite($conn, "HELO localhost\r\n" ) !== FALSE) {
					$this->readLine($conn, $this->errno, $this->error);
					// Il server ci ha salutato correttamente
					if ($this->errno === "250") {
						// verifica l'autorizzazione
						if ($with_auth) {
						}
						
						// Invia il messaggio
						$cmd = array(
							'MAIL FROM:<'.$this->from.">\r\n",
							'RCPT TO:<'.$this->to.">\r\n",
							"DATA\r\n"
						);
						if ($this->sendBatch($conn, $cmd, $this->errno, $this->error)) {
							$cmd = array(
								"Date: ".date("r")."\r\n",
								"From: ".$this->from."\r\n",
								"To: ".$this->to."\r\n",
								"Subject: ".$this->subject."\r\n",
								"MIME-Version: 1.0\r\n",
								"Content-Type: text/plain; charset=\"utf-8\"\r\n",
								
								"Content-Transfer-Encoding: 8bit\r\n",
								"\r\n",
								wordwrap($this->body, 76, "\r\n")
								
								/*
								"Content-Transfer-Encoding: base64\r\n",
								"\r\n",
								chunk_split(base64_encode($this->body), 76, "\r\n")
								*/
							);
							
							foreach ($cmd as $c) {
								if ($this->isConnected($conn)) {
									if (fwrite($conn, $c) !== FALSE) {
										echo "Sent: ", $c;
									}
								} else {
									return FALSE;
								}
							}
							// Invia l'ultimo comando
							$status = $this->sendBatch($conn, "\r\n.\r\n", $this->errno, $this->error);
						}
					}
				}
			}
			
			// Chiude la connessione
			fclose($conn);
			return $status;
		} else {
			return FALSE;
		}
	}

	/**
	 * Legge una (o più) righe in ingresso dal server
	 * 
	 * @param resource $conn connessione su cui opera
	 * @param integer $code codice di errore ritornato dal server
	 * @param string $str stringa di testo letta 
	 */
	private function readLine($conn, &$code, &$str) {
		$data = "";
		$code = 0;
		$str = "";
		do {
			$s = fgets($conn, 512);
			if ($s === FALSE) break;
			if (feof($conn)) break;
			$data .= $s;
			if (strlen($s) < 512 || $s[ strlen($s) - 1 ] == '\n' ) break;
		} while (1);
		
		// Esamina la stringa ottenuta
		if (is_numeric(substr( $data, 0, 3 ))) {
			$code = substr( $data, 0, 3 );
			$str = substr($data, 3);
		} else {
			$str = $data;
		}
	}
	
	/**
	 * Informa se siamo connessi al server di posta.
	 * 
	 * 
	 * @param resource $conn la connessione al server
	 * @return boolean TRUE se siamo connessi, FALSE altrimenti
	 */
	private function isConnected(&$conn) {
		if (!empty($conn)) {
			$data = stream_get_meta_data($conn);
			if ($data['eof']) {
				fclose($conn);
			} else {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Invia uno o più comandi al server.
	 * 
	 * In caso di più comandi si ferma al primo errore.
	 * 
	 * @param resource $conn connessione da utilizzare
	 * @param string|array $str stringa o array di stringhe coi comandi
	 * @param integer $code codice di errore
	 * @param string $message messaggio di errore
	 * @return boolean TRUE se tutto ok, FALSE in caso di errore.
	 */
	private function sendBatch($conn, $str, &$code, &$message) {
		if (!is_array($str)) $str = array($str);
		foreach($str as $s) {
			if ($this->isConnected($conn)) {
				if (fwrite($conn, $s) !== FALSE) {
					$this->readLine($conn, $code, $message);
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}
		return TRUE;
	}

}

