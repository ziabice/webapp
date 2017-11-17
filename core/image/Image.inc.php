<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Semplice gestione delle immagini, maschera le funzionalità
 * più comuni offerte dalla libreria GD
 *
 * Non si può costruire direttamente, ma solo attraverso {@link createFromFile} o 
 * funzioni simili.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 **/
class Image {
	protected
		$path = '',
		$type, $width, $height,
		$jpeg_quality, $png_compression_level,
		$canvas;

	/**
	 * Crea un nuovo oggetto immagine.
	 * 
	 * Viene creata una immagine vuota, senza dati, ma con solo le informazioni di base.
	 * La bitmap va caricata in un secondo momento usando {@link loadCanvas}.
	 * 
	 * @param integer $img_type una delle costanti IMAGETYPE_*
	 * @param integer $width larghezza dell'immagine in pixel
	 * @param integer $height altezza dell'immagine in pixel
	 * @see loadCanvas
	 * @see getSupportedImageTypes
	 * @throws ImageException se il tipo di immagine non è supportato
	*/
	protected function __construct($img_type, $width, $height) {
		if (!in_array($img_type, self::getSupportedImageTypes() )) {
			throw new ImageException("Image type not supported.", ImageException::IMAGETYPE_NOT_SUPPORTED);
		}
		$this->canvas = FALSE;
		$this->width = $width;
		$this->height = $height;
		$this->type = $img_type;
		$this->jpeg_quality = 75;
		$this->png_compression_level = 9;
	}
	
	public function __destruct() {
		if (is_resource($this->canvas)) imagedestroy($this->canvas);
	}

	/**
	 * Crea una nuova istanza di Image utilizzando il file in ingresso.
	 * A seconda dei casi mantiene in memoria o meno il file.
	 * 
	 * @param string $path path di una immagine utilizzabile
	 * @param boolean $dont_allocate_canvas TRUE non alloca in memoria i dati i mmagine, FALSE altrimenti
	 * @return Image l'immagine inizializzata
	 * @throws ImageException in caso di errore
	*/
	public static function createFromFile($path, $dont_allocate_canvas = FALSE) {
		$info = @getimagesize($path);
		if ($info === FALSE) {
			throw new ImageException("Failed to open image.", ImageException::FILE_OPEN_ERROR);
		}
		$img = new Image($info[2], $info[0], $info[1]);
		$img->path = $path;
		
		if ($dont_allocate_canvas) return $img;
		
		if ($img->loadCanvas() == FALSE) {
			throw new ImageException("Image creation from file failed.", ImageException::FAILED_CREATION_FROM_FILE);
		}
		
		return $img;
	}
	
	/**
	 * Crea una nuova immagine associata ad un file.
	 * 
	 * Le proprietà dell'immagine associata non vengono utilizzate per creare l'oggetto,
	 * che avrà 
	 * 
	 * @param string $path percorso del file immagine su disco
	 * @param string $mime tipo MIME dell'immagine
	 * @param integer $width larghezza in pixel dell'immagine
	 * @param integer $height altezza in pixel dell'immagine
	 * @throws ImageException in caso di errore
	 */
	public static function createImage($path, $mime, $width, $height, $load_image = FALSE) {
		// Deduce il tipo di immagine dal tipo mime
		$img_type = self::MIMEtoImageType($mime);
		
		if ($img_type === FALSE) throw new ImageException('Unsupported Image MIME type', ImageException::IMAGETYPE_NOT_SUPPORTED);
		$img = new Image($img_type, $width, $height);
		$img->path = strval($path);
		if ($load_image) {
			if (!$img->loadCanvas()) throw new ImageException('Image loading failed.', ImageException::FAILED_CREATION_FROM_FILE);
		}
		return $img;
	}
	
