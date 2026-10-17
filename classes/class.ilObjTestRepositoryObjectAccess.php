<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Please do not create instances of large application classes
 * Write small methods within this class to determine the status.
 * @author                    Alex Killing <alex.killing@gmx.de>
 * @author                    Oskar Truffer <ot@studer-raimann.ch>
 */
class ilObjTestRepositoryObjectAccess extends ilObjectPluginAccess implements ilConditionHandling
{

    /**
     * Checks whether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     * Please do not check any preconditions handled by
     * ilConditionHandler here. Also don't do usual RBAC checks.
     * @param string $cmd        command (not permission!)
     * @param string $permission permission
     * @param int    $ref_id     reference id
     * @param int    $obj_id     object id
     * @return bool true, if everything is ok
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
    {
        $user_id ??= $this->user->getId();

        switch ($permission) {
            case "read":
                if (!self::checkOnline($obj_id) &&
                    !$this->access->checkAccessOfUser($user_id, "write", "", $ref_id)) {
                    return false;
                }
                break;
        }

        return true;
    }

    public static function checkOnline(int $object_id) : bool
    {
        $ref_ids = ilObject::_getAllReferences($object_id);
        $ref_id = array_shift($ref_ids);
        if ($ref_id === null) {
            return false;
        }

        return new ilObjTestRepositoryObject($ref_id)->isOnline();
    }

    public static function getConditionOperators() : array
    {
        return [
            ilConditionHandler::OPERATOR_FAILED,
            ilConditionHandler::OPERATOR_PASSED
        ];
    }

    public static function checkCondition(
        int $a_trigger_obj_id,
        string $a_operator,
        string $a_value,
        int $a_usr_id
    ) : bool {
        $ref_ids = ilObject::_getAllReferences($a_trigger_obj_id);
        $ref_id = array_shift($ref_ids);
        if ($ref_id === null) {
            return false;
        }

        $object = new ilObjTestRepositoryObject($ref_id);
        return match ($a_operator) {
            ilConditionHandler::OPERATOR_PASSED =>
                $object->getLPStatusForUser($a_usr_id) === ilLPStatus::LP_STATUS_COMPLETED_NUM,
            ilConditionHandler::OPERATOR_FAILED =>
                $object->getLPStatusForUser($a_usr_id) === ilLPStatus::LP_STATUS_FAILED_NUM,
            default => false
        };
    }
}