<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestione delle date come oggetti.
 *
 * Accetta in ingresso una data particolare '0000-00-00 00:00:00',
 * che rappresenta la data 'vuota', come se la data fosse impostata a NULL.
 *
 * Necessario perchè DateTime non esiste nelle versioni di php < 5.2
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class CDateTime extends ObjectConnectAdapter {
	const
		WITH_DATE = 1,
		WITH_TIME = 2,
		WITH_ALL = 3,

		// Numero di secondi in un'ora
		SECONDS_PER_HOUR = 3600,
		
		// Numero di secondi in un giorno
		SECONDS_PER_DAY = 86400;
		
	
	protected
		$timezone, // stringa con la timezone
		$date; // array con la data

	/**
	 * Inizializza l'oggetto con una data.
	 * 
	 * La data in ingresso è una stringa che può essere:
	 * 
	 * 'now' - inizializza con data e ora corrente, per la timezone specificata
	 * YYYY-mm-dd h:m:s - una stringa nel formato ISO 
	 * dd-mm-YYYY h:m:s - una stringa nel formato europeo
	 * una stringa accettata da strtotime
	 * NULL - la data vuota (0000-00-00 00:00:00)
	 * 
	 * Il giorno valido va da 1 a 31
	 * Il mese valido va da 1 a 12
	 * L'ora valida va da 0 a 23
	 * I minuti validi e i secondi vanno da 0 a 59.
	 * 
	 * La timezone deve essere una di quelle accettate da date_default_timezone_set.
	 *
	 * @param string $date stringa con la data
	 * @param string $timezone stringa con la timezone
	 * @throws Exception se la data non è valida (è stato impossibile determinare una data dalla stringa)
	 **/
	public function __construct($date = 'now', $timezone = '') {
		$this->date = array(
			'dd' => -1,
			'mm' => -1,
			'yy' => -1,
			'h' => -1,
			'm' => -1,
			's' => -1
		);
		
		
		if (function_exists('date_default_timezone_set')) {
			$this->timezone = (empty($timezone) ? date_default_timezone_get() : $timezone);
		} else {
			$this->timezone = 'UTC';
		}
		
		// la data è vuota
		if (is_null($date)) return;

		// Mette la data corrente
		if ($date === 'now') {
			if (function_exists('date_default_timezone_set')) {
				date_default_timezone_set($this->timezone);
			}
			$t = getdate();
			$this->date = array(
			'dd' => $t['mday'],
			'mm' => $t['mon'],
			'yy' => $t['year'],
			'h' => $t['hours'],
			'm' => $t['minutes'],
			's' => $t['seconds']
			);
		} else {
			// Potrebbe essere una data in formato ISO
			$p = array();
			if (preg_match('/^(\d{4})[-\/]([0-1]?[0-9])[-\/]([0-3]?[0-9])( ([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?/', $date, $p) == 1) {
				$this->date['yy'] = intval($p[1]);
				$this->date['mm'] = intval($p[2]);
				$this->date['dd'] = intval($p[3]);
				if(isset($p[4])) {
					$this->date['h'] = intval($p[5]);
					$this->date['m'] = intval($p[6]);
					$this->date['s'] = intval($p[7]);
				} else {
					$this->date['h'] = 0;
					$this->date['m'] = 0;
					$this->date['s'] = 0;
				}
			} elseif (preg_match('/^([0-3]?[0-9])[-\/]([0-1]?[0-9])[-\/](\d{4})( ([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?/', $date, $p)  == 1) {
				// Oppure una europea dd-mm-YYYY h:m:s
				$this->date['yy'] = intval($p[3]);
				$this->date['mm'] = intval($p[2]);
				$this->date['dd'] = intval($p[1]);

				if(isset($p[4])) {
					$this->date['h'] = intval($p[5]);
					$this->date['m'] = intval($p[6]);
					$this->date['s'] = intval($p[7]);
				} else {
					$this->date['h'] = 0;
					$this->date['m'] = 0;
					$this->date['s'] = 0;
				}
			} else {
				// Prova in qualche modo...
				if (function_exists('date_default_timezone_set')) {
					date_default_timezone_set($this->timezone);
				}
				$t = strtotime($date);
				if ($t !== FALSE || $t !== -1) {
					$this->date = array(
						'dd' => intval(date('j', $t)),
						'mm' => intval(date('n', $t)),
						'yy' => intval(date('Y', $t)),
						'h' => intval(date('G', $t)),
						'm' => intval(date('i', $t)),
						's' => intval(date('s', $t))
					);
				} else {
					throw new Exception('CDateTime: invalid date supplied.');
				}
			}
		}
	}
	
	/**
	 * Informa se la data sia valida.
	 * 
	 * Una data valida è una data esistente.
	 * 
	 * @return boolean TRUE se è valida, FALSE altrimenti
	 * */
	public function isValid() {
		return (checkdate($this->date['mm'], $this->date['dd'], $this->date['yy']) && ($this->date['h'] >= 0 && $this->date['h'] <= 23 && $this->date['m'] >= 0 && $this->date['m'] <= 59 && $this->date['s'] >= 0 && $this->date['s'] <= 59 )  );
	}
	
	/**
	 * Copia le informazioni da un oggetto CDateTime.
	 * 
	 * @param CDateTime $dt oggetto da cui copiare le informazioni
	 * */
	public function copy(CDateTime $dt) {
		$this->date['yy'] = $dt->date['yy'];
		$this->date['mm'] = $dt->date['mm'];
		$this->date['dd'] = $dt->date['dd'];
		$this->date['h'] = $dt->date['h'];
		$this->date['m'] = $dt->date['m'];
		$this->date['s'] = $dt->date['s'];
		$this->timezone = $dt->timezone;
		
		$this->touch();
	}
	
	
	/**
	 * Crea una nuova data "vuota", ossia che indica '0000-00-00 00:00:00'
	 * 
	 * E' un metodo factory
	 *
	 * @return CDateTime l'instanza creata
	*/	
	public static function newEmpty() {
		return new CDateTime(NULL);
	}

	/**
	 * Imposta la timezone.
	 *
	 * Il parametro deve essere una stringa accettata da date_default_timezone_set.
	 *
	 * @param string $timezone stringa con la timezone
	 */
	public function setTimezone($timezone = 'UTC') {
		$this->timezone = $timezone;
		$this->touch();
	}

	/**
	 * Ritorna la timezone di questa data.
	 *
	 * @return string la timezone della data
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 * Imposta data e ora usando le sue varie parti. Non viene fatta nessuna verifica di validità
	 *
	 * @param integer $dd giorno del mese (1-31)
	 * @param integer $mm mese dell'anno (da 1 a 12)
	 * @param integer $yy anno
	 * @param integer $h ora (0-23)
	 * @param integer $m minuti (0-59)
	 * @param integer $s secondi (0-59)
	 */
	public function set($dd, $mm, $yy, $h = 0, $m = 0, $s = 0) {
		$this->date = array(
			'dd' => intval($dd),
			'mm' => intval($mm),
			'yy' => intval($yy),
			'h' => intval($h),
			'm' => intval($m),
			's' => intval($s)
		);
		$this->touch();
	}

	/**
	 * Imposta la data usando le sue varie parti. Non viene fatta nessuna verifica di validità
	 *
	 * @param integer $dd giorno del mese (1-31)
	 * @param integer $mm mese dell'anno (da 1 a 12)
	 * @param integer $yy anno
	 */
	public function setDate($dd, $mm, $yy) {
		$this->date['dd'] = intval($dd);
		$this->date['mm'] = intval($mm);
		$this->date['yy'] = intval($yy);
		$this->touch();
	}

	/**
	 * Imposta l'ora usando le sue varie parti. Non viene fatta nessuna verifica di validità
	 *
	 * @param integer $h ora (0-23)
	 * @param integer $m minuti (0-59)
	 * @param integer $s secondi (0-59)
	 */
	public function setTime($h, $m, $s) {
		$this->date['h'] = intval($h);
		$this->date['m'] = intval($m);
		$this->date['s'] = intval($s);
		$this->touch();
	}

	/**
	 * Ritorna la data in una stringa in formato iso
	 * Indica quali parti della data si vuole ritornare:
	 *
	 * WITH_DATE - ritorna la data
	 * WITH_TIME - ritorna l'ora
	 * WITH_ALL - ritorna entrambi
	 *
	 * La data ritornata è nel formato: YYYY-MM-DD HH:MM:SS
	 * 
	 * @param integer $show campo bit a bit che indica cosa ritornare
	 * @return string la stringa con la data
	 *
	*/
	public function toISO($show = self::WITH_ALL) {
		if ($this->isEmpty()) {
			$out = '';
			if ($show & self::WITH_DATE) $out = '0000-00-00';
			if ($show & self::WITH_TIME) $out .= (empty($out) ? '' : ' ').'00:00:00';
			return $out;
		} else {
			if ($show & self::WITH_DATE) $out = 'Y-m-d';
			if ($show & self::WITH_TIME) $out .= (empty($out) ? '' : ' ').'H:i:s';
			return $this->format($out);
		}
	}

	/**
	 * Indica se sia una data vuota (ossia non esistente, resa come '0000-00-00 00:00:00')
	 *
	 * @return boolean TRUE se è una data vuota, FALSE altrimenti
	 */
	public function isEmpty() {
		return ($this->date['yy'] == -1 && $this->date['mm'] == -1 && $this->date['dd'] == -1 &&
			$this->date['h'] == -1 && $this->date['m'] == -1 && $this->date['s'] == -1);
	}

	/**
	 * Imposta la data come vuota.
	 * 
	 * La data vuota è una data inesistente utile per indicare uno stato
	 * di "non impostato".
	 * 
	 */
	public function setEmpty() {
		$this->date['yy'] = -1;
		$this->date['mm'] = -1;
		$this->date['dd'] = -1;
		$this->date['h'] = -1;
		$this->date['m'] = -1;
		$this->date['s'] = -1;
	}

	/**
	 * Ritorna una rappresentazione stringa della data interna.
	 *
	 * Flag supportati (vedi help della funzione date):
	 *
	 * Y - anno 4 cifre (0 padded)
	 * y - anno 2 cifre
	 * m - mese 2 cifre (0 padded)
	 * n - mese numerico
	 * M - nome del mese in 3 lettere (tradotto nella lingua corrente, se disponibile)
	 * F - nome del mese esteso (tradotto nella lingua corrente, se disponibile)
	 * n - giorno del mese 1 o 2 cifre
	 * d - giorno 2 cifre (0 padded)
	 * j - giorno 1 o 2 cifre
	 * H - ora (0 padded)
	 * h - ora (da 1 a 12, 0 padded)
	 * g - ora (da 1 a 12)
	 * G - ora (0 - 24)
	 * i - minuti (0 padded)
	 * s - secondi
	 *
	 * @param string $format una stringa accettata da date
	 * @return string la stringa formattata
	 */
	public function format($format = 'Y-m-d') {

		$mesi = self::getMonthNames();

		// Viene gestita in modo particolare?
		// supporta solo alcuni flag
		$out = '';
		$c = 0;
		$max_c = strlen($format);


		while ($c < $max_c) {
			switch($format[$c]) {
				case 'y': $out .= substr( str_pad($this->date['yy'], 4, '0', STR_PAD_LEFT), 2); break;
				case 'Y': $out .= str_pad($this->date['yy'], 4, '0', STR_PAD_LEFT); break;
				case 'm': $out .= str_pad($this->date['mm'], 2, '0', STR_PAD_LEFT); break;
				case 'n': $out .= strval($this->date['mm']); break;
				case 'F': $out .= $mesi[ $this->date['mm'][0] ]; break;
				case 'M': $out .= $mesi[ $this->date['mm'][1] ]; break;
				case 'd': $out .= str_pad($this->date['dd'], 2, '0', STR_PAD_LEFT); break;
				case 'j': $out .= strval($this->date['dd']); break;
				case 'H': $out .= str_pad($this->date['h'], 2, '0', STR_PAD_LEFT); break;
				case 'h': $out .= str_pad(($this->date['h'] > 12 ? $this->date['h'] - 12 : $this->date['h']), 2, '0', STR_PAD_LEFT); break;
				case 'g': $out .= strval($this->date['h'] > 12 ? $this->date['h'] - 12 : $this->date['h']); break;
				case 'G': $out .= strval($this->date['h']); break;
				case 'i': $out .= str_pad($this->date['m'], 2, '0', STR_PAD_LEFT); break;
				case 's': $out .= str_pad($this->date['s'], 2, '0', STR_PAD_LEFT); break;
				case '\\':
					$c++;
					if ($c < $max_c) $out .= $format[$c];
				break;
				default:
					$out .= $format[$c];
			}
			$c++;
		}
		
		return $out;
	}

	/**
	 * Ritorna i nomi dei mesi.
	 *
	 * Ritorna un array contenente i nomi dei mesi tradotti secondo il
	 * locale indicato, oppure quello corrente.
	 *
	 * L'array è nella forma:
	 *
	 * 1 => array( 'Nome Esteso', 'Nome Breve'  ),
	 * 2 => array( 'Nome Esteso', 'Nome Breve'  ),
	 * ...
	 * 12 => array( 'Nome Esteso', 'Nome Breve'  )
	 *
	 * Dove "Nome esteso" indica il mese per intero (es. "Gennaio", "Febbraio"), mentre
	 * "Nome Breve" il mese in tre lettere (es. "Gen", "Feb"). L'indice dell'array
	 * parte convenientemente da 1 e arriva a 12.
	 *
	 * La lingua va indicata nel formato xx_YY, es it_IT per l'italiano.
	 *
	 * Utilizza le funzioni di libreria di PHP, in particolare setftime.
	 *
	 * @param string $locale stringa con il locale da utilizzare
	 * @return array array coi nomi dei mesi
	 */
	public static function getMonthNames($locale = NULL) {
		$saved_locale = setlocale(LC_TIME, '0');
		if (is_null($locale) || strlen($locale) == 0) $locale = WebApp::getInstance()->getI18N()->getLocale();
		/*
		if (!empty($locale)) {
			$lang = WebApp::getInstance()->getI18N()->getLocale();
			WebApp::getInstance()->getI18N()->setLocale($locale);
		}
		$out = array(
			1 => array(tr('Gennaio'), tr('Gen')),
			2 => array(tr('Febbraio'), tr('Feb')),
			3 => array(tr('Marzo'), tr('Mar')),
			4 => array(tr('Aprile'), tr('Apr')),
			5 => array(tr('Maggio'), tr('Mag')),
			6 => array(tr('Giugno'), tr('Giu')),
			7 => array(tr('Luglio'), tr('Lug')),
			8 => array(tr('Agosto'), tr('Ago')),
			9 => array(tr('Settembre'), tr('Set')),
			10 => array(tr('Ottobre'), tr('Ott')),
			11 => array(tr('Novembre'), tr('Nov')),
			12 => array(tr('Dicembre'), tr('Dic'))
		);
		if (!empty($locale)) WebApp::getInstance()->getI18N()->setLocale($lang);
		return $out;
		*/

		$out = array();
		if (function_exists('date_default_timezone_set')) {
			// date_default_timezone_set($timezone);
		}

		for ($mese = 1; $mese < 13; $mese++) {
			setlocale(LC_TIME, $locale);
			$out[] = array( strftime('%B', mktime(0, 0, 0, $mese, 1, 1970)), strftime('%b', mktime(0, 0, 0, $mese, 1, 1970)) );
		}
		setlocale(LC_TIME, $saved_locale);
		return $out;
	}
	
	/**
	 * Ritorna una rappresetanzione stringa in base al locale corrente
	 *
	 * @param string $format una stringa nel formato di strftime
	 * @param string $locale NULL o una stringa col locale
	 * @return string la stringa formattata
	 */
	public function lformat($format = '%Y-%m-%d', $locale = NULL) {
		if (function_exists('date_default_timezone_set')) date_default_timezone_set($this->timezone);
		
		if (is_null($locale)) {
			return strftime($format, $this->getTimeStamp());
		} else {
			$loc = setlocale(LC_TIME, 0);
			$s = strftime($format, $this->getTimeStamp());
			setlocale(LC_TIME, $loc);
			return $s;
		}
	}
	

	/**
	 * Ritorna il timestamp che rappresenta la data associata
	 * @return integer intero col timestamp della data
	 * */
	public function getTimeStamp() {
		return mktime($this->date['h'],$this->date['m'], $this->date['s'], $this->date['mm'], $this->date['dd'], $this->date['yy']);
	}

	/**
	 * Ritorna l'array interno che contiene la rappresentazione della data
	 * array(
	 *  'dd' => giorno
	 *  'mm' => mese
	 *  'yy' => anno
	 * 	'h' => ora
	 * 	'm' => minuti
	 * 	's' => secondi
	 * );
	 *
	 * @return array
	 */
	public function get() {
		return $this->date;
	}
	
	/**
	 * Ritorna l'anno.
	 * 
	 * Se la data è vuota ritorna -1.
	 * 
	 * @return integer l'anno
	 */
	public function getYear() {
		return $this->date['yy'];
	}
	
	/**
	 * Ritorna il mese.
	 * 
	 * Se la data è vuota ritorna -1.
	 * 
	 * @return integer il mese
	 */
	public function getMonth() {
		return $this->date['mm'];
	}
	
	/**
	 * Ritorna il mese.
	 * 
	 * Se la data è vuota ritorna -1.
	 * 
	 * @return integer il giorno
	 */
	public function getDay() {
		return $this->date['dd'];
	}
	
	/**
	 * Ritorna le ore.
	 * 
	 * Se la data è vuota ritorna -1.
	 * 
	 * @return integer le ore
	 */
	public function getHours() {
		return $this->date['h'];
	}
	
	/**
	 * Ritorna i minuti.
	 * 
	 * Se la data è vuota ritorna -1.
	 * 
	 * @return integer i minuti
	 */
	public function getMinutes() {
		return $this->date['m'];
	}

	/**
	 * Ritorna i secondi.
	 * 
	 * Se la data è vuota ritorna -1.
	 * 
	 * @return integer i secondi
	 */
	public function getSeconds() {
		return $this->date['s'];
	}




	/**
	 * FIXME: non tiene conto della timezone.
	 *
	 * Verifica se questa data sia precedente a quella indicata.
	 *
	 * Non tiene in conto l'eventuale timezone.
	 *
	 * Se una delle date è vuota ritorna FALSE.
	 *
	 * @param CDateTime $date_to_check il limite da verificare
	 * @return boolean TRUE se questa data è antecedente, FALSE altrimenti
	 */
	public function isBefore(CDateTime $date_to_check) {
		if ($date_to_check->isEmpty() || $this->isEmpty()) return FALSE;
		$check = ($this->date['yy'] <= $date_to_check->date['yy'] && $this->date['mm'] <= $date_to_check->date['mm']);
		if ($this->date['yy'] == $date_to_check->date['yy'] && $this->date['mm'] == $date_to_check->date['mm']) {
			$check = $check && ($this->date['dd'] < $date_to_check->date['dd']);
		}
		return $check;
	}

	/**
	 * FIXME: non tiene conto della timezone.
	 * 
	 * Verifica se questa data sia posteriore a quella indicata.
	 *
	 * Non tiene in conto l'eventuale timezone.
	 *
	 * Se una delle date è vuota ritorna FALSE.
	 *
	 * @param CDateTime $date_to_check il limite da verificare
	 * @return boolean TRUE se questa data è posteriore, FALSE altrimenti
	 */
	public function isAfter(CDateTime $date_to_check) {
		if ($date_to_check->isEmpty() || $this->isEmpty()) return FALSE;
		$check = ($this->date['yy'] >= $date_to_check->date['yy'] && $this->date['mm'] >= $date_to_check->date['mm']);
		if ($this->date['yy'] == $date_to_check->date['yy'] && $this->date['mm'] == $date_to_check->date['mm']) {
			$check = $check && ($this->date['dd'] > $date_to_check->date['dd']);
		}
		return $check;
	}

	/**
	 * FIXME: non tiene conto della timezone.
	 * 
	 * Verifica se questa data compresa tra quelle indicate.
	 *
	 * Non tiene in conto l'eventuale timezone.
	 *
	 * Se una delle date è vuota ritorna FALSE.
	 * 
	 * @param CDateTime $date_from il limite superiore da verificare
	 * @param CDateTime $date_to il limite inferiore da verificare
	 * @return boolean TRUE se questa data è compresa, FALSE altrimenti
	 */
	public function isInBetween(CDateTime $date_from, CDateTime $date_to) {
		if ($date_from->isEmpty() || $date_to->isEmpty() || $this->isEmpty()) return FALSE;
		$check_date_from = FALSE;
		$check_date_to = FALSE;
		// Corner case: le due date sono uguali, bisogna confrontare le ore
		if ($this->isSameDate($date_from)) {
			$check_date_from = ($this->date['h'] >= $date_from->date['h'] &&
				$this->date['m'] >= $date_from->date['m'] &&
				$this->date['s'] >= $date_from->date['s'] );
		} else {
			$check_date_from = ($this->date['yy'] >= $date_from->date['yy'] && $this->date['mm'] >= $date_from->date['mm']);
			if ($this->date['yy'] == $date_from->date['yy'] && $this->date['mm'] == $date_from->date['mm']) {
				$check_date_from = $check_date_from && ($this->date['dd'] >= $date_from->date['dd'] );
			}
		}

		if ($this->isSameDate($date_to)) {
			$check_date_to = ($this->date['h']  <= $date_to->date['h'] &&
				$this->date['m'] <= $date_to->date['m'] &&
				$this->date['s'] <= $date_to->date['s'] );
		} else {
			$check_date_to = ($this->date['yy'] <= $date_to->date['yy']  && $this->date['mm'] <= $date_to->date['mm']);
			if ($this->date['yy'] == $date_to->date['yy']  && $this->date['mm'] == $date_to->date['mm']) {
				$check_date_to = $check_date_to && ($this->date['dd'] <= $date_to->date['dd'] );
			}
		}

		return ($check_date_from && $check_date_to);
	}

	/**
	 * FIXME: non tiene conto della timezone.
	 * 
	 * Verifica che due date siano uguali.
	 * 
	 * Verifica solo la data, non l'ora. 
	 * 
	 * @param CDateTime $date_to_check data da verificare
	 * @return boolean TRUE se le date sono uguali, FALSE altrimenti 
	 */
	public function isSameDate(CDateTime $date_to_check) {
		return ($this->date['yy'] == $date_to_check->date['yy'] && $this->date['mm'] == $date_to_check->date['mm'] && $this->date['dd'] == $date_to_check->date['dd']);
	}

	/**
	 * Ritorna il timestamp Unix che rappresenta la data.
	 * 
	 * Ritorna il numero di secondi intercorsi dal primo gennaio 1970 alla data dell'oggetto.
	 * Ritorna FALSE in caso di errore.
	 * 
	 * @return integer FALSE in caso di errore, altrimenti un long integer con il timestamp
	 */
	public function toTimestamp() {
		return mktime($this->date['h'], $this->date['m'], $this->date['s'], $this->date['mm'], $this->date['dd'], $this->date['yy']);
	}
	
	/**
	 * Imposta la data da un timestamp Unix.
	 * 
	 * @param integer $timestamp un long integer con il timestamp
	 * @throws Exception in caso di timestamp non valido
	 */
	public function fromTimestamp($timestamp) {
		if ($timestamp < 0) throw new Exception("Invalid timestamp.");
		
		$this->date = array(
			'dd' => intval( date("j", $timestamp) ),
			'mm' => intval( date("n", $timestamp) ),
			'yy' => intval( date("Y", $timestamp) ),
			'h' => intval( date("G", $timestamp) ),
			'm' => intval( date("i", $timestamp) ),
			's' => intval( date("s", $timestamp) )
		);
	}
	
	/**
	 * Costruisce da un timestamp Unix.
	 * 
	 * @param integer $timestamp un long integer con il timestamp
	 * @return CDateTime l'oggetto con la data
	 * @throws Exception in caso di timestamp non valido
	 */
	public static function newFromTimestamp($timestamp) {
		$d = new CDateTime(NULL);
		$d->fromTimestamp($timestamp);
		return $d;
	}
	
	/**
	 * Aggiunge dei secondi alla data.
	 * 
	 * Funziona solo con date riconducibili alla Unix Epoch.
	 * 
	 * @param integer $seconds numero di secondi da aggiungere
	 */
	public function add($seconds) {
		$this->fromTimestamp( $this->toTimestamp() + abs($seconds) );
	}
	
	/**
	 * Sottrae dei secondi alla data.
	 * 
	 * Funziona solo con date riconducibili alla Unix Epoch.
	 * 
	 * @param integer $seconds numero di secondi da sottrarre
	 */
	public function sub($seconds) {
		$this->fromTimestamp( $this->toTimestamp() - abs($seconds) );
	}

}