	/**
	 * Data una stringa contenente il tipo MIME ritorna il tipo immagine GD.
	 * 
	 * Per il tipo TIFF ritorna sempre IMAGETYPE_TIFF_II, ma un valore valido è
	 * anche IMAGETYPE_TIFF_MM.
	 * 
	 * @param string $mime il tipo MIME
	 * @return integer ritorna la costante relativa al tipo o FALSE se il tipo è sconosciuto o non valido
	 */
	public static function MIMEtoImageType($mime) {
		switch ($mime) {
			case 'image/gif': return IMAGETYPE_GIF;	
			case 'image/pjpeg':
			case 'image/jpeg': 
				return IMAGETYPE_JPEG;	
			case 'image/png' :
			case 'image/x-png': 
				return IMAGETYPE_PNG;
			case 'application/x-shockwave-flash' : return IMAGETYPE_SWF;
			case 'image/psd' : return IMAGETYPE_PSD;
			case 'image/bmp' : return IMAGETYPE_BMP;
			case 'image/tiff': return IMAGETYPE_TIFF_II;
			case 'image/jp2' : return IMAGETYPE_JP2	;
			case 'image/iff' : return IMAGETYPE_IFF;
			case 'image/vnd.wap.wbmp' : return IMAGETYPE_WBMP;
			case 'image/xbm' : return IMAGETYPE_XBM;
			case 'image/vnd.microsoft.icon' : return IMAGETYPE_ICO;	
		}
		return FALSE;
	}
	
	/**
	 * Ritorna il path del file originale (da cui è stata creata l'immagine o inizializzato l'oggetto).
	 *
	 * @return string una stringa col percorso, vuota se l'oggetto non è stato creato da file
	*/
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * Informa se l'immagine abbia una canvas, ossia se sia stata
	 * allocata in memoria.
	 *
	 * 	@return boolean TRUE se ha una canvas, FALSE altrimenti
	*/
	public function hasCanvas() {
		return is_resource($this->canvas);
	}
	
	/**
	 * Nel caso sia una immagine creata da file, carica in memoria l'immagine
	 * dal path specificato al momento della creazione
	 * @return boolean TRUE se tutto ok, FALSE in caso di errore
	 * @see getPath
	*/
	public function loadCanvas() {
		if (empty($this->path)) return FALSE;
		if (@file_exists($this->path) && @is_file($this->path)) {
			$info = @getimagesize($this->path);
			if ($info === FALSE) return FALSE;
			switch($info[2]) {
				case IMAGETYPE_GIF: $this->canvas = @imagecreatefromgif($this->path); break;
				case IMAGETYPE_JPEG: $this->canvas = @imagecreatefromjpeg($this->path); break;
				case IMAGETYPE_PNG: $this->canvas = @imagecreatefrompng($this->path); break;
				case IMAGETYPE_WBMP: $this->canvas = @imagecreatefromwbmp($this->path); break;
				case IMAGETYPE_XBM: $this->canvas = @imagecreatefromxbm($this->path); break;
				// case IMAGETYPE_XPM: $this->canvas = @imagecreatefromxpm($this->path); break;
				default:
					return FALSE;
			}
			
			// reimposta il tipo immagine e le informazioni, in modo da riflettere
			// quelle reali del file
			$this->type = $info[2];
			$this->width = $info[0];
			$this->height = $info[1];
			
			return TRUE;
		}
	}
	
	/**
	 * Salva l'immagine in un file, eventualmente cambiandone il tipo
	 * E' possibile salvare solo nei tipi:
	 *  - IMAGETYPE_GIF
	 *  - IMAGETYPE_JPEG
	 *  - IMAGETYPE_PNG
	 *  - IMAGETYPE_WBMP
	 * 
	 * @param string $destination_path path completo del file da generare
	 * @param integer $destination_type una delle costanti IMAGETYPE_* o NULL per usare il tipo originale
	 * @return boolean TRUE se tutto ok, FALSE in caso di errore
	*/
	public function saveCanvas($destination_path, $destination_type = NULL) {
		if (!$this->hasCanvas()) return FALSE;
		if (!is_null($destination_type) && $destination_type != $this->type) $t = $destination_type;
		else $t = $this->type;
		switch($t) {
			case IMAGETYPE_GIF: $saved = @imagegif($this->canvas, $destination_path); break;
			case IMAGETYPE_JPEG: $saved = @imagejpeg($this->canvas, $destination_path, $this->getJPEGQuality()); break;
			case IMAGETYPE_PNG: $saved = @imagepng($this->canvas, $destination_path, $this->getPNGCompressionLevel()); break;
			case IMAGETYPE_WBMP: $saved = @imagewbmp($this->canvas, $destination_path); break;
			default:
				return FALSE;
		}
		return $saved;
	}
	
