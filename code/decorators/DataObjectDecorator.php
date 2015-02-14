<?php
class DataObjectDecorator extends DataExtension {
	/**
	 * @return boolean
	 */
	public function isNew() {
		return empty($this->owner->ID) || !is_numeric($this->owner->ID);
	}
}