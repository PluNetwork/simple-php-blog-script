<?php

/**
 * Parse URL for page, search, and tag parameters.
 *
 * @param string $uri
 * @return array
 */
function parse_url_parameters($uri)
{
    $url_parts = explode('/', trim($uri, '/'));
    $params = [
        'page' => 1,
        'category' => '',
        'search' => '',
        'tag' => ''
    ];

    foreach ($url_parts as $key => $part) {
        if ($part === 'page' && isset($url_parts[$key + 1])) {
            $params['page'] = max(1, intval($url_parts[$key + 1]));
        }
        if ($part === 'category' && isset($url_parts[$key + 1])) {
            $params['category'] = urldecode($url_parts[$key + 1]);
        }
        if ($part === 'search' && isset($url_parts[$key + 1])) {
            $params['search'] = urldecode($url_parts[$key + 1]);
        }
        if ($part === 'tag' && isset($url_parts[$key + 1])) {
            $params['tag'] = urldecode($url_parts[$key + 1]);
        }
    }

    return $params;
}


/**
 * Get paginated posts based on the current page, search query, and tag.
 *
 * @param array $posts
 * @param string $search
 * @param string $tag
 * @param string $category
 * @param int $current_page
 * @param int $posts_per_page
 * @return array
 */
function get_paginated_posts($posts, $search, $tag, $category, $current_page, $posts_per_page)
{
    $filtered_posts = get_filtered_posts($posts, $category, $search, $tag);
    $total_filtered_posts = count($filtered_posts);
    $total_pages = ceil($total_filtered_posts / $posts_per_page);
    $current_page = min($current_page, $total_pages);
    $offset = ($current_page - 1) * $posts_per_page;
    $paged_posts = array_slice($filtered_posts, $offset, $posts_per_page);

    return [
        'paged_posts' => $paged_posts,
        'total_filtered_posts' => $total_filtered_posts,
        'total_pages' => $total_pages,
        'current_page' => $current_page
    ];
}


/**
 * Get filtered posts based on search query and tag.
 *
 * @param string $post_path Path pattern to the post files.
 * @param string $category Category to filter posts.
 * @param string $search Search query to filter posts.
 * @param string $tag Tag to filter posts.
 * @return array Filtered posts.
 */
function get_filtered_posts($post_path, $category, $search, $tag = '')
{
    $posts = glob($post_path);
    $filtered_posts = [];

    foreach ($posts as $post) {
        if (!is_readable($post)) {
            continue; // Skip unreadable files
        }

        $content = file_get_contents($post);
        $meta = extract_meta_data($content);

        if ($meta) {
            $searchContent = strtolower(implode(' ', [$meta['title'], $meta['description'], $meta['keywords'], $meta['category'], implode(' ', $meta['tags'])]));
            $category_url = strtolower(str_replace(' ', '-', $meta['category']));

            if ((empty($search) || stripos($searchContent, $search) !== false) &&
                (empty($tag) || in_array(strtolower($tag), array_map('strtolower', $meta['tags']))) &&
                (empty($category) || $category_url === $category)
            ) {
                $filtered_posts[] = [
                    'title' => $meta['title'],
                    'description' => $meta['description'],
                    'image' => $meta['image'],
                    'category' => $meta['category'],
                    'date' => $meta['date'],
                    'author' => $meta['author'],
                    'slug' => basename($post, '.html'),
                    'searchContent' => $searchContent,
                    'tags' => $meta['tags']
                ];
            }
        }
    }

    // Sort the filtered posts by date, most recent first
    usort($filtered_posts, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    return $filtered_posts;
}



/**
 * Generate meta data for the blog.
 *
 * @param string $search Search query.
 * @param string $tag Tag query.
 * @param string $category Category query.
 * @param int $page Current page number.
 * @return array Meta data including title, description, canonical URL, and schema JSON.
 */
function generate_meta_data_blog($search = '', $tag = '', $category = '', $page = 1)
{
    $site_name = SITE_NAME;
    $base_title = BASE_TITLE;
    $base_description = SITE_DESCRIPTION;

    $title = "{$site_name} Blog | {$base_title}";
    $description = $base_description;

    if (!empty($search) && !empty($tag)) {
        $sanitized_search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
        $sanitized_tag = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
        $title = "Posts tagged '{$sanitized_tag}' and containing '{$sanitized_search}' | {$site_name} Blog";
        $description = "Explore posts tagged with '{$sanitized_tag}' and containing '{$sanitized_search}' on the {$site_name} Blog.";
    } elseif (!empty($search)) {
        $sanitized_search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
        $title = "Search results for \"" . ucfirst($sanitized_search) . "\" | {$site_name} Blog";
        $description = "Explore posts about '{$sanitized_search}' on the {$site_name} Blog.";
    } elseif (!empty($tag)) {
        $sanitized_tag = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
        $title = ucfirst($sanitized_tag) . " Posts | {$site_name} Blog";
        $description = "Discover posts tagged with '{$sanitized_tag}' on the {$site_name} Blog.";
    } elseif (!empty($category)) {
        $category_name = ucwords(str_replace('-', ' ', $category));
        $sanitized_category = htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8');
        $title = $sanitized_category . " Category | {$site_name} Blog";
        $description = "Read posts in the '{$sanitized_category}' category on the {$site_name} Blog.";
    }

    if ($page > 1) {
        $title .= " - Page {$page}";
        $description .= " Continue exploring {$site_name} Blog - Page {$page}.";
    }

    // Ensure title length is within 60-70 characters
    if (strlen($title) > 70) {
        $title = substr($title, 0, 67) . '...';
    }

    // Ensure description length is within 150-160 characters
    if (strlen($description) > 160) {
        $description = substr($description, 0, 157) . '...';
    }

    // Generate canonical URL
    $canonical_url = SITE_URL . "/blog";

    if (!empty($search) && !empty($tag)) {
        $canonical_url .= "/search/" . urlencode($search) . "/tag/" . urlencode($tag);
    } elseif (!empty($search)) {
        $canonical_url .= "/search/" . urlencode($search);
    } elseif (!empty($tag)) {
        $canonical_url .= "/tag/" . urlencode($tag);
    } elseif (!empty($category)) {
        $canonical_url .= "/category/" . urlencode($category);
    }

    if ($page > 1) {
        $canonical_url .= "/page/{$page}";
    }

    return [
        'title' => $title,
        'description' => $description,
        'canonical_url' => $canonical_url,
        'schema_json' => json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => "{$site_name} Blog",
            'description' => $base_description,
            'url' => $canonical_url,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical_url
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $site_name,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => SITE_URL . '/assets/logo.png'
                ]
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    ];
}