	/**
	 * 	Dato un tipo di immagine a 8bit, ritorna un tipo appropriato
	 *  per il salvataggio, ossia un tipo in cui possa essere convertito
	 * @param integer $imagetype tipo di immagine in ingresso (una delle costante IMAGETYPE_*)
	 * @param $dont_want_gif boolean TRUE non proporre il formato GIF per immagini a 8bit, FALSE scegli il formato GIF se possibile
	 * @return integer il tipo di immagine di destinazione (una delle costante IMAGETYPE_*)
	*/
	public static function getSaveType($imagetype, $dont_want_gif = TRUE) {
		// Immagini ad 8 bit, vai di png
		if ($dont_want_gif && $imagetype == IMAGETYPE_GIF) return IMAGETYPE_PNG; 
		switch($imagetype) {
			case IMAGETYPE_PNG: 
			case IMAGETYPE_WBMP: 
			case IMAGETYPE_XBM: 
			// case IMAGETYPE_XPM: 
			return IMAGETYPE_PNG; 
			default:
				return $imagetype;
		}
	}
	
	/**
	 * Ritorna la qualità delle JPEG generate, un intero da 0 (peggiore) a 100 (ottima),
	 * con 75 come qualità standard
	 *
	 * @return integer da 0 a 100
	*/
	public function getJPEGQuality() {
		return $this->jpeg_quality;
	}
	
	/**
	 * Imposta la qualità delle JPEG generate, un intero da 0 (peggiore) a 100 (ottima),
	 * con 75 come qualità standard
	 * @param integer $quality da 0 a 100
	*/
	public function setJPEGQuality($quality = 75) {
		$this->jpeg_quality = $quality;
	}
	
	/**
	 * Imposta il livello di compressione delle PNG generate, da 0 (nessuna) a 9 (ottimale)
	 *
	 * @param integer $level da 0 a 9
	*/
	public function setPNGCompressionLevel($level) {
		$this->png_compression_level = $level;
	}
	
	/**
	 * Ritorna il livello di compressione delle PNG generate, da 0 (nessuna) a 9 (ottimale)
	 *
	 * @return integer da 0 a 9
	*/
	public function getPNGCompressionLevel() {
		return $this->png_compression_level;
	}
	
	/**
	 * Crea una nuova immagine del tipo e dimensione specificate
	 *
	 * Factory method.
	 *
	 * @param integer $img_type tipo di immagine (una delle costanti IMAGETYPE_*)
	 * @param integer $width larghezza dell'immagine in pixel
	 * @param integer $height altezza dell'immagine in pixel
	 * @throws ImageException in caso di errore
	*/
	public static function create($img_type, $width, $height) {
		$img = new Image($img_type, $width, $height);
		if (function_exists('imagecreatetruecolor')) {
			$img->canvas = @imagecreatetruecolor($width, $height);
		} elseif(function_exists('imagecreatetruecolor')) {
			$img->canvas = @imagecreate($width, $height);
		} else {
			throw ImageException("Image creation failed.", ImageException::FAILED_CREATION);
		}
		if ($img->canvas === FALSE) {
			throw new ImageException("Image type not supported.", ImageException::FAILED_CREATION);
		}
		return $img;
	}
	
	/**
	 * Crea una nuova bitmap GD delle dimensioni specificate
	 * Cerca di crearla truecolor, se possibile
	 * @param integer $width larghezza dell'immagine in pixel
	 * @param integer $height altezza dell'immagine in pixel
	 * @return mixed FALSE in caso di fallimento, altrimenti la risorsa GD
	*/
	public function createImageCanvas($width, $height) {
		if (function_exists('imagecreatetruecolor')) {
			return @imagecreatetruecolor($width, $height);
		} elseif(function_exists('imagecreatetruecolor')) {
			return @imagecreate($width, $height);
		}
		return FALSE; 
	}
	
