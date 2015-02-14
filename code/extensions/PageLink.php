<?php
class PageLink extends DataExtension {
	/**
	 * @var array
	 */
	private static $db = array(
		'Title' => 'Varchar(255)',
		'Type' => 'Enum(array("External", "Internal"))',
		'InternalID' => 'Int',
		'ExternalURL' => 'Varchar(2083)',
		'Target' => 'Enum(array("Current Window/Tab", "New Window/Tab"))'
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'Page' => 'Page'
	);

	/**
	 * @var array
	 */
	private static $summary_fields = array(
		'Title',
		'Link',
	);

	/**
	 * @var array
	 */
	private static $defaults = array(
		'Type' => 'Internal',
		'Target' => 'Current Window/Tab'
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldsToTab('Root.Main', array(
			new TextField('Title'),
			new DropdownField('Type', 'Type', singleton($this->owner->ClassName)->dbObject('Type')->enumValues()),
			new TreeDropdownField('InternalID', 'Internal Page', 'Page'),
			new URLField('ExternalURL', 'External URL'),
			new OptionsetField('Target', 'Target', singleton($this->owner->ClassName)->dbObject('Target')->enumValues())
		));
	}

	/**
	 * @return string
	 */
	public function Link() {
		if('External' == $this->owner->Type && $this->owner->ExternalURL) {
			return $this->owner->ExternalURL;
		}

		if($this->owner->InternalID && $this->Internal()) {
			return $this->Internal()->Link();
		}

		return Director::baseURL();
	}

	/**
	 * @return string
	 */
	public function Target() {
		return 'Current Window/Tab' == $this->owner->getField('Target') ? '_self' : '_blank';
	}

	/**
	 * @return Page
	 */
	public function Internal() {
		return DataObject::get_by_id('Page', $this->owner->InternalID);
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if($this->owner->ExternalURL && (false === strpos($this->owner->ExternalURL, '://'))) {
			$this->owner->ExternalURL = 'http://' . $this->owner->ExternalURL;
		}

		if(trim($this->owner->Title)) {
			return false;
		}

		if('External' == $this->owner->Type) {
			$this->owner->Title = 'Link to ' . $this->owner->ExternalURL;
		}

		if($this->Internal()) {
			$this->owner->Title = $this->Internal()->Title;
		}
	}
}