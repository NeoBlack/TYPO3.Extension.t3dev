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

/*
@TODO: Weiter testen mit rel und file
*/

class tx_t3dev_flexformField {
	protected $LANG;
	/**
	 * @var tx_t3dev_flexformModule
	 */
	protected $pObj;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pMod;
	protected $name;
	protected $extkey;
	protected $config;
	protected $configOptions = array(
		'name' 				=> 'text',
		'eval' 				=> 'selectm|date|time|timesec|datetime|year|int|upper|lower|double2|alpha|num|alphanum|alphanum_x|nospace|is_in',
		'size' 				=> 'text',
		'is_in'				=> 'text',
		'max' 				=> 'text',
		'required'			=> 'check',
		'checkbox'			=> 'check',
		'trim'				=> 'check',
		'password'			=> 'check',
		'md5'				=> 'check',
		'unique'			=> 'radio|G|L|',
		'wiz_color'			=> 'check',
		'wiz_link'			=> 'check',
		'wiz_addrec'		=> 'check',
		'wiz_listrec'		=> 'check',
		'wiz_editrec'		=> 'check',
		'cols' 				=> 'text',
		'rows' 				=> 'text',
		'rte'				=> 'select|tt_content|none',
		'check_default'		=> 'check',
		'numberBoxes'		=> 'text',
		'select_items'		=> 'text',
		'select_icons'		=> 'check',
		'maxitems'			=> 'text',
		'relations_mm'		=> 'check',
		'rel_table'			=> 'select|tables',
		'rel_type'			=> 'select|group|select|select_cur|select_root|select_storage',
		'rel_dummyitem'		=> 'check',
		'files_type'		=> 'select|images|webimages|all',
		'files'				=> 'text',
		'max_filesize'		=> 'text',
		'files_selsize'		=> 'text',
		'files_thumbs'		=> 'check',
	);
	
	protected $fieldConfigs = array(
		'input'				=> array('name', 'type_t3dev', 'size', 'max', 'required'),
		'input_advanced'	=> array('name', 'type_t3dev', 'size', 'max', 'is_in', 'required', 'checkbox', 'eval', 'trim', 'password', 'md5', 'unique', 'wiz_color', 'wiz_link'),
		'textarea'			=> array('name', 'type_t3dev', 'cols', 'rows'),
		'textarea_rte'		=> array('name', 'type_t3dev', 'rte'),
		'textarea_nowrap'	=> array('name', 'type_t3dev', 'cols', 'rows'),
		'check'				=> array('name', 'type_t3dev', 'check_default'),
		//'check_multi'		=> array('name', 'type_t3dev', 'numberBoxes'),
		'link'				=> array('name', 'type_t3dev', 'checkbox'),
		'date'				=> array('name', 'type_t3dev'),
		'datetime'			=> array('name', 'type_t3dev'),
		'integer'			=> array('name', 'type_t3dev'),
		'select'			=> array('name', 'type_t3dev', 'select_items', 'maxitems', 'size'),
		'radio'				=> array('name', 'type_t3dev', 'select_items'),
		//'rel'				=> array('name', 'type_t3dev', 'rel_table', 'rel_type', 'rel_dummyitem', 'relations', 'relations_selsize', 'relations_mm', 'wiz_addrec', 'wiz_listrec', 'wiz_editrec'),
		//'files'				=> array('name', 'type_t3dev', 'files_type', 'files', 'max_filesize', 'files_selsize', 'files_thumbs'),
		'none'				=> array('name', 'type_t3dev'),
	);
	
	protected $fieldTypeMapping = array(
		'input'				=> 'input',
		'input_advanced'	=> 'input',
		'textarea'			=> 'text',
		'textarea_rte'		=> 'text',
		'textarea_nowrap'	=> 'text',
		'check'				=> 'check',
		'check_multi'		=> 'check',
		'link'				=> 'input',
		'date'				=> 'input',
		'datetime'			=> 'input',
		'integer'			=> 'input',
		'select'			=> 'select',
		'radio'				=> 'radio',
		'rel'				=> 'group',
		'files'				=> 'group',
		'none'				=> 'none',
		'passthrough'		=> 'passthrough',
	);
	
	protected $evalValues = array(
		'required',
		'trim',
		'password',
		'md5'
	);
	
