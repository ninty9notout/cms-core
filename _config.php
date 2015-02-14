<?php
/**
 * - CORE_DIR: Path relative to webroot, e.g. "core"
 * - CORE_PATH: Absolute filepath, e.g. "/var/www/my-webroot/core"
 */
define('CORE_DIR', 'core');
define('CORE_PATH', BASE_PATH . '/' . CORE_DIR);

if(Director::isDev()) {
	SSViewer::flush_template_cache();
}

Requirements::set_suffix_requirements(false);
Requirements::set_combined_files_enabled(false);

// FulltextSearchable::enable();