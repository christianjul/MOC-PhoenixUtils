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
			))->execute()->toArray();
		$workspace->publishNodes($nodes,'live');
		$this->outputLine('All removed nodes in workspace "%s" was published to Live.', array($workspace->getName()));
	}

	/**
	 * Publish everything from workspace to live
	 *
	 * @param string $workspaceName Name of workspace to publish from (required)
	 * @param string $path restrict to nodes in this path (node in root of path included)
	 * @return void
	 */
	public function publishCommand($workspaceName,$path = null) {
		$workspace = $this->workspaceRepository->findOneByName($workspaceName);
		If(! $workspace instanceof \TYPO3\TYPO3CR\Domain\Model\Workspace) {
			$this->outputLine('"%s" doesn\'t seem to be a valid workspace name, it would usually be "user-[username]".',array($workspaceName));
			$this->quit();
		}
		if($path !== null) {
			$nodeQuery = $this->nodeRepository->createQuery();
			$nodes = $nodeQuery->matching(
				$nodeQuery->logicalAnd(
					$nodeQuery->equals('workspace',$workspace),
					$nodeQuery->like('path','%' . $path . '%')
				))->execute()->toArray();
			$workspace->publishNodes($nodes,'live');
			$this->outputLine('Published all nodes in workspace "%s" that contains "%s" in the path',array($workspace->getName(),$path));
		} else {
			$workspace->publish('live');
			$this->outputLine('Published all nodes in workspace "%s"',array($workspace->getName()));
		}
	}

	/**
	 * Remove all nodes from workspace - ie. resetting to live.
	 *
	 * @param string $workspaceName Name of workspace to publish from (required)
	 * @param string $path restrict to nodes in this path (node in root of path included)
	 * @return void
	 */

	public function cleanWorkspaceCommand($workspaceName) {
		$workspace = $this->workspaceRepository->findOneByName($workspaceName);
		$nodeQuery = $this->nodeRepository->createQuery();
		$nodes = $nodeQuery->matching(
					$nodeQuery->equals('workspace',$workspace)
				)->execute();
		foreach($nodes as $node) {
			if($node->getPath() !== '/') {
				$this->nodeRepository->remove($node);
			}
		}
		$this->outputLine('Removed all nodes in workspace "%s"',array($workspace->getName()));
	}


}

?>