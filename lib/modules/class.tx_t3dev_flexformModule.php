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
require_once(t3lib_extMgm::extPath('t3dev').'lib/modules/class.tx_t3dev_basicsModule.php');
require_once(t3lib_extMgm::extPath('t3dev').'lib/class.tx_t3dev_flexform.php');

class tx_t3dev_flexformModule implements tx_t3dev_moduleInterface {
	protected $LANG;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pObj;
	/**
	 * @var tx_t3dev_basicsModule
	 */
	protected $basics;
	protected $moduleId = 'flexformModule';
	
	public function __construct(&$pObj, &$LANG) {
		$this->pObj = $pObj;
		$this->LANG = &$LANG;
		$basicsModuleClassname = t3lib_div::makeInstanceClassName('tx_t3dev_basicsModule');
		$this->basics = new $basicsModuleClassname($this->pObj, $this->LANG);
		$this->sessionData = $GLOBALS['BE_USER']->getSessionData('tx_t3dev_'.$this->moduleId);
		
		$action = t3lib_div::_GP('createNewFileAction');
		if ($action) {
			$this->createNewFileAction();
		}
	}
	
	public function getTitle() {
		return $this->LANG->getLL($this->moduleId.'Title');
	}
	
	public function getContent() {
		$ret =  $this->LANG->getLL($this->moduleId.'Description');
		$ret .= $this->pObj->doc->divider(5);
		$ret .= $this->getGlobalConfigHeader();
		
		//$ret .= $this->pObj->doc->dfw('test');
		//$ret .= $this->pObj->doc->rfw('red');
		return $ret;
	}
	
	public function getPObj() {
		return $this->pObj;
	}
	
	public function setToSession($key, $value) {
		$this->sessionData[$key] = $value;
		$GLOBALS['BE_USER']->setAndSaveSessionData('tx_t3dev_'.$this->moduleId, $this->sessionData);
	}
	
	public function getFromSession($key) {
		return $this->sessionData[$key];
	}
	
	protected function getGlobalConfigHeader() {
		$ret .= $this->pObj->doc->funcMenu($this->LANG->getLL('label_select_extensiontype'), $this->basics->getSelectForExtensionType());
		$ret .= $this->pObj->doc->funcMenu($this->LANG->getLL('label_select_extension'), $this->basics->getSelectForLocalExtensions());
		$ret .= $this->pObj->doc->funcMenu($this->LANG->getLL('label_select_file'), $this->basics->getSelectForExtensionFiles('xml'));
		$selectedFile = $this->basics->getCurrentFileName();
		if (is_array($selectedFile)) {
			$ret .= $this->pObj->doc->section($this->LANG->getLL('label_selected_file'), $selectedFile[0]);
			$ret .= $this->pObj->doc->divider(5);

			$flexformClassname = t3lib_div::makeInstanceClassName('tx_t3dev_flexform');
			$flexform = new $flexformClassname($this, $this->LANG, $this->basics->getCurrentExtKey());
			$flexform->setFilename($selectedFile[0]);
			$flexform->init();
			$ret .= $flexform->getContent();
		} else {
			$ret .= $this->pObj->doc->section($this->LANG->getLL('label_selected_file'), $selectedFile);
			$ret .= $this->getCreateNewFileBox();
			$ret .= $this->pObj->doc->divider(5);
		}
		return $ret;
	}
	
	protected function getCreateNewFileBox() {
		$content  = $this->basics->getCurrentExtDir() . '...';
		$newFileName = ($this->pObj->MOD_SETTINGS['newFileName']) ? ($this->pObj->MOD_SETTINGS['newFileName']) : 'flexforms/';
		$content .= '<input type="text" size="60" name="SET[newFileName]" value="'.htmlspecialchars($newFileName).'" />';
		$content .= $this->pObj->doc->spacer(5);
		$content .= '<input type="submit" name="createNewFileAction" value="'.htmlspecialchars($this->LANG->getLL('label_create_file')).'" />';
		
		return $this->pObj->doc->section($this->LANG->getLL('label_new_file'), $content);
	}
	
	protected function createNewFileAction() {
		$settings = t3lib_div::_GP('SET');
		$newFileName = $this->basics->getCurrentExtDir() . $settings['newFileName'];
		$isDir = false;
		if (is_dir(dirname($newFileName))) {
			$isDir = true;
		} elseif ((!is_dir(dirname($newFileName)) && (substr($newFileName,0,1) == '/'))) {
			if (t3lib_div::mkdir(dirname($newFileName))) {
				$isDir = true;
			}
		} 
		if ($isDir) {
			$basicFlexform = '<T3DataStructure>
 <meta>
       <langDisable>1</langDisable>
 </meta>
 <sheets>
  <sDEF>
   <ROOT>
    <TCEforms>
     <sheetTitle>LLL:EXT:'.$this->basics->getCurrentExtKey().'/locallang_db.php:tt_content.pi_flexform.sheet_general</sheetTitle>
    </TCEforms>
    <type>array</type>
    <el>
    </el>
   </ROOT>
  </sDEF>
 </sheets>
</T3DataStructure>';
			t3lib_div::writeFile($newFileName, $basicFlexform);
		}
	}
}
?>
