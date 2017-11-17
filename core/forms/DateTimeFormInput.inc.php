<?php 
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Inserimento facilitato di date nelle FormView usando oggetti CDateTime.
 *
 * Lo stile del controllo è governato dalle costanti (usate bit a bit):
 *
 * STYLE_DATE - mostra il selettore per la data
 * STYLE_TIME - nostra il selettore per l'ora (ora, minuti e secondi)
 * STYLE_MONTHNAME - il selettore per la data presenta i nomi dei mesi invece del numero del mese
 * STYLE_TIME_SHORT - orario con solo ora e minuti (senza secondi)
 * STYLE_WITHEMPTY_DATE - data con campo vuoto
 * STYLE_WITHEMPTY_TIME - ora con campo vuoto
 * STYLE_WITHEMPTY - aggiungi il campo vuoto
 * STYLE_DEFAULT - data e ora con campo vuoto
 * STYLE_DEFAULT_SHORT - data e ora breve con campo vuoto
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * @see CDateTime
*/
class DateTimeFormInput {
	
	const
		STYLE_DATE = 1,
		STYLE_TIME = 2,
		STYLE_MONTHNAME =  4,
		STYLE_MONTHNAME_SHORT = 8,
		STYLE_WITHEMPTY_DATE = 16,
		STYLE_WITHEMPTY_TIME = 32,
		STYLE_WITHEMPTY = 48,
		STYLE_TIME_SHORT = 64,
		
		// STYLE_DEFAULT = self::STYLE_DATE | self::STYLE_TIME | self::STYLE_WITHEMPTY;
		STYLE_DEFAULT = 51,
		
		// STYLE_DEFAULT_SHORT = self::STYLE_DATE | self::STYLE_TIME_SHORT | self::STYLE_WITHEMPTY;
		STYLE_DEFAULT_SHORT = 113,
	
		// STYLE_DEFAULT_NOEMPTY = self::STYLE_DATE | self::STYLE_TIME
		STYLE_DEFAULT_NOEMPTY = 3,
	
		// STYLE_DEFAULT_SHORT_NOEMPTY = self::STYLE_DATE | self::STYLE_TIME_SHORT
		STYLE_DEFAULT_SHORT_NOEMPTY = 65;
	
	/**
	 * Ritorna il codice XHTML per i controlli (che andranno validati utilizzando DateTimeInputFilter)
	 *
	 * Utilizza l'istanza di FormView per generare i controlli.
	 *
	 * @param CDateTime $date
	 * @param FormView $formview
	 * @param string $name nome base dei controlli
	 * @param integer $style stile da usare per il controllo
	 * @return string stringa col codice XHTML
	 * @see DateTimeInputFilter
	 */
	public static function get(CDateTime $date, FormView $formview, $name = 'frm_date', $style = self::STYLE_DEFAULT) {
		return $formview->getStyle()->getDateTimeInput($date, $name, $style);
	}
	
	/**
	 * Ritorna il codice javascript per creare una etichetta che
	 * permette di compilare i campi con la data e ora attuali.
	 * 
	 * @param string $label etichetta XHTML da utilizzare
	 * @param string $name nome dei controlli
	 * @param integer $style stile utilizzato dai controlli
	 * @return string il codice javascript
	 * */
	public static function getNowSetter($label, $name, $style = self::STYLE_DEFAULT) {
		return WebApp::getInstance()->getPageLayout()->getWidgetStyle()->getFormViewStyle()->getDateTimeInputNowSetter($label, $name, $style);
	}
}

