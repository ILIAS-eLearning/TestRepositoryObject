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
 * Class ilTestRepositoryObjectImporter
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilTestRepositoryObjectImporter extends ilXmlImporter
{
    protected ilComponentFactory $component_factory;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->component_factory = $DIC['component.factory'];
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        $pl = $this->component_factory->getPlugin(ilTestRepositoryObjectPlugin::ID);
        $xml = simplexml_load_string($a_xml);
        if ($xml === false) {
            throw new ilException('Invalid TestRepositoryObject export XML.');
        }
        $entity = new ilObjTestRepositoryObject();
        $entity->setTitle($xml->title . ' ' . $pl->txt('copy'));
        $entity->setDescription((string) $xml->description);
        $entity->setOnline((int) $xml->online === 1);
        $entity->setImportId($a_id);
        $entity->create();
        $new_id = $entity->getId();
        $a_mapping->addMapping('Plugins/TestObjectRepository', ilTestRepositoryObjectPlugin::ID, $a_id, (string) $new_id);
    }
}