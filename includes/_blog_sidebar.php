                <!-- Sidebar Start -->
                <div class="col-lg-4">
                    <!-- Search Form Start -->
                    <?php if (!isset($disable_sidebar_search) || $disable_sidebar_search !== true) : ?>
                        <div class="mb-5">
                            <form role="search" aria-label="Blog search">
                                <div class="input-group">
                                    <label for="searchInput" class="visually-hidden">Search blog posts</label>
                                    <input type="text" class="form-control p-3" id="searchInput" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" aria-label="Search blog posts">
                                    <button class="btn btn-primary px-4" id="searchButton" type="submit" aria-label="Submit search">
                                        <i class="fas fa-search" aria-hidden="true"></i>
                                        <span class="visually-hidden">Search</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                    <!-- Search Form End -->

                    <!-- Category Start -->
                    <div class="mb-5">
                        <div class="section-title section-title-sm position-relative pb-3 mb-4">
                            <h3 class="mb-0">Categories</h3>
                        </div>
                        <div class="link-animated d-flex flex-column justify-content-start">
                            <?php foreach (BLOG_CATEGORIES as $category) : ?>
                                <a class="h5 fw-semi-bold bg-light rounded text-decoration-none p-3 mb-2" style="--bs-bg-opacity: 0.5;" href="/blog/category/<?php echo urlencode(strtolower(str_replace(' ', '-', $category))); ?>">
                                    <i class="bi bi-arrow-right me-2"></i><?php echo htmlspecialchars($category); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Category End -->

                    <!-- Recent Post Start -->
                    <div class="mb-5">
                        <div class="section-title section-title-sm position-relative pb-3 mb-4">
                            <h3 class="mb-0">Recent Posts</h3>
                        </div>
                        <?php
                        $recent_posts = get_recent_posts(GLOBAL_POST_PATTERN, BLOG_POST_LIMIT);
                        foreach ($recent_posts as $post) :
                        ?>
                            <div class="d-flex rounded overflow-hidden mb-3">
                                <img class="img-fluid" src="<?php echo SITE_URL; ?>/assets/blog/<?php echo htmlspecialchars($post['image']); ?>" style="width: 100px; height: 100px; object-fit: cover;" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <a href="<?php echo SITE_URL; ?>/blog/post/<?php echo $post['slug']; ?>" class="h5 fw-semi-bold d-flex align-items-center bg-light px-3 mb-0 text-decoration-none" style="--bs-bg-opacity: 0.5;">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Recent Post End -->

                    <!-- Tags Start -->
                    <div class="mb-5">
                        <div class="section-title section-title-sm position-relative pb-3 mb-4">
                            <h3 class="mb-0">Tags</h3>
                        </div>
                        <div class="d-flex flex-wrap m-n1">
                            <?php
                            $all_tags = get_all_tags(GLOBAL_POST_PATTERN);
                            foreach ($all_tags as $tag_name) :
                                $encoded_tag = urlencode($tag_name);
                                $is_active = isset($tag) && ($tag_name === $tag);
                            ?>
                                <?php if (isset($tag)) : ?>
                                    <button class="btn <?php echo $is_active ? 'btn-primary' : 'btn-light'; ?> m-1 tag-link" data-tag="<?php echo $encoded_tag; ?>" <?php echo $is_active ? 'disabled' : ''; ?> style="--bs-btn-disabled-opacity:0.8;">
                                        <?php echo htmlspecialchars($tag_name); ?>
                                    </button>
                                <?php else : ?>
                                    <a href="/blog/tag/<?php echo $encoded_tag; ?>" class="btn btn-light m-1 tag-link" data-tag="<?php echo $encoded_tag; ?>">
                                        <?php echo htmlspecialchars($tag_name); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Tags End -->

                </div>
                <!-- Sidebar End -->