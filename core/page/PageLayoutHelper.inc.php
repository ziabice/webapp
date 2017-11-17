<?php
/**
 * (c) 2012-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
 
/**
 *
 * Stampa il testo del tag indicato.
 *
 * Il testo viene preso dal buffer di output dell'azione attuale.
 *
 * @param string $tag nome del tag
 */
function put_text($tag) {
	WebApp::getInstance()->getPageLayout()->putText($tag);
}

/**
 * Stampa il testo del corpo pagina.
 *
 * Il testo viene preso dal buffer di output principale dell'azione attuale.
 *
 */
function put_page_body() {
	WebApp::getInstance()->getPageLayout()->putText( WebApp::getInstance()->getPageLayout()->getBodyTag() );
}

/**
 * Stampa il testo dell'encoding di pagina.
 *
 * Viene usato il page layout (istanza di PageLayout) attuale.
 *
 * @see PageLayout::getXHTMLEncoding
 */
function put_page_encoding() {
    echo WebApp::getInstance()->getPageLayout()->getXHTMLEncondig();
}

/**
 * Stampa il titolo di pagina.
 *
 * Viene usato il page layout (istanza di PageLayout) attuale.
 *
 * @see PageLayout::getPageTitle
 */
function put_page_title() {
    echo WebApp::getInstance()->getPageLayout()->getPageTitle();
}

/**
 * Verifica se ci sia del testo nel tag indicato.
 *
 * @param string $tag il tag da verificare
 * @return boolean TRUE se c'è del testo, FALSE altrimenti
 */
function has_text($tag) {
	return WebApp::getInstance()->getPageLayout()->hasText($tag);
}

/**
 * Verifica se ci sia del testo nel corpo della pagina.
 *
 * @return boolean TRUE se c'è del testo, FALSE altrimenti
 */
function has_body_text() {
	return WebApp::getInstance()->getPageLayout()->hasText( WebApp::getInstance()->getPageLayout()->getBodyTag() );
}

/**
 * Include un file di template.
 * 
 * Il file viene cercato nei seguenti percorsi:
 * 
 * 	<modulocorrente>/view/NOMEFILE
 *  <applicazione_corrente>/view/NOMEFILE
 * 
 * @param string $filename nome del file da includere
 * @return boolean TRUE se il file è stato trovato e incluso, FALSE altrimenti
 */
function include_template($filename) {
	return WebApp::getInstance()->getPageLayout()->includeFile($filename);
}
