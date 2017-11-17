<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Definisce alcune costanti e funzioni comuni alle pagine XHMTL,
 * astrae il codice dei tag.
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class XHTML {
	const
		// Costanti per la DOCTYPE
		DTD_XHTML_STRICT = 1, // XHTML 1.0 Strict
		DTD_XHTML_TRANSITIONAL = 2, // XHTML 1.0 Transitional
		DTD_XHTML_11 = 3, // XHTML Strict 1.1
		DTD_XHTML_FRAMESET = 4, // XHTML 1.0 Frameset
		DTD_XHTML_BASIC = 5; // XHTML 1.0 Basic
	
	/**
	 * Ritorna la stringa di doctype per un certo tipo di documento XHTML.
	 *
	 * Il parametro $doctype è una delle costanti seguenti costanti:
	 * 
	 * DTD_XHTML_STRICT - XHTML 1.0 Strict
	 * DTD_XHTML_TRANSITIONAL - XHTML 1.0 Transitional
	 * DTD_XHTML_11 - XHTML Strict 1.1
	 * DTD_XHTML_FRAMESET - XHTML 1.0 Frameset
	 * DTD_XHTML_BASIC - XHTML 1.0 Basic
	 * 
	 * @param integer $doctype intero con il doctype
	 * @return string stringa xhtml con il doctype scelto, o stringa vuota se sconociuto
	*/
	public static function getDoctype($doctype) {
		switch ($doctype) {
			case self::DTD_XHTML_STRICT: return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'; break;
			case self::DTD_XHTML_TRANSITIONAL:  return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'; break;
			case self::DTD_XHTML_11: return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'; break;
			case self::DTD_XHTML_FRAMESET: return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">'; break;
			case self::DTD_XHTML_BASIC: return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">'; break;
			default:
				return '';
		}
	}
	
	/**
	 * Ritorna il prologo XML di una pagina XHTML
	 *
	 * @param string $encoding stringa con l'encoding desiderato dei caratteri
	*/
	public static function getXMLProlog($encoding) {
		return '<?xml version="1.0" encoding="'.$encoding.'"?>';
	}
	
	/**
	 * Ritorna il tag di apertura HTML, specifico per XHTML
	 *
	 * @return string una stringa con l'xhtml
	*/
	public static function getHTMLOpeningTag() {
		return '<html xmlns="http://www.w3.org/1999/xhtml">';
	}
	
	/**
	 * Astrae il tag H1
	 * 
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function h1($text, $attr = array()) {
		return self::tag('h1', $text, $attr);
	}
	
	/**
	 * Astrae il tag H2
	 * 
	 * @param $text string codice xhtml del corpo
	 * @param $attr array array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function h2($text, $attr = array()) {
		return self::tag('h2', $text, $attr);
	}
	
	/**
	 * Astrae il tag H3
	 * 
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function h3($text, $attr = array()) {
		return self::tag('h3', $text, $attr);
	}
	
	/**
	 * Astrae il tag H4
	 * 
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function h4($text, $attr = array()) {
		return self::tag('h4', $text, $attr);
	}
	
	/**
	 * Astrae il tag H5
	 * 
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function h5($text, $attr = array()) {
		return self::tag('h5', $text, $attr);
	}
	
	/**
	 * Astrae il tag H6
	 * 
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function h6($text, $attr = array()) {
		return self::tag('h6', $text, $attr);
	}
	
	/**
	 * Astrae il tag P
	 * 
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function p($text, $attr = array()) {
		return self::tag('p', $text, $attr);
	}
	
	/**
	 * Astrae il tag DIV
	 * 
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function div($text, $attr = array()) {
		return self::tag('div', $text, $attr);
	}
	
	/**
	 * Astrae il tag SPAN
	 * @param string $text codice xhtml del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function span($text, $attr = array()) {
		return self::tag('span', $text, $attr);
	}
	
	/**
	 * Astrae il tag A (ancora ipertestuale)
	 * 
	 * @param string $href stringa con l'href del tag
	 * @param string $text codice xhtml del corpo
	 * @param string $title stringa col titolo dell'ancora
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function a($href, $text, $title = '', $attr = array()) {
		$attr['href'] = $href;
		$attr['title'] = $title;
		return self::tag('a', $text, $attr);
	}
	
	/**
	 * Astrae il tag IMG
	 * 
	 * @param string $src stringa con l'URI dell'immagine
	 * @param integer $width intero con la larghezza dell'immagine
	 * @param integer $height intero con l'altezza dell'immagine
	 * @param string $alt stringa col testo alternativo per l'immagine
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function img($src, $width, $height, $alt = '', $attr = array()) {
		$attr['src'] = $src;
		if (!empty($width)) $attr['width'] = $width;
		if (!empty($height)) $attr['height'] = $height;
		$attr['alt'] = $alt;
		return self::singletag('img', $attr);
	}
	
	/**
	 * Astrae un tag XHTML
	 * 
	 * Gli attributi aggiuntivi vanno specificati in un array associativo, di cui viene
	 * usata la chiave per il nome dell'attributo.
	 * 
	 * Ad esempio l'array:
	 * <code>
	 * array(
	 *   'foo' => 'foovalue',
	 *   'bar' => 'barvalue'
	 * )
	 * </code>
	 * 
	 * Genererebbe gli attributi:
	 * <code>
	 *  <... foo="foovalue" bar="barvalue">...</...>
	 * </code>
	 * 
	 * @param string $tag stringa col nome del tag, in minuscolo
	 * @param string $body codice xhtml o testo del corpo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 *
	*/
	public static function tag($tag, $body = '', $attr = array()) {
		// compone gli attributi
		if (count($attr) > 0) {
			$attr = ' '.implode( array_map(array('XHTML', 'makeAttr'), array_keys($attr), array_values($attr) ), ' ');
		} else {
			$attr = '';
		}
		return "<{$tag}{$attr}>".$body."</{$tag}>";
	}
	
	/**
	 * Astrae un tag singolo XHTML.
	 * 
	 * Per il formato degli attributi riferirsi a {@link tag}.
	 * 
	 * @param string $tag stringa col nome del tag, in minuscolo
	 * @param array $attr array con gli attributi del tag, nella forma 'attributo' => 'valore'
	 * @return string il codice xhtml del tag
	 * @see tag
	*/
	public static function singletag($tag, $attr = '') {
		if (count($attr) > 0) {
			$attr = ' '.implode( array_map(array('XHTML', 'makeAttr'), array_keys($attr), array_values($attr) ), ' ');
		} else {
			$attr = '';
		}
		return "<{$tag}{$attr}/>";
	}
	
	private static function makeAttr($a, $v) {
		return strval($a).'="'.strval($v).'"';
	}
	
	/**
	 * Converte un testo in XHTML.
	 * 
	 * Funziona come htmlentities, solo che usa l'encoding dei caratteri definito
	 * nel front controller.
	 * 
	 * @param string $str stringa da traslare
	 * @return la stringa con le entità html
	 **/
	public static function toHTML($str) {
		if (function_exists('mb_detect_encoding')) {
			$enc = mb_detect_encoding($str);
			if ($enc != strtoupper(WebApp::getInstance()->getEncoding())) $str = mb_convert_encoding($str, $enc, strtoupper(strtoupper(WebApp::getInstance()->getEncoding())));
		}

		/*
		elseif (function_exists('iconv')) {
			$str = iconv(WebApp::getInstance()->getEncoding(), WebApp::getInstance()->getEncoding(), $str);
		}
		elseif (function_exists('utf8_encode')) {
			$str = utf8_encode($str);
		}
		*/
		return htmlentities($str, ENT_COMPAT, WebApp::getInstance()->getEncoding());
	}
}

