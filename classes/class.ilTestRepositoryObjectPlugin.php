<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 */
class ilTestRepositoryObjectPlugin extends ilRepositoryObjectPlugin
{
	const ID = "xtst";

	// must correspond to the plugin subdirectory
	function getPluginName()
	{
		return "TestRepositoryObject";
	}

	protected function uninstallCustom() {
		// TODO: Nothing to do here.
	}

	/**
	 * @inheritdoc
	 */
	public function allowCopy()
	{
		return true;
	}

}
?>