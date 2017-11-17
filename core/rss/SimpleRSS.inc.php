<?php

/**
 * Un semplice generatore di RSS 1.0
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * */
class SimpleRSS {

	protected
		$channel_url,
		$encoding,
		$items = array(),
		$modules = array(),
		$channel_title = '',
		$channel_description = '',
		$channel_image = '',
		$channel_textinput = '';
		
	/**
	 * Costruisce con l'URL del canale.
	 * 
	 * @param string $channel_url l'URL del canale per il quale questo RSS è l'indice
	 * @param string $title una stringa col titolo del canale
	 * @param string $description una descrizione del canale
	 * */
	public function __construct($channel_url, $title, $description) {
		$this->channel_url = $channel_url;
		$this->encoding = $encoding;
		$this->channel_title = $title;
		$this->channel_description = $description;
	}
	
	/**
	 * Imposta il titolo del canale.
	 * @param string $title una stringa col titolo del canale
	 * */
	public function setTitle($title) {
		$this->channel_title = $title;
	}
	
	/**
	 * Ritorna il titolo del canale.
	 * @return string una stringa col titolo.
	 * */
	public function getTitle() {
		return $this->channel_title;
	}
	
	/**
	 * Imposta la descrizione del canale.
	 * @param string $description una descrizione del canale
	 * */
	public function setDescription($description) {
		$this->channel_description = $description;
	}
	
	/**
	 * Ritorna la descrizione del canale.
	 * @return string la descrizione del canale
	 * */
	public function getDescription() {
		return $this->channel_description;
	}
	
	/**
	 * Imposta l'immagine del canale.
	 * 
	 * @param string $image_url l'url dell'immagine
	 * @param string $link l'url a cui rimanda l'immagine
	 * @param string $title il titolo dell'immagine
	 * @param string $description una descrizione dell'immagine
	 * */
	public function setImage($image_url, $link, $title, $description) {
		$this->channel_image = array(
			'title' => $title,
			'description' => $description,
			'link' => $link,
			'url' => $image_url
		);
	}
	
	
	/**
	 * Imposta il campo di testo per il motore di ricerca del canale.
	 * 
	 * @param string $link l'url a cui rimanda il campo di ricerca
	 * @param string $name nome del campo input
	 * @param string $title il titolo 
	 * @param string $description una descrizione 
	 * */
	public function setTextInput($link, $name, $title, $description) {
		$this->channel_textinput = array(
			'link' => $link,
			'name' => $name,
			'title' => $title,
			'description' => $description
		);
	}

	/**
	 * Ritorna il codice XML di apertura del feed.
	 * 
	 * Il codice va preceduto dal tag di apertura xml (una stringa '<?xml ...>')
	 * 
	 * @return string una stringa XML
	 * */
	public function getRootOpen() {
		$xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n";
		$xml .= $this->getChannelModulesXML();
		$xml .= 'xmlns="http://purl.org/rss/1.0/" >'."\n";
		return $xml;
	}
	
	/**
	 * Ritorna il codice XML di chiusura del feed.
	 * 
	 * @return string una stringa XML
	 * */
	public function getRootClose() {
		return "</rdf:RDF>\n";
	}
	
	/**
	 * Ritorna il codice XML per l'immagine associata nel channel (da usare nel tag <channel>).
	 * 
	 * @return string una stringa col codice XML 
	 * @see setImage
	 * */
	public function getChannelImageHeader() {
		if (empty($this->channel_image)) {
			return '';
		} else {
			return '<image rdf:resource="'.htmlspecialchars($this->channel_image['url'], ENT_QUOTES,'UTF-8').'" />'."\n";
		}
	}
	
	/**
	 * Ritorna il codice XML per l'immagine associata nel channel.
	 * 
	 * @return string una stringa col codice XML 
	 * @see setImage
	 * */
	public function getChannelImageXML() {
		if (empty($this->channel_image)) {
			return '';
		} else {
			return '<image rdf:about="'.htmlspecialchars($this->channel_image['url'], ENT_QUOTES,'UTF-8').'" >'."\n".
			"\t<title>".htmlspecialchars($this->channel_image['title'], ENT_QUOTES,'UTF-8')."</title>\n".
			"\t<description>".htmlspecialchars($this->channel_image['description'], ENT_QUOTES,'UTF-8')."</description>\n".
			"\t<link>".htmlspecialchars($this->channel_image['link'], ENT_QUOTES,'UTF-8')."</link>\n".
			"\t<url>".htmlspecialchars($this->channel_image['url'], ENT_QUOTES,'UTF-8')."</url>\n".
			"</image>";
		}
	}
	
	/**
	 * Aggiunge una voce del feed.
	 * 
	 * @param string $link l'url per accedere alla risorsa
	 * @param string $title il titolo 
	 * @param string $description una descrizione 
	 * */
	public function addItem($link, $title, $description) {
		$this->items[] = array(
			'link' => $link,
			'title' => $title,
			'description' => $description
		);
	}
	
	/**
	 * Ritorna il codice XML per gli elementi.
	 * 
	 * Gli elementi vanno aggiunti usando addItem.
	 * 
	 * @return string una stringa col codice XML
	 * @see addItem
	 * */
	public function getItemsXML() {
		$xml = '';
		foreach($this->items as $i) {
			$xml .= "<item rdf:about=\"".htmlspecialchars($i['link'], ENT_QUOTES,'UTF-8')."\">\n";
			$xml .= "\t<title>".htmlspecialchars($i['title'], ENT_QUOTES,'UTF-8')."</title>\n";
			$xml .= "\t<description>".htmlspecialchars($i['description'], ENT_QUOTES,'UTF-8')."</description>\n";
			$xml .= "\t<link>".htmlspecialchars($i['link'], ENT_QUOTES,'UTF-8')."</link>\n";
			$xml .= "</item>\n";
		}
		return $xml;
	}
	
