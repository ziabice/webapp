<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Astrae l'upload di uno o più file.
 * 
 * Uno o più file file di cui si richiede l'upload.
 * Implementa un Iterator perchè il file di cui si vuole fare l'upload potrebbe
 * essere più di uno, ciclando sull'iteratore si accede al file voluto.
 * Ad ogni file in upload è associato un descrittore, istanza di {@link UploadedFileDescriptor},
 * che ne descrive le caratteristiche. In particolare ne ricava il tipo MIME, dato
 * che le informazioni che si ricevono dal client non sono plausibili.
 *
 * Esempi d'uso:
 *
 * Scenario: Avete creato una form xhtml con i giusti parametri per l'upload di
 * file. Il controllo xhtml con il file in upload si chiama "uploaded_file".
 * I file vanno posti nella directory "uploads/".
 *
 * <code>
 * try {
 *
 *  $upload = new UploadedFile( 'uploaded_file' );
 *
 *  # Verifica che il file sia presente
 *  if ($upload->hasUploadedFile()) {
 *		# Sposta il file di cui si è fatto l'upload
 *
 *		# 1. crea un nome univoco, usando il nome del file originale
 *		$uf = $upload->getCurrentFileDescriptor();
 *		$filename = FileUtils::allocateUniqueFile('uploads/', FileUtils::normalizeName($uf->getName()), 0755);
 *
 *		# 2. ottenuto un nome, sposta il file nella destinazione
 *		if ($filename !== FALSE) {
 *			$upload->moveTo( 'uploads/'.$filename );
 *		} else {
 *			echo "Impossibile salvare il file.";
 *		}
 *
 *  } else {
 *    echo "Il file inviato è vuoto o è stato impossibile utilizzarlo.";
 *  }
 * }
 * catch (FileUploadException $e) {
 *   echo $e->getMessage();
 * }
 *
 * </code>
 *
 * Scenario: si vogliono caricare più file in una form, col nome "uploaded_file".
 *
 * Bisogna creare una form che presenti dei campi col nome "uploaded_file[]":
 * <code>
 * ...
 * <input type="file" name="uploaded_file[]">
 * <input type="file" name="uploaded_file[]">
 * <input type="file" name="uploaded_file[]">
 * ...
 * </code>
 *
 * In questo modo accettiamo in input 3 campi coi file. Poi bisogna usare
 * il seguente codice per compiere le operazioni di upload:
 *
 * <code>
 * try {
 *	$upload = new UploadedFile( 'uploaded_file', TRUE );
 *
 *	# cicla sui file
 *  foreach($upload as $uploaded_file) {
 *		if ($upload->hasUploadedFile()) {
 *			$uf = $upload->getCurrentFileDescriptor();
 *			$filename = FileUtils::allocateUniqueFile('uploads/', FileUtils::normalizeName($uf->getName()), 0755);
 *
 *			if ($filename !== FALSE) {
 *				$upload->moveTo( 'uploads/'.$filename );
 *			} else {
 *				echo "Impossibile salvare il file ", $uf->getName();
 *			}
 *		}
 *  }
 * }
 * catch (FileUploadException $e) {
 *   echo $e->getMessage();
 * }
 * </code>
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * @see UploadedFileDescriptor
*/
class UploadedFile implements Iterator {
  
  private 
	$upload_filename, // stringa col nome del (o dei) file di cui fare l'upload
	$is_multiple, // boolean: è un upload multiplo?
	$desc_table, // array di UploadedFileDescriptor per file multipli
	$current_file; // UploadedFileDescriptor col file corrente 
  
	/**
	 * Costruisce l'oggetto informandolo del nome del o dei file che si vuole caricare sul server
	 * e se si tratti di un upload multiplo.
	 * 
	 * Si porta subito sul primo file disponibile.
	 *
	 * @param string $filename stringa col nome dei file con cui si vuole lavorare
	 * @param boolean $is_multiple TRUE se si tratta di upload di file multipli, FALSE altrimenti
	 * @throws FileUploadException se non esiste un file col nome $filename specificato
	*/
	public function __construct($filename, $is_multiple = FALSE) {
		$this->upload_filename = $filename;
		$this->is_multiple = $is_multiple;
		
		// Se il file non esiste, solleva un'eccezione
		if (!array_key_exists($filename, $_FILES) ) {
			throw new FileUploadException('', UPLOAD_ERR_NO_FILE);
		}
		if ($is_multiple) {
			$this->desc_table = array();
			$n = count( $_FILES[$filename]['name'] );
			for($i = 0; $i < $n; $i++) $this->desc_table[] = $this->createUploadedFileDescriptor($filename, $i);
			$this->current_file = $this->desc_table[0];
		} else {
			$this->desc_table = array(
				$this->createUploadedFileDescriptor($filename)
			);
		}
		$this->current_file = $this->desc_table[0];
		$this->valid_iter = FALSE;
	}
	
