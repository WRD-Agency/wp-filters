<?php
/**
 * Entrypoint for library.
 *
 * @package wrd\wp-filters
 */

require_once __DIR__ . '/filter_types/post-meta.php';
require_once __DIR__ . '/filter_types/sort.php';
require_once __DIR__ . '/filter_types/taxonomy.php';
require_once __DIR__ . '/filter.php';
require_once __DIR__ . '/loop.php';
require_once __DIR__ . '/rest.php';
