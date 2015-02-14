<?php
/**
 * Text input field with validation for URLs that are accepted by the VideoItem class.
 * 
 * @package forms
 * @subpackage fields-formattedinput
 */
class VideoItemURLField extends URLField {
	/**
	 * Validates entered URL against {@link VideoItem::$allowed_sources}
	 * 
	 * @param Validator $validator
	 * @return string
	 */
	public function validate($validator) {
		$this->value = trim($this->value);

		if(!VideoItem::video_details($this->value)) {
			$validator->validationError(
				$this->name,
				_t('VideoURLField.VALIDATION', 'Please enter a valid ' . implode(' or ', implode(' or ', array_keys(VideoItem::allowed_sources()))) . ' URL'),
				'validation'
			);

			return false;
		}

		return true;
	}
}