<?php
namespace OliverHader\IrreWorkspaces\Alternative;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015 Oliver Hader <oliver.hader@typo3.org>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use OliverHader\IrreWorkspaces\Hook\ReductionHook;

/**
 * This class handles pages records that might be a version
 * and uses the ReductionService to resolve versions without
 * real changes to the data-set.
 *
 * @package OliverHader\IrreWorkspaces\Alternative
 */
class PagetreeCollectionsProcessor extends \TYPO3\CMS\Workspaces\ExtDirect\PagetreeCollectionsProcessor
{

    /**
     * Sets the CSS Class on all pages which have versioned records
     * in the current workspace. This method handles only pages records
     * that might be a version.
     *
     * @param \TYPO3\CMS\Backend\Tree\TreeNode|\TYPO3\CMS\Backend\Tree\ExtDirectNode $node
     */
    protected function highlightVersionizedElements(\TYPO3\CMS\Backend\Tree\TreeNode $node) {
        if (strpos($node->getCls(), 'ver-element') !== FALSE
            && !ReductionHook::create()->isPageVersion($node->getId(), $GLOBALS['BE_USER']->workspace))
        {
            // && !$this->getWorkspaceService()->hasPageRecordVersions($GLOBALS['BE_USER']->workspace, $node->getId())
            $classNames = GeneralUtility::trimExplode(' ', $node->getCls(), TRUE);
            $classNames = array_diff($classNames, array(    'ver-element'));
            $node->setCls(implode(' ', $classNames));
        }

        parent::highlightVersionizedElements($node);
    }

}