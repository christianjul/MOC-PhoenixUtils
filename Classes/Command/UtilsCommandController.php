<?php
namespace MOC\PhoenixUtils\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "MOC.PhoenixUtils".           *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * utils command controller for the MOC.PhoenixUtils package
 *
 * @FLOW3\Scope("singleton")
 */
class UtilsCommandController extends \TYPO3\FLOW3\MVC\Controller\CommandController {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\WorkspaceRepository
	 */
	protected $workspaceRepository;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeRepository
	 */
	protected $nodeRepository;


	/**
	 * Publish all deleted pages from workspace to live
	 *
	 * @param string $workspaceName Name of workspace to publish from (required)
	 * @return void
	 */
	public function publishDeletedCommand($workspaceName) {
		$workspace = $this->workspaceRepository->findOneByName($workspaceName);
		If(! $workspace instanceof \TYPO3\TYPO3CR\Domain\Model\Workspace) {
			$this->outputLine('"%s" doesn\'t seem to be a valid workspace name, it would usually be "user-[username]".',array($workspaceName));
			$this->quit();
		}
		$nodeQuery = $this->nodeRepository->createQuery();
		$nodes = $nodeQuery->matching(
			$nodeQuery->logicalAnd(
				$nodeQuery->equals('workspace',$workspace),
				$nodeQuery->equals('removed',1)
			))->execute();
		$workspace->publishNodes($nodes->toArray(),'live');
		$this->outputLine('All removed node was published to Live.', array($workspace->getName()));
	}

	/**
	 * Publish everything from workspace to live
	 *
	 * @param string $workspaceName Name of workspace to publish from (required)
	 * @return void
	 */
	public function publishCommand($workspaceName) {
		$workspace = $this->workspaceRepository->findOneByName($workspaceName);
		If(! $workspace instanceof \TYPO3\TYPO3CR\Domain\Model\Workspace) {
			$this->outputLine('"%s" doesn\'t seem to be a valid workspace name, it would usually be "user-[username]".',array($workspaceName));
			$this->quit();
		}
		$workspace->publish('live');
	}

}

?>