/**
 * Generate meta data for a blog post detail page.
 *
 * @param string $post_title The title of the blog post.
 * @param string $post_description The description of the blog post.
 * @param array|string $post_keywords Keywords related to the blog post.
 * @param string $post_date The publication date of the blog post.
 * @param string $post_author The author of the blog post.
 * @param string $post_slug The slug of the blog post.
 * @return array Meta data for the blog post detail page.
 */
function generate_meta_data_blog_detail($post_title, $post_description, $post_keywords, $post_author, $post_date, $post_slug)
{
    $site_name = SITE_NAME;
    $base_url = SITE_URL;

    // Optimize title for SEO (max 60 characters)
    $title = substr($post_title, 0, 60);

    // Optimize description (between 150-160 characters)
    $description = strlen($post_description) > 160 ? substr($post_description, 0, 157) . '...' : $post_description;

    // Ensure keywords are in the correct format (limit to top 7 keywords)
    $keywords = is_array($post_keywords) ? array_slice($post_keywords, 0, 7) : explode(',', $post_keywords);
    $keywords = array_slice(array_map('trim', $keywords), 0, 7);
    $keywords_string = implode(', ', $keywords);

    // Generate canonical URL
    $canonical_url = "{$base_url}/blog/post/{$post_slug}";

    // Format date for schema
    $formatted_date = date('c', strtotime($post_date));

    return [
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords_string,
        'canonical_url' => $canonical_url,
        'schema_json' => json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post_title,
            'description' => $description,
            'keywords' => $keywords_string,
            'datePublished' => $formatted_date,
            'dateModified' => $formatted_date,
            'author' => [
                '@type' => 'Organization',
                'name' => $post_author
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $site_name,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => "{$base_url}/assets/logo.png"
                ]
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical_url
            ],
            'image' => [
                '@type' => 'ImageObject',
                'url' => "{$base_url}/assets/blog/default-image.jpg"
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    ];
}


/**
 * Get all unique tags from the posts.
 *
 * @param string $post_path Path pattern to the post files.
 * @return array Unique tags.
 */
function get_all_tags($post_path)
{
    $posts = glob($post_path);
    $all_tags = [];

    foreach ($posts as $post) {
        if (!is_readable($post)) {
            continue; // Skip unreadable files
        }

        $content = file_get_contents($post);
        $meta = extract_meta_data($content);

        if ($meta && isset($meta['tags'])) {
            $all_tags = array_merge($all_tags, $meta['tags']);
        }
    }

    return array_unique(array_filter($all_tags));
}


/**
 * Get the most recent posts based on a limit.
 *
 * @param string $post_path Path pattern to the post files.
 * @param int $limit Number of recent posts to retrieve.
 * @return array Recent posts.
 */
