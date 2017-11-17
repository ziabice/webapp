<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un gestore di sessione
 *
 * Permette di mascherare il gestore di sessione in un oggetto. Tutti gli elementi
 * della sessione cadono sotto un dominio ("realm"), che permette di circoscrivere
 * i valori impostati nella sessione. Le operazioni di impostazione/rimozione dei valori
 * avverranno sempre nell'ambito del dominio dichiarato.
 * 
 * Il realm di sessione può essere forzato usando: 
 * 
 * <code>
 * WebAppConfig::set('WEBAPP_SESSION_REALM', 'nome del realm');
 * </code>
 * 
 * Nel file config.inc.php dell'applicazione.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
abstract class BaseSessionManager {
	
	protected
		$realm; // stringa col nome del realm della sessione
	
	/**
	 * Costruisce il gestore di sessione: una sessione è circoscritta ad un determinato
	 * ambito (realm): all'infuori di tale ambito le variabili non sono accessibili
	 * @param string $realm stringa col realm
	*/
	public function __construct($realm) {
		$this->realm = $realm;
	}
	
	/**
	 * Imposta un elemento alla sessione, specificandone il campo
	 * di visibilità.
	 * 
	 * Aggiunge un elemento se non esistente, lo reimposta altrimenti.
	 *
	 * Il campo di visibilità indica una serie di destinazioni, anche relative 
	 * ad applicazioni diverse, purché condividano lo stesso realm di sessione.
	 * 
	 * La stringa di visibilità ha la sintassi:
	 * 
	 * [applicazione:]modulo[/azione]
	 * 
	 * Esempi di visibilità:
	 * 
	 * foo: - tutta l'applicazione 'foo'
	 * foo:bar - applicazione 'foo', tutto il modulo 'bar'
	 * bar -  il modulo 'bar' nell'applicazione corrente (tutte le azioni)
	 * bar/ - l'azione principale del modulo 'bar'
	 * bar/baz - modulo 'bar' e azione 'baz'
	 * 
	 * Il front controller ripulisce la sessione dagli
	 * elementi non visibili nell'ambito corrente usando {@link clearVolatile}. 
	 *
	 * Per comporre una stringa di visibilità di può utilizzare {@link visibility}
	 *
	 * Se si inserisce un oggetto nella sessione, ricordarsi di dichiararne
	 * completamente la classe prima di inizializzare il gestore di sessione: a tale
	 * scopo il front controller permette il precaricamento degli oggetti nel metodo
	 * {@link WebApp::loadSessionHooks} invocato prima di inizializzare la sessione.
	 *
	 * @param string $name nome della variabile da impostare
	 * @param mixed $value valore della variabile
	 * @param array $visibility FALSE non imposta la visibilità (è visibile in ogni modulo), altrimenti un array di stringhe
	 * @see visibility
	 * @see clearVolatile
	 * @see WebApp::initializeSessionManager
	 * @see WebApp::loadSessionHooks
	 */
	abstract public function set($name, $value, $visibility = FALSE);

	/**
	 * Rimuove la variabile dalla sessione
	 * @param strng $name nome della variabile da rimuovere
	 */
	abstract public function del($name);

	/**
	 * Ritorna il valore della variabile dalla sessione.
	 *
	 * Non verifica se la variabile esista: questa operazione va fatta usando {@link has}
	 * @param string $name nome della variabile
	 * @see has
	 */
	abstract public function get($name);

	/**
	 * Informa se una variabile sia registrata in sessione
	 *
	 * @param string $name nome della variabile da verificare
	 * @return boolean TRUE se la variabile esiste in sessione, FALSE altrimenti
	 *
	 */
	abstract public function has($name);
	
	/**
	 * Ritorna un array con tutti i nomi di variabile
	 * definiti nella sessione
	 *
	 * @return array un array di stringhe
	 */
	abstract public function getAllNames();
	
	/**
	 * Ritorna la visibilità di un oggetto all'interno della sessione, ossia
	 * i moduli in cui un elemento salvato in sessione può esistere.
	 * 
	 * @param string $name nome della variabile
	 * @return boolean FALSE se l'oggetto non esiste o non ha una visibilità impostata, altrimenti un array con
	 * i nomi dei moduli in cui può esistere
	 */
	abstract public function getVisibility($name);
	
	/**
	 * Rimuove tutte le variabili definite nel realm attuale
	*/
	abstract public function clear();

	/**
	 * Ritorna il nome della variabile nella richiesta HTTP che contiene
	 * l'ID di sessione
	 * @return string il nome della sessione
	 */
	abstract public function getSessionName();

	/**
	 * Ritorna l'ID univoco della sessione
	 *
	 * @return string l'ID della sessione
	 */
	abstract public function getSessionID();
	
	/**
	 * Ritorna il realm attuale
	 *
	 * @return string una stringa col realm
	 */
	public function getRealm() {
		return $this->realm;
	}
	
