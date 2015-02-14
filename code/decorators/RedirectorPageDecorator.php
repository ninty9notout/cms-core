<?php
class RedirectorPageDecorator extends DataExtension {
	/**
	 * @var boolean
	 */
	static $can_be_root = false;

	/**
	 * @var array
	 */
	private static $defaults = array(
		'ShowInMenus' => true,
		'ShowInSearch' => false,
		'AllowComments' => false
	);
}