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

use OliverHader\IrreWorkspaces\Bootstrap;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class WorkspaceService extends \TYPO3\CMS\Workspaces\Service\WorkspaceService {

	/**
	 * @var array
	 */
	protected $reduction = array();

	/**
	 * @var \OliverHader\IrreWorkspaces\Service\Version\ReductionService
	 * @inject
	 */
	protected $reductionService;

	/**
	 * @param string $for
	 */
	public function setReduction($for) {
		$this->reduction[$for] = TRUE;
	}

	/**
	 * @return array
	 */
	public function selectVersionsInWorkspace() {
		$versions = call_user_func_array('parent::selectVersionsInWorkspace', func_get_args());

		if (!empty($this->reduction[__FUNCTION__])) {
			$versions = $this->getReductionService()->reduce($versions);
		}

		return $versions;
	}

	/**
	 * @return \OliverHader\IrreWorkspaces\Service\Version\ReductionService
	 */
	protected function getReductionService() {
		return Bootstrap::getObjectManager()->get(
			'OliverHader\\IrreWorkspaces\\Service\\Version\\ReductionService'
		);
	}

}