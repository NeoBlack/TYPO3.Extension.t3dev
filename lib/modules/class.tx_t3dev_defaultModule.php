<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Frank Nägler <typo3@naegler.net>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(t3lib_extMgm::extPath('t3dev').'lib/interfaces/class.tx_t3dev_moduleInterface.php');

class tx_t3dev_defaultModule implements tx_t3dev_moduleInterface {
	protected $LANG;
	protected $pObj;
	protected $moduleId = 'defaultModule';
	
	public function __construct(&$pObj, &$LANG) {
		$this->pObj = $pObj;
		$this->LANG = &$LANG;
	}
	
	public function getTitle() {
		return $this->LANG->getLL($this->moduleId.'Title');
	}
	
	public function getContent() {
		return $this->LANG->getLL($this->moduleId.'Description');
	}
}
?>