	/**
	 * Salva la sessione in una URL.
	 * 
	 * Salva nella URL una variabile con nome e valori pari a quelli della sessione
	 * e del suo ID.
	 * 
	 * @param URL $url istanza di URL
	 * @see getSessionName
	 * @see getSessionID
	*/
	public function toURL(URL $url) {
		$url->set($this->getSessionName(), $this->getSessionID());
	}
	
	/**
	 * Rimuove i dati di sessione dalla URL.
	 * 
	 * Rimuove (se presente) dalla URL il parametro che indica questa sessione: un parametro
	 * con nome pari a quello restituito da {@link getSessionName}
	 * 
	 * @param URL istanza di URL su cui operare
	 * @see getSessionName
	*/
	public function removeFromURL(URL $url) {
		$url->remove($this->getSessionName());
	}
	
	/**
	 * Aggiunge i dati di sessione ad una form.
	 *
	 * Aggiunge dei campi nascosti nella form con in parametri della sessione: in particolare
	 * viene creato un campo col nome pari a quello restituito da {@link getSessionName}
	 * @param Form $form la form su cui operare
	 * @see getSessionName
	 * @see getSessionID
	*/
	public function toForm(Form $form) {
		$form->getHiddenControls()->set($this->getSessionName(), $this->getSessionID()); 
	}
	
	/**
	 * Rimuove i valori della sessione dalla form.
	 *
	 * @param Form $form la form su cui operare
	 * @see toForm
	*/
	public function removeFromForm(Form $form) {
		$form->getHiddenControls()->del($this->getSessionName());
	}

	/**
	 * Rimuove gli elementi "volatili" dalla sessione.
	 *
	 * Rimuove tutti gli elementi della sessione che abbiano specificato un
	 * ambito di visibilità e che questo ambito non preveda il modulo e l'azione indicati.
	 *
	 * @param string $module stringa col nome del modulo
	 * @param string $action stringa col nome dell'azione o FALSE se non si prevede una azione
	 * @see set
	 * @see visibility
	 */
	public function clearVolatile($module, $action = FALSE) {
		$n = $this->getAllNames();
		$e = $this->visibility($module, $action);
		foreach($n as $name) {
			$visibility = $this->getVisibility($name);
			if ($visibility !== FALSE) {
				$must_delete = TRUE;
				// verifica se sia esplicitata una applicazione
				foreach($visibility as $v) {
					// Estrae l'eventuale applicazione e la stringa effettiva di visibilità
					$dot = strpos($v, ':');
					if ($dot !== FALSE) {
						$app = substr($v, 0, $dot);
						$vis = substr($v, $dot + 1);
					} else {
						$app = WebApp::getInstance()->getName();
						$vis = $v;
					}
					
					// Se è valido nell'applicazione corrente, potrebbe non doverlo rimuovere
					if ($app == WebApp::getInstance()->getName()) {
						// E' valido per tutta l'applicazione, non fare niente
						if (empty($vis)) {
							$must_delete = FALSE;
							break;
						}
						// Verifica se si tratti di un singolo modulo dell'applicazione
						if (strcmp($module, $vis) == 0) {
							$must_delete = FALSE;
							break;
						}
						// Allora tutta la stringa
						if (strcmp($e, $vis) == 0) {
							$must_delete = FALSE;
							break;
						}
					}
				}
				
				if ($must_delete) $this->del($name);
			}
		}
	}
	
	/**
	 * Compone la stringa di visibilità di un oggetto.
	 *
	 * Una stringa di visibilità indica una destinazione dell'applicazione, ossia
	 * un modulo ed una azione.
	 * L'output di questo metodo può essere usato come parametro di visibilità
	 * di {@link set}.
	 *
	 * @param string $module nome del modulo
	 * @param string $action nome dell'azione o FALSE se non si vuole specificare
	 * @param string $application nome dell'applicazione o FALSE se non si vuole specificare
	 */
	public static function visibility($module, $action = FALSE, $application = FALSE) {
		return ($application !== FALSE ? $application.':' : '').strval($module).($action !== FALSE ? '/'.strval($action) : '');
	}

	/**
	 * Cambia realm attuale, eventualmente creandolo
	 *
	 * @param string $realm nome del realm
	 */
	public function switchRealm($realm) {
		$this->realm = strval($realm);
	}
	
	/**
	 * Distrugge la sessione attuale e la reinizializza.
	 *
	 * Di solito non rigenera un nuovo ID di sessione.
	 * @see restartSession
	 */
	abstract public function destroySession();
	
	/**
	 * Reinizializza la sessione, rigenerando anche il session id.
	 *
	 */
	abstract public function restartSession();

	/**
	 * Ritorna un valore dalla sessione.
	 *
	 * Verifica che una chiave esista e ritorna il valore corrispondente.
	 *
	 * @param string $key la chiave col valore da estrarre
	 * @return mixed NULL se il valore non esiste, altrimenti il valore
	 */
	public function getValue($key) {
		if ($this->has($key)) return $this->get ($key);
		else return NULL;
	}
}
