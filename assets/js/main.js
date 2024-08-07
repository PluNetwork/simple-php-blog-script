
import BlogModule from './BlogModule.js';

// ---------Debounce-function-----------
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function updateContainerSelector(element, containerSelector) {
    const navbarContent = document.getElementById('navbarContent');

    const update = debounce(() => {
        const itemRect = element.getBoundingClientRect();
        const navbarRect = navbarContent.getBoundingClientRect();

        containerSelector.style.top = `${itemRect.top - navbarRect.top}px`;
        containerSelector.style.left = `${element.offsetLeft}px`;
        containerSelector.style.height = `${itemRect.height}px`;
        containerSelector.style.width = `${itemRect.width}px`;
    }, 10);

    const resizeObserver = new ResizeObserver(update);
    resizeObserver.observe(element);

    update();
}

function bindNavbarEvents(tabsNewAnim, containerSelector) {
    tabsNewAnim.addEventListener("click", function (event) {
        const targetLi = event.target.closest('li');
        if (targetLi && tabsNewAnim.contains(targetLi)) {
            tabsNewAnim.querySelectorAll('li').forEach(li => li.classList.remove("active"));
            targetLi.classList.add('active');
            updateContainerSelector(targetLi, containerSelector);
        }
    });
}

function containerAnimation() {
    const tabsNewAnim = document.getElementById('navbarContent');
    if (!tabsNewAnim) {
        console.warn('Navbar content element not found');
        return;
    }

    const activeItemNewAnim = tabsNewAnim.querySelector('.active');
    if (!activeItemNewAnim) {
        console.warn('Active item not found in navbar');
        return;
    }

    const containerSelector = document.querySelector(".containerSelector");
    if (!containerSelector) {
        console.warn('Container selector element not found');
        return;
    }

    updateContainerSelector(activeItemNewAnim, containerSelector);
    bindNavbarEvents(tabsNewAnim, containerSelector);
}


function updateActiveNavItem() {
    const path = window.location.pathname;
    const navItems = document.querySelectorAll('#navbarContent ul li a');

    navItems.forEach(item => item.parentElement.classList.remove('active'));

    if (path === '/') {
        document.querySelector('#navbarContent ul li a[href="/"]').parentElement.classList.add('active');
    } else {
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (path.startsWith(href) && href !== '/') {
                item.parentElement.classList.add('active');
                return;
            }
        });
    }

    if (path.startsWith('/blog')) {
        document.querySelector('#navbarContent ul li a[href="/blog"]').parentElement.classList.add('active');
    }
}

function performAction(page = 1, search = '', tag = '') {
    try {
        BlogModule.currentPage = page;
        BlogModule.currentSearch = search;
        BlogModule.currentTag = tag;
        BlogModule.loadPosts();
        BlogModule.updateURL();
    } catch (error) {
        console.error('Error in performAction:', error);
    }
}


function initialLoad() {
    try {
        const path = window.location.pathname;
        const parts = path.split('/').filter(Boolean);
        const pageIndex = parts.indexOf('page');
        const searchIndex = parts.indexOf('search');
        const tagIndex = parts.indexOf('tag');

        const page = pageIndex !== -1 ? parts[pageIndex + 1] : '1';
        const search = searchIndex !== -1 ? decodeURIComponent(parts[searchIndex + 1]) : '';
        const tag = tagIndex !== -1 ? decodeURIComponent(parts[tagIndex + 1]) : '';

        console.log({ page, search, tag });

        // Update BlogModule states if any
        BlogModule.currentPage = parseInt(page);
        BlogModule.currentSearch = search;
        BlogModule.currentTag = tag;


    } catch (error) {
        console.error('Error in initialLoad:', error);
    }
}

function setupSearchListener(inputElement, buttonElement, callback) {
    if (buttonElement) {
        buttonElement.addEventListener('click', (e) => {
            e.preventDefault();
            callback(inputElement.value.trim());
        });
    }

    inputElement.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            callback(inputElement.value.trim());
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    updateActiveNavItem();
    setTimeout(containerAnimation);

    window.addEventListener('resize', debounce(containerAnimation, 250));

    document.querySelector(".navbar-toggler").addEventListener("click", function () {
        const navbarCollapse = document.querySelector(".navbar-collapse");
        const computedStyle = window.getComputedStyle(navbarCollapse);

        if (computedStyle.display === 'none') {
            navbarCollapse.style.display = 'block';
        } else {
            navbarCollapse.style.display = 'none';
        }

        setTimeout(containerAnimation);
    });

    const searchButton = document.getElementById('searchButton');
    const searchInput = document.getElementById('searchInput');
    const tagButtons = document.querySelectorAll('.tag-link');

    if (searchInput) {
        setupSearchListener(searchInput, searchButton, (searchValue) => {
            performAction(1, searchValue, BlogModule.currentTag);
        });
    }

    if (tagButtons.length > 0) {
        tagButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Remove active class from all buttons
                tagButtons.forEach(btn => {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-light');
                    btn.disabled = false;
                });

                // Add active class to clicked button
                this.classList.remove('btn-light');
                this.classList.add('btn-primary');
                this.disabled = true;

                performAction(1, BlogModule.currentSearch, this.getAttribute('data-tag'));
            });
        });
    }
    // Initial load
    initialLoad();
});

window.addEventListener('popstate', function (event) {
    if (event.state) {
        performAction(event.state.page, event.state.search || '', event.state.tag || '');
    } else {
        performAction(1, '', '');
    }
});
