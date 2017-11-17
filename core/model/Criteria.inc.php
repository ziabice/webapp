<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Astrazione dei criteri di selezione di elementi del database.
 * 
 * Un elemento è indicato attraverso la sua chiave primaria
 * 
 * Un Criteria viene interpretato da un oggetto Model, che ne ricava
 * le informazioni per eseguire le operaioni (di solito query SQL).
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class Criteria extends ObjectConnectAdapter {
	const
		// Modo di funzionamento
		MODE_NONE = 0, // Non opera
		MODE_ALL = 1, // Opera su tutti gli elementi
		MODE_PK = 2, // Opera sul singolo elemento indicato dalla chiave primaria
		MODE_PKS = 3, // Opera sugli elementi indicati dalle chiavi primarie
		MODE_CUSTOM = 10, // Opera in base alle proprietà impostate

		// Criteri di ordinamento
		ORDER_ASC = 'asc', // Ordinamento ascendente
		ORDER_DESC = 'desc'; // Ordinamento discendente
		
	public
		/**
		 * @var HashMap contiene i criteri di selezione
		 */
		$criteria;

	protected
		$custom_mode, // modo di selezione dei dati per criteri Custom
		$limit_start,
		$limit_count,
		$order_by,
		$mode,
		$pk,
		$model; // istanza di Model da utilizzare
	
	/**
	 * Costruisce un oggetto con dei valori di defualt:
	 * 
	 * Modo di funzionamento: self::MODE_ALL
	 * Nessuna chiave primaria
	 * Nessun ordinamento
	 * Nessun limite di estrazione
	*/
	public function __construct() {
		$this->model = NULL;
		$this->order_by = array();
		$this->criteria = new HashMap();
		$this->criteria->connect($this);
		$this->setMode(self::MODE_ALL);
		$this->setLimit(NULL, NULL);
	}
	
	/**
	 * Rimuove tutti i criteri impostati.
	 * 
	 * Azzera i limiti di selezione, rimuove le clausole di ordinamento,
	 * rimuove tutte le chiavi di selezione definite.
	 * 
	 * Imposta la modalità di selezione su MODE_NONE.
	 * 
	 * Non rimuove l'associazione col Model.
	 */
	public function reset() {
		$this->criteria->clear();
		$this->clearOrderBy();
		$this->clearLimit();
		$this->setMode(self::MODE_NONE);
	}
	
	/**
	 * Ritorna l'HashMap con le associazioni interne.
	 * @return HashMap una istanza di HashMap
	*/
	public function getCriteria() {
		return $this->criteria;
	}
	
	/**
	 * Imposta una etichetta dei criteri con un valore.
	 * 
	 * Un proxy per getCriteria()::set()
	 * 
	 * @param string $label etichetta di testo
	 * @param mixed $value valore da impostare per l'etichetta
	*/
	public function set($label, $value) {
		$this->criteria->set($label, $value);
	}
	
	/**
	 * Legge una etichetta dei criteri con un valore, se presente.
	 * Se l'etichetta non è presente ritorna NULL.
	 * Un proxy per getCriteria()::get()
	 *
	 * @param string $label etichetta di testo
	 * @return mixed NULL o il valore impostato per l'etichetta
	*/
	public function get($label) {
		return $this->criteria->getValue($label);
	}
	
	/**
	 * Imposta una stringa (un tag) che permette di meglio esplicitare
	 * il modo di selezione di un criterio di tipo MODE_CUSTOM
	 * @param string $mode stringa col tag
	*/
	public function setCustomMode($mode) {
		$this->custom_mode = strval($mode);
	}
	
	/**
	 * Ritorna il tag impostato con un modo operativo MODE_CUSTOM
	 * @return string stringa col tag del modo operativo
	*/
	public function getCustomMode() {
		return $this->custom_mode;
	}
	
	/**
	 * Imposta il modo di interagire coi dati.
	 * 
	 * Il modo è una delle costanti interne:
	 * 
	 * MODE_ALL - Opera su tutti gli elementi
	 * MODE_PK - Opera sul singolo elemento indicato dalla chiave primaria
	 * MODE_PKS - Opera sugli elementi indicati dalle chiavi primarie
	 * MODE_CUSTOM - Opera in base alle proprietà impostate
	 * 
	 * Quando il modo è MODE_CUSTOM, è possibile specificare ulteriormente l'operazione
	 * usando {@link setCustomMode}. Ciò perché un Criteria può prevedere modi personalizzati
	 * multipli di funzionamento.
	 * 
	 * @param integer $mode modo di funzionamento
	*/
	public function setMode($mode) {
		$this->mode = $mode;
	}
	
	/**
	 * Informa sul modo corrente di selezione degli elementi
	 * @param integer intero col valore di una delle costanti MODE_*
	*/
	public function getMode() {
		return $this->mode;
	}
	
	/**
	 * Imposta un limite di selezione degli elementi, dove il sottostante
	 * storage lo preveda
	 * 
	 * @param integer $startrecord intero con l'elemento da cui cominciare la selezione, NULL se non si vogliono limiti
	 * @param integer $record_count intero col numero di record da selezionare, se NULL viene ignorato
	*/
	public function setLimit($startrecord, $record_count = NULL) {
		$this->limit_start = $startrecord;
		$this->limit_count = $record_count;
	}
	
	/**
	 * Azzera (rimuovendoli) i limiti di selezione degli elementi.
	 * 
	 */
	public function clearLimit() {
		$this->limit_count = NULL;
		$this->limit_start = NULL;
	}
	
	/**
	 * Informa se questo criterio abbia dei limiti di selezione
	 * @return boolean TRUE se ha limiti, FALSE altrimenti
	*/
	public function hasLimit() {
		return !is_null($this->limit_start) || !is_null($this->limit_count);
	}
	
	/**
	 * Informa se ci sia un limite al numero di record da estrarre
	 * @return boolean TRUE se c'è un limite al numero di record da estrarre
	*/
	public function hasRecordCount() {
		return !is_null($this->limit_count);
	}
	
	/**
	 * Informa se ci sia un limite sul record iniziale
	 * @return boolean TRUE se c'è un limite sul record iniziale da estrarre
	*/
	public function hasStartRecord() {
		return !is_null($this->limit_start);
	}
	
	/**
	 * Ritorna i limiti di selezione impostati per questo oggetto
	 * @return array un array nella forma: array( start_record, record_count )
	*/
	public function getLimit() {
		return array($this->limit_start, $this->limit_count);
	}
	
	/**
	 * Ritorna il record iniziale (valido nelle selezioni con limite)
	 * @return integer un intero o NULL
	*/
	public function getStartRecord() {
		return $this->limit_start;
	}
	
	/**
	 * Ritorna il numero di elementi da estrarre
	 * @return integer un intero o NULL
	*/
	public function getRecordCount() {
		return $this->limit_count;
	}
	
	/**
	 * Verifica se una chiave primaria sia formalmente valida, usando il Model associato.
	 * 
	 * Per compiere l'operazione usa {@link Model::checkPrimaryKey}, ma solo se presente
	 * un model associato. Altrimenti verifica solamente che la chiave sia un intero.
	 *
	 * @return boolean TRUE se la chiave primaria è valida, FALSE altrimenti
	*/
	public function checkPK($pk) {
		return (is_object($this->model) ? $this->model->checkPrimaryKey($pk) : is_numeric($pk));
	}
	
	/**
	 * Ritorna la o le chiavi primarie salvate nell'oggetto
	 * @return mixed con la chiave o array di mixed con le chiavi primarie
	*/
	public function getPK() {
		return $this->pk;
	}
	
	/**
	 * Imposta la chiave primaria per la selezione.
	 * 
	 * Setta inoltre il modo di funzionamento a MODE_PK.
	 * Una chiave primaria di solito è un intero, ma può essere qualsiasi cosa: in ogni caso 
	 * essa viene verificata invocando {@link checkPK}
	 *
	 * @param $pk mixed che può indicare una chiave primaria
	 * @throws Exception se la chiave primaria non è valida
	*/
	public function setPK($pk) {
		if ($this->checkPK($pk)) {
			$this->pk = $pk;
			$this->setMode(self::MODE_PK);
		} else {
			throw new Exception("Criteria::setPK: invalid primary key");
		}
	}
	
	/**
	 * Imposta le chiavi primarie per la selezione multipla.
	 * 
	 * Setta inoltre il modo di funzionamento a MODE_PKS.
	 * 
	 * Le singole chiavi primarie vengono verificate usando {@link checkPK}
	 * 
	 * @param $pk array di mixed che indicano le chiavi primarie
	 * @throws Exception se il parametro passato non è un array o una
	 * delle chiavi primarie non è utilizzabile
	*/
	public function setPKS($pk) {
		if (is_array($pk)) {
			foreach($pk as $k) {
				if ($this->checkPK($k) == FALSE) {
					throw new Exception("Criteria::setPKS: invalid primary key");
				}
			}
			$this->pk = $pk;
			$this->setMode(self::MODE_PKS);
		} else {
			throw new Exception('Criteria::setPKS: supplied parameter is not an array.');
		}
	}
	
	/**
	 * Imposta l'istanza di Model associata
	 * 
	 * @param Model $model instanza di Model
	 * @return Model l'istanza di Model
	*/
	public function setModel(Model $model) {
		$this->model = $model;
		return $model;
	}
	
	/**
	 * Ritorna l'istanza di Model associata
	 * @return Model istanza di Model
	*/
	public function getModel() {
		return $this->model;
	}
	
	/**
	 * Verifica se abbia un Model associato
	 * @return boolean TRUE se ha una istanza di Model associata, FALSE altrimenti
	*/
	public function hasModel() {
		return is_obejct($this->model);
	}
	
	/**
	 * Crea un nuovo Criteria per la selezione di un oggetto in base alla sua
	 * chiave primaria.
	 * 
	 * @param Model $model istanza di Model a cui fa riferimento
	 * @param mixed $primary_key chiave primaria da utilizzare per la selezione
	 * @return Criteria una istanza di Criteria
	*/
	public static function newWithPK(Model $model, $primary_key) {
		$c = new Criteria();
		$c->setModel($model);
		$c->setPK($primary_key);
		return $c;
	}
	
	/**
	 * Crea un nuovo Criteria per la selezione di più oggetto in base
	 * alla loro chiave primaria.
	 * 
	 * @param Model $model istanza di Model a cui fa riferimento
	 * @param mixed $primary_keys array di chiavi primarie da utilizzare per la selezione
	 * @return Criteria una istanza di Criteria
	*/
	public static function newWithPKS(Model $model, $primary_keys) {
		$c = new Criteria();
		$c->setModel($model);
		$c->setPKS($primary_keys);
		return $c;
	}
	
	/**
	 * Crea un nuovo oggetto per la selezione di più oggetto in base
	 * al record iniziale ed al numero di elementi
	 * 
	 * @param Model $model istanza di Model a cui fa riferimento
	 * @param integer $start_record record iniziale da cui selezionare o NULL l'inizio
	 * @param integer $record_count numero di record da estrarre o NULL per tutti i record
	 * @return Criteria una istanza di Criteria
	*/
	public static function newWithLimit(Model $model, $start_record, $record_count = NULL) {
		$c = new Criteria();
		$c->setModel($model);
		$c->setMode(self::MODE_ALL);
		$c->setLimit($start_record, $record_count);
		return $c;
	}
	
	/**
	 * Ritorna una nuova istanza di Criteria impostata per una
	 * selezione degli elementi personalizzata
	 * 
	 * @param Model $model istanza di Model a cui fa riferimento
	 * @param string $mode tag con cui marcare il modo operativo
	 * @return Criteria una istanza di Criteria
	*/
	public static function newCustom(Model $model, $mode = '') {
		$c = new Criteria();
		$c->setModel($model);
		$c->setMode( self::MODE_CUSTOM );
		$c->setCustomMode( $mode );
		return $c;
	}
	
	/**
	 * Ritorna una nuova istanza di Criteria con modalità MODE_ALL.
	 * 
	 * @param Model $model istanza di Model a cui fa riferimento
	 * @return Criteria istanza di Criteria
	*/
	public static function newCatchAll(Model $model) {
		$c = new Criteria();
		$c->setModel($model);
		$c->setMode(self::MODE_ALL);
		return $c;
	}
	
	/**
	 * Ritorna semplicemente una nuova istanza
	 * @return Criteria l'istanza di Criteria
	*/
	public static function newInstance() {
		return new Criteria();
	}
	
	/**
	 * Informa se sia un criterio di tipo "prendi tutti"
	 *
	 * @return boolean TRUE se è un criterio "prendi tutti", FALSE altrimenti
	*/
	public function isCatchAll() {
		return $this->mode == self::MODE_ALL;
	}
	
	/**
	 * Informa se sia un criterio di tipo "chiave singola"
	 * @return boolean TRUE se è un criterio "chiave singola", FALSE altrimenti
	*/
	public function isSinglePK() {
		return $this->mode == self::MODE_PK;
	}
	
	/**
	 * Informa se sia un criterio di tipo "chiave multipla"
	 * @return boolean TRUE se è un criterio "chiave multipla", FALSE altrimenti
	*/
	public function isMultiplePKS() {
		return $this->mode == self::MODE_PKS;
	}
	
	/**
	 * Informa se sia un criterio di tipo personalizzato
	 * @return boolean TRUE se è un criterio personalizzato, FALSE altrimenti
	*/
	public function isCustom() {
		return $this->mode == self::MODE_CUSTOM;
	}
	
	// ------------- Gestione dell'ordinamento dei record
	
	/**
	 * Aggiunge un criterio di ordinamento.
	 * 
	 * I criteri di ordinamento verranno poi applicati nell'ordine in cui
	 * sono stati definiti.
	 * 
	 * Per il tipo di ordinamento:
	 * ORDER_ASC - ordine ascendente
	 * ORDER_DESC - ordine discendente
	 * 
	 * @param string $field_key chiave da ordinare
	 * @param integer $order tipo di ordinamento
	 */
	public function addOrderBy($field_key, $order = self::ORDER_ASC) {
		$this->order_by[] = array($field_key, $order);
	}
	
	/**
	 * Imposta il criterio di ordinamento per un a certa chiave (che indica
	 * un campo per il quale ordinare).
	 * 
	 * @param string $field_key nome della chiave
	 * @param string $order tipo di ordinamento (una delle costanti ORDER_*)
	*/
	public function setOrderBy($field_key, $order) {
		$f = FALSE;
		foreach($this->order_by as $k => $v) {
			if ($v[0] == $field_key) {
				$this->order_by[$k] = $order;
				$f = TRUE;
				break;
			}
		}
		if (!$f) $this->addOrderBy($field_key, $order);
	}
	
	/**
	 * Rimuove l'ordinamento impostato per quel campo
	 * @param string $field_key nome del campo
	*/
	public function delOrderBy($field_key) {
		foreach($this->order_by as $k => $v) {
			if ($v[0] == $field_key) {
				unset($this->order_by[$k]);
				break;
			}
		}
	}
	
	/**
	 * Ritorna le clausole di ordinamento definite
	 *
	 * Ritorna un array in cui la chiave è il nome del campo, mentre
	 * il valore una delle costanti ORDER_* per l'ordinamento
	 *
	 * @return array un array di associazioni campo-tipo di ordinamento
	*/
	public function getOrderBy() {
		return $this->order_by;
	}

	/**
	 * Rimuove tutte le clausole di ordinamento definite
	 */
	public function clearOrderBy() {
		$this->order_by = array();
	}
}
