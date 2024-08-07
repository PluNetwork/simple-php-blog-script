<?php
header('Content-Type: application/json');
require_once('functions.php');
require_once('config.php');


$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tag = isset($_GET['tag']) && $_GET['tag'] !== 'undefined' ? urldecode(trim($_GET['tag'])) : '';

$filtered_posts = get_filtered_posts(GLOBAL_POST_PATTERN, '', $search, $tag);
$total_posts = count($filtered_posts);
$total_pages = ceil($total_posts / BLOG_POST_LIMIT);

if ($total_posts > 0) {
    $offset = ($page - 1) * BLOG_POST_LIMIT;
    $paged_posts = array_slice($filtered_posts, $offset, BLOG_POST_LIMIT);
} else {
    $paged_posts = [];
    $total_pages = 0;
}

$meta_data = generate_meta_data_blog($search, $tag, $page);

// Get the current URL
$current_url = isset($_SERVER['REQUEST_URI']) ?  SITE_URL : "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Prepare the response
$response = [
    'posts' => $paged_posts,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'meta' => [
        'title' => $meta_data['title'],
        'description' => $meta_data['description'],
        'url' => $current_url
    ]
];

echo json_encode($response);
