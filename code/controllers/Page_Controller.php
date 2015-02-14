<?php
class Page_Controller extends ContentController {
	/**
	 * Add 'rss' to the list of allowed action so the controller won't throw any errors for when an
	 * RSS feed is requested.
	 *
	 * @var array
	 */
	private static $allowed_actions = array(
		'rss',
	);

	/**
	 * Generate slugs based on ClassName, root Title, and page URLSegment for HTML body tag
	 *
	 * @return string
	 */
	public function BodyClass() {
		$classes = array();

		$page = Director::get_current_page();

		while($page) {
			$section = $page->URLSegment;
			$page = $page->Parent;
		}

		$classes[] = 'section-' . $section;

		$template = URLSegmentFilter::create()->filter($this->dataRecord->singular_name());

		$classes[] = 'template-' . $template;

		$classes[] = 'page-' . $this->dataRecord->URLSegment;

		return implode(' ', $classes);
	}

	/**
	 * @return HasManyList
	 */
	public function FeatureBanners() {
		if(!$this->dataRecord->FeatureBanners()->count()) {
			return false;
		}

		return $this->dataRecord->FeatureBanners();
	}

	/**
	 * @param boolean $includeHomepage
	 * @param boolean $showHidden
	 * @return ArrayList
	 */
	public function Lineage($includeHomepage = false, $showHidden = false) {
		$page = $this;
		$pages = new ArrayList();

		while($page) {
			if(!$showHidden || $page->ShowInMenus) {
				$pages->unshift($page);
			}

			$page = $page->getParent();
		}

		if($includeHomepage) {
			$homepage = static::get_by_link(Config::inst()->get('RootURLController', 'default_homepage_link'));

			if($homepage) {
				$pages->unshift($homepage);
			}
		}

		return $pages;
	}

	/**
	 * @return boolean
	 */
	public function IsHomepage() {
		return $this->owner->ClassName == 'Homepage';
	}
}
