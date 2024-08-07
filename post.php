<?php

include('includes/config.php');
include('includes/functions.php');

$disable_sidebar_search = true;

$slug = $_GET['slug'] ?? '';
$postPath = str_replace('{slug}', $slug, GLOBAL_POST_FOLDER);
$post = get_post_details($postPath, $slug);

if (!$post) {
    // Post not found, redirect to /blog
    header("Location: /blog");
    exit;
}

$meta_data = generate_meta_data_blog_detail(
    $post['title'],
    $post['description'],
    $post['keywords'],
    $post['category'],
    $post['date'],
    $post['author'],
    $post['slug']
);

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
            <?php include('includes/_breadcrumb.php'); ?>
            <!-- Blog list Start -->
            <div class="col-lg-8">
                <!-- Blog Detail Start -->
                <article class="blog-detail mb-5">
                    <div class="blog-image-container position-relative mb-5">
                        <img class="img-fluid w-100 rounded" src="/assets/blog/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <div class="blog-meta position-absolute bottom-0 start-0 w-100 p-4 text-white">
                            <div class="blog-image-overlay position-absolute top-0 start-0 w-100 h-100"></div>
                            <div class="position-relative">
                                <span class="me-3"><i class="far fa-user me-2"></i><?php echo $post['author']; ?></span>
                                <span><i class="far fa-calendar-alt me-2"></i><time datetime="<?php echo $post['date']; ?>"><?php echo date('F j, Y', strtotime($post['date'])); ?></time></span>
                            </div>
                        </div>
                    </div>
                    <h1 class="mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="blog-content">
                        <?php echo $post['content']; ?>
                    </div>
                </article>
                <!-- Blog Detail End -->
            </div>
            <!-- Blog list End -->

            <?php include('includes/_blog_sidebar.php'); ?>
        </div>
    </div>
    <!-- Blog End -->

    <?php include('includes/_footer.php'); ?>
</body>