	public function __construct(&$pObj, &$LANG, $name, $extkey, $config = array()) {
		$this->pObj = $pObj;
		$this->pMod = $this->pObj->getPObj();
		$this->LANG = &$LANG;
		$this->name = (strlen($name)) ? $name : 'a'.substr(md5(time()), 0, 10);
		$this->extkey = $extkey;
		$this->config = $config;
		$this->request = t3lib_div::_GP('ffgen');
		$this->config['TCEforms']['config']['type'] = $this->fieldTypeMapping[$config['TCEforms']['config']['type_t3dev']];

		// parse eval values
		if ($this->config['TCEforms']['config']['eval']) {
			if (is_array($this->config['TCEforms']['config']['eval'])) {
				$parts = $this->config['TCEforms']['config']['eval'];
			} else {
				$parts = t3lib_div::trimExplode(',',$this->config['TCEforms']['config']['eval']);
			}
			$configOptions = array_keys($this->configOptions);
			for ($i=0; $i<count($parts); $i++) {
				if (in_array($parts[$i], $configOptions)) {
					$this->config['TCEforms']['config'][$parts[$i]] = 1;
				}
			}
			$this->config['TCEforms']['config']['eval'] = implode(',', $parts);
		}
		
		// parse wizards
		if (is_array($this->config['TCEforms']['config']['wizards']['link'])) {
			$this->config['TCEforms']['config']['wiz_link'] = 1;
		}
		if (is_array($this->config['TCEforms']['config']['wizards']['color'])) {
			$this->config['TCEforms']['config']['wiz_color'] = 1;
		}
		
		// parse RTE
		debug($this->config, 'RTE');
		if (strlen($this->config['TCEforms']['defaultExtras'])) {
			if ($this->config['TCEforms']['defaultExtras'] == 'richtext[*]') {
				$this->config['TCEforms']['config']['rte'] = 'none';
			} else {
				$this->config['TCEforms']['config']['rte'] = 'tt_content';
			}
		}
		
		// parse select
		if ($this->config['TCEforms']['config']['type_t3dev'] == 'select') {
			if (is_array($this->config['TCEforms']['config']['items'])) {
				$this->config['TCEforms']['config']['select_items'] = count($this->config['TCEforms']['config']['items']);
			}
		}
		
		// parse radio
		if ($this->config['TCEforms']['config']['type_t3dev'] == 'radio') {
			if (is_array($this->config['TCEforms']['config']['items'])) {
				$this->config['TCEforms']['config']['select_items'] = count($this->config['TCEforms']['config']['items']);
			}
		}
		
		// get title
		$this->config['TCEforms']['label'] = 'LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.field_'.$this->name;
	}
	
	public function init() {
	}
	
	public function getFieldOverview() {
		debug($this->config, 'conf');
		$ret = '<div class="t3dev_field_'.$this->config['TCEforms']['config']['type_t3dev'].'">';
		for ($i=0; $i < count($this->fieldConfigs[$this->config['TCEforms']['config']['type_t3dev']]); $i++) {
			if ($this->fieldConfigs[$this->config['TCEforms']['config']['type_t3dev']][$i] == 'type') {
				continue;
			}
			if ($this->fieldConfigs[$this->config['TCEforms']['config']['type_t3dev']][$i] == 'name') {
				$ret .= '
				<label class="label_name">'.$this->LANG->getLL('label_flexform_param_name').'</label>
					'.$this->getEditField('name', $this->name).'			
				';
			} else {
				$ret .= '
				<label class="label_'.$this->fieldConfigs[$this->config['TCEforms']['config']['type_t3dev']][$i].'">'.$this->LANG->getLL('label_flexform_param_'.$this->fieldConfigs[$this->config['TCEforms']['config']['type_t3dev']][$i]).'</label>
				'.$this->getEditField($this->fieldConfigs[$this->config['TCEforms']['config']['type_t3dev']][$i], $this->config['TCEforms']['config'][$this->fieldConfigs[$this->config['TCEforms']['config']['type_t3dev']][$i]]).'			
				';
			}
		}
		$ret .= '</div>';
		return $this->pMod->doc->section($this->name, $ret);
	}
	
	public function getFieldsConfig() {
		return $this->fieldConfigs;
	}
	
