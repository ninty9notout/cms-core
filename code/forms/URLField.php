<?php
/**
 * Text input field with validation for correct URL (IRI) format according to RFC 3987.
 */
class URLField extends TextField {
	/**
	 * @return string
	 */
	public function Type() {
		return 'url text';
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		return array_merge(parent::getAttributes(), array(
			'type' => 'url'
		));
	}

	/**
	 * Validates for RFC 3987 compliant IRI.
	 * @see http://www.faqs.org/rfcs/rfc3987.html
	 * @see http://www.php.net/manual/en/function.preg-match.php#93824
	 * 
	 * @param Validator $validator
	 * @return string
	 */
	public function validate($validator) {
		$this->value = trim($this->value);

		$pcrePattern = '^((https?|ftp)\://)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?([a-z0-9-.]*)\.([a-z]{2,3})(\:[0-9]{2,5})?(/([a-z0-9+\$_-]\.?)+)*/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+/\$_.-]*)?(\#[a-z_.-][a-z0-9+\$_.-]*)?$';

		// PHP uses forward slash (/) to delimit start/end of pattern, so it must be escaped
		$pregSafePattern = str_replace('/', '\\/', $pcrePattern);

		if($this->value && !preg_match('/' . $pregSafePattern . '/i', $this->value)) {
			$validator->validationError(
				$this->name, _t('URLField.VALIDATION', 'Please enter a valid URL'), 'validation'
			);

			return false;
		}

		return true;
	}
}