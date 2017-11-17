<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un resultset generato dal db PostgreSQL
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class PgSQLResultSet extends ResultSet {

	private
		$my_fetchmode = NULL;

	public function setFetchMode($mode) {
		$this->my_fetchmode = PGSQL_ASSOC;

		if ($mode == self::DB_ASSOC) $this->my_fetchmode = PGSQL_ASSOC;
		elseif ($mode == self::DB_NUM) $this->my_fetchmode = PGSQL_NUM;
		elseif ($mode == self::DB_BOTH) $this->my_fetchmode = PGSQL_BOTH;
		parent::setFetchMode($mode);
	}

	public function initialize() {
		$this->affected_rows = @pg_affected_rows($this->query_id);
		$this->record_count = @pg_num_rows($this->query_id);
	}


	public function fetchRow($row = NULL) {
		if (($r = @pgsql_fetch_array($this->query_id, $row, $this->my_fetchmode)) !== FALSE) {
			return array_map(array($this, 'my_stripslashes'), $r);
		} else {
			return FALSE;
		}
	}

	public function fetchAll() {
		$data = array();
		while (($r = @pgsql_fetch_array($this->query_id, NULL, $this->my_fetchmode)) !== FALSE) {
			$data[] = array_map(array($this, 'my_stripslashes'), $r);
		}
		return $data;
	}

	private function my_stripslashes($v) {
		return (is_null($v) ? NULL : stripslashes($v));
	}

	public function free() {
		$this->query_id = NULL;
		@pg_free_result($this->getResultResource());
	}
}
