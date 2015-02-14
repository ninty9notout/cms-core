<?php
class Homepage extends SectionLandingPage {
	/**
	 * @var string
	 */
	private static $default_child = 'SectionLandingPage';

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
	 * @return boolean
	 */
	protected function enableFeaturedContent() {
		$enable = true;

		$this->extend('enableFeaturedContent', $enable);

		return $enable;
	}

	/**
	 * Creates an instance of this page after checking if one already exists
	 */
	public static function defaultRecords() {
		if(SiteTree::get_by_link(Config::inst()->get('RootURLController', 'default_homepage_link'))) {
			return false;
		}

		$page = new Homepage();
		$page->Title = 'Home';
		$page->URLSegment = Config::inst()->get('RootURLController', 'default_homepage_link');
		$page->Sort = 1;
		$page->ShowInMenus = true;
		$page->ShowInSearch = false;
		$page->AllowComments = false;
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();

		DB::alteration_message('Home page created', 'created');
	}
}