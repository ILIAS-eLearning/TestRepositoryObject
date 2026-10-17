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

class ilObjTestRepositoryObject extends ilObjectPlugin implements ilLPStatusPluginInterface
{
    protected bool $online = false;
    protected ilDBInterface $db;

    public function __construct(int $a_ref_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();
        parent::__construct($a_ref_id);
    }

    final protected function initType() : void
    {
        $this->setType(ilTestRepositoryObjectPlugin::ID);
    }

    protected function doCreate(bool $clone_mode = false) : void
    {
        $this->db->manipulate(
            "INSERT INTO rep_robj_xtst_data " .
            "(id, is_online, option_one, option_two) VALUES (" .
            $this->db->quote($this->getId(), "integer") . "," .
            $this->db->quote((int) $this->isOnline(), "integer") . "," .
            $this->db->quote("default 1", "text") . "," .
            $this->db->quote("default 2", "text") .
            ")"
        );
    }

    protected function doRead() : void
    {
        $set = $this->db->query(
            "SELECT * FROM rep_robj_xtst_data " .
            " WHERE id = " . $this->db->quote($this->getId(), "integer")
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->setOnline((bool) $rec["is_online"]);
        }
    }

    protected function doUpdate() : void
    {
        $this->db->manipulate(
            "UPDATE rep_robj_xtst_data SET " .
                " is_online = " . $this->db->quote((int) $this->isOnline(), "integer") .
                " WHERE id = " . $this->db->quote($this->getId(), "integer")
        );
    }

    protected function doDelete() : void
    {
        $this->db->manipulate(
            "DELETE FROM rep_robj_xtst_data WHERE " .
            " id = " . $this->db->quote($this->getId(), "integer")
        );
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null) : void
    {
        assert($new_obj instanceof ilObjTestRepositoryObject);
        $new_obj->setOnline($this->isOnline());
        $new_obj->update();
    }

    public function setOnline(bool $a_val) : void
    {
        $this->online = $a_val;
    }

    public function isOnline() : bool
    {
        return $this->online;
    }

    public function getLPCompleted() : array
    {
        return [];
    }

    public function getLPNotAttempted() : array
    {
        return [];
    }

    public function getLPFailed() : array
    {
        return [6];
    }

    public function getLPInProgress() : array
    {
        return [];
    }

    public function getLPStatusForUser(int $a_user_id) : int
    {
        global $ilUser;

        if ($ilUser->getId() === $a_user_id) {
            return $_SESSION[ilObjTestRepositoryObjectGUI::LP_SESSION_ID] ?? ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }

        return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

}