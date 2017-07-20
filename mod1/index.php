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


// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

require_once(t3lib_extMgm::extPath('fn_lib').'lib/class.tx_fnlib_hookHandler.php');

require_once(t3lib_extMgm::extPath('t3dev').'lib/modules/class.tx_t3dev_basicsModule.php');
require_once(t3lib_extMgm::extPath('t3dev').'lib/modules/class.tx_t3dev_defaultModule.php');
require_once(t3lib_extMgm::extPath('t3dev').'lib/modules/class.tx_t3dev_flexformModule.php');

$LANG->includeLLFile('EXT:t3dev/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);    // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]



/**
 * Module 'T3dev' for the 't3dev' extension.
 *
 * @author     <>
 * @package    TYPO3
 * @subpackage    tx_t3dev
 */
class  tx_t3dev_module1 extends t3lib_SCbase {
	var $pageinfo;
	var $modules;
	var $localExtensionDir = 'typo3conf/ext/';
	var $globalExtensionDir = 'typo3/ext/';
	var $systemExtensionDir = 'typo3/sysext/';
	var $extensionDir = '';

	/**
	 * Initializes the Module
	 * @return    void
	 */
	function init()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$this->modules = array(
			0	=> 'tx_t3dev_defaultModule',
			1	=> 'tx_t3dev_flexformModule',
		);

		parent::init();

		/*
		 if (t3lib_div::_GP('clear_all_cache'))    {
		 $this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		 }
		 */
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return    void
	 */
	function menuConfig()    {
		global $LANG;
		$this->MOD_MENU = array(
			'extScope' => array(
				'L' => $LANG->getLL('ext_local'),
				'G' => $LANG->getLL('ext_global'),
				'S' => $LANG->getLL('ext_system'),
			),
			'extSel' => '',
			'file' => '',
			'tuneXHTML' => '',
			'tuneQuotes' => '',
			'tuneBeautify' => '',
		);
		
		$hookObjectClassname = t3lib_div::makeInstanceClassName('tx_fnlib_hookHandler');
		$this->hookHandler = new $hookObjectClassname('t3dev/mod1/index.php');
		
		// call hookObject->includeModule();
		$modulesToInclude = array();
		$this->hookHandler->call('includeModule', $modulesToInclude, $this);
		
		if (count($modulesToInclude)) {
			for ($i=0; $i<count($modulesToInclude); $i++) {
				debug($modulesToInclude[$i]);
				require_once(t3lib_div::getFileAbsFileName($modulesToInclude[$i]));
			}
		}
		
		// call hookObject->addModule(&$modules, &$pObj);
		$this->hookHandler->call('addModule', $this->modules, $this);
		
		foreach ($this->modules as $k => $v) {
			$moduleClassname = $this->modules[$k];
			$module = new $moduleClassname($this, $LANG);
			$this->MOD_MENU['function'][$k] = $module->getTitle();
		}
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return    [type]        ...
	 */
	function main()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))    {

			switch($this->MOD_SETTINGS['extScope']) {
				case 'G':
					$this->extensionDir = $this->globalExtensionDir;
					break;
				case 'S':
					$this->extensionDir = $this->systemExtensionDir;
					break;
				default:
				case 'L':
					$this->extensionDir = $this->localExtensionDir;
				break;
			}
				
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

			// JavaScript
			$this->doc->JScode = '
                            <script language="javascript" type="text/javascript">
                                script_ended = 0;
                                function jumpToUrl(URL)    {
                                    document.location = URL;
                                }
                            </script>
                        ';
			$this->doc->postCode='
                            <script language="javascript" type="text/javascript">
                                script_ended = 1;
                                if (top.fsMod) top.fsMod.recentIds["web"] = 0;
                            </script>
                        ';

			$this->doc->inDocStylesArray[] = t3lib_div::getURL(t3lib_extMgm::extPath('t3dev'). 'mod1/main.css');
			
			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section(
				'',
				$this->doc->funcMenu(
					'',
					t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])
				)
			);
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())    {
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return    void
	 */
	function printContent()    {

		$this->content.=$this->doc->endPage();
		echo $this->content;

	}

	/**
	 * Generates the module content
	 *
	 * @return    void
	 */
	function moduleContent() {
		global $LANG;
		$this->moduleId = intval($this->MOD_SETTINGS['function']);
		$this->moduleClassname = $this->modules[$this->moduleId];

		$moduleClass = t3lib_div::makeInstanceClassname($this->moduleClassname);
		$this->module = new $moduleClass($this, $LANG);

		$this->content .= $this->doc->section($this->module->getTitle(), $this->module->getContent(), 0, 1);
	}
	
	public function getExtensionDir() {
		return $this->extensionDir;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3dev/mod1/index.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3dev/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_t3dev_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)    include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
