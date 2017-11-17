<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un controller per pager che ignora la paginazione e prende
 * tutti gli elementi.
 *
 * Le destinazioni a cui punta il pager sono vuote, tenerne conto
 * quando si realizza il view.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class CatchAllPagerController extends PagerController {
    public function __construct() {
		parent::__construct('', '', -1, 1);
	}
	
	protected function getPageFromHTTP() {
		return FALSE;
	}

	public function getPageDestination($page) {
		return '';
	}

	protected function updatePager($total_count) {
		$this->getPager()->setTotalCount( $total_count );
		$this->getPager()->setPageSize($total_count);
	}

	/*
	protected function readModelData() {
		$this->setModelData( $this->getModel()->read(Criteria::newCatchAll($this->getModel()))  );
	}*/
}

