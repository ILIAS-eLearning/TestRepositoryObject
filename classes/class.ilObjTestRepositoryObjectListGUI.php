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

/**
 * handles the presentation in container items (categories, courses, ...)
 * together with the corresponding ...Access class.
 * PLEASE do not create instances of larger classes here. Use the
 * ...Access class to get DB data and keep it small.
 */
class ilObjTestRepositoryObjectListGUI extends ilObjectPluginListGUI
{

    public function initType() : void
    {
        $this->setType(ilTestRepositoryObjectPlugin::ID);
    }

    public function getGuiClass() : string
    {
        return "ilObjTestRepositoryObjectGUI";
    }

    public function initCommands() : array
    {
        return array
        (
            array(
                "permission" => "read",
                "cmd" => "showContent",
                "default" => true
            ),
            array(
                "permission" => "write",
                "cmd" => "editProperties",
                "txt" => $this->txt("edit"),
                "default" => false
            )
        );
    }

    /**
     * Get item properties
     * @return        array                array of property arrays:
     *                                "alert" (boolean) => display as an alert property (usually in red)
     *                                "property" (string) => property name
     *                                "value" (string) => property value
     */
    public function getProperties() : array
    {
        global $lng, $ilUser;

        $props = array();

        $this->plugin->includeClass("class.ilObjTestRepositoryObjectAccess.php");
        if (!ilObjTestRepositoryObjectAccess::checkOnline($this->obj_id)) {
            $props[] = array("alert" => true,
                             "property" => $this->txt("status"),
                             "value" => $this->txt("offline")
            );
        }

        return $props;
    }
}