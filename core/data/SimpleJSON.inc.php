<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Implementa una parser/encoder JSON
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class SimpleJSON {
	const
		// Usate internamente
		JSON_STRING = 10,
		JSON_COMMA = 12,
		JSON_COLON = 13,
		JSON_ARRAY = 14,
		JSON_OBJECT = 16,
		JSON_INTEGER = 18,
		JSON_FLOAT = 20,
		JSON_ARRAY_START = 22,
		JSON_ARRAY_END = 23,
		JSON_OBJECT_START = 25,
		JSON_OBJECT_END = 26,
		JSON_BOOLEAN = 30,
		JSON_NULL = 32,
		JSON_EMPTY = 1000,
		JSON_INCOMPLETE = 1100,
		JSON_END = 1200,
		JSON_PARSE_ERROR = 666,
		JSON_ACCEPT_STRING = 9999;

	protected static
		$json,
		$position,
		$last_parser_error = '';

	/**
	 * Codifica un dato in una stringa JSON.
	 *
	 * Gli array associativi vengono ritornati come oggetti.
	 * Gli array normali come array
	 *
	 * @return string una stringa col codice JSON
	 */
	public static function encode($object) {

		if (function_exists('json_encode')) return json_encode($object);

		if (is_null($object)) {
			return 'null';
		} elseif (is_object($object)) {
			return "{\n".implode(array_map(array('SimpleJSON', 'key_value'), array_keys(get_object_vars($object)), get_object_vars($object)), ",\n")."}\n";
		} elseif (is_string($object)) {
			return self::encodeString($object);
		} elseif(is_array($object)) {
			// Un array associativo viene convertito in un oggetto
			// se c'è anche solo una chiave di tipo stringa allora l'array è associativo
			$is_assoc = FALSE;
			foreach(array_keys($object) as $k) {
				if (is_string($k)) {
					$is_assoc = TRUE;
					break;
				}
			}
			if ($is_assoc) {
				return "{\n".implode(array_map(array('SimpleJSON', 'key_value'), array_keys($object), $object), ",\n")."}\n";
			} else {
				return '['.implode( array_map(array('SimpleJSON', 'encode'), $object ), ',' )."]\n";
			}
		} elseif(is_bool($object)) {
			return ($object ? 'true' : 'false');
		} elseif (is_float($object) || is_int($object)) {
			return strval($object);
		}
	}

	/**
	 * Compone chiave e valore nella stringa:
	 *
	 * "chiave" : valore
	 *
	 * @param string $k la chiave
	 * @param string $v il valore
	 * @return string
	 */
	private static function key_value($k, $v) {
		return SimpleJSON::encode($k).' : '.SimpleJSON::encode($v);
	}




	/**
	 * Codifica una stringa in UTF8.
	 *
	 * Adapted from Json.php from Solar PHP - http://solarphp.com/
	 */
	private static function encodeString($string) {
		 // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
		$ascii = '';
		$strlen_var = strlen($string);

		/**
		* Iterate over every character in the string,
		* escaping with a slash or encoding to UTF-8 where necessary
		*/
		for ($c = 0; $c < $strlen_var; ++$c) {

			$ord_var_c = ord($string[$c]);

			switch (true) {
				case $ord_var_c == 0x08:
					$ascii .= '\b';
					break;
				case $ord_var_c == 0x09:
					$ascii .= '\t';
					break;
				case $ord_var_c == 0x0A:
					$ascii .= '\n';
					break;
				case $ord_var_c == 0x0C:
					$ascii .= '\f';
					break;
				case $ord_var_c == 0x0D:
					$ascii .= '\r';
					break;

				case $ord_var_c == 0x22:
				case $ord_var_c == 0x2F:
				case $ord_var_c == 0x5C:
					// double quote, slash, slosh
					$ascii .= '\\'.$string{$c};
					break;

				case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
					// characters U-00000000 - U-0000007F (same as ASCII)
					$ascii .= $string{$c};
					break;

				case (($ord_var_c & 0xE0) == 0xC0):
					// characters U-00000080 - U-000007FF, mask 110XXXXX
					// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
					$char = pack('C*', $ord_var_c, ord($string{$c + 1}));
					$c += 1;
					$utf16 = self::utf82utf16($char);
					$ascii .= sprintf('\u%04s', bin2hex($utf16));
					break;

				case (($ord_var_c & 0xF0) == 0xE0):
					// characters U-00000800 - U-0000FFFF, mask 1110XXXX
					// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
					$char = pack('C*', $ord_var_c,
								 ord($string{$c + 1}),
								 ord($string{$c + 2}));
					$c += 2;
					$utf16 = self::utf82utf16($char);
					$ascii .= sprintf('\u%04s', bin2hex($utf16));
					break;

				case (($ord_var_c & 0xF8) == 0xF0):
					// characters U-00010000 - U-001FFFFF, mask 11110XXX
					// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
					$char = pack('C*', $ord_var_c,
								 ord($string{$c + 1}),
								 ord($string{$c + 2}),
								 ord($string{$c + 3}));
					$c += 3;
					$utf16 = self::utf82utf16($char);
					$ascii .= sprintf('\u%04s', bin2hex($utf16));
					break;

				case (($ord_var_c & 0xFC) == 0xF8):
					// characters U-00200000 - U-03FFFFFF, mask 111110XX
					// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
					$char = pack('C*', $ord_var_c,
								 ord($string{$c + 1}),
								 ord($string{$c + 2}),
								 ord($string{$c + 3}),
								 ord($string{$c + 4}));
					$c += 4;
					$utf16 = self::utf82utf16($char);
					$ascii .= sprintf('\u%04s', bin2hex($utf16));
					break;

				case (($ord_var_c & 0xFE) == 0xFC):
					// characters U-04000000 - U-7FFFFFFF, mask 1111110X
					// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
					$char = pack('C*', $ord_var_c,
								 ord($string{$c + 1}),
								 ord($string{$c + 2}),
								 ord($string{$c + 3}),
								 ord($string{$c + 4}),
								 ord($string{$c + 5}));
					$c += 5;
					$utf16 = self::utf82utf16($char);
					$ascii .= sprintf('\u%04s', bin2hex($utf16));
					break;
			}
		}

		return '"'.$ascii.'"';
	}

	/**
     *
     * Convert a string from one UTF-8 char to one UTF-16 char.
     *
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibye string extension.
     *
	 * --- Adapted from Json.php from Solar PHP - http://solarphp.com/
	 *
     * @param string $utf8 UTF-8 character
     *
     * @return string UTF-16 character
     *
     */
    private static function utf82utf16($utf8)
    {
        // oh please oh please oh please oh please oh please
        if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
        }

        switch (strlen($utf8)) {
            case 1:
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return $utf8;

            case 2:
                // return a UTF-16 character from a 2-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x07 & (ord($utf8{0}) >> 2))
                     . chr((0xC0 & (ord($utf8{0}) << 6))
                         | (0x3F & ord($utf8{1})));

            case 3:
                // return a UTF-16 character from a 3-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr((0xF0 & (ord($utf8{0}) << 4))
                         | (0x0F & (ord($utf8{1}) >> 2)))
                     . chr((0xC0 & (ord($utf8{1}) << 6))
                         | (0x7F & ord($utf8{2})));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }

    /**
    * convert a string from one UTF-16 char to one UTF-8 char
    *
    * Normally should be handled by mb_convert_encoding, but
    * provides a slower PHP-only method for installations
    * that lack the multibye string extension.
    *
    * --- Adapted from Json.php from Solar PHP - http://solarphp.com/
    *
    * @param    string  $utf16  UTF-16 character
    * @return   string  UTF-8 character
    * @access   private
    */
    private static function utf162utf8($utf16)
    {
        // oh please oh please oh please oh please oh please
        if(function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
        }

        $bytes = (ord($utf16{0}) << 8) | ord($utf16{1});

        switch(true) {
            case ((0x7F & $bytes) == $bytes):
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x7F & $bytes);

            case (0x07FF & $bytes) == $bytes:
                // return a 2-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xC0 | (($bytes >> 6) & 0x1F))
                     . chr(0x80 | ($bytes & 0x3F));

            case (0xFFFF & $bytes) == $bytes:
                // return a 3-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xE0 | (($bytes >> 12) & 0x0F))
                     . chr(0x80 | (($bytes >> 6) & 0x3F))
                     . chr(0x80 | ($bytes & 0x3F));
        }
	}

	/**
	* Decodifica una stringa JSON in un array associativo.
	*
	* @param string $json la stringa JSON da decodificare
	* @return array|boolean FALSE se c'è un errore nel parsing, altrimenti un array associativo coi dati
	*/
	public static function decode($json) {
		
		if (function_exists('json_decode')) return json_decode($json, TRUE);
		
		self::$json = $json;
		self::$position = 0;
		self::$last_parser_error = '';

		$done = FALSE;
		$value = FALSE;
		while (!$done) {
			$t = self::getNextToken();

			if ($t[0] == self::JSON_ARRAY_START) {
				$a = self::parseArray();
				if ($a[0] == self::JSON_ARRAY) {
					$value = $a[1];
				} else {
					return FALSE;
				}
			} elseif($t[0] == self::JSON_OBJECT_START) {
				$obj = self::parseObject();
				if ($obj[0] == self::JSON_OBJECT) {
					$value = $obj[1];
				} else {
					return FALSE;
				}
			} elseif ($t[0] == self::JSON_INCOMPLETE || $t[0] == self::JSON_END || $t[0] == self::JSON_PARSE_ERROR) {
				$done = TRUE;
			}
		}
		if ($t[0] == self::JSON_INCOMPLETE || $t[0] == self::JSON_PARSE_ERROR) {
			if ($t[0] == self::JSON_PARSE_ERROR) self::$last_parser_error = $t[1];
			else self::$last_parser_error = 'Incomplete JSON data';
			return FALSE;
		} else {
			return $value;
		}
	}

	/**
	* Ritorna l'ultimo errore generato dal parser.
	* @return string una stringa con il messaggio di errore
	*/
	public static function getLastParsingError() {
		return self::$last_parser_error;
	}

	/**
	* Decodifica un array dalla stringa JSON attuale.
	*
	* @return array i dati estratti
	*/
	protected static function parseArray() {
		$done = FALSE;
		$arr = array();

		$expected_token = array(self::JSON_ARRAY_START, self::JSON_ARRAY_END,
		self::JSON_BOOLEAN, self::JSON_STRING, self::JSON_OBJECT_START,
		self::JSON_FLOAT, self::JSON_INTEGER, self::JSON_NULL );

		while (!$done) {
			$token = self::getNextToken();
			if ( in_array( $token[0], $expected_token ) ) {
				if ($token[0] == self::JSON_ARRAY_START) {
					$a = self::parseArray();
					if ($a[0] == self::JSON_ARRAY) {
						$arr[] = $a[1];
					} else {
						return array(self::JSON_PARSE_ERROR, 'Array expected');
					}
					$expected_token = array(self::JSON_COMMA, self::JSON_ARRAY_END);
				} elseif ($token[0] == self::JSON_ARRAY_END) {
					$done = TRUE;
				} elseif ($token[0] == self::JSON_OBJECT_START) {
					$o = self::parseObject();
					if ($o[0] == self::JSON_OBJECT) {
						$arr[] = $o[1];
					} else {
						return array(self::JSON_PARSE_ERROR, 'Object expected');
					}
					$expected_token = array(self::JSON_COMMA, self::JSON_ARRAY_END);
				} else {
					// aggiunge il token finchè c'è qualcosa
					if ($token[0] == self::JSON_COMMA) {
						$expected_token = array(self::JSON_ARRAY_START,
						self::JSON_BOOLEAN, self::JSON_STRING, self::JSON_OBJECT_START,
						self::JSON_FLOAT, self::JSON_INTEGER, self::JSON_NULL );
					} else {
						$arr[] = $token[1];
						$expected_token = array(self::JSON_COMMA, self::JSON_ARRAY_END);
					}
				}
			} else {
				return array(self::JSON_PARSE_ERROR, 'Parse error');
			}
		}

		return array(self::JSON_ARRAY, $arr);
	}

	/**
	* Decodifica un oggetto dalla stringa JSON attuale
	*
	* @return array un array con i dati
	*/
	protected static function parseObject() {
		$done = FALSE;
		$arr = array();

		$expected_token = array(self::JSON_STRING, self::JSON_OBJECT_END );

		$obj_prop = '';

		while (!$done) {
			$token = self::getNextToken();

			if ( in_array( $token[0], $expected_token ) ) {
				if ($token[0] == self::JSON_STRING) {
					// se la stringa è un valore dopo un COLON
					if (in_array(self::JSON_ACCEPT_STRING, $expected_token) ) {
						$arr[$obj_prop] = $token[1];
						$expected_token = array(self::JSON_COMMA, self::JSON_OBJECT_END);
					} else {
						$obj_prop = $token[1];
						if (array_key_exists($obj_prop, $arr)) {
							// errore!
							return array(self::JSON_PARSE_ERROR, 'Duplicate or empy property name');
						} else {
							// dopo una proprietà deve esserci il separatore
							$expected_token = array( self::JSON_COLON);
						}
					}
				} elseif ($token[0] == self::JSON_ARRAY_START) {
					$a = self::parseArray();
					if ($a[0] == self::JSON_ARRAY) {
						$arr[$obj_prop] = $a[1];
					} else {
						return array(self::JSON_PARSE_ERROR, 'Array expected');
					}
					$expected_token = array(self::JSON_COMMA, self::JSON_OBJECT_END);
				} elseif ($token[0] == self::JSON_ARRAY_END) {
					$done = TRUE;
				} elseif ($token[0] == self::JSON_OBJECT_START) {
					$obj = self::parseObject();
					if ($obj[0] == self::JSON_OBJECT) {
						$arr[$obj_prop] = $obj[1];
					} else {
						return array(self::JSON_PARSE_ERROR, 'Object expected');
					}
					$expected_token = array(self::JSON_COMMA, self::JSON_OBJECT_END);
				} elseif ($token[0] == self::JSON_OBJECT_END) {
					$done = TRUE;
				} else {
					// aggiunge il token finchè c'è qualcosa
					if ($token[0] == self::JSON_COLON) {
						$expected_token = array(self::JSON_ARRAY_START,
						self::JSON_BOOLEAN, self::JSON_STRING, self::JSON_OBJECT_START,
						self::JSON_FLOAT, self::JSON_INTEGER, self::JSON_NULL, self::JSON_ACCEPT_STRING );
					} elseif($token[0] == self::JSON_COMMA){
						$expected_token = array(self::JSON_STRING, self::JSON_OBJECT_END);
					} else {
						if (empty($obj_prop)) {
							return array(self::JSON_PARSE_ERROR, 'Undefined property');
						} else {
							$arr[$obj_prop] = $token[1];
							$expected_token = array(self::JSON_COMMA, self::JSON_OBJECT_END);
						}
					}
				}
			} else {
				return array(self::JSON_PARSE_ERROR, 'Parse error');
			}
		}

		return array(self::JSON_OBJECT, $arr);
	}

	/**
	* Estrae il prossimo token dalla stringa JSON
	*
	* @return array i dati estratti
	*/
	protected static function getNextToken() {
		$token = array(self::JSON_INCOMPLETE, NULL);
		$data = '';
		while ( self::$position < strlen(self::$json) ) {
			$c = self::$json[self::$position];

			if ($c == 'f' || $c == 't' || $c == 'n') {
				if ($c == 'f') $str = array('false', 5, self::JSON_BOOLEAN, FALSE);
				elseif ($c == 't') $str = array('true', 4, self::JSON_BOOLEAN, TRUE);
				else $str = array('null', 4, self::JSON_NULL, NULL);

				$s = substr(self::$json, self::$position, $str[1]);
				if ($s === FALSE) {
					break;
				} else {
					self::$position += ($str[1] - 1);
					if (strcmp( $s, $str[0]) == 0) {
						// verifica anche il carattere successivo
						$nc = substr(self::$json, self::$position + 1, 1);
						if ($nc !== FALSE) {
							if (self::isSpace($nc) || $nc == ']' || $nc == '}' || $nc == ',') {
								$token = array($str[2], $str[3]);
								break;
							}
						} else {
							break;
						}
					} else {
						break;
					}
				}

			} elseif ($c == '"') {
				// Una sequenza stringa
				// Il valore è una stringa
				$str = '';
				self::$position++;
				while ( self::$position < strlen(self::$json) ) {
					$c = self::$json[self::$position];
					if ($c == '\\') {
						// potrebbe esserci una sequenza UTF8 o un carattere speciale
						$c = substr(self::$json, self::$position + 1, 1);
						if ($c === FALSE) break; // return array(self::JSON_INCOMPLETE, NULL);

						// sequenza UTF8
						if ($c == 'u') {

							self::$position++;
							$utf = substr(self::$json, self::$position + 1, 4);
							if ($utf !== FALSE) {
								// verifica che sia effettivamente esadecimale
								for($i=0; $i < 4; $i++) {
									if (strpos('0123456789abcdef', $utf[$i]) === FALSE) {
										return array(self::JSON_PARSE_ERROR, 'Parse error: invalid unicode sequence at '.strval(self::position));
									}
								}
								self::$position += 4;
								$utf16 = chr(hexdec(substr($utf, 0, 2)))
                                       . chr(hexdec(substr($utf, 2, 2)));
                                $str .= self::utf162utf8($utf16);
							} else {
								return array(self::JSON_INCOMPLETE, NULL);
							}
						} elseif ($c == '"' || $c == '\\' || $c == '/') {
							$str .= $c;
						} elseif ($c == 'b') {
							$str .= "\b";
						} elseif ($c == 'f') {
							$str .= "\f";
						} elseif ($c == 'n') {
							$str .= "\n";
						} elseif ($c == 'r') {
							$str .= "\r";
						} elseif ($c == 't') {
							$str .= "\t";
						}
					} else {
						if ($c == '"') {
							self::$position++;
							// verifica se la stringa termini correttamente
							$nc = substr(self::$json, self::$position, 1);
							if ($nc !== FALSE) {
								if (self::isSpace($nc) || $nc == ']' || $nc == '}' || $nc == ',' || $nc == ':') {
									return array(self::JSON_STRING, $str);
								}
							} else {
								return array(self::JSON_PARSE_ERROR, 'Parse error: invalid string');
							}
						}
						$str .= $c;
					}

					self::$position++;
				}
				// se siamo arrivati qui ritornerà "stringa incompleta"
			} elseif ($c == '-' || strpos('0123456789', $c) !== FALSE) {

				$segno = '';
				// segno
				if ($c == '-') {
					$segno = $c;
					self::$position++;
				}

				if (self::$position < strlen(self::$json) ) {
					// Prende tutto quello che c'è fino all'eventuale punto
					$num = '';
					while (self::$position < strlen(self::$json) ) {
						if (strpos('0123456789', self::$json[self::$position]) === FALSE) break;
						else $num .= self::$json[self::$position];
						self::$position++;
					}
					// interrompe se siamo alla fine della stringa
					if (self::$position >= strlen(self::$json) || strlen($num) == 0) break;

					// Verifica la parte intera
					if ( self::$json[self::$position] == '.' ) {
						// Se c'è il punto può essere uguale a zero
						if (strcmp($num, '0') == 0 || strpos('123456789', $num[0]) !== FALSE ) {
							// procede con la parte decimale
							// parte decimale
							$dec_part = '';
							self::$position++;
							while (self::$position < strlen(self::$json)) {
								if (strpos('0123456789', self::$json[self::$position] ) === FALSE) break;
								else $dec_part .= self::$json[self::$position];
								self::$position++;
							}
							// Se è vuota, allora c'è un errore!
							if (strlen($dec_part) == 0) {
								return array(self::JSON_PARSE_ERROR, NULL);
							}
							// esponente
							// interrompe se siamo alla fine della stringa
							if (self::$position >= strlen(self::$json) || strlen($num) == 0) break;
							// verifica se ci sia l'esponente
							$exp = '';

							if ( strtolower(self::$json[ self::$position ]) == 'e' ) {
								$exp .= 'e';
								// verifica il segno
								$exp_s = substr(self::$json, self::$position + 1, 1);
								if ($exp_s !== FALSE) {
									if (strpos('+-', $exp_s) !== FALSE) $exp .= $exp_s;
									else {
										return array(self::JSON_PARSE_ERROR, NULL);
									}
									// prende la mantissa
									$m = '';
									self::$position += 2;
									while (self::$position < strlen(self::$json)) {
										if (strpos('0123456789', self::$json[self::$position] ) === FALSE) break;
										else $m .= self::$json[self::$position];
										self::$position++;
									}
									if (strlen($m) == 0) return array(self::JSON_PARSE_ERROR, NULL);
									else $exp .= $m;
								} else {
									return array(self::JSON_PARSE_ERROR, NULL);
								}
							}
							// resta da verificare se il prossimo carattere sia accettabile
							$nc = substr(self::$json, self::$position, 1);
							if ($nc !== FALSE) {
								if (self::isSpace($nc) || $nc == ']' || $nc == '}' || $nc == ',') {
									return array(self::JSON_FLOAT, floatval($segno.$num.'.'.$dec_part.$exp));
								}
							} else {

								return array(self::JSON_PARSE_ERROR, 'Parse error: wrong number at '.strval(self::$position));
							}
						} else {
							break;
						}
					} else {
						// il primo carattere NON può essere 0, se dopo ce ne sono altri
						if (strncmp($num, '0', 1) == 0 && strlen($num) > 1) {
							return array(self::JSON_PARSE_ERROR, 'Parse error: wrong number at '.strval(self::$position));
						} elseif(strcmp($num, '0') == 0 && strlen($segno) > 0) {
							// lo zero non ha segno!
							return array(self::JSON_PARSE_ERROR, 'Parse error: wrong number at '.strval(self::$position));
						} else {
							// verifica che ci siano carattere accettabile
							$nc = substr(self::$json, self::$position, 1);
							if ($nc !== FALSE) {
								if (self::isSpace($nc) || $nc == ']' || $nc == '}' || $nc == ',') {
									return array(self::JSON_INTEGER, intval($segno.$num));
								}
							} else {
								return array(self::JSON_PARSE_ERROR, 'Parse error: wrong number at '.strval(self::$position));
							}
						}
					}
				} else {
					break;
				}
			} elseif ($c == ',') {
				self::$position++;
				return array(self::JSON_COMMA, NULL);
			} elseif ($c == ':') {
				self::$position++;
				return array(self::JSON_COLON, NULL);
			}  elseif ($c == '[') {
				self::$position++;
				return array(self::JSON_ARRAY_START, NULL);
			}  elseif ($c == ']') {
				self::$position++;
				return array(self::JSON_ARRAY_END, NULL);
			} elseif ($c == '{') {
				self::$position++;
				return array(self::JSON_OBJECT_START, NULL);
			}  elseif ($c == '}') {
				self::$position++;
				return array(self::JSON_OBJECT_END, NULL);
			} elseif (ord($c) < 0x21 || ord($c) > 0x7f)  {
				// salta gli spazi
			} else {
				$data .= $c;
			}
			self::$position++;

			if (self::$position >= strlen(self::$json)) {
				if (strlen($data) == 0 && $token[0] == self::JSON_INCOMPLETE) $token[0] = self::JSON_END;
				if ($token[0] == self::JSON_INCOMPLETE) $token[1] = $data;
			}
		}

		if (strlen($data) == 0 && $token[0] == self::JSON_INCOMPLETE) $token[0] = self::JSON_END;
		if ($token[0] == self::JSON_INCOMPLETE) $token[1] = $data;

		return $token;
	}

	/**
	* Informa se il carattere sia uno spazio e vada ignorato.
	*
	* @param string $c un carattere da verificare
	* @return boolean TRUE se è uno spazio, FALSE altrimenti
	*/
	private static function isSpace($c) {
		return (ord($c) < 0x21);
	}
}

