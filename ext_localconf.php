<?php
defined('TYPO3_MODE') or die('Access denied.');

\OliverHader\IrreWorkspaces\Bootstrap::registerHooks();
\OliverHader\IrreWorkspaces\Bootstrap::registerSlots();
\OliverHader\IrreWorkspaces\Bootstrap::registerAlternatives();
\OliverHader\IrreWorkspaces\Bootstrap::registerCaches();

/*
DISABLED - Since the workspace module does not show unmodified elements,
there is no need to manipulate data structures (without changes) on editing

// @todo: Might use processDatamap_beforeStart for TYPO3 4.7, see http://forge.typo3.org/issues/35161

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/T3lib_TceMain.php';
*/