	public function getEditField($param, $value) {
		if ($param == 'type_t3dev') {
			$ret = '<select class="field_'.$param.'" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']">';
			foreach ($this->fieldConfigs as $k => $v) {
				$sel = ($k == $value) ? ' selected="selected"' : '';
				$ret .= '<option value="'.$k.'"'.$sel.'>'.$this->LANG->getLL('label_flexform_'.$k).'</option>';
			}
			$ret .= '</select>';
			return $ret;
		}
		if (is_array($this->configOptions[$param])) {
			$ret = '<select class="field_'.$param.'" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']">';
			for ($i=0; $i<count($this->configOptions[$param]); $i++) {
				$sel = ($this->configOptions[$param][$i] == $value) ? ' selected="selected"' : '';
				$ret .= '<option value="'.$this->configOptions[$param][$i].'"'.$sel.'>'.$this->LANG->getLL('label_flexform_'.$param.'_'.$this->configOptions[$param][$i]).'</option>';
			}
			$ret .= '</select>';
			return $ret;
		} else {
			$parts = t3lib_div::trimExplode('|', $this->configOptions[$param]);
			switch ($parts[0]) {
				case 'text' :
					return '<input class="field_'.$param.'" type="text" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="'.$value.'" />';
				break;
				case 'check' :
					if ($value) {
						$checked = ' checked="checked"';
					}
					return '<input class="field_'.$param.'" type="checkbox" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="1" '.$checked.'/>';
				break;
				case 'selectm' :
				case 'select' :
					$values = t3lib_div::trimExplode('|', $this->configOptions[$param]);
					$multi  = ($parts[0] == 'selectm') ? ' size="5" multiple="multiple"' : '';
					$multin = ($parts[0] == 'selectm') ? '[]' : '';
					$ret = '<select class="field_'.$param.'" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']'.$multin.'"'.$multi.'>';
					if ($param == 'rel_table') {
						$ret .= '<option value=""></option>';
						$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLES');
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
							while ($data = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
								$sel = ($data[0] == $value) ? ' selected="selected"' : '';
								$ret .= '<option value="'.$data[0].'"'.$sel.'>'.$data[0].'</option>';
							}
						}
					} else {
						$value = ($parts[0] == 'selectm') ? t3lib_div::trimExplode(',', $value) : $value;
						for ($i = 1; $i<count($values); $i++) {
							if ($parts[0] == 'selectm') {
								$sel = (in_array($values[$i], $value)) ? ' selected="selected"' : '';
							} else {
								$sel = ($values[$i] == $value) ? ' selected="selected"' : '';
							}
							$ret .= '<option value="'.$values[$i].'"'.$sel.'>'.$this->LANG->getLL('label_flexform_'.$param.'_'.$values[$i]).'</option>';
						}
					}
					$ret .= '</select>';
					return $ret;
				break;
				case 'radio' :
					$values = t3lib_div::trimExplode('|', $this->configOptions[$param]);
					for ($i = 1; $i<count($values); $i++) {
						$sel = ($values[$i] == $value) ? ' checked="checked"' : '';
						$ret .= '<input class="field_'.$param.'" type="radio" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="'.$values[$i].'"'.$sel.' /> '.$this->LANG->getLL('label_flexform_'.$param.'_'.$values[$i]) . '<br />';
					}
					return $ret;
				break;
			}
		}
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setType($type) {
		$this->config['TCEforms']['config']['type_t3dev'] = $type;
		$this->config['TCEforms']['config']['type'] = $this->fieldTypeMapping[$type];
	}		
	
	public function asArray() {
		// post process eval values
		$ret = $this->config;
		for ($i=0; $i<count($this->evalValues); $i++) {
			if ($value = $ret['TCEforms']['config'][$this->evalValues[$i]]) {
				if (!t3lib_div::inList($ret['TCEforms']['config']['eval'], $this->evalValues[$i])) {
					$ret['TCEforms']['config']['eval'] = $this->evalValues[$i] . ',' . $ret['TCEforms']['config']['eval'];
				}
				unset($ret['TCEforms']['config'][$this->evalValues[$i]]);
			}
		}
		
		// post process wiz_link
		if ($ret['TCEforms']['config']['wiz_link']) {
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['link'] = array(
				'type' => 'popup',
				'title' => 'Link',
				'icon' => 'link_popup.gif',
				'script' => 'browse_links.php?mode=wizard',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
			);
			unset($ret['TCEforms']['config']['wiz_link']);
		}

		// post process wiz_color
		if ($ret['TCEforms']['config']['wiz_color']) {
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['color'] = array(
				'title' => 'Color:',
				'type' => 'colorbox',
				'dim' => '12x12',
				'tableStyle' => 'border:solid 1px black;',
				'script' => 'wizard_colorpicker.php',
				'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
			);
			unset($ret['TCEforms']['config']['wiz_color']);
		}
		
		// post process for field textarea_rte
		if ($ret['TCEforms']['config']['type_t3dev'] == 'textarea_rte') {
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['RTE'] = array(
				'notNewRecords' => 1,
				'RTEonly' => 1,
				'type' => 'script',
				'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
				'icon' => 'wizard_rte2.gif',
				'script' => 'wizard_rte.php',
			);
			debug($ret, 'RET');
			switch ($ret['TCEforms']['config']['rte']) {
				case 'tt_content':
					$ret['TCEforms']['defaultExtras'] = 'richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts]';
				break;
				case 'none':
					$ret['TCEforms']['defaultExtras'] = 'richtext[*]';
				break;
			}
			unset($ret['TCEforms']['config']['rte']);
		}
	
		// post process for field textarea_nowrap
		if ($ret['TCEforms']['config']['type_t3dev'] == 'textarea_nowrap') {
			$ret['TCEforms']['config']['wrap'] = 'OFF';
		}
	
		// post process for field link
		if ($ret['TCEforms']['config']['type_t3dev'] == 'link') {
			$ret['TCEforms']['config']['size'] = 15;
			$ret['TCEforms']['config']['max'] = 255;
			$ret['TCEforms']['config']['eval'] = 'trim';
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['link'] = array(
				'type' => 'popup',
				'title' => 'Link',
				'icon' => 'link_popup.gif',
				'script' => 'browse_links.php?mode=wizard',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
			);
		}
		
		// post process for field date
		if ($ret['TCEforms']['config']['type_t3dev'] == 'date') {
			$ret['TCEforms']['config']['size'] = 8;
			$ret['TCEforms']['config']['max'] = 20;
			$ret['TCEforms']['config']['eval'] = 'date';
			$ret['TCEforms']['config']['default'] = 0;
			$ret['TCEforms']['config']['checkbox'] = 0;
		}
		
		// post process for field datetime
		if ($ret['TCEforms']['config']['type_t3dev'] == 'datetime') {
			$ret['TCEforms']['config']['size'] = 12;
			$ret['TCEforms']['config']['max'] = 20;
			$ret['TCEforms']['config']['eval'] = 'datetime';
			$ret['TCEforms']['config']['default'] = 0;
			$ret['TCEforms']['config']['checkbox'] = 0;
		}
		
		// post process for field integer
		if ($ret['TCEforms']['config']['type_t3dev'] == 'integer') {
			$ret['TCEforms']['config']['size'] = 4;
			$ret['TCEforms']['config']['max'] = 4;
			$ret['TCEforms']['config']['eval'] = 'int';
			$ret['TCEforms']['config']['default'] = 0;
			$ret['TCEforms']['config']['checkbox'] = 0;
			$ret['TCEforms']['config']['range'] = array(
				'upper' => 1000,
				'lower' => 10
			);
		}

		// post process for field select
		if ($ret['TCEforms']['config']['type_t3dev'] == 'select') {
			$ret['TCEforms']['config']['items'] = array();
			for ($i=0; $i < $ret['TCEforms']['config']['select_items']; $i++) {
				$ret['TCEforms']['config']['items'][] = Array('LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.field_'.$this->name.'.I.'.$i, $i);
			}
			unset($ret['TCEforms']['config']['select_items']);
		}
		
		// post process for field select
		if ($ret['TCEforms']['config']['type_t3dev'] == 'radio') {
			$ret['TCEforms']['config']['items'] = array();
			for ($i=0; $i < $ret['TCEforms']['config']['select_items']; $i++) {
				$ret['TCEforms']['config']['items'][] = Array('LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.field_'.$this->name.'.I.'.$i, $i);
			}
			unset($ret['TCEforms']['config']['select_items']);
		}

		// post process for field rel
		if ($ret['TCEforms']['config']['type_t3dev'] == 'rel') {
			$ret['TCEforms']['config']['internal_type'] = 'db';
			$ret['TCEforms']['config']['allowed'] = $ret['TCEforms']['config']['rel_table'];
			unset($ret['TCEforms']['config']['rel_table']);
			
		}
		return $ret;
	}
}
?>
