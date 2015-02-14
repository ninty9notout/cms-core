<?php
class ImageItem extends DataObject {
	/**
	 * @var array
	 */
	private static $db = array(
		'Title' => 'Varchar(255)',
		'Description' => 'Text',
	);

	/**
	 * @var array
	 */
	private static $has_one = array(
		'Page' => 'Page',
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
		'Title' => 'Title',
		'Description.Summary' => 'Description',
		'Thumbnail' => 'Thumbnail'
	);

	/**
	 * @var array
	 */
	private static $searchable_fields = array(
		'Title',
		'Description'
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

		$fields->addFieldsToTab('Root.Main', array(
			new TextField('Title'),
			new TextareaField('Description'),
			$uploadField = new UploadField('File')
		));

		$uploadField->getValidator()->setAllowedExtensions(static::$allowed_extensions);
		$uploadField->getValidator()->setAllowedMaxFileSize(static::$allowed_max_file_size);

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	/**
	 * @return HTMLText
	 */
	public function Content() {
		if($this->Description) {
			return $this->Description;
		}

		return $this->Title;
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

	protected function onBeforeWrite() {
		parent::onBeforeWrite();

		if(!$this->Title) {
			$this->Title = $this->File()->getTreeTitle();
		}
	}
}