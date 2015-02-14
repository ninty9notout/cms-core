<?php
class FooterLinksHolder extends RedirectorPage {
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
		if(DataObject::get_one('FooterLinksHolder')) {
			return false;
		}

		$page = new FooterLinksHolder();
		$page->Title = 'Footer Links';
		$page->URLSegment = 'site';
		$page->LinkToID = SiteTree::get_by_link(Config::inst()->get('RootURLController', 'default_homepage_link'))->ID;
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();

		DB::alteration_message('Footer Links page created', 'created');

		// Create other footer links
		$about = new RedirectorPage();
		$about->Title = 'About Us';
		$about->ParentID = $page->ID;
		$about->LinkToID = DataObject::get_one('InformationPage')->ID;
		$about->write();
		$about->publish('Stage', 'Live');
		$about->flushCache();

		$contact = new RedirectorPage();
		$contact->Title = 'Contact Us';
		$contact->ParentID = $page->ID;
		$contact->LinkToID = DataObject::get_one('ContactPage')->ID;
		$contact->write();
		$contact->publish('Stage', 'Live');
		$contact->flushCache();

		$terms = new InformationPage();
		$terms->ParentID = $page->ID;
		$terms->Title = 'Terms & Conditions';
		$terms->write();
		$terms->publish('Stage', 'Live');
		$terms->flushCache();

		$privacy = new InformationPage();
		$privacy->ParentID = $page->ID;
		$privacy->Title = 'Privacy & Cookies';
		$privacy->write();
		$privacy->publish('Stage', 'Live');
		$privacy->flushCache();
	}
}