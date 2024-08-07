<?php

// Set the current working directory to the script's directory
chdir(__DIR__);

define('INDEX_POST_LIMIT', 3);
define('BLOG_POST_LIMIT', 6);
define('GLOBAL_POST_FOLDER', __DIR__ . '/../posts/{slug}.html');
define('GLOBAL_POST_PATTERN', __DIR__ . '/../posts/*.html');

define('SITE_NAME', 'Simple PHP Blog Script');
define('BASE_TITLE', 'Create a blog in less than 5 minutes');
define('SITE_URL', 'http://127.0.0.1');
define('SITE_DESCRIPTION', 'Site Description');

// Blog categories
$blog_categories = [
    'Blog Category 1',
    'Blog Category 2',
    'Blog Category 3',
    'Blog Category 4',
    'Blog Category 5',
    'Blog Category 6'
];

// Export the categories
if (!defined('BLOG_CATEGORIES')) {
    define('BLOG_CATEGORIES', $blog_categories);
}
