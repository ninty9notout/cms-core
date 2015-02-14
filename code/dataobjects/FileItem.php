<?php
class FileItem extends DataObject {
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
		'File' => 'File'
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
		'Description.Summary' => 'Description'
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
		'zip', 
		'doc', 'docx', 
		'txt', 
		'xls', 'xlsx', 
		'pages', 
		'ppt', 'pptx', 'pps', 
		'csv', 
		'html', 
		'xml', 
		'pdf'
	);

	/**
	 * @var int
	 */
	static $allowed_max_file_size = 52428800; // 50MB

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
	 * HTMLText
	 */
	public function Content() {
		if($this->Description) {
			return $this->Description;
		}

		return $this->Title;
	}
}