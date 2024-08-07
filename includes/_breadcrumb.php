<?php

const POST_PAGE = 'post.php';
$current_page_href = basename($_SERVER['PHP_SELF']);

if ($current_page_href === POST_PAGE) : ?>
<section class="col-lg-8 col-12" id="breadcrumb">
    <?php
        $breadcrumb_items = [
            ['text' => 'Blog', 'link' => '/blog'],
            $post['title']
        ];
        echo generate_breadcrumb($breadcrumb_items);
        ?>
</section>
<?php else : ?>
<!-- Breadcrumb Start -->
<section class="col-12" id="breadcrumb">
    <?php
        $breadcrumb_items = ['Blog'];
        echo generate_breadcrumb($breadcrumb_items);
        ?>
</section>
<!-- Breadcrumb End -->
<?php endif; ?>