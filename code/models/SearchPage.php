<?php
class SearchPage extends Page {
	/**
	 * @var boolean
	 */
	private static $can_be_root = true;

	/**
	 * @var array
	 */
	private static $defaults = array(
		'ShowInMenus' => false,
		'ShowInSearch' => false,
		'AllowComments' => false
	);

	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canCreate($member = null) {
		return !DataObject::get_one($this->class);
	}

	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canDelete($member = null) {
		return false;
	}

	/**
	 * Creates an instance of this page after checking if one already exists
	 */
	public static function defaultRecords() {
		if(DataObject::get_one('SearchPage')) {
			return false;
		}

		$page = new SearchPage();
		$page->Title = 'Search';
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();
		
		DB::alteration_message('Search page created', 'created');
	}
}