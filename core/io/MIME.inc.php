<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestisce un registro di tipi MIME
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class MIME {
	public static
		$registry = array(
		'application' => array(
		'application/andrew-inset' => array('ez'), 'application/mac-binhex40' => array('hqx'),
		'application/mac-compactpro' => array('cpt'), 'application/msword' => array('doc'),
		'application/octet-stream' => array('bin','dms','lha','lzh','exe','class','so','dll'),
		'application/oda' => array('oda'),'application/pdf' => array('pdf'),
		'application/postscript' => array('ps','ai','eps'),'application/smil' => array('smi','smil'),
		'application/vndmif' => array('mif'),'application/vndms-excel' => array('xls'),
		'application/vndms-powerpoint' => array('ppt'),'application/vndwapwbxml' => array('wbxml'),
		'application/vndwapwmlc' => array('wmlc'),'application/vndwapwmlscriptc' => array('wmlsc'),
		'application/x-bcpio' => array('bcpio'),'application/x-cdlink' => array('vcd'),'application/x-chess-pgn' => array('pgn'),
		'application/x-cpio' => array('cpio'),'application/x-csh' => array('csh'),'application/x-director' => array('dcr','dir','dxr'),
		'application/x-dvi' => array('dvi'),'application/x-futuresplash' => array('spl'),'application/x-gtar' => array('gtar'),'application/x-gzip' => array('gz'),
		'application/x-hdf' => array('hdf'),'application/x-javascript' => array('js'),'application/x-koan' => array('skp','skd','skt','skm'),
		'application/x-latex' => array('latex'),'application/x-netcdf' => array('nc','cdf'),'application/x-sh' => array('sh'),
		'application/x-shar' => array('shar'),'application/x-shockwave-flash' => array('swf'),'application/x-stuffit' => array('sit'),
		'application/x-sv4cpio' => array('sv4cpio'),'application/x-sv4crc' => array('sv4crc'),'application/x-tar' => array('tar'),
		'application/x-tcl' => array('tcl'),'application/x-tex' => array('tex'),'application/x-texinfo' => array('texinfo','texi'),
		'application/x-troff' => array('t','tr','roff'),'application/x-troff-man' => array('man'),'application/x-troff-me' => array('me'),
		'application/x-troff-ms' => array('ms'),'application/x-ustar' => array('ustar'),'application/x-wais-source' => array('src'),
		'application/xhtml+xml' => array('xhtml','xht'),'application/zip' => array('zip')
		),
		'audio' => array (
		'audio/basic' => array('au','snd'),'audio/midi' => array('mid','midi','kar'),'audio/mpeg' => array('mpga','mp2','mp3'),
		'audio/x-aiff' => array('aif','aiff','aifc'), 'audio/x-mpegurl' => array('m3u'),'audio/x-pn-realaudio' => array('ram','rm'),
		'audio/x-pn-realaudio-plugin' => array('rpm'), 'audio/x-realaudio' => array('ra'),'audio/x-wav' => array('wav')
		),
		'chemical' => array( 'chemical/x-pdb' => array('pdb'),'chemical/x-xyz' => array('xyz') ),
		'image' => array(
		'image/bmp' => array('bmp'),'image/gif' => array('gif'),'image/ief' => array('ief'),'image/jpeg' => array('jpg','jpeg','jpe'),
		'image/pjpeg' => array('jpg','jpeg','jpe'),'image/x-png' => array('png'),
		'image/png' => array('png'),'image/tiff' => array('tiff','tif'),'image/vnddjvu' => array('djvu','djv'),
		'image/vndwapwbmp' => array('wbmp'),'image/x-cmu-raster' => array('ras'),'image/x-portable-anymap' => array('pnm'),
		'image/x-portable-bitmap' => array('pbm'),'image/x-portable-graymap' => array('pgm'),'image/x-portable-pixmap' => array('ppm'),
		'image/x-rgb' => array('rgb'),'image/x-xbitmap' => array('xbm'),'image/x-xpixmap' => array('xpm'), 'image/x-xwindowdump' => array('xwd')
		),
		'model' => array(
			'model/iges' => array('igs','iges'),'model/mesh' => array('msh','mesh','silo'),'model/vrml' => array('wrl','vrml')
		),
		'text' => array(
		'text/css' => array('css'),'text/html' => array('html','htm'),'text/plain' => array('txt','asc'),'text/richtext' => array('rtx'),
		'text/rtf' => array('rtf'),'text/sgml' => array('sgml','sgm'),'text/tab-separated-values' => array('tsv'),
		'text/vndwapwml' => array('wml'),'text/vndwapwmlscript' => array('wmls'),'text/x-setext' => array('etx'),
		'text/xml' => array('xml','xsl')
		),
		'video' => array(
			'video/mpeg' => array('mpeg','mpg','mpe'),'video/quicktime' => array('mov','qt'),'video/vndmpegurl' => array('mxu'),
			'video/x-msvideo' => array('avi'),'video/x-sgi-movie' => array('movie'),
			'video/x-flv' => array('flv')
		),
		'various' => array('x-conference/x-cooltalk' => array('ice'))
		);

	/**
	 * Ritorna i tipi MIME per le immagini web: gif, jpeg e png
	 * @return array un array di stringhe coi tipi MIME
	 */
	public static function getWebImageMIME() {
		return array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
	}
	
	/**
	 * Data una estensione, ritorna il tipo MIME corrispondente, se esiste nel registro
	 * @param string $extension estensione del file
	 * @return string stringa col tipo MIME o una stringa vuota se non è nel registro
	*/
	public static function getMIME($extension) {
		foreach(self::$registry as $tipi) {
			foreach($tipi as $mime => $v) {
				if (in_array($extension, $v)) return $mime;
			}
		}
		return '';
	}

	/**
	 * Ritorna i tipi MIME per le immagini
	 * @return array un array di stringhe coi tipi MIME
	 */
	public static function getImageMIME() {
		return array_keys(self::$registry['image']);
	}

	/**
	 * Informa se il tipo MIME si riferisca ad una immagine
	 * @param string $mime tipo MIME da verificare
	 * @return boolean TRUE se il tipo MIME è di una immagine, FALSE altrimenti
	*/
	public static function isImage($mime) {
		return in_array($mime, array_keys(self::$registry['image']));
	}
	
	/**
	 * Informa se il tipo MIME si riferisca ad un video
	 * @param string $mime tipo MIME da verificare
	 * @return boolean TRUE se il tipo MIME è di un video, FALSE altrimenti
	*/
	public static function isVideo($mime) {
		return in_array($mime, array_keys(self::$registry['video']));
	}
	
	/**
	 * Informa se il tipo MIME si riferisca ad un file audio
	 * @param string $mime tipo MIME da verificare
	 * @return boolean TRUE se il tipo MIME è di un file audio, FALSE altrimenti
	*/
	public static function isAudio($mime) {
		return in_array($mime, array_keys(self::$registry['audio']));
	}

	/**
	 * Informa se il tipo mime sia uno di quello delle immagini per il web.
	 *
	 * @param string $mime tipo MIME da verificare
	 * @return boolean TRUE se il tipo MIME è uno di una immagine, FALSE altrimenti
	 * @see getWebImageMIME
	 */
	public static function isWebImageMIME($mime) {
		return in_array($mime, self::getWebImageMIME());
	}

	/**
	 * Ritorna i tipi mime per un certo tipo.
	 *
	 * Es: 'video' ritorna i tipi video/mpeg, video/quicktime, etc...
	 *
	 * Occorre verificare prima che i tipi esistano usando {@link typeExists}
	 *
	 * @param string $mime i tipi di mime da estrarre
	 * @return array un array di stringhe
	 */
	public static function getType($mime) {
		return array_keys(self::$registry[$mime]);
	}

	/**
	 * Informa se una classe di tipi mime esista.
	 *
	 * @param string $mime la classe di tipi mime da verificare
	 * @return boolean TRUE se la classe esiste, FALSE altrimenti
	 */
	public static function typeExists($mime) {
		return array_key_exists($mime, self::$registry);
	}

}
