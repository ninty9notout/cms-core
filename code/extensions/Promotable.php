<?php
class Promotable extends DataExtension {
	/**
	 * @var array
	 */
	private static $db = array(
		'Promotion' => 'Enum(array("Demote", "Neutral", "Promote"))'
	);

	/**
	 * @var array
	 */
	private static $indexes = array(
		'idx_promotion' => '(Promotion)'
	);

	/**
	 * @var array
	 */
	private static $defaults = array(
		'Promotion' => 'Neutral'
	);

	public function updateCMSFields(FieldList $fields) {
		$options = $this->owner->dbObject('Promotion')->enumValues();

		$fields->insertAfter(new OptionsetField('Promotion', 'Promotion', $options), 'MenuTitle');
	}
}