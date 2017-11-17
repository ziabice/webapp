<?php

/**
 * (c) 2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una parte in un messaggio MIME
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class MIMEMailPart {
	public
		$separator,
		$content,
		$content_type,
		$content_transfer_encoding,
		$content_id,
		$headers;
	
	public function __construct() {
		$this->separator = "\r\n";
		$this->headers = array();
	}
	
	/**
	 * Imposta il transfer encoding per il contenuto.
	 * 
	 * Valori validi:
	 * 7bit
	 * 8bit
	 * binary
	 * quoted-printable
	 * base64
	 * 
	 * @param string $transfer_encoding il transfer encoding
	 */
	public function setContentTransferEncoding($transfer_encoding) {
		$this->content_transfer_encoding = $transfer_encoding;
	}
	
	/**
	 * Imposta il tipo MIME per il contenuto.
	 * 
	 * Va passata una stringa completa nel caso il tipo abbia dei parametri:
	 * Esempio: setContentType("text/plain; charset=utf-8");
	 * 
	 * @param string $content_type il tipo MIME del contenuto
	 */
	public function setContentType($content_type) {
		$this->content_type = $content_type;
	}
	
	/**
	 * Imposta il contenuto della parte.
	 * 
	 * La stringa deve rispettare sia il content type che il transfer encoding.
	 * 
	 * @param string $content una stringa coi dati
	 */
	public function setContent($content) {
		$this->content = $content;
	}
	
	/**
	 * Imposta l'ID unicovo della parte.
	 * 
	 * @param string $id l'ID univoco della parte
	 */
	public function setContentID($id) {
		$this->content_id = strval($id);
	}

	/**
	 * Genera una parte MIME per del testo.
	 * 
	 * Il set di caratteri della stringa di testo delle rispettare quello
	 * dichiarato.
	 * 
	 * @param string $text la stringa di testo
	 * @param string $charset il set di caratteri usato per la codifica
	 * @param string $mime il tipo MIME del testo
	 * @return MIMEMailPart
	 */
	public static function newTextPart($text, $charset="utf-8", $mime = 'text/plain') {
		$p = new MIMEMailPart();
		
		if (function_exists('quoted_printable_encode')) {
			$te = "quoted-printable";
			$p->setContent( quoted_printable_encode($text) );
		} elseif(function_exists('imap_8bit')) {
			$te = "quoted-printable";
			$p->setContent( imap_8bit($text) );
		} else {
			$te = "base64";
			$p->setContent(chunk_split(base64_encode($text)));
		}
		
		$p->setContentType($mime."; charset=".$charset);
		$p->setContentTransferEncoding($te);
	
		return $p;
	}
	
	/**
	 * Genera una parte di dati codificata in base64.
	 * 
	 * @param string $mime tipo MIME dei dati
	 * @param string $data stringa di dati
	 * @param string $name nome del file indicato dalla parte
	 * @return MIMEMailPart
	 */
	public static function newBase64Part($mime, $data, $name) {
		$p = new MIMEMailPart();
		$p->setContentTransferEncoding( "base64" );
		$p->setContent(chunk_split(base64_encode($data)));
		
		$p->setContentType($mime.(empty($name) ? '' : '; name="'.$name.'"' ));
		$p->setHeader("Content-Disposition", "attachment; filename=\"".(empty($name) ? 'file' : $name )."\"");
		return $p;
	}

	/**
	 * Ritorna la rappresentazione testuale della parte.
	 * 
	 * Ogni riga è terminata per CRLF.
	 * 
	 * @return string la rappresentazione testuale della parte
	 */
	public function get() {
		return "Content-Type: ".$this->content_type.$this->separator.
				"Content-Transfer-Encoding: ".$this->content_transfer_encoding.$this->separator.
				(empty($this->content_id) ? '' : 'Content-ID: <'.$this->content_id.'>'.$this->separator).
				$this->getHeaders().
				$this->separator.
				$this->content.$this->separator;
	}
	
	/**
	 * Imposta un header.
	 * 
	 * @param string $name nome dell'header
	 * @param string $value valore per l'header
	 */
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}
			
	
	/**
	 * Formatta gli header come se dovessero essere inviati in un messaggio.
	 * 
	 * @return string la stringa con gli header
	 */
	public function getHeaders() {
		$str = '';
		foreach ($this->headers as $h => $v) $str .= $h.": ".$v.$this->separator;
		return $str;
	}
	
}

