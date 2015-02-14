<?php
class ContactPage extends UserDefinedForm {
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
		if(DataObject::get_one('ContactPage')) {
			return false;
		}

		$page = new ContactPage();
		$page->Title = 'Contact Us';
		$page->Sort = 40;
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();

			$nameField = new EditableTextField();
			$nameField->Name = 'EditableTextField1';
			$nameField->Title = 'Name';
			$nameField->Sort = 1;
			$nameField->Required = true;
			$nameField->CustomSettings = serialize(array(
				'Rows' => 1,
				'ShowOnLoad' => 'Show'
			));
			$nameField->ParentID = $page->ID;
			$nameField->write();
			$nameField->publish('Stage', 'Live');
			$nameField->flushCache();

			$emailField = new EditableEmailField();
			$emailField->Name = 'EditableEmailField2';
			$emailField->Title = 'Email';
			$emailField->Sort = 2;
			$emailField->Required = true;
			$emailField->CustomSettings = serialize(array(
				'ShowOnLoad' => 'Show'
			));
			$emailField->ParentID = $page->ID;
			$emailField->write();
			$emailField->publish('Stage', 'Live');
			$emailField->flushCache();

			$subjectField = new EditableDropdown();
			$subjectField->Name = 'EditableDropdown3';
			$subjectField->Title = 'Subject';
			$subjectField->Sort = 3;
			$subjectField->Required = false;
			$subjectField->CustomSettings = serialize(array(
				'ShowOnLoad' => 'Show'
			));
			$subjectField->ParentID = $page->ID;
			$subjectFieldID = $subjectField->write();
			$subjectField->publish('Stage', 'Live');
			$subjectField->flushCache();

				$generalEnquiriesOption = new EditableOption();
				$generalEnquiriesOption->Name = 'option1';
				$generalEnquiriesOption->Title = 'General Enquiries';
				$generalEnquiriesOption->ParentID = $subjectFieldID;
				$generalEnquiriesOption->write();
				$generalEnquiriesOption->publish('Stage', 'Live');
				$generalEnquiriesOption->flushCache();

				$feedbackOption = new EditableOption();
				$feedbackOption->Name = 'option2';
				$feedbackOption->Title = 'Feedback';
				$feedbackOption->ParentID = $subjectFieldID;
				$feedbackOption->write();
				$feedbackOption->publish('Stage', 'Live');
				$feedbackOption->flushCache();

				$recruitmentOption = new EditableOption();
				$recruitmentOption->Name = 'option3';
				$recruitmentOption->Title = 'Recruitment';
				$recruitmentOption->ParentID = $subjectFieldID;
				$recruitmentOption->write();
				$recruitmentOption->publish('Stage', 'Live');
				$recruitmentOption->flushCache();

				$pressOption = new EditableOption();
				$pressOption->Name = 'option4';
				$pressOption->Title = 'Press';
				$pressOption->ParentID = $subjectFieldID;
				$pressOption->write();
				$pressOption->publish('Stage', 'Live');
				$pressOption->flushCache();

			$messageField = new EditableTextField();
			$messageField->Name = 'EditableTextField4';
			$messageField->Title = 'Message';
			$messageField->Sort = 4;
			$messageField->Required = true;
			$messageField->CustomSettings = serialize(array(
				'Rows' => 10,
				'ShowOnLoad' => 'Show'
			));
			$messageField->ParentID = $page->ID;
			$messageField->write();
			$messageField->publish('Stage', 'Live');
			$messageField->flushCache();

		DB::alteration_message('Contact Us page created', 'created');
	}
}