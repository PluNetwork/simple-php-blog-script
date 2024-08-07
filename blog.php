<?php

include('includes/config.php');
include('includes/functions.php');

$disable_sidebar_search = false;

// Ensure REQUEST_URI is set
$request_uri = isset($_SERVER['REQUEST_URI']) ?  SITE_URL : "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Parse URL for parameters
$params = parse_url_parameters($request_uri);
$current_page = isset($params['page']) ? filter_var($params['page'], FILTER_VALIDATE_INT) : 1;
$search = isset($params['search']) ? htmlspecialchars(trim($params['search'])) : '';
$tag = isset($params['tag']) ? htmlspecialchars(urldecode(trim($params['tag']))) : '';
$category = isset($params['category']) ? htmlspecialchars(urldecode(trim($params['category']))) : '';

// Get paginated posts
$pagination_data = get_paginated_posts(GLOBAL_POST_PATTERN, $search, $tag, $category, $current_page, BLOG_POST_LIMIT);

// Generate meta data
$meta_data = generate_meta_data_blog($search, $tag, $category, $pagination_data['current_page']);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("includes/_head.php") ?>

    <?php if (!empty($search) || !empty($tag)) : ?>
        <meta name="robots" content="noindex, follow">
    <?php endif; ?>
</head>

<body>

    <?php include('includes/_navbar.php') ?>
    <!-- Blog Start -->
    <div class="container-fluid container-lg mx-auto gx-5 py-5">

        <div class="row g-5" id="blogContainer">
            <!-- Blog list Start -->
            <div class="col-lg-8">
                <div class="row g-3 g-xl-5" id="blogPosts">
                    <?php include('includes/_breadcrumb.php'); ?>
                    <section class="col-12 <?php echo (empty($pagination_data['paged_posts'])) ? 'd-block' : 'd-none'; ?>" id="no-posts">
                        <div class="alert alert-info text-center py-5 shadow-sm rounded" role="alert">
                            <i class="fab fa-searchengin display-4 mb-3"></i>
                            <h4 class="alert-heading fw-bold">No Posts Found</h4>
                            <p class="mb-1">We couldn't find any posts matching your criteria.</p>
                            <p class="mb-0">Try adjusting your search or exploring different tags.</p>
                        </div>
                    </section>
                    <?php if (isset($pagination_data['paged_posts']) && is_array($pagination_data['paged_posts'])) : ?>
                        <?php foreach ($pagination_data['paged_posts'] as $index => $post) :
                        ?>
                            <div class="col-md-6 blog-post" data-search="<?php echo htmlspecialchars($post['searchContent']); ?>">
                                <div class="blog-item bg-light rounded overflow-hidden" style="--bs-bg-opacity: 0.5;--bs-link-color-rgb: 9, 63, 127;">
                                    <div class="blog-img position-relative overflow-hidden">
                                        <div class="blog-img-inner" style="background-image: url('<?php echo SITE_URL; ?>/assets/blog/<?php echo htmlspecialchars($post['image']); ?>'); background-size: cover; background-position: center; height: 400px;">
                                        </div>
                                        <a class="position-absolute top-0 start-0 bg-primary text-white rounded-end mt-5 py-2 px-4 text-decoration-none" href="/blog/category/<?php echo urlencode(strtolower(str_replace(' ', '-', $post['category']))); ?>">
                                            <?php echo htmlspecialchars($post['category']); ?>
                                        </a>
                                    </div>
                                    <div class="p-4">
                                        <div class="d-flex mb-3">
                                            <small class="me-3"><i class="far fa-user text-primary me-2"></i><?php echo $post['author']; ?></small>
                                            <small><i class="far fa-calendar-alt text-primary me-2"></i><time datetime="<?php echo $post['date']; ?>"><?php echo date('F j, Y', strtotime($post['date'])); ?></time></small>
                                        </div>
                                        <h4 class="mb-3 blog-title"><?php echo htmlspecialchars($post['title']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($post['description'], 0, 100)); ?>...</p>
                                        <a class="text-uppercase text-decoration-none" href="<?php echo SITE_URL; ?>/blog/post/<?php echo $post['slug']; ?>">Read More
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Pagination -->
                <div class="row gy-3 my-auto">
                    <div class="col-12">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-lg m-0" id="pagination">
                                <?php if ($pagination_data['total_pages'] > 1) : ?>
                                    <li class="page-item <?php echo ($pagination_data['current_page'] <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-0 <?php echo ($pagination_data['current_page'] <= 1) ? 'disabled' : ''; ?>" href="<?php echo ($pagination_data['current_page'] > 1) ? '/blog/page/' . ($pagination_data['current_page'] - 1) . ($category ? '/category/' . urlencode($category) : '') . ($search ? '/search/' . urlencode($search) : '') . ($tag ? '/tag/' . urlencode($tag) : '') : '#'; ?>" aria-label="Previous" <?php echo ($pagination_data['current_page'] <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?> <?php echo ($pagination_data['current_page'] > 1) ? 'rel="prev"' : ''; ?>>
                                            <span aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                                        </a>
                                    </li>

                                    <?php for ($i = 1; $i <= $pagination_data['total_pages']; $i++) : ?>
                                        <li class="page-item <?php echo ($i == $pagination_data['current_page']) ? 'active' : ''; ?>">
                                            <a class="page-link" href="/blog/page/<?php echo $i; ?><?php echo $category ? '/category/' . urlencode($category) : ''; ?><?php echo $search ? '/search/' . urlencode($search) : ''; ?><?php echo $tag ? '/tag/' . urlencode($tag) : ''; ?>" <?php echo ($i == $pagination_data['current_page'] - 1) ? 'rel="prev"' : (($i == $pagination_data['current_page'] + 1) ? 'rel="next"' : ''); ?>>
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo ($pagination_data['current_page'] >= $pagination_data['total_pages']) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-0 <?php echo ($pagination_data['current_page'] >= $pagination_data['total_pages']) ? 'disabled' : ''; ?>" href="<?php echo ($pagination_data['current_page'] < $pagination_data['total_pages']) ? '/blog/page/' . ($pagination_data['current_page'] + 1) . ($category ? '/category/' . urlencode($category) : '') . ($search ? '/search/' . urlencode($search) : '') . ($tag ? '/tag/' . urlencode($tag) : '') : '#'; ?>" aria-label="Next" <?php echo ($pagination_data['current_page'] >= $pagination_data['total_pages']) ? 'tabindex="-1" aria-disabled="true"' : ''; ?> <?php echo ($pagination_data['current_page'] < $pagination_data['total_pages']) ? 'rel="next"' : ''; ?>>
                                            <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            <!-- Blog list End -->

            <?php include('includes/_blog_sidebar.php'); ?>
        </div>
    </div>
    <!-- Blog End -->

    <?php include('includes/_footer.php'); ?>
</body>