<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una astrazione di un paginatore di una serie di dati
 *
 * Fornisce tutte le informazioni per calcolare i dati
 * di una visualizzazione paginata
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class Pager {
	protected
		$total_count, // Numero totale di elementi
		$group_size, // Numero di pagine per gruppo di pagine
		$groups, // Numero totale di gruppi di pagine
		$page_size, // Numero di elementi per pagina
		$last_pager; // Ultimo pager generato da getPager

	/**
	 * Costruisce un nuovo pager
	 * 
	 * @param integer $page_size intero con il numero di elementi per pagina
	 * @param integer $group_size intero col numero di pagina per gruppo di pagine
	 * @param integer $totalcount intero col numero totale di elementi
	*/
	public function __construct($page_size = 15, $group_size = 10, $totalcount = 0) {
		$this->last_pager = array();
		$this->setTotalCount($totalcount);
		$this->setPageSize($page_size);
		$this->setGroupSize($group_size);
	}

	/**
	 * Imposta il numero totale di elementi
	 * @param integer $totalcount intero con il numero di elementi
	*/
	public function setTotalCount($totalcount) {
		$this->total_count = $totalcount;
	}
	
	/**
	 * Ritorna il numero totale di elementi.
	 * 
	 * @return integer il numero di elementi
	 * */
	public function getTotalCount() {
		return $this->total_count;
	}
	
	/**
	 * Imposta la dimensione della pagina, ossia il numero di
	 * elementi che compongono una pagina
	 * 
	 * @param integer $pagesize intero col numero di elementi
	*/
	public function setPageSize($pagesize) {
		$pagesize = intval($pagesize);
		$this->page_size = ($pagesize < 0 ? 0 : $pagesize);
	}
	
	/**
	 * Imposta la quantità di pagine che vogliamo in ogni gruppo di pagine
	 * @param integer $pages_per_group intero col numero di pagine per gruppo di pagine
	*/
	public function setGroupSize($pages_per_group) {
		$this->group_size = $pages_per_group;
	}
	
	/**
	 * Ritorna la dimensione in righe della pagina.
	 * 
	 * @return integer il numero di elementi per pagina
	 * */
	public function getPageSize() {
		return $this->page_size;
	}
	
	/**
	 * Ritorna il numero di pagine per gruppo di pagine.
	 * 
	 * @return integer il numero di elementi
	 * */
	public function getGroupSize() {
		return $this->group_size;
	}

	
	/**
	 * Ritorna le informazioni relative al pager per una determinata pagina.
	 *
	 * Le informazioni generate vengono mantenute in memoria e possono essere ricavate
	 * di nuovo con una chimata a {@link getLastPager}.
	 * 
	 * Ritorna un array associativo contenente i link da utilizzare per costruire 
	 * un pager, con i seguenti indici:
	 * 'total_count' - intero col numero totale di record
	 * 'showpage' - intero con la pagina che verrà visualizzata (0 per la prima pagina)
	 * 'page_size' - numero di elementi per pagina
	 * 'startrecord' - intero con l'indice del record da estrarre (in una clausola SQL LIMIT)
	 * 'recordcount' - intero con il numero di record da estrarre (in una clasuola SQL LIMIT)
	 * 'groups_count' - intero col numero di gruppi di pagine
	 * 'group_size' - intero col numero di pagine per gruppo di pagine
	 * 'pages_count' - intero col numero totale di pagine
	 * 'wrongpagetoshow' - boolean pari a TRUE settato solo se la pagina che si vuole visualizzare non esiste o non è valida
	 * 'prev' - intero da utilizzare nelle URL per indicare la pagina precedente
	 * 'next' - intero da utilizzare nelle URL per indicare la pagina successiva
	 * 'prev_id' - stringa che indica la pagina precedente nelle etichette
	 * 'next_id' - stringa che indica la pagina successiva nelle etichette
	 * 'next_pages' - prossimo blocco di pagine
	 * 'prev_pages' - precedente blocco di pagine
	 * 'current' - intero da utilizzare nelle URL per indicare la pagina corrente
	 * 'current_id' - stringa che indica la pagina corrente nelle etichette
	 * 'first' - intero da utilizzare nelle URL per indicare la prima pagina
	 * 'first_id' - stringa che indica la prima pagina
	 * 'last' - intero da utilizzare nelle URL per indicare l'ultima pagina
	 * 'last_id' - stringa che indica l'ultima pagina
	 * 'groups' - gruppi di pagine: un array multidimensionale nella forma:
	 * 		"idgruppo" => array(
	 *			'from' => id della pagina iniziale, 
	 *			'to' => id della pagina finale,
	 *			'url' -  oggetto URL con link alla pagina iniziale del gruppo)
	 * 'pages' - pagine specifiche (nel blocco corrente)
	 * 		è un array multidimensionale nella forma:
	 *  			"idpagina" => array('id' => numero di pagina, 'url' => url della pagina)
	 * 
	 * IMPORTANTE: le pagine internamente partono da 0, mentre la numerazione per l'utente parte
	 * da 1. Quindi se voglio richiedere la prima pagina, essa sarà indicata con 0 nella url, ma
	 * il testo dell'eventuale etichetta sarà 1.
	 * 
	 * 0 <= $showpage < pages_count
	 * 
	 * @param $showpage intero (a partire da 0) che indica la pagina che si vorrebbe visualizzare
	 * @return array array associativo coi dati del pager
	*/
	public function getPager($showpage = 0) {
		$showpage = intval($showpage);
		if ($showpage < 0) $showpage = 0;

		$this->last_pager = array(
			'total_count' => $this->total_count,
			'showpage' => $showpage,
			'page_size' => $this->page_size,
			'startrecord' => 0,
			'recordcount' => $this->page_size,
			'groups_count' => 0,
			'group_size' => $this->group_size
		);
		
		// Setta il numero totale di pagine
		if ($this->total_count == 0 || $this->page_size == 0) {
			$this->last_pager['pages_count'] = 0;
		} else {
			$this->last_pager['pages_count'] = (int) (ceil((float)$this->total_count / (float)$this->page_size));
		}
		
		// Aggiorna di conseguenza il numero reale della pagina 
		// che si vuole visualizzare
		// se è fuori range, la porta a zero, ed imposta un flag di errore
		if ($showpage < 0 || $showpage >= $this->last_pager['pages_count']) {
			$this->last_pager['showpage'] = 0;
			$this->last_pager['wrongpagetoshow'] = TRUE;
		}
		
		// Ricalcola il record iniziale
		$this->last_pager['startrecord'] = $this->last_pager['showpage'] * $this->last_pager['page_size'];
		
		// Quantità di gruppi di pagine
		$this->last_pager['groups_count'] = (int)ceil($this->last_pager['pages_count'] / $this->group_size);
		
		// Imposta i parametri di navigazione
		$this->last_pager['prev'] = NULL;
		$this->last_pager['next'] = NULL;
		$this->last_pager['prev_id'] = '';
		$this->last_pager['next_id'] = '';
		$this->last_pager['first'] = 0;
		$this->last_pager['first_id'] = '1';
		$this->last_pager['last'] = $this->last_pager['pages_count'] - 1;
		$this->last_pager['last_id'] = strval($this->last_pager['pages_count']);
		$this->last_pager['next_pages'] = '';
		$this->last_pager['prev_pages'] = '';
		$this->last_pager['current'] = '';
		$this->last_pager['current_id'] = '';
		$this->last_pager['pages'] = array();
		$this->last_pager['groups'] = array();
	
		
		if ($this->last_pager['pages_count'] > 0) {
			
			$this->last_pager['current_id'] = $showpage + 1;
			$this->last_pager['current'] = $showpage;
			if ($showpage > 0) {
				$this->last_pager['prev_id'] = $this->last_pager['current_id'] - 1;
				$this->last_pager['prev'] = $showpage - 1;
			}
			if ($showpage < $this->last_pager['last'] ) {
				$this->last_pager['next_id'] = $this->last_pager['current_id'] + 1;
				$this->last_pager['next'] = $showpage + 1;
			}

			// Gruppo di pagine corrente
			/*
			 * NB: sp e ep partono da 0, mentre il conteggio delle pagine parte da 1
			 * */

			$gruppo = (int)floor($showpage / $this->group_size);
			$sp = $gruppo * $this->group_size;
			$ep = $sp + $this->group_size - 1;
			if ($ep + 1 >= $this->last_pager['pages_count']) $ep = $this->last_pager['pages_count'] - 1;
			if ($ep == $sp) {
				$this->last_pager['pages'][$sp] = array(
						'id' => strval($sp + 1),
						'url' => $sp
					);
			} else {
				foreach(range($sp, $ep) as $p) {
					$this->last_pager['pages'][$p] = array(
						'id' => strval($p + 1),
						'url' => $p
					);
				}
			}


			foreach(range(0, $this->last_pager['groups_count'] - 1) as $g) {
				$pg_from = $g * $this->group_size + 1;
				$pg_to = ($g + 1) * $this->group_size;
				$this->last_pager['groups'][$g] = array(
					'id' => strval($g + 1),
					'url' => $g * $this->group_size,
					'from' => $pg_from,
					'to' => $pg_to
				);
				if ($this->last_pager['groups'][$g]['to'] > $this->last_pager['pages_count']) $this->last_pager['groups'][$g]['to'] = $this->last_pager['pages_count'];
			}
		}
		
		return $this->last_pager;
	}
	
	/**
	 * Ritorna l'ultimo pager generato da {@link getPager}
	 * @return array un array col pager
	*/
	public function getLastPager() {
		return $this->last_pager;
	}
}

