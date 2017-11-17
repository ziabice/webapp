<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un PagerController per gestire un motore di ricerca
 * 
 * Questo PagerController estrae dalla richiesta HTTP la
 * stringa di ricerca e crea il criteria opportuno
*/
class SearchEnginePagerController extends PagerController {
	protected
		$search_lang,
		$search_str = '',
		$search_str_name;
	
	/**
	 * @param string $pager_name nome del pager
	 * @param string $search_str_name nome della variabile HTTP che contiene la stringa di ricerca
	 * @param string $lang stringa con la lingua, nel formato ISO xx_YY, es: it_IT
	 * @param string $destination destinazione base del pager
	 * @param integer $page_size dimensione della pagina (numero di elementi)
	 * @param integer $group_size dimensione dei gruppi di pagine (numero di pagine per gruppo)
	*/
	public function __construct($pager_name, $search_str_name, $lang, $destination = '', $page_size = 15, $group_size = 10) {
		$this->search_lang = $lang;
		$this->search_str_name = $search_str_name;
		parent::__construct($pager_name, $destination, $page_size, $group_size);
	}
	
	protected function getCriteriaFromHTTP() {
		
		$str_check = new TextFieldInputFilter($this->search_str_name, 0, 255, TRUE, '/^[\w\pL\pN]/u');
		if ($str_check->isValid()) {
			Logger::getInstance()->debug('SearchEnginePagerController::getCriteriaFromHTTP: Valid input string: "'.$str_check->getValue().'"');
			$this->search_str = $str_check->getValue();
			$criteria = $this->getDefaultCriteria();
			$this->setSearchCriteria($criteria, $this->search_str, $this->search_lang);
			return $criteria;
		} else {
			// Se la stringa è errata, blocca il pager
			if (!$str_check->isNULL()) {
				$this->setStatus(self::STATUS_INPUT_ERROR);
			}
			$this->search_str = NULL;
		}
		return NULL;
	}

	/**
	 * Dato un criterio di ricerca ne imposta i valori per la ricerca
	 * 
	 * @param Criteria $criteria l'istanza di aggiornare
	 * @param string $search_string stringa da usare per la ricerca
	 * @param string $search_lang lingua da utilizzare per la ricerca (nella forma xx_YY, es: it_IT)
	*/
	protected function setSearchCriteria($criteria, $search_string, $search_lang) {
		Logger::getInstance()->debug('SearchEnginePagerController::setSearchCriteria (query: '.$search_string.' / lang: '.$search_lang);
	}

	public function getPageDestination($page) {
		return parent::getPageDestination($page).'&'.$this->search_str_name.'='.urlencode($this->search_str);
	}
	
	/**
	 * Ritorna la stringa di ricerca fornita dalla richiesta HTTP
	 *
	 * @return string la stringa di ricerca fornita dalla richiesta HTTP
	*/
	public function getSearchString() {
		return $this->search_str;
	}
}