	/**
	 * Crea un descritto di file.
	 *
	 * Usato internamente, crea un {@link UploadedFileDescriptor} in base al nome del
	 * file. Nel caso di upload multipli utilizza l'indice dello stesso.
	 * 
	 * @param string $name nome del file in $_FILES
	 * @param integer $index NULL o l'eventuale indice di un file in un upload multplo
	 * @return UploadedFileDescriptor con i dati del file
	*/
	protected function createUploadedFileDescriptor($name, $index = null) {
		if ( is_null($index) ) {
			$out = new UploadedFileDescriptor( (get_magic_quotes_gpc() ? stripslashes($_FILES[$name]['name']) : $_FILES[$name]['name']),
				$_FILES[$name]['size'],
				$_FILES[$name]['type'],
				FileUtils::getMIME($_FILES[$name]['tmp_name'], TRUE),
				$_FILES[$name]['tmp_name'],
				$_FILES[$name]['error'] );
		} else {
			$out = new UploadedFileDescriptor( (get_magic_quotes_gpc() ? stripslashes($_FILES[$name]['name'][$index]) : $_FILES[$name]['name'][$index]) ,
				$_FILES[$name]['size'][$index],
				$_FILES[$name]['type'][$index],
				FileUtils::getMIME($_FILES[$name]['tmp_name'][$index], TRUE),
				$_FILES[$name]['tmp_name'][$index],
				$_FILES[$name]['error'][$index] );
		}
		
		return $out;
	}
	
	/**
	 * Informa se si sta lavorando con upload multipli
	 *
	 * @return boolean TRUE se lavora in modalità file multipli, FALSE altrimenti
	*/
	public function isMultiple() {
		return $this->is_multiple;
	}
	
	/**
	 * In caso di upload multipli ritorna il numero dei file caricati
	 *
	 * @return integer un intero col numero di file caricati
	*/
	public function getFileCount() {
		return count( $this->desc_table );
	}
	
	/**
	 * Ritorna il il descrittore del file correntemente in upload
	 *
	 * @return UploadedFileDescriptor una istanza del descrittore
	*/
	public function getCurrentFileDescriptor() {
		return $this->current_file;
	}
	
	/**
	 * Informa se ci sia una condizione di errore col file correntemente in upload.
	 *
	 * @return boolean TRUE se c'è un errore, FALSE altrimenti
	*/
	public function isError() {
		return $this->current_file->getError() != UPLOAD_ERR_OK;
	}
  
	/**
	 * Informa se l'utente abbia effettivamente fornito un file per l'upload
	 *
	 * Non verifica se gli eventuali file forniti siano corretti
	 *
	 * @return boolean TRUE se c'è il file, FALSE altrimenti
	*/
	public function hasUploadedFile() {
		return !$this->current_file->isEmptySize() && $this->current_file->hasTempFile();
	}
	
	/**
	 * Sposta il file correntmente in upload verso una destinazione.
	 * 
	 * La destinazione è un path completo /foo/my_file.ext: se il file esiste già viene sovrascritto
	 * 
	 * @param string $destination_path percorso completo di destinazione
	*/
	public function moveUploadedFile($destination_path) {
		return $this->current_file->moveTo($destination_path);
	}

	// ------------- Implementazione dell'iterator
	public function rewind() {
		$this->current_file = reset( $this->desc_table );
	}
	
	public function next() {
		$this->current_file = next( $this->desc_table );
	}
	
	public function current() {
		return $this->current_file;
	}
	
	public function key() {
		return key($this->desc_table);
	}
	
	public function valid() {
		return is_object($this->current_file);
	}
}

