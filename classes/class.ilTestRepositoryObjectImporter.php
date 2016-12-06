<?php

require_once("./Services/Export/classes/class.ilXmlImporter.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TestRepositoryObject/classes/class.ilObjTestRepositoryObject.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TestRepositoryObject/classes/class.ilTestRepositoryObjectPlugin.php");

/**
 * Class ilTestRepositoryObjectImporter
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilTestRepositoryObjectImporter extends ilXmlImporter {

	/**
	 * Import xml representation
	 *
	 * @param    string        entity
	 * @param    string        target release
	 * @param    string        id
	 * @return    string        xml string
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping) {
		$xml = simplexml_load_string($a_xml);
		$pl = new ilTestRepositoryObjectPlugin();
		$entity = new ilObjTestRepositoryObject();
		$entity->setTitle((string) $xml->title." ".$pl->txt("copy"));
		$entity->setDescription((string) $xml->description);
		$entity->setOnline((string) $xml->online);
		$entity->setImportId($a_id);
		$entity->create();
		$new_id = $entity->getId();
		$a_mapping->addMapping("Plugins/TestObjectRepository", "xtst", $a_id, $new_id);
		return $new_id;
	}
}