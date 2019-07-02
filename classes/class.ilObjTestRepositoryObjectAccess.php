<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TestRepositoryObject/classes/class.ilObjTestRepositoryObject.php");
require_once("./Services/Conditions/interfaces/interface.ilConditionHandling.php"); //bugfix mantis 24891

/**
 * Please do not create instances of large application classes
 * Write small methods within this class to determine the status.
 *
 * @author                 Alex Killing <alex.killing@gmx.de>
 * @author					Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id$
 */
class ilObjTestRepositoryObjectAccess extends ilObjectPluginAccess implements ilConditionHandling
{

	/**
	 * Checks whether a user may invoke a command or not
	 * (this method is called by ilAccessHandler::checkAccess)
	 *
	 * Please do not check any preconditions handled by
	 * ilConditionHandler here. Also don't do usual RBAC checks.
	 *
	 * @param       string $a_cmd command (not permission!)
	 * @param       string $a_permission permission
	 * @param       int $a_ref_id reference id
	 * @param       int $a_obj_id object id
	 * @param 		int$a_user_id user id (default is current user)
	 * @return bool true, if everything is ok
	 */
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = 0)
	{
		global $ilUser, $ilAccess;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission)
		{
			case "read":
				if (!ilObjTestRepositoryObjectAccess::checkOnline($a_obj_id) &&
					!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
				{
					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * @param $a_id int
	 * @return bool
	 */
	static function checkOnline($a_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT is_online FROM rep_robj_xtst_data ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
		);
		$rec  = $ilDB->fetchAssoc($set);
		return (boolean) $rec["is_online"];
	}

	/**
	 * Returns an array with valid operators for the specific object type
	 */
	public static function getConditionOperators() {
		include_once './Services/Conditions/classes/class.ilConditionHandler.php'; //bugfix mantis 24891
		return array(
			ilConditionHandler::OPERATOR_FAILED,
			ilConditionHandler::OPERATOR_PASSED
		);
	}

	/**
	 * check condition for a specific user and object
	 * @param type $a_trigger_obj_id
	 * @param type $a_operator
	 * @param type $a_value
	 * @param type $a_usr_id
	 * @return bool
	 */
	public static function checkCondition($a_trigger_obj_id, $a_operator, $a_value, $a_usr_id) {
		$ref_id = array_shift(ilObject::_getAllReferences($a_trigger_obj_id));
		$object = new ilObjTestRepositoryObject($ref_id);
		switch ($a_operator) {
			case ilConditionHandler::OPERATOR_PASSED:
				return $object->getLPStatusForUser($a_usr_id) == ilLPStatus::LP_STATUS_COMPLETED_NUM;
			case ilConditionHandler::OPERATOR_FAILED:
				return $object->getLPStatusForUser($a_usr_id) == ilLPStatus::LP_STATUS_FAILED_NUM;
		}
		return false;
	}
}

?>