<?php

include('includes/config.php');
include('includes/functions.php');

// Generate meta data
$meta_data = generate_meta_data_blog();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("includes/_head.php") ?>
</head>

<body>
    <?php include('includes/_navbar.php') ?>


    <div id="main-content">
        <!-- Blog Start -->
        <div class="container-fluid py-5">
            <div class="container py-5">
                <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 800px;">
                    <h5 class="fw-bold text-primary text-uppercase">Latest Blog</h5>
                    <h1 class="mb-0">Latest Blog Releases</h1>
                </div>
                <div class="row g-5">
                    <?php
                    $posts = get_filtered_posts(GLOBAL_POST_PATTERN, '', '');
                    $count = 0;
                    foreach ($posts as $post) :
                        if ($count >= INDEX_POST_LIMIT) break; // Limit to 3 posts
                    ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-12">
                        <div class="blog-item bg-light rounded overflow-hidden"
                            style="--bs-bg-opacity: 0.5;--bs-link-color-rgb: 9, 63, 127;">
                            <div class="blog-img position-relative overflow-hidden">
                                <div class="blog-img-inner"
                                    style="background-image: url('<?php echo SITE_URL; ?>/assets/blog/<?php echo htmlspecialchars($post['image']); ?>'); background-size: cover; background-position: center; height: 400px;">
                                </div>
                                <a class="position-absolute top-0 start-0 bg-primary text-white rounded-end mt-5 py-2 px-4 text-decoration-none"
                                    href="/blog/category/<?php echo urlencode(strtolower(str_replace(' ', '-', $post['category']))); ?>">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </a>
                            </div>
                            <div class="p-4">
                                <div class="d-flex mb-3">
                                    <small class="me-3"><i
                                            class="far fa-user text-primary me-2"></i><?php echo $post['author']; ?></small>
                                    <small><i class="far fa-calendar-alt text-primary me-2"></i><time
                                            datetime="<?php echo $post['date']; ?>"><?php echo date('F j, Y', strtotime($post['date'])); ?></time></small>
                                </div>
                                <h4 class="mb-3 blog-title"><?php echo htmlspecialchars($post['title']); ?></h4>
                                <p><?php echo htmlspecialchars(substr($post['description'], 0, 100)); ?>...</p>
                                <a class="text-uppercase text-decoration-none"
                                    href="<?php echo SITE_URL; ?>/blog/post/<?php echo $post['slug']; ?>">Read More
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                    <?php
                        $count++;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/_footer.php'); ?>
</body>

</html>