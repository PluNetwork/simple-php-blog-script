// BlogModule.js
const BlogModule = (function () {
    let currentPage = 1;
    let currentSearch = '';
    let currentTag = '';
    const $postsContainer = $('#blogPosts');

    function getErrorMessageTemplate(message) {
        return `
            <div class="alert alert-danger" role="alert" aria-live="assertive">
                <h4 class="alert-heading">Error</h4>
                <p>${message}</p>
            </div>
        `;
    }

    function updatePosts(posts) {
        const $breadcrumb = $postsContainer.find('section#breadcrumb').detach();
        const $post_message = $postsContainer.find('#no-posts').detach();
        $postsContainer.empty().prepend($breadcrumb).prepend($post_message);

        posts.forEach((post) => {
            const categoryUrl = encodeURIComponent(post.category.toLowerCase().replace(/ /g, '-'));
            const postHtml = `
                <div class="col-md-6 blog-post" data-search="${post.searchContent}">
                    <div class="blog-item bg-light rounded overflow-hidden" style="--bs-bg-opacity: 0.5;">
                        <div class="blog-img position-relative overflow-hidden">
                            <div class="blog-img-inner"
                                style="background-image: url('/assets/blog/${post.image}'); background-size: cover; background-position: center; height: 400px;">
                            </div>
                            <a class="position-absolute top-0 start-0 bg-primary text-white rounded-end mt-5 py-2 px-4 text-decoration-none"
                                href="/blog/category/${categoryUrl}">${post.category}</a>
                        </div>
                        <div class="p-4">
                            <div class="d-flex mb-3">
                                <small class="me-3"><i
                                    class="far fa-user text-primary me-2"></i>${post.author}</small>
                                <small><i class="far fa-calendar-alt text-primary me-2"></i><time
                                    datetime="${post.date}">${post.date}</time></small>
                            </div>
                            <h4 class="mb-3 blog-title">${post.title}</h4>
                            <p>${post.description.substring(0, 100)}...</p>
                            <a class="text-uppercase text-decoration-none"
                                href="/blog/post/${post.slug}">Read More
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            `;
            $postsContainer.append(postHtml);
        });
    }

    function updatePagination(totalPages) {
        const $pagination = $('#pagination');

        if (totalPages <= 1) {
            $pagination.empty();
            return;
        }

        let paginationHtml = '';

        paginationHtml += `
            <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                <a class="page-link rounded-0" href="#" data-page="${currentPage - 1}" data-search="${currentSearch}" data-tag="${currentTag}" aria-label="Previous">
                    <span aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                </a>
            </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            paginationHtml += `
                <li class="page-item ${i === parseInt(currentPage) ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}" data-search="${currentSearch}" data-tag="${currentTag}">${i}</a>
                </li>
            `;
        }

        paginationHtml += `
            <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                <a class="page-link rounded-0" href="#" data-page="${currentPage + 1}" data-search="${currentSearch}" data-tag="${currentTag}" aria-label="Next">
                    <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                </a>
            </li>
        `;

        $pagination.html(paginationHtml);

        $pagination.find('a.page-link').on('click', function (e) {
            e.preventDefault();
            var page = $(this).data('page');
            loadPosts(page);
        });
    }

    function loadPosts(page, retryCount = 0) {
        let pageCount = page ? parseInt(page) : currentPage;

        $.ajax({
            url: '/includes/blogRequests',
            method: 'GET',
            data: { page: pageCount, search: currentSearch, tag: currentTag },
            dataType: 'json',
            success: handleSuccess,
            error: handleError
        });

        function handleSuccess(response) {
            try {
                const $breadcrumb = $postsContainer.find('section#breadcrumb').detach();
                const $posts_message = $postsContainer.find('#no-posts').detach();

                $postsContainer.empty().append($breadcrumb);

                if (response.posts.length > 0) {
                    updatePosts(response.posts);
                    currentPage = response.current_page;
                    $posts_message.removeClass('d-block').addClass('d-none');
                    updatePagination(response.total_pages);
                } else {
                    $posts_message.removeClass('d-none').addClass('d-block');
                    updatePagination(0);
                }

                $postsContainer.append($posts_message);
                updateURL();
                $('#searchInput').val(currentSearch);
                updateMetaTags(response.meta);
                scrollToTop();

            } catch (error) {
                console.error('Error processing response:', error);
                displayErrorMessage('An error occurred while processing the data.');
            }
        }

        function handleError(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);

            if (retryCount < 3) {
                setTimeout(() => {
                    loadPosts(retryCount + 1);
                }, 1000 * (retryCount + 1));
            } else {
                displayErrorMessage('We\'re having trouble loading the posts. Please check your internet connection and try again.');
            }
        }

        function displayErrorMessage(message) {
            $('#blogPosts').html(getErrorMessageTemplate(message));
            updatePagination(0);
        }
    }

    function updateMetaTags(meta) {
        document.title = meta.title;
        $('meta[name="description"]').attr('content', meta.description);
        $('link[rel="canonical"]').attr('href', meta.url);
    }

    function scrollToTop() {
        $('html, body').animate({
            scrollTop: $('#blogContainer').offset().top - 100
        }, 500);
    }

    function updateURL() {
        let url = '/blog';
        if (currentPage > 1 || currentSearch || currentTag) {
            url += `/page/${currentPage}`;
            if (currentSearch) {
                url += `/search/${encodeURIComponent(currentSearch)}`;
            }
            if (currentTag) {
                url += `/tag/${encodeURIComponent(currentTag)}`;
            }
        }

        if (window.location.pathname !== url) {
            history.pushState({ page: currentPage, search: currentSearch, tag: currentTag }, '', url);
        }
    }

    return {
        loadPosts,
        updateURL,
        updatePosts,
        updatePagination,
        updateMetaTags,
        scrollToTop,
        get currentPage() { return currentPage; },
        set currentPage(value) { currentPage = value; },
        get currentSearch() { return currentSearch; },
        set currentSearch(value) { currentSearch = value; },
        get currentTag() { return currentTag; },
        set currentTag(value) { currentTag = value; }
    };
})();

export default BlogModule;
