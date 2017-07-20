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

require_once(t3lib_extMgm::extPath('t3dev').'lib/class.tx_t3dev_flexformField.php');

class tx_t3dev_flexform {
	protected $LANG;
	/**
	 * @var tx_t3dev_flexformModule
	 */
	protected $pObj;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pMod;
	protected $extkey;
	protected $filename;
	protected $flexform;
	protected $flexformArray;
	protected $request;

	public function __construct(&$pObj, &$LANG, $extkey) {
		$this->pObj = $pObj;
		$this->pMod = $pObj->getPObj();
		$this->LANG = &$LANG;
		$this->extkey = $extkey;
		$this->request = t3lib_div::_GP('ffgen');
		debug($this->request, '$this->request', '', '', 10);
	}
	
	public function init() {
		$this->flexform = t3lib_div::getURL($this->filename);
		$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure');
		debug($this->flexformArray, 'FlexfromArray', '', '', 10);
		
		if (strlen($this->request['newSheet'])) {
			$this->createNewSheet($this->request['newSheet']);
		}

		if (strlen($this->request['newField'])) {
			$this->createNewField($this->request['newField']);
		}

		if (strlen($this->request['update'])) {
			$this->updateFields($this->request['sheet'], $this->request['sheetData'][$this->request['sheet']]);
		}
	}
	
	public function getPMod() {
		return $this->pMod;
	}
	
	public function getContent() {
		$ret .= $this->getSheetSelector();
		$ret .= $this->getNewSheetField();
		$ret .= $this->getSubmitButton();
		$ret .= $this->pMod->doc->divider(5);
		$ret .= $this->getNewFieldSelector();
		$ret .= $this->pMod->doc->divider(5);
		$ret .= $this->getFieldsForCurrentSheet();
		
		return $this->pMod->doc->section($this->LANG->getLL('label_ffgen'), $ret);
	}

	public function setFilename($filename) {
		$this->filename = $filename;
	}
	
	protected function createNewSheet($sheet) {
		$newSheet = trim($sheet);
		$this->flexformArray['sheets'][$newSheet] = array(
			'ROOT' => array(
				'TCEforms' => array(
					'sheetTitle' => 'LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.sheet_'.$newSheet
				),
				'type' => 'array',
				'el' => array()
			)
		);
		$this->save();
		$this->flexform = t3lib_div::getURL($this->filename);
		$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure');
	}
	
	protected function createNewField($field) {
		$newField = new tx_t3dev_flexformField($this->pObj, $this->LANG, '', $this->extkey);
		$newField->setType($field);
		if (!is_array($this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'])) {
			$this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'] = array();
		}
		$this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'][$newField->getName()] = $newField->asArray();
		$this->save();
	}
	
	protected function updateFields($sheet, $data) {
		foreach ($data as $k => $v) {
			debug($data[$k]['TCEforms']['config'], 'updateFields');
			$name = $data[$k]['TCEforms']['config']['name'];
			unset($data[$k]['TCEforms']['config']['name']);
			$newField = new tx_t3dev_flexformField($this->pObj, $this->LANG, $name, $this->extkey, $data[$k]);
			unset($this->flexformArray['sheets'][$sheet]['ROOT']['el'][$k]);
			$this->flexformArray['sheets'][$sheet]['ROOT']['el'][$newField->getName()] = $newField->asArray();
		}
		$this->save();
	}
	
	protected function save() {
		$content = t3lib_div::array2xml($this->flexformArray, '', 0, 'T3DataStructure', 1);
		$result = t3lib_div::writeFile($this->filename, $content);
	}
	
	protected function getSheetSelector() {
		if (!$this->pObj->getFromSession('sheet')) {
			$this->pObj->setToSession('sheet', 'sDEF');
		}
		$ret .= '<select name="ffgen[sheet]" onchange="jumpToUrl(\'?ffgen[sheet]=\'+this.options[this.selectedIndex].value,this);">';
		foreach ($this->flexformArray['sheets'] as $k => $v) {
			if (!strlen($this->request['sheet'])) {
				$this->request['sheet'] = $this->pObj->getFromSession('sheet');
			}
			if (!strlen($this->request['sheet'])) {
				$this->request['sheet'] = $k;
			}
			$sel = '';
			if ($this->request['sheet'] == $k) {
				$sel = ' selected="selected"';
				$this->pObj->setToSession('sheet', $this->request['sheet']);
			}
			$ret .= '<option value="'.$k.'"'.$sel.'>'.$k.'</option>';
		}
		$ret .= '</select>';
		return $this->pMod->doc->funcMenu($this->LANG->getLL('label_sheets'), $ret);
	}
	
	protected function getNewSheetField() {
		$ret .= '<input type="text" name="ffgen[newSheet]" value="" />';
		return $this->pMod->doc->funcMenu($this->LANG->getLL('label_new_sheet'), $ret);
	}
		
	protected function getNewFieldSelector() {
		$ret .= '<select name="ffgen[newField]" onchange="jumpToUrl(\'?ffgen[newField]=\'+this.options[this.selectedIndex].value,this);">';
		$ret .= '<option value=""></option>';
		$dummyField = new tx_t3dev_flexformField($this->pObj, $this->LANG, '', $this->extkey);
		$fieldTypes = $dummyField->getFieldsConfig();
		foreach ($fieldTypes as $k => $v) {
			$ret .= '<option value="'.$k.'">'.$this->LANG->getLL('label_flexform_'.$k).'</option>';
		}
		$ret .= '</select>';
		return $this->pMod->doc->funcMenu($this->LANG->getLL('label_new_field'), $ret);
	}
	
	protected function getFieldsForCurrentSheet() {
		$currentFields = $this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'];
		if (is_array($currentFields) && (count($currentFields) > 0)) {
			//$flexformFieldClassname = t3lib_div::makeInstanceClassName('tx_t3dev_flexformField');
			foreach ($currentFields as $k => $v) {
				debug($currentFields, 'getFieldsForCurrentSheet');
				$flexformField = new tx_t3dev_flexformField($this->pObj, $this->LANG, $k, $this->extkey, $currentFields[$k]);
				$flexformField->init();
				$ret .= $flexformField->getFieldOverview();
				$ret .= $this->getUpdateButton();
				$ret .= $this->pMod->doc->divider(2);
			}
			return $ret;
		} else {
			return 'no fields';
		}
	}
	
	protected function getSubmitButton() {
		$ret .= '<input type="submit" name="ffgen[submit]" value="'.$this->LANG->getLL('label_submit').'" />';
		return $this->pMod->doc->funcMenu('', $ret);
	}
	
	protected function getUpdateButton() {
		$ret .= '<input type="submit" name="ffgen[update]" value="'.$this->LANG->getLL('label_update').'" />';
		return $this->pMod->doc->funcMenu('', $ret);
	}
}
?>
