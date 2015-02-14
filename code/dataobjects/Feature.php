<?php
class Feature extends DataObject {
	/**
	 * @var array
	 */
	private static $extensions = array(
		'PageLink'
	);

	/**
	 * @var array
	 */
	private static $db = array(
		'Prefix' => 'Varchar(255)',
		'Suffix' => 'Varchar(255)',
		'Description' => 'Text',
		'Display' => 'Boolean',
		'LinkText' => 'Varchar(255)'
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'File' => 'Image'
	);

	/**
	 * @var array
	 */
	private static $casting = array(
		'Description' => 'HTMLText'
	);

	/**
	 * @var array
	 */
	private static $summary_fields = array(
		'Enabled' => 'Enabled',
		'Thumbnail' => 'Thumbnail',
		'Title' => 'Title',
		'Description.Summary' => 'Description'
	);

	/**
	 * @var array
	 */
	private static $defaults = array(
		'Display' => true,
		'LinkText' => 'Read More'
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
	static $allowed_max_file_size = 10485760; // 10MB

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = new FieldList(new TabSet('Root', new Tab('Main')));

		$this->extend('updateCMSFields', $fields);

		$fields->insertAfter(new TextField('Prefix'), 'Title');
		$fields->insertAfter(new TextField('Suffix'), 'Prefix');
		$fields->insertAfter(new TextareaField('Description'), 'Suffix');
		$fields->insertAfter(new TextField('LinkText'), 'ExternalURL');

		$fields->addFieldsToTab('Root.Main', array(
			new CheckboxField('Display'),
			$uploadField = new UploadField('File', 'Image')
		));

		$uploadField->getValidator()->setAllowedExtensions(static::$allowed_extensions);
		$uploadField->getValidator()->setAllowedMaxFileSize(static::$allowed_max_file_size);

		return $fields;
	}

	/**
	 * @return string|char
	 */
	public function Enabled() {
		return $this->Display ? '✔' : '✘';
	}

	/**
	 * @return boolean
	 */
	public function HasPrefix() {
		return !empty($this->Prefix);
	}

	/**
	 * @return boolean
	 */
	public function HasSuffix() {
		return !empty($this->Suffix);
	}

	/**
	 * @return boolean
	 */
	public function HasDescription() {
		return !empty($this->Description);
	}

	/**
	 * @return Image
	 */
	public function Thumbnail() {
		return $this->File()->CMSThumbnail();
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return Image
	 */
	public function CroppedImage($width, $height) {
		return $this->File()->CroppedImage($width, $height);
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return Image
	 */
	public function SetRatioSize($width, $height) {
		return $this->File()->SetRatioSize($width, $height);
	}
}