	/**
	 * Informa se l'immagine attuale sia di tipo True Color
	 * @return boolean TRUE se è true color, FALSE altrimenti (è a 8 bit)
	*/
	public function isTruecolor() {
		return imageistruecolor( $this->canvas );
	}
	
	/**
	 * Ritorna un array coi tipi di immagine supportati
	 * @return array di interi coi valori delle costanti IMAGETYPE_*
	*/
	public static function getSupportedImageTypes() {
		$supported = array();
		$i = imagetypes(); 
		if ($i & IMG_GIF) $supported[] = IMAGETYPE_GIF;
		if ($i & IMG_JPG) $supported[] = IMAGETYPE_JPEG;
		if ($i & IMG_PNG) $supported[] = IMAGETYPE_PNG;
		if ($i & IMG_WBMP) $supported[] = IMAGETYPE_WBMP;
		if ($i & IMG_XPM) {
			$supported[] = IMAGETYPE_XBM;
		}
		
		return $supported;
	}
	
	/**
	 * Ritorna un array di stringhe con i tipi mime supportati
	 * @return array array di stringhe coi tipi MIME
	*/
	public static function getSupportedImageMIME() {
		return array_map('image_type_to_mime_type', self::getSupportedImageTypes());
	}
	
	/**
	 * Ritorna la larghezza in pixel dell'immagine
	 * @return integer la larghezza in pixel
	*/
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * Ritorna l'altezza  in pixel dell'immagine
	 * @return integer l'altezza in pixel
	*/
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * Ritorna il tipo dell'immagine, una delle costanti IMAGETYPE_*
	 * @return integer il tipo dell'immagine
	*/
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Ritorna la risorsa GD su cui vengono eseguite le operazioni
	 * @return resource risorsa GD
	*/
	public function getCanvas() {
		return $this->canvas;
	}

	/**
	 * Calcola le dimensioni di una immagine fino ad un massimo, mantenendo le proporzioni.
	 * 
	 * Va utilizzata quando si vuole rimpicciolire una immagine: se le dimensioni originali
	 * dell'immagine sono già minori di quelle assegnate non fa nulla.
	 * 
	 * @param integer $w larghezza dell'immagine (impostare con la larghezza iniziale dell'immagine)
	 * @param integer $h altezza dell'immagine (impostare con l'altezza iniziale dell'immagine)
	 * @param integer $maxw larghezza massima dell'immagine: se < 0 lascia libera
	 * @param integer $maxh altezza massima dell'immagine: se < 0 lascia libera
	 * @return boolean FALSE se è impossibile procedere, TRUE altrimenti
	*/
	public static function calculateScaleAt(&$w, &$h, $maxw = -1, $maxh = -1) {
		if ($w == 0 || $h == 0) return FALSE;
		$ow = $w;
		$oh = $h;
		$ar = $ow / $oh; // Aspect ratio
		if ($maxw > 0 && $maxh > 0) {
			// Non scala se le dimensioni sono già minori
			if ($w <= $maxw && $h <= $maxh) return TRUE;
			
			$w = $maxw;
			$w++;
			while(($h > $maxh && $w) || $ar != ($w / $h) ) {
				$w--;
				$h = $w / $ar;
			}
			$h = (int)floor($h);
		} else {
			if ($maxw > 0 &&  $ow > $maxw) {
				$w = $maxw;
				$h = (int)floor($w / $ar);
			} elseif ($maxh > 0 &&  $oh > $maxh) {
				$h = $maxh;
				$w = (int)floor($h * $ar);
			}
		}
		return TRUE;
	}
	
