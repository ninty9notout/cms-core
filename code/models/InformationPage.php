<?php
class InformationPage extends Page {
	/**
	 * @var boolean
	 */
	private static $can_be_root = true;

	/**
	 * @var boolean
	 */
	private static $defaults = array(
		'ShowInMenus' => true,
		'ShowInSearch' => true,
		'AllowComments' => false
	);

	/**
	 * @return boolean
	 */
	protected function enableComments() {
		$enable = true;

		$this->extend('enableComments', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableMedia() {
		$enable = true;

		$this->extend('enableMedia', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableListing() {
		$enable = true;

		$this->extend('enableListing', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableGallery() {
		$enable = true;

		$this->extend('enableGallery', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableRelatedPages() {
		$enable = true;

		$this->extend('enableRelatedPages', $enable);

		return $enable;
	}

	/**
	 * Creates the about us page after checking if only one page exists (homepage)
	 */
	public static function defaultRecords() {
		if(DB::query('SELECT COUNT(*) FROM SiteTree')->value() != 1) {
			return false;
		}

		$page = new InformationPage();
		$page->Title = 'About Us';
		$page->Sort = 30;
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();
		
		DB::alteration_message('About Us page created', 'created');
	}
}
