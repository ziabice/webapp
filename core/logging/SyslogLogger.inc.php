<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Un logger basato sullo standard syslog
 *
 * @author Luga Gambetta <l.gambetta@bluenine.it>
 */
class SyslogLogger implements LoggerInterface {

	/**
	 * Inizializa il logger.
	 *
	 * Occorre indicare una facility, mediante una delle costanti (dal manuale di openlog):
	 * LOG_AUTH  	 security/authorization messages (use LOG_AUTHPRIV instead in systems where that constant is defined)
	 * LOG_AUTHPRIV 	security/authorization messages (private)
	 * LOG_CRON 	clock daemon (cron and at)
	 * LOG_DAEMON 	other system daemons
	 * LOG_KERN 	kernel messages
	 * LOG_LOCAL0 ... LOG_LOCAL7 	reserved for local use, these are not available in Windows
	 * LOG_LPR 	line printer subsystem
	 * LOG_MAIL 	mail subsystem
	 * LOG_NEWS 	USENET news subsystem
	 * LOG_SYSLOG 	messages generated internally by syslogd
	 * LOG_USER 	generic user-level messages
	 * LOG_UUCP 	UUCP subsystem
	 *
	 * Note: LOG_USER is the only valid log type under Windows operating systems
	 *
	 * @param string $application_tag nome dell'applicazione che genera i messaggi
	 * @param integer $facility applicazione che genera i messaggi
	 */
	public function  __construct($application_tag = 'webapp', $facility = LOG_USER) {
		openlog($application_tag, LOG_ODELAY, $facility);
	}

	public function log($message, $level, $level_desc) {
		switch($level) {
			case Logger::LOG_ALERT: $priority = LOG_ALERT; break;
			case Logger::LOG_ERROR: $priority = LOG_ERR; break;
			case Logger::LOG_WARNING: $priority = LOG_WARNING; break;
			case Logger::LOG_NOTICE: $priority = LOG_NOTICE; break;
			case Logger::LOG_INFO: $priority = LOG_INFO; break;
			case Logger::LOG_DEBUG: $priority = LOG_DEBUG; break;

		}
		syslog($priority, (string)$message);
	}

	public function shutdown() {
		closelog();
	}
}