	/**
	 * Calcola le dimensioni di una immagine fino ad un massimo, mantenendo le proporzioni.
	 * 
	 * @param integer $w larghezza dell'immagine (impostare con la larghezza iniziale dell'immagine)
	 * @param integer $h altezza dell'immagine (impostare con l'altezza iniziale dell'immagine)
	 * @param integer $maxw larghezza massima dell'immagine: se < 0 lascia libera
	 * @param integer $maxh altezza massima dell'immagine: se < 0 lascia libera
	 * @return boolean FALSE se è impossibile procedere, TRUE altrimenti
	 **/
	public static function calculateScale(&$w, &$h, $maxw = -1, $maxh = -1) {
		if ($w == 0 || $h == 0) return FALSE;
		$ow = $w;
		$oh = $h;
		$ar = $ow / $oh; // Aspect ratio
		if ($maxw > 0 && $maxh > 0) {
			$w = $maxw;
			$w++;
			while(($h > $maxh && $w) || $ar != ($w / $h) ) {
				$w--;
				$h =  $w / $ar;
			}
			$h = (int)ceil($h);
		} else {
			if ($maxw > 0) {
				$w = $maxw;
				$h = (int)floor($w / $ar);
			} elseif ($maxh > 0) {
				$h = $maxh;
				$w = (int)floor($h * $ar);
			}
		}
		return TRUE;
	}
	
	/**
	 * Scala l'immagine ad una nuova dimensione, eventualmente
	 * crea una nuova immagine.
	 * Se non si crea una nuova immagine viene scalata l'immagine attuale
	 * 
	 * @param integer $new_w nuova larghezza
	 * @param integer $new_h nuova altezza
	 * @param boolean $create_new TRUE crea una nuova immagine, FALSE opera su quella corrente
	 * @return mixed TRUE se l'operazione ha successo (scala in memoria), FALSE altrimenti, Image l'istanza della nuova immagine creata se $create_new è TRUE
	 * @throws ImageException se non può allocare la memoria
	*/
	public function scale($new_w, $new_h, $create_new = FALSE) {
		if (!$this->hasCanvas()) return FALSE;
		$dest_canvas = $this->createImageCanvas($new_w, $new_h);
		if ($dest_canvas === FALSE) throw new ImageException("Destination image creation failed", ImageException::FAILED_CREATION);
		if (function_exists('imagecopyresampled')) {
			$success = @imagecopyresampled ( $dest_canvas, $this->canvas, 0, 0, 0, 0, $new_w, $new_h, $this->width, $this->height );
		} elseif( function_exists('imagecopyresized')) {
			$success = @imagecopyresized ( $dest_canvas, $this->canvas, 0, 0, 0, 0, $new_w, $new_h, $this->width, $this->height );
		} else {
			$success = FALSE;
		} 
		if ($success) {
			if ($create_new) {
				$img = new Image($this->type, $new_w, $new_h);
				$img->canvas = $dest_canvas;
				return $img;
			} else {
				@imagedestroy($this->canvas);
				$this->canvas = $dest_canvas;
				$this->width = $new_w;
				$this->height = $new_h;
			}
		}
		return $success;
	}

	/**
	 * Effettua il crop dell'immagine, ritagliandone una parte.
	 *
	 * Lavora sulla immagine corrente.
	 * Le coordinate partono dall'angolo in alto a sinistra.
	 *
	 * Colore = array( r, g, b)
	 *
	 * @param integer $x coordinata X da cui iniziare il ritaglio
	 * @param integer $y coordinata Y da cui iniziare il ritaglio
	 * @param integer $width larghezza del ritaglio
	 * @param integer $height altezza del ritaglio
	 * @param boolean $create_new crea una nuova immagine o aggiorna quella che c'è
	 * @param array $canvas_color un array di interi con la terna rgb del colore con cui riempire la canvas di destinazione
	 * @return boolean|Image ritorna TRUE se tutto ok, FALSE se fallisce, e una istanza di Image se $create_new è TRUE
	 */
	public function crop($width, $height, $x = 0, $y = 0, $create_new = FALSE, $canvas_color = array()) {
		if (!$this->hasCanvas()) return FALSE;
		$dest_canvas = $this->createImageCanvas($width, $height);
		if ($dest_canvas === FALSE) throw new ImageException("Destination image creation failed", ImageException::FAILED_CREATION);
		if (!empty($canvas_color)) {
			$col = imagecolorallocate($dest_canvas, $canvas_color[0], $canvas_color[1], $canvas_color[2] );
			imagefill($dest_canvas, 0, 0, $col );
			// imagecolordeallocate($dest_canvas, $col);
		}
		if (function_exists('imagecopy')) {
			$success = @imagecopy ( $dest_canvas, $this->canvas, 0, 0, $x, $y, $width, $height );
		} else {
			$success = FALSE;
		}
		if ($success) {
			if ($create_new) {
				$img = new Image($this->type, $width, $height);
				$img->canvas = $dest_canvas;
				return $img;
			} else {
				@imagedestroy($this->canvas);
				$this->canvas = $dest_canvas;
				$this->width = $width;
				$this->height = $height;
			}
		}
		return $success;
	}

