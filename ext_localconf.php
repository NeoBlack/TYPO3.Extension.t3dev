<?php

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	define('T3dev_version', '1.1.5');
	$TYPO3_CONF_VARS['typo3/backend.php']['additionalBackendItems'][] = t3lib_extMgm::extPath('t3dev').'class.tx_t3dev_additionalToolbarIcons.php';

	$_REL_PATH = t3lib_extMgm::extRelPath('t3dev');
	
	$TYPO3_CONF_VARS['SC_OPTIONS']['ext/t3dev/class.tx_t3dev_additionalToolbarIcons.php']['links']=array(
			// Backend
		array('Backend', '#', $_REL_PATH.'icons/ico_t3.gif'),
		array('t3lib/', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/t3lib_api.html', $_REL_PATH.'icons/ico_lib.gif'),
		array('div', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/t3lib_div.html', $_REL_PATH.'icons/ico_div.gif'),
		array('extMgm', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/t3lib_extmgm.html', $_REL_PATH.'icons/ico_em.gif'),
		array('BEfunc', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/t3lib_befunc.html', $_REL_PATH.'icons/ico_befunc.gif'),
		array('DB', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/t3lib_db.html', $_REL_PATH.'icons/ico_db.gif'),
		array('template', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/typo3_template.html', $_REL_PATH.'icons/ico_template.gif'),
		array('lang', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/typo3_lang.html', $_REL_PATH.'icons/ico_lang.gif'),

			// Frontend:
		array('Frontend', '#', $_REL_PATH.'icons/ico_t3.gif'),
		array('pibase', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/tslib_pibase_api.html', $_REL_PATH.'icons/ico_pibase.gif'),
		array('cObj', t3lib_extMgm::extRelPath($_EXTKEY).'apidocs/tslib_content_api.html', $_REL_PATH.'icons/ico_cobj.gif'),
		array('TSref', 'http://typo3.org/documentation/document-library/references/doc_core_tsref/4.1.0/view/', $_REL_PATH.'icons/ico_ts.gif'),
		array('TSconfig', 'http://typo3.org/documentation/document-library/references/doc_core_tsconfig/4.1.0/view/', $_REL_PATH.'icons/ico_ts.gif'),

			// Other 
		array('Other', '#', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 Core API', 'http://typo3.org/documentation/document-library/core-documentation/doc_core_api/current/view/', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 Coding Guidlines', 'http://typo3.org/documentation/document-library/core-documentation/doc_core_cgl/4.1.0/view/', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3.org', 'http://typo3.org/', $_REL_PATH.'icons/ico_t3.gif'),
			
			// APIDocs
		array('APIDocs', '#', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 API 4.4.0beta2', 'http://www.typo3-unleashed.net/typo3apidocs/typo3api_4.4.0beta2/html/index.html', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 API 4.3.3', 'http://www.typo3-unleashed.net/typo3apidocs/typo3api_4.3.3/html/index.html', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 API 4.3.0', 'http://www.typo3-unleashed.net/typo3apidocs/typo3api_4.3.0/html/index.html', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 API 4.2.6', 'http://www.typo3-unleashed.net/typo3apidocs/typo3api_4.2.6/html/index.html', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 API 4.2.5', 'http://www.typo3-unleashed.net/typo3apidocs/typo3api_4.2.5/html/index.html', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 API 4.2.1', 'http://www.typo3-unleashed.net/typo3apidocs/typo3api_4.2.1/html/index.html', $_REL_PATH.'icons/ico_t3.gif'),
		array('TYPO3 API 4.2.0', 'http://www.typo3-unleashed.net/typo3apidocs/typo3api_4.2.0/html/index.html', $_REL_PATH.'icons/ico_t3.gif'),
	
			// T3dev
		array('T3dev', '#', $_REL_PATH.'icons/ico_t3.gif'),
		array('T3dev project homepage', 'http://forge.typo3.org/projects/show/extension-t3dev', $_REL_PATH.'icons/ico_t3.gif'),
	);
}
?>
