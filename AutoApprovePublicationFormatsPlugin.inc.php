<?php

/**
 * @file plugins/generic/autoApprovePublicationFormats/AutoApprovePublicationFormatsPlugin.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AutoApprovePublicationFormatsPlugin
 * @ingroup plugins_generic_autoApprovePublicationFormats
 *
 * @brief Automatically approve all publication formats and turn all files approved and OA by default.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class AutoApprovePublicationFormatsPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				HookRegistry::register('publicationformatdao::_insertobject', array($this, 'publicationFormatInsertDefaults'));
				HookRegistry::register('submissionfilesmetadataform::execute', array($this, 'executeSubmissionFilesUploadForm'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Hook to the publicationformatdao::_insertobject function.
	 *
	 * @param $hookName string Hook name
	 * @param $params array [
	 *  @option Query string
	 *  @option Values array
	 * ]
	 * @return boolean
	 */
	function publicationFormatInsertDefaults($hookName, $params) {
		$queryValues =& $params[1];
		$queryValues[0] = 1; // isApproved
		$queryValues[25] = 1; // isAvailable
		return false;
	}

	/**
	 * Hook to the submissionfilesmetadataform::execute function.
	 *
	 * @param $hookName string Hook name
	 * @param $params array [
	 *  @option submissionFilesMetadataForm
	 * ]
	 * @return boolean
	 */
	function executeSubmissionFilesUploadForm($hookName, $params) {
		$submissionFilesMetadataForm = $params[0];
		$submissionFile = $submissionFilesMetadataForm->getSubmissionFile();
		if ($submissionFile->getFileStage() == SUBMISSION_FILE_PROOF) {
			$submissionFile->setViewable(true);
			$submissionFile->setDirectSalesPrice(0);
			$submissionFile->setSalesType('openAccess');
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$submissionFileDao->updateObject($submissionFile);
		}
		return false;
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.autoApprovePublicationFormats.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.autoApprovePublicationFormats.description');
	}
}