	/**
	 * Ritorna il tipo MIME dell'immagine
	 * @return string stringa col tipo MIME
	*/
	public function getMIME() {
		return image_type_to_mime_type ( $this->type );
	}

	/**
	 * Ritorna l'esensione per il tipo immagine corrente
	 *
	 * @param boolean $with_dot TRUE include il punto, FALSE ritorna solo l'estensione
	 * @return string l'estensione da usare
	 */
	public function getFileExt($with_dot = FALSE) {
		return self::getImageExt($this->type, $with_dot);
	}
	
	/**
	 * Ritorna l'estensione che avrebbe il file rispondente all'immagine
	 * @param integer $image_type tipo di immagine (una delle costanti IMAGETYPE_*)
	 * @param boolean $with_dot TRUE prepende il punto, FALSE altrmenti
	 * @return string una stringa con l'estensione del file immagine
	*/
	public static function getImageExt($image_type, $with_dot = FALSE) {
		switch($image_type) {
			case IMAGETYPE_GIF: $ext = 'gif';break;
			case IMAGETYPE_JPEG: $ext = 'jpg';break;
			case IMAGETYPE_PNG: $ext = 'png';break;
			case IMAGETYPE_SWF: $ext = 'swf';break;
			case IMAGETYPE_PSD: $ext = 'psd';break;
			case IMAGETYPE_BMP: $ext = 'bmp';break;
			case IMAGETYPE_WBMP: $ext = 'wbmp';break;
			case IMAGETYPE_XBM: $ext = 'xbm';break;
			case IMAGETYPE_TIFF_II: 
			case IMAGETYPE_TIFF_MM: $ext = 'tif';break;
			case IMAGETYPE_IFF: $ext = 'iff';break;
			case IMAGETYPE_JB2: $ext = 'jb2';break;
			case IMAGETYPE_JPC: $ext = 'jpc';break;
			case IMAGETYPE_JP2: $ext = 'jp2';break;
			case IMAGETYPE_JPX: $ext = 'jpx';break;
			case IMAGETYPE_SWC: $ext = 'swc';break;
			case IMAGETYPE_ICO: $ext = 'ico';break;
		}
 		return $with_dot ? '.'.$ext : $ext;
	}
	
	/**
	 * Emette l'immagine verso il browser.
	 * 
	 * Gli header non vengono mandati all'oggetto Response corrente.
	 * 
	 * @param boolean $with_headers TRUE aggiunge anche le informazioni relative agli header
	 * @param integer $output_type tipo verso il quale bisogna eseguire l'output, se NULL usa quello attuale
	 * @return boolean TRUE se ha emesso l'immagine, FALSE altrimenti
	*/
	public function serve($with_headers = TRUE, $output_type = NULL) {
		if (!$this->hasCanvas()) return FALSE;
		if (is_null($output_type)) $output_type = $this->type;
		
		switch($this->type) {
			case IMAGETYPE_GIF: 
				if ($with_headers) header('Content-type: '.image_type_to_mime_type ( $output_type ));
				return imagegif($this->canvas); 
			break;
			case IMAGETYPE_JPEG: 
				if ($with_headers) header('Content-type: '.image_type_to_mime_type ( $output_type ));
				return imagejpeg($this->canvas, NULL, $this->getJPEGQuality()); 
			break;
			case IMAGETYPE_PNG: 
				if ($with_headers) header('Content-type: '.image_type_to_mime_type ( $output_type ));
				return imagepng($this->canvas, NULL, $this->getPNGCompressionLevel());
			break;
			case IMAGETYPE_WBMP: 
				if ($with_headers) header('Content-type: '.image_type_to_mime_type ( $output_type ));
				return imagewbmp($this->canvas); 
			break;
		}
		return FALSE;	
	}
}
