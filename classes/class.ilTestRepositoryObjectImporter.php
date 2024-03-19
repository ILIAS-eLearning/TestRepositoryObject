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
 * Class ilTestRepositoryObjectImporter
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilTestRepositoryObjectImporter extends ilXmlImporter
{
    /**
     * Import xml representation
     * @param string        entity
     * @param string        target release
     * @param string        id
     * @return    string        xml string
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        global $DIC;

        $component_repository = $DIC["component.factory"];
        $pl = $component_repository->getPlugin("xtst");
        $xml = simplexml_load_string($a_xml);
        $entity = new ilObjTestRepositoryObject();
        $entity->setTitle((string) $xml->title . " " . $pl->txt("copy"));
        $entity->setDescription((string) $xml->description);
        $entity->setOnline((string) $xml->online);
        $entity->setImportId($a_id);
        $entity->create();
        $new_id = $entity->getId();
        $a_mapping->addMapping("Plugins/TestObjectRepository", "xtst", $a_id, $new_id);
    }
}