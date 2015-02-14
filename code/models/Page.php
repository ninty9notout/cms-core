<?php
class Page extends SiteTree {
	/**
	 * @var boolean
	 */
	private static $can_be_root = false;

	/**
	 * @var string
	 */
	private static $default_child = 'InformationPage';

	/**
	 * @var array
	 */
	private static $db = array(
		'CanCommentType' => 'Enum(array("Anyone", "LoggedInUsers", "OnlyTheseUsers", "Inherit"))',
		'AllowComments' => 'Boolean'
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'MainImage' => 'ImageItem',
		'MainVideo' => 'VideoItem',
		'ListImage' => 'ImageItem'
	);

	/**
	 * @var array
	 */
	private static $has_many = array(
		'FeatureBanners' => 'FeatureBanner',
		'FeatureBoxes' => 'FeatureBox',
		'RelatedPages' => 'RelatedPage'
	);

	/**
	 * @var array
	 */
	private static $many_many = array(
		'Images' => 'ImageItem',
		'Videos' => 'VideoItem',
		'Audio' => 'AudioItem',
		'Files' => 'FileItem'
	);

	/**
	 * @var array
	 */
	private static $defaults = array(
		'CanCommentType' => 'Inherit',
		'ShowInMenus' => false,
		'ShowInSearch' => false,
		'AllowComments' => false
	);

	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canCreate($member = null) {
		return $this->class != 'Page';
	}

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		if($this->enableMedia()) {
			$fields->insertAfter(
				$mainImage = new DropdownField('MainImageID', 'Main Image', $this->Images()->map('ID', 'Title')),
				'MenuTitle'
			);

			$fields->insertAfter(
				$mainVideo = new DropdownField('MainVideoID', 'Main Video', $this->Videos()->map('ID', 'Title')),
				'MainImageID'
			);

			$mainImage->setEmptyString('None');
			$mainVideo->setEmptyString('None');
		}

		if($this->enableListing()) {
			$fields->insertBefore(
				$listImage = new DropdownField('ListImageID', 'List Image', $this->Images()->map('ID', 'Title')),
				'Content'
			);

			$listImage->setEmptyString('None');
		}

		if($this->enableFeaturedContent()) {
			$fields->addFieldsToTab('Root.FeaturedContent', array(
				new GridField('FeatureBanners', 'Feature Banners', $this->FeatureBanners(), GridFieldConfig_RelationEditor::create()),
				new GridField('FeatureBoxes', 'Feature Boxes', $this->FeatureBoxes(), GridFieldConfig_RelationEditor::create())
			));
		}

		if($this->enableGallery()) {
			$fields->addFieldsToTab('Root.Media & Downloads', array(
				new GridField('Images', 'Images', $this->Images(), GridFieldConfig_RelationEditor::create()),
				new GridField('Videos', 'Videos', $this->Videos(), GridFieldConfig_RelationEditor::create()),
				new GridField('Audio', 'Audio', $this->Audio(), GridFieldConfig_RelationEditor::create()),
				new GridField('Files', 'Files', $this->Files(), GridFieldConfig_RelationEditor::create())
			));
		}

		if($this->enableRelatedPages()) {
			$fields->addFieldsToTab('Root.RelatedPages', array(
				new GridField('RelatedPages', 'Related Pages', $this->RelatedPages(), GridFieldConfig_RecordEditor::create())
			));
		}

		return $fields;
	}

	/**
	 * @return FieldList
	 */
	public function getSettingsFields() {
		$fields = parent::getSettingsFields();

		$fields->insertBefore($extras = new FieldGroup(), 'CanViewType');

		$extras->setTitle(_t('SiteTree.Extras', 'Extras'));

		if($this->enableComments()) {
			$groupsMap = Group::get()->map('ID', 'Breadcrumbs')->toArray();
			asort($groupsMap);

			$fields->addFieldsToTab('Root.Settings', array(
				$commentersOptionsField = new OptionsetField('CanCommentType', _t('SiteTree.COMMENTERHEADER', 'Who can comment on this page?')),
				$commenterGroupsField = ListboxField::create('CommenterGroups',  _t('SiteTree.COMMENTERGROUPS', 'Commenter Groups'))
			));

			$commentersOptionsField->setSource(array(
				'Inherit' => _t('SiteTree.INHERIT', 'Inherit from parent page'),
				'Anyone' => _t('SiteTree.COMMENTERANYONE', 'Anyone'),
				'LoggedInUsers' => _t('SiteTree.COMMENTERLOGGEDIN', 'Logged-in users'),
				'OnlyTheseUsers' => _t('SiteTree.COMMENTERONLYTHESE', 'Only these people (choose from list)')
			));

			$commenterGroupsField
				->setMultiple(true)
				->setSource($groupsMap)
				->setAttribute('data-placeholder', _t('SiteTree.GroupPlaceholder', 'Click to select group'));

			$extras->push(new CheckBoxField('AllowComments', _t('SiteTree.ALLOWCOMMENTS', 'Allow comments?')));
		}

		if(!Permission::check('SITETREE_GRANT_ACCESS')) {
			$fields->makeFieldReadonly($commentersOptionsField);
			if($this->CanViewType == 'OnlyTheseUsers') {
				$fields->makeFieldReadonly($commenterGroupsField);
			} else {
				$fields->removeByName('CommenterGroups');
			}
		}

		return $fields;
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

		// Check for inheritance from parent
		if($this->CanCommentType == 'Inherit') {
			return $this->ParentID ? $this->Parent()->canComment($member) : $this->getSiteConfig()->canComment($member);
		}

		// Check for any logged-in users
		if($this->CanViewType == 'LoggedInUsers' && $member) {
			return true;
		}

		// Check for specific groups
		if($member && is_numeric($member)) {
			$member = DataObject::get_by_id('Member', $member);
		}

		return $this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups());
	}

	/**
	 * @return boolean
	 */
	protected function enableComments() {
		$enable = false;

		$this->extend('enableComments', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableMedia() {
		$enable = false;

		$this->extend('enableMedia', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableListing() {
		$enable = false;

		$this->extend('enableListing', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableFeaturedContent() {
		$enable = false;

		$this->extend('enableFeaturedContent', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableGallery() {
		$enable = false;

		$this->extend('enableGallery', $enable);

		return $enable;
	}

	/**
	 * @return boolean
	 */
	protected function enableRelatedPages() {
		$enable = false;

		$this->extend('enableRelatedPages', $enable);

		return $enable;
	}

	/**
	 * @return Image
	 */
	public function PlaceholderImage() {
		return SiteConfig::current_site_config()->PlaceholderImage();
	}

	/**
	 * Return "link", "current" or section depending on if this page is the current page, or not on the current page but
	 * in the current section.
	 *
	 * @return string
	 */
	public function LinkingMode() {
		$linkingMode = parent::LinkingMode();

		$this->extend('linkingMode', $linkingMode);

		return $linkingMode;
	}
}
