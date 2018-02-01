<?php

require_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Class ilTestRepositoryObjectExporter
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilTestRepositoryObjectExporter extends ilXmlExporter {

	/**
	 * Get xml representation
	 *
	 * @param    string        entity
	 * @param    string        schema version
	 * @param    string        id
	 * @return    string        xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id) {
		$ref_ids = ilObject::_getAllReferences($a_id);
		$ref_id = array_shift($ref_ids);
		$entity = new ilObjTestRepositoryObject($ref_id);

		include_once "./Services/Xml/classes/class.ilXmlWriter.php";
		$writer = new ilXmlWriter();
		$writer->xmlStartTag("xtst");
		$writer->xmlElement("title", null, $entity->getTitle());
		$writer->xmlElement("description", null, $entity->getDescription());
		$writer->xmlElement("online", null, $entity->isOnline());
		$writer->xmlEndTag("xtst");

		return $writer->xmlDumpMem(false);
	}

	public function init() {
		// TODO: Implement init() method.
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top. Example:
	 *
	 *        return array (
	 *        "4.1.0" => array(
	 *            "namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
	 *            "xsd_file" => "ilias_md_4_1.xsd",
	 *            "min" => "4.1.0",
	 *            "max" => "")
	 *        );
	 *
	 *
	 * @return        array
	 */
	public function getValidSchemaVersions($a_entity) {
		return array (
	         "5.2.0" => array(
	             "namespace" => "http://www.ilias.de/Plugins/TestRepositoryObject/md/5_2",
	             "xsd_file" => "ilias_md_5_2.xsd",
	             "min" => "5.2.0",
	             "max" => "")
	         );
	}
}