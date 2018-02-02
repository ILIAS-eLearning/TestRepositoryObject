<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilTextInputGUI.php");
require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
require_once("./Services/Tracking/classes/class.ilLearningProgress.php");
require_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
require_once("./Services/Tracking/classes/status/class.ilLPStatusPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TestRepositoryObject/classes/class.ilTestRepositoryObjectPlugin.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");

/**
 * @ilCtrl_isCalledBy ilObjTestRepositoryObjectGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTestRepositoryObjectGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI
 */
class ilObjTestRepositoryObjectGUI extends ilObjectPluginGUI
{
	const LP_SESSION_ID = 'xtst_lp_session_state';

	/** @var  ilCtrl */
	protected $ctrl;

	/** @var  ilTabsGUI */
	protected $tabs;

	/** @var  ilTemplate */
	public $tpl;

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilCtrl, $ilTabs, $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->tpl = $tpl;
	}

	/**
	 * Get type.
	 */
	final function getType()
	{
		return ilTestRepositoryObjectPlugin::ID;
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":   // list all commands that need write permission here
			case "updateProperties":
			case "saveProperties":
			case "showExport":
				$this->checkPermission("write");
				$this->$cmd();
				break;

			case "showContent":   // list all commands that need read permission here
			case "setStatusToCompleted":
			case "setStatusToFailed":
			case "setStatusToInProgress":
			case "setStatusToNotAttempted":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	 * Get standard command
	 */
	function getStandardCmd()
	{
		return "showContent";
	}

//
// DISPLAY TABS
//

	/**
	 * Set tabs
	 */
	function setTabs()
	{
		global $ilCtrl, $ilAccess;

		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$this->tabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->tabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
			$this->tabs->addTab("export", $this->txt("export"), $ilCtrl->getLinkTargetByClass("ilexportgui", ""));
		}

		// standard permission tab
		$this->addPermissionTab();
		$this->activateTab();
	}

	/**
	 * Edit Properties. This commands uses the form class to display an input form.
	 */
	protected function editProperties()
	{
		$this->tabs->activateTab("properties");
		$form = $this->initPropertiesForm();
		$this->addValuesToForm($form);
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function initPropertiesForm() {
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt("obj_xtst"));

		$title = new ilTextInputGUI($this->plugin->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);

		$description = new ilTextInputGUI($this->plugin->txt("description"), "description");
		$form->addItem($description);

		$online = new ilCheckboxInputGUI($this->plugin->txt("online"), "online");
		$form->addItem($online);

		$form->setFormAction($this->ctrl->getFormAction($this, "saveProperties"));
		$form->addCommandButton("saveProperties", $this->plugin->txt("update"));

		return $form;
	}

	/**
	 * @param $form ilPropertyFormGUI
	 */
	protected function addValuesToForm(&$form) {
		$form->setValuesByArray(array(
			"title" => $this->object->getTitle(),
			"description" => $this->object->getDescription(),
			"online" => $this->object->isOnline(),
		));
	}

	/**
	 *
	 */
	protected function saveProperties() {
		$form = $this->initPropertiesForm();
		$form->setValuesByPost();
		if($form->checkInput()) {
			$this->fillObject($this->object, $form);
			$this->object->update();
			ilUtil::sendSuccess($this->plugin->txt("update_successful"), true);
			$this->ctrl->redirect($this, "editProperties");
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function showContent() {
		$this->tabs->activateTab("content");

		/** @var ilObjTestRepositoryObject $object */
		$object = $this->object;

		$form = new ilPropertyFormGUI();
		$form->setTitle($object->getTitle());

		$i = new ilNonEditableValueGUI($this->plugin->txt("title"));
		$i->setInfo($object->getTitle());
		$form->addItem($i);

		$i = new ilNonEditableValueGUI($this->plugin->txt("description"));
		$i->setInfo($object->getDescription());
		$form->addItem($i);

		$i = new ilNonEditableValueGUI($this->plugin->txt("online_status"));
		$i->setInfo($object->isOnline()?"Online":"Offline");
		$form->addItem($i);

		global $ilUser;
		$progress = new ilLPStatusPlugin($this->object->getId());
		$status = $progress->determineStatus($this->object->getId(), $ilUser->getId());
		$i = new ilNonEditableValueGUI($this->plugin->txt("lp_status"));
		$i->setInfo($this->plugin->txt("lp_status_".$status));
		$form->addItem($i);

		$i = new ilNonEditableValueGUI();
		$i->setInfo("<a href='".$this->ctrl->getLinkTarget($this, "setStatusToCompleted")."'> ".$this->plugin->txt("set_completed"));
		$form->addItem($i);

		$i = new ilNonEditableValueGUI();
		$i->setInfo("<a href='".$this->ctrl->getLinkTarget($this, "setStatusToNotAttempted")."'> ".$this->plugin->txt("set_not_attempted"));
		$form->addItem($i);

		$i = new ilNonEditableValueGUI();
		$i->setInfo("<a href='".$this->ctrl->getLinkTarget($this, "setStatusToFailed")."'> ".$this->plugin->txt("set_failed"));
		$form->addItem($i);

		$i = new ilNonEditableValueGUI();
		$i->setInfo("<a href='".$this->ctrl->getLinkTarget($this, "setStatusToInProgress")."'> ".$this->plugin->txt("set_in_progress"));
		$form->addItem($i);

		$i = new ilNonEditableValueGUI($this->plugin->txt("important"));
		$i->setInfo($this->plugin->txt("lp_status_info"));
		$form->addItem($i);

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @param $object ilObjTestRepositoryObject
	 * @param $form ilPropertyFormGUI
	 */
	private function fillObject($object, $form) {
		$object->setTitle($form->getInput('title'));
		$object->setDescription($form->getInput('description'));
		$object->setOnline($form->getInput('online'));
	}

	protected function showExport() {
		require_once("./Services/Export/classes/class.ilExportGUI.php");
		$export = new ilExportGUI($this);
		$export->addFormat("xml");
		$ret = $this->ctrl->forwardCommand($export);

	}

	/**
	 * We need this method if we can't access the tabs otherwise...
	 */
	private function activateTab() {
		$next_class = $this->ctrl->getCmdClass();

		switch($next_class) {
			case 'ilexportgui':
				$this->tabs->activateTab("export");
				break;
		}

		return;
	}

	private function setStatusToCompleted() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_COMPLETED_NUM);
	}

	private function setStatusAndRedirect($status) {
		global $ilUser;
		$_SESSION[self::LP_SESSION_ID] = $status;
		ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
		$this->ctrl->redirect($this, $this->getStandardCmd());
	}

	protected function setStatusToFailed() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_FAILED_NUM);
	}

	protected function setStatusToInProgress() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
	}

	protected function setStatusToNotAttempted() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
	}
}
?>