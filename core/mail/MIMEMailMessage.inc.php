<?php
/**
 * (c) 2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un messaggio da inviare per email.
 * 
 * Presuppone l'utilizzo della codifica utf-8.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class MIMEMailMessage {
    
	protected
		/**
		 * @var string il separatore tra le varie parti
		 */
		$boundary,
		/**
		 * @var array delle varie parti
		 */
		$parts,
		/**
		 * @var array array associativo con gli header
		 */
		$headers,
		/**
		 * @var array destinatari in Cc
		 */
		$recipients_cc,
		/**
		 * @var array destinatari in Bcc
		 */
		$recipients_bcc,
		$from, // stringa Indirizzo from dell'email
		$subject, // stringa con l'oggetto dell'email
		$header_separator;  // stringa con il separatore per gli header

	public function __construct($from, $subject) {
		$this->from = trim($from);
		$this->subject = trim($subject);
		$this->header_separator = "\r\n";
		
		$this->boundary = "_MIMEBoundary-".strval(time())."_";
		
		$this->addHeader("MIME-Version", "1.0");
		$this->addHeader("From", $this->from);
		$this->setContentType("multipart/mixed;\n boundary=\"".$this->boundary."\"");
		$this->recipients_bcc = array();
		$this->recipients_cc = array();
	}
	
	public function getBoundary() {
		return $this->boundary;
	}
	
	public function setContentType($content_type) {
		$this->addHeader("Content-Type", $content_type);
	}
	
	public function setFrom($from) {
		$this->from = trim($from);
		$this->addHeader("From", $this->from);
	}
	
	public function addCc($address) {
		$this->recipients_cc[] = trim($address);
	}
	
	public function addBcc($address) {
		$this->recipients_bcc[] = trim($address);
	}

	/**
	 * Imposta l'oggetto dell'email.
	 * 
	 * @param string $subject l'oggetto dell'email
	 */
	public function setSubject($subject) {
		$this->subject = strval($subject);
	}
	
	/**
	 * Ritorna l'oggetto dell'email correttamente codificato per andare in un header.
	 * 
	 * @return string l'oggetto codificato
	 */
	public function getEncodedSubject() {
		return "=?UTF-8?B?".base64_encode($this->subject)."?=";
	}
	
	/**
	 * Ritorna il separatore usato per gli header.
	 *
	 * Ritorna di solito la sequenza "\r\n" CRLF come da RFC 2822.
	 *
	 * @return string
	 */
	public function getHeaderSeparator() {
		return $this->header_separator;
	}

	/**
	 * Imposta il separatore usato per gli header.
	 *
	 * Normalmente il separatore viene già posto a CRLF, ma per alcuni
	 * mailer potrebbe essere
	 */
	public function setHeaderSeparator($separator) {
		$this->header_separator = $separator;
	}

	/**
	 * Aggiunge un header.
	 *
	 * @param string $name nome dell'header
	 * @param string $value valore dell'headerma
	 */
	public function addHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	
	/**
	 * Aggiunge una parte al messaggio.
	 * 
	 * @param MIMEMailPart $part
	 * @return MIMEMailPart
	 */
	public function addPart(MIMEMailPart $part) {
		$this->parts[] = $part;
		return $part;
	}
	
	/**
	 * Ritorna le parti che compongono il messaggio.
	 * 
	 * @return array array di MIMEMailPart
	 */
	public function getParts() {
		return $this->parts;
	}
	
	/**
	 * Ritorna il corpo del messaggio.
	 * 
	 * Viene ritornato il messaggio con ogni parte compresa
	 * nei separatori delle parti.
	 * Ogni riga termina per CRLF. La stringa termina con una riga vuota con CRLF.
	 * 
	 * @return string il corpo del messaggio
	 */
	public function getMessage() {
		$str = '';
		foreach($this->parts as $part) {
			
			$str .= "--".$this->boundary."\r\n";
			$str .= $part->get();
			$str .= "\r\n";
		}
		
		if (!empty($this->parts)) $str .= "--".$this->boundary."--\r\n";
		$str .= "\r\n";
		return $str;
	}
	
	/**
	 * Ritorna tutti gli header che compongono il messaggio.
	 * 
	 * Ogni riga termina per CRLF.
	 * 
	 * @return string una stringa
	 */
	public function getMessageHeaders() {
		$str = '';
		foreach ($this->headers as $h => $v) $str .= $h.": ".$v."\r\n";
		
		if (!empty($this->recipients_cc)) {
			$str .= "Cc: ".implode(",", $this->recipients_cc)."\r\n";
		}
		
		if (!empty($this->recipients_bcc)) {
			$str .= "Bcc: ".implode(",", $this->recipients_bcc)."\r\n";
		}
		
		return $str;
	}
	
}
