<?php
class SiteConfigDecorator extends DataExtension {
	/**
	 * @var array
	 */
	private static $db = array(
		'CanCommentType' => 'Enum("Anyone, LoggedInUsers, OnlyTheseUsers", "Anyone")',
		'GoogleAnalytics' => 'Varchar(32)',
		'Disqus' => 'Varchar(32)',
		'AddThis' => 'Varchar(32)',
		'Facebook' => 'Varchar(255)',
		'Twitter' => 'Varchar(255)',
		'YouTube' => 'Varchar(255)',
		'GooglePlus' => 'Varchar(255)'
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'PlaceholderImage' => 'Image'
	);

	/**
	 * @var array
	 */
	private static $defaults = array(
		'GoogleAnalytics' => '00-0000000-0',
		'Disqus' => 'hangar18games',
		'AddThis' => 'ra-53d14db329715982',
		'Facebook' => 'Hangar18GamesStudio',
		'Twitter' => 'hangar18games',
		'YouTube' => 'hangar18games',
		'GooglePlus' => 'hangar18games'
	);

	/**
	 * @var array
	 */
	static $allowed_extensions = array(
		'jpg',
		'jpeg',
		'png'
	);

	/**
	 * @var int
	 */
	static $allowed_max_file_size = 10485760;

	/**
	 * @param FieldList $fields
	 */
	public function updateCMSFields(FieldList $fields) {
		$fields->removeByName('Theme');

		$fields->addFieldsToTab('Root.Main', array(
			$uploadField = new UploadField('PlaceholderImage', 'Placeholder Image'),
			new TextField('GoogleAnalytics', 'Google Analytics'),
			new TextField('AddThis', 'AddThis Publisher ID')
		));

		$uploadField->getValidator()->setAllowedExtensions(static::$allowed_extensions);
		$uploadField->getValidator()->setAllowedMaxFileSize(static::$allowed_max_file_size);

		$groupsMap = Group::get()->map('ID', 'Breadcrumbs')->toArray();
		asort($groupsMap);

		$fields->addFieldsToTab('Root.Access', array(
			$commentersOptionsField = new OptionsetField('CanCommentType', _t('SiteConfig.COMMENTERHEADER', 'Who can comment on this page?')),
			$commenterGroupsField = ListboxField::create('CommenterGroups',  _t('SiteTree.COMMENTERGROUPS', 'Commenter Groups'))
		));

		$commentersOptionsField->setSource(array(
			'Anyone' => _t('SiteTree.COMMENTERANYONE', 'Anyone'),
			'LoggedInUsers' => _t('SiteTree.COMMENTERLOGGEDIN', 'Logged-in users'),
			'OnlyTheseUsers' => _t('SiteTree.COMMENTERONLYTHESE', 'Only these people (choose from list)')
		));

		$commenterGroupsField
			->setMultiple(true)
			->setSource($groupsMap)
			->setAttribute('data-placeholder', _t('SiteTree.GroupPlaceholder', 'Click to select group'));

		if(!Permission::check('EDIT_SITECONFIG')) {
			$fields->makeFieldReadonly($commentersOptionsField);
			$fields->makeFieldReadonly($commenterGroupsField);
		}

		$fields->addFieldsToTab('Root.Social', array(
			new TextField('Facebook', 'Facebook URL or ID'),
			new TextField('Twitter', 'Twitter @'),
			new TextField('YouTube', 'YouTube Channel'),
			new TextField('GooglePlus', 'Google+ URL or ID'),
			new TextField('Disqus', 'Disqus ID')
		));
	}

	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canComment($member = null) {
		if(!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
			$member = Member::currentUserID();
		}

		// Admin override
		if($member && Permission::checkMember($member, array('ADMIN'))) {
			return true;
		}

		// Check for anyone
		if(!$this->CanCommentType || $this->CanCommentType == 'Anyone') {
			return true;
		}

		// Check for any logged-in users
		if($this->CanViewType == 'LoggedInUsers' && $member) {
			return true;
		}

		// Check for specific groups
		if($member && is_numeric($member)) {
			$member = DataObject::get_by_id('Member', $member);
		}

		return $this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups()) ? true : false;
	}

	/**
	 * @see {@link SiteTree::requireDefaultRecords()}
	 */
	public function requireDefaultRecords() {
		parent::requireDefaultRecords();

		Homepage::defaultRecords();

		InformationPage::defaultRecords();

		ContactPage::defaultRecords();

		SearchPage::defaultRecords();

		SitemapPage::defaultRecords();

		FooterLinksHolder::defaultRecords();
	}
}