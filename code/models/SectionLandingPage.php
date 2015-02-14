<?php
class SectionLandingPage extends Page {
	/**
	 * @var boolean
	 */
	private static $can_be_root = true;

	/**
	 * @var array
	 */
	private static $defaults = array(
		'ShowInMenus' => true,
		'ShowInSearch' => true,
		'AllowComments' => false
	);
}