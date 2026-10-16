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
 * Class ilTestRepositoryObjectExporter
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilTestRepositoryObjectExporter extends ilXmlExporter
{

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $ref_ids = ilObject::_getAllReferences((int) $a_id);
        $ref_id = array_shift($ref_ids);
        $entity = new ilObjTestRepositoryObject($ref_id);

        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElementNS(null, 'xtst', 'https://www.ilias.de/Plugins/TestRepositoryObject/md/5_2');
        $writer->writeElement('title', $entity->getTitle());
        $writer->writeElement('description', $entity->getDescription());
        $writer->writeElement('online', $entity->isOnline() ? '1' : '0');
        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    public function init() : void
    {
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top. Example:
     *        return array (
     *        "4.1.0" => array(
     *            "namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
     *            "xsd_file" => "ilias_md_4_1.xsd",
     *            "min" => "4.1.0",
     *            "max" => "")
     *        );
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return [
            '5.2.0' => [
                'namespace' => 'https://www.ilias.de/Plugins/TestRepositoryObject/md/5_2',
                'xsd_file' => 'ilias_md_5_2.xsd',
                'min' => '5.2.0',
                'max' => ''
            ]
        ];
    }
}