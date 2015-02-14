<?php
class ErrorPageDecorator extends DataExtension {
	/**
	 * @var boolean
	 */
	static $can_be_root = true;

	public function requireDefaultRecords() {
		parent::requireDefaultRecords();

		// Ensure that an assets path exists before we do any error page creation
		if(!file_exists(ASSETS_PATH)) {
			mkdir(ASSETS_PATH);
		}

		$defaultPages = array(
			array(
				'ErrorCode' => 404,
				'Title' => _t('ErrorPage.DEFAULTERRORPAGETITLE', 'Page not found'),
				'Content' => _t('ErrorPage.DEFAULTERRORPAGECONTENT', '<p>Sorry, it seems you were trying to access a page that doesn\'t exist.</p><p>Please check the spelling of the URL you were trying to access and try again.</p>')
			),
			array(
				'ErrorCode' => 500,
				'Title' => _t('ErrorPage.DEFAULTSERVERERRORPAGETITLE', 'Server error'),
				'Content' => _t('ErrorPage.DEFAULTSERVERERRORPAGECONTENT', '<p>Sorry, there was a problem with handling your request.</p>')
			)
		);
	
		foreach($defaultPages as $defaultData) {
			$code = $defaultData['ErrorCode'];
			$page = DataObject::get_one('ErrorPage', sprintf("ErrorPage.ErrorCode = '%s'", $code));
			$pageExists = ($page && $page->exists());
			$pagePath = ErrorPage::get_filepath_for_errorcode($code);

			if(!($pageExists && file_exists($pagePath))) {
				if(!$pageExists) {
					$page = new ErrorPage($defaultData);
					$page->ShowInMenus = false;
					$page->ShowInSearch = false;
					$page->AllowComments = false;
					$page->write();
					$page->publish('Stage', 'Live');
				}

				// Ensure a static error page is created from latest error page content
				$response = Director::test(Director::makeRelative($page->Link()));
				$written = null;
				
				if($fh = fopen($pagePath, 'w')) {
					$written = fwrite($fh, $response->getBody());
					fclose($fh);
				}

				if($written) {
					DB::alteration_message(sprintf('%s error page created', $code), 'created');
				} else {
					DB::alteration_message(sprintf('%s error page could not be created at %s. Please check permissions', $code, $pagePath), 'error');
				}
			}
		}
	}
}