function get_recent_posts($post_path, $limit)
{
    $posts = glob($post_path);
    $post_meta = [];

    // Extract metadata and store with posts
    foreach ($posts as $post) {
        if (!is_readable($post)) {
            continue; // Skip unreadable files
        }

        $content = file_get_contents($post);
        $meta = extract_meta_data($content);

        if ($meta) {
            $post_meta[] = [
                'file' => $post,
                'title' => $meta['title'],
                'image' => $meta['image'],
                'slug' => basename($post, '.html'),
                'date' => $meta['date'],
            ];
        }
    }

    // Sort posts by date in descending order
    usort($post_meta, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // Get the most recent posts based on the limit
    return array_slice($post_meta, 0, $limit);
}


/**
 * Get post details based on the provided slug.
 *
 * @param string $post_file The file path of the post.
 * @param string $slug The slug of the post.
 * @return array|null An array containing post details or null if not found.
 */
function get_post_details($post_file, $slug)
{
    if (!file_exists($post_file)) {
        return null; // Post not found
    }

    $content = file_get_contents($post_file);
    $meta = extract_meta_data($content);

    // Extract style
    preg_match('/<style>(.*?)<\/style>/s', $content, $style_match);
    if (isset($style_match[1])) {
        $meta['style'] = trim($style_match[1]);
    }

    $title = $meta['title'] ?? 'Untitled';
    $description = $meta['description'] ?? '';
    $keywords = $meta['keywords'] ?? '';
    $style = $meta['style'] ?? '';
    $image = htmlspecialchars($meta['image'] ?? 'default-image.jpg', ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($meta['category'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8');
    $author = $meta['author'] ?? 'Admin';
    $date = htmlspecialchars($meta['date'] ?? date("Y-m-d"), ENT_QUOTES, 'UTF-8');
    $tags = isset($meta['tags']) ? array_map('htmlspecialchars', array_map('trim', $meta['tags'])) : [];

    // Remove META information and style tags from content
    $content = preg_replace('/(<!--META.*?-->)|(<style>.*?<\/style>)/s', '', $content);

    return [
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'image' => $image,
        'category' => $category,
        'date' => $date,
        'author' => $author,
        'tags' => $tags,
        'slug' => $slug,
        'content' => trim($content),
        'style' => $style
    ];
}


/**
 * Extract meta data from the content of a post.
 *
 * @param string $content Content of the post file.
 * @return array|null Extracted meta data or null if no meta data found.
 */
function extract_meta_data($content)
{
    preg_match('/<!--META\s*(.*?)\s*-->/s', $content, $meta_match);

    if (!isset($meta_match[1])) {
        return null; // No meta data found
    }

    $meta_lines = explode("\n", $meta_match[1]);
    $meta = [];

    foreach ($meta_lines as $line) {
        $line = trim($line);
        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $meta[strtolower(trim($key))] = trim($value);
        }
    }

    return [
        'title' => $meta['title'] ?? 'Untitled',
        'description' => $meta['description'] ?? '',
        'keywords' => $meta['keywords'] ?? '',
        'image' => $meta['image'] ?? 'default-image.jpg',
        'category' => $meta['category'] ?? 'Uncategorized',
        'date' => $meta['date'] ?? date("Y-m-d"),
        'author' => $meta['author'] ?? 'Admin',
        'tags' => isset($meta['tags']) ? array_map('trim', explode(',', $meta['tags'])) : []
    ];
}

/**
 * Generate a breadcrumb navigation.
 *
 * @param array $items Array of breadcrumb items, where each item is an associative array with 'link' and 'text' keys.
 * @return string The generated HTML for the breadcrumb.
 */

function generate_breadcrumb($items)
{
    $output = '<nav aria-label="breadcrumb" class="bg-light bg-opacity-50 px-3 rounded-3 breadcrumb-font">';
    $output .= '<ol class="breadcrumb mb-0">';
    $output .= '<li class="breadcrumb-item"><a href="/" class="text-secondary text-opacity-75 text-decoration-none fw-medium"><i class="fas fa-home"></i></a></li>';

    foreach ($items as $index => $item) {
        if ($index === array_key_last($items)) {
            $output .= '<li class="breadcrumb-item active text-dark fw-medium" aria-current="page">' . htmlspecialchars($item) . '</li>';
        } else {
            $output .= '<li class="breadcrumb-item"><a href="' . $item['link'] . '" class="text-secondary text-decoration-none fw-lighter">' . htmlspecialchars($item['text']) . '</a></li>';
        }
    }

    $output .= '</ol></nav>';
    return $output;
}