	/**
	 * Ritorna il codice XML per gli elementi, da utilizzare nell'header (il tag <channel>).
	 * 
	 * Gli elementi vanno aggiunti usando addItem.
	 * @return string una stringa col codice XML
	 * @see addItem
	 * */
	public function getItemsHeader() {
		$xml = "<items>\n";
		if (count($this->items) > 0) {
			$xml .= "\t<rdf:Seq>\n";
			foreach($this->items as $i) {
				$xml .= "\t\t<rdf:li resource=\"".htmlspecialchars($i['link'], ENT_QUOTES,'UTF-8')."\" />\n";
			}
			$xml .= "\t</rdf:Seq>\n";
		}
		
		$xml .= "</items>\n";
		
		return $xml;
	}
	
	/**
	 * Ritorna il codice di apertura del canale (il tag <channel>).
	 * @return string il codice XML
	 * */
	public function openChannel() {
		return "<channel rdf:about=\"".htmlspecialchars($this->channel_url, ENT_QUOTES,'UTF-8')."\">\n".
		"\t<title>".htmlspecialchars($this->channel_title, ENT_QUOTES,'UTF-8')."</title>\n".
		"\t<description>".htmlspecialchars($this->channel_description, ENT_QUOTES,'UTF-8')."</description>\n".
		"\t<link>".htmlspecialchars($this->channel_url, ENT_QUOTES,'UTF-8')."</link>\n";
	}
	
	/**
	 * Ritorna il codice XML di chiusura del channel (tag <channel>).
	 * @return string il codice XML
	 * */
	public function closeChannel() {
		return "</channel>\n";
	}
	
	public function getChannelModulesXML() {
		return '';
	}
	
	/**
	 * Ritorna il codice XML per il campo di input, da utilizzare nell'header (il tag <channel>).
	 * 
	 * Il campo di input viene impostato usando setTextInput.
	 * @return string una stringa col codice XML
	 * */
	public function getTextInputHeader() {
		if (empty($this->channel_textinput)) {
			return '';
		} else {
			return "<textinput rdf:resource=\"".htmlspecialchars($this->channel_textinput['link'], ENT_QUOTES,'UTF-8')."\" />\n";
		}
	}
	
	/**
	 * Ritorna il codice XML per il campo di input.
	 * 
	 * Il campo di input viene impostato usando setTextInput.
	 * @return string una stringa col codice XML
	 * */
	public function getTextInputXML() {
		if (empty($this->channel_textinput)) {
			return '';
		} else {
			return "<textinput rdf:about=\"".htmlspecialchars($this->channel_textinput['link'], ENT_QUOTES,'UTF-8')."\">\n".
			"\t<title>".htmlspecialchars($this->channel_textinput['title'], ENT_QUOTES,'UTF-8')."</title>\n".
			"\t<description>".htmlspecialchars($this->channel_textinput['description'], ENT_QUOTES,'UTF-8')."</description>\n".
			"\t<link>".htmlspecialchars($this->channel_textinput['link'], ENT_QUOTES,'UTF-8')."</link>\n".
			"\t<name>".htmlspecialchars($this->channel_textinput['name'], ENT_QUOTES,'UTF-8')."</name>\n".
			"</textinput>\n";
		}
	}
	
	/**
	 * Ritorna l'XML completo  per il feed.
	 * 
	 * @return string la stringa con l'xml
	 * @see getRootOpen
	 * @see getRootClose
	 * @see getChannelHeader
	 * @see getChannelBody
	 * */
	public function getXML() {
		$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$xml .= $this->getRootOpen();
		
		// Intestazione
		$xml .= $this->getChannelHeader();
		
		// Contenuti
		$xml .= $this->getChannelBody();
		
		$xml .= $this->getRootClose();
		return $xml;
	}
	
	/**
	 * Ritorna il codice XML per la testata del feed (tutto il tag <channel>).
	 * 
	 * @return string una stringa col codice XML
	 * */
	public function getChannelHeader() {
		return $this->openChannel().
		$this->getChannelImageHeader().
		$this->getItemsHeader().
		$this->getTextInputHeader().
		$this->closeChannel();
	}
	
	/**
	 * Ritorna il codice XML per il corpo del feed.
	 * 
	 * @return string una stringa col codice XML
	 * */
	public function getChannelBody() {
		return $this->getChannelImageXML().
		$this->getItemsXML().
		$this->getTextInputXML();
	}
	
	/**
	 * Ritorna il tag XHTML per includere un feed RSS generato da questa classe.
	 * 
	 * Il codice va usato nel tag <head> di una pagina XHTML.
	 * 
	 * @param string $feed_url stringa con la URL per raggiungere il generatore del feed
	 * @param string $title stringa col titolo del feed
	 * @return string il codice XHTML
	 * */
	public static function getMetaRSSTag($feed_ur, $title = "RSS 1.0") {
		return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".$title."\" href=\"".htmlspecialchars($feed_url, ENT_QUOTES,'UTF-8')."\" />\n";
	}
}
