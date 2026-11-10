/*
 * CCST Portal: skeleton loading frames.
 *
 * Load this in <head> WITHOUT defer, together with /css/skeleton.css:
 *   <link rel="stylesheet" href="/css/skeleton.css">
 *   <script src="/js/ui.js"></script>
 *
 * 1. Waiting for the next page: when someone clicks a link or sends a form, a skeleton of the
 *    page they are going to appears at once and stays until the server answers.
 * 2. A page opening: the real page is drawn as skeleton bars for a moment, then settles.
 *
 * To skip the skeleton for one link or form, add the attribute data-no-skeleton to it.
 * There is no inline styling: the bars are sized by classes in skeleton.css.
 */
(function () {
    'use strict';

    var root = document.documentElement;
    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

    // Going back or forward restores the page from the browser's cache, so skip the open animation
    var navEntry = (window.performance && performance.getEntriesByType) ? performance.getEntriesByType('navigation')[0] : null;
    var cameFromHistory = !!(navEntry && navEntry.type === 'back_forward');

    var OPEN_MIN_MS = 350;      // how long the opening skeleton stays at least
    var OPEN_MAX_MS = 3500;     // safety: never stay in skeleton longer than this
    var NAV_MAX_MS = 20000;     // safety: remove the waiting skeleton if the page never changes

    var navTimer = null;

    /* ------------------------------------------------------------
       2. A page opening
       ------------------------------------------------------------ */
    var SKIP_TAGS = {
        SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, OPTION: 1, OPTGROUP: 1, SELECT: 1, TEXTAREA: 1,
        INPUT: 1, SVG: 1, TITLE: 1, HEAD: 1, META: 1, LINK: 1, CODE: 1
    };

    function markSkeletonParts() {
        var all = document.body ? document.body.getElementsByTagName('*') : [];
        var i, j, el, nodes;

        for (i = 0; i < all.length; i++) {
            el = all[i];
            if (SKIP_TAGS[el.tagName.toUpperCase()]) { continue; }
            nodes = el.childNodes;
            for (j = 0; j < nodes.length; j++) {
                if (nodes[j].nodeType === 3 && /\S/.test(nodes[j].nodeValue)) {
                    el.classList.add('sk-text');
                    break;
                }
            }
        }

        var controls = document.querySelectorAll(
            'input:not([type=hidden]):not([type=radio]):not([type=checkbox]):not([type=submit]), select, textarea'
        );
        for (i = 0; i < controls.length; i++) {
            controls[i].classList.add('sk-ctl');
        }
    }

    function reveal() {
        if (!root.classList.contains('is-loading')) { return; }
        root.classList.remove('is-loading');
        root.classList.add('is-revealed');
        window.setTimeout(function () { root.classList.remove('is-revealed'); }, 600);
    }

    var wantOpening = !reduceMotion && !cameFromHistory;

    if (wantOpening) {
        root.classList.add('is-loading');

        var domReadyAt = 0;
        var pageLoaded = false;

        var tryReveal = function () {
            if (!pageLoaded || !domReadyAt) { return; }
            var waited = Date.now() - domReadyAt;
            window.setTimeout(reveal, Math.max(0, OPEN_MIN_MS - waited));
        };

        document.addEventListener('DOMContentLoaded', function () {
            try { markSkeletonParts(); } catch (err) { /* if marking fails the page still shows */ }
            domReadyAt = Date.now();
            root.classList.add('sk-ready');
            tryReveal();
        });

        window.addEventListener('load', function () {
            pageLoaded = true;
            tryReveal();
        });

        // Safety net: if anything goes wrong, show the page
        window.setTimeout(function () {
            root.classList.add('sk-ready');
            reveal();
        }, OPEN_MAX_MS);
    }

    /* ------------------------------------------------------------
       1. Waiting for the next page
       ------------------------------------------------------------ */
    function bar(w, h, extra) {
        return '<span class="sk-box sk-w-' + w + ' sk-h-' + h + (extra ? ' ' + extra : '') + '"></span>';
    }

    function card(inner) {
        return '<div class="sk-card">' + inner + '</div>';
    }

    function repeat(n, fn) {
        var out = '';
        for (var i = 0; i < n; i++) { out += fn(i); }
        return out;
    }

    // One skeleton per kind of page. They match the real pages closely enough to feel continuous.
    var LAYOUTS = {
        auth: function () {
            return '<div class="sk-card sk-card-auth">' +
                bar(50, 'lg', 'sk-center') +
                bar(70, 'md') +
                bar(100, 'btn') +
                repeat(3, function () { return bar(30, 'sm') + bar(100, 'input'); }) +
                bar(100, 'btn') +
                '</div>';
        },

        landing: function () {
            return '<div class="sk-split">' +
                '<div class="sk-stack">' +
                    bar(40, 'md') + bar(90, 'hero') + bar(70, 'hero') + bar(80, 'md') + bar(60, 'md') +
                    '<div class="sk-row">' + bar(40, 'btn') + bar(30, 'btn') + '</div>' +
                '</div>' +
                card(bar(50, 'lg') + repeat(4, function () {
                    return '<div class="sk-row"><span class="sk-box sk-dot"></span><div class="sk-stack sk-w-80">' + bar(50, 'md') + bar(90, 'sm') + '</div></div>';
                })) +
                '</div>';
        },

        student: function () {
            return '<div class="sk-section sk-stack">' + bar(40, 'xl') + bar(60, 'md') + '</div>' +
                '<div class="sk-section"><div class="sk-grid sk-grid-4">' +
                    repeat(4, function () { return card(bar(50, 'sm') + bar(70, 'lg')); }) +
                '</div></div>' +
                '<div class="sk-section"><div class="sk-grid sk-grid-3">' +
                    repeat(3, function () { return card(bar(60, 'md') + bar(100, 'sm') + bar(90, 'sm') + bar(40, 'btn')); }) +
                '</div></div>' +
                card(repeat(4, function () { return bar(60, 'md'); }));
        },

        admin: function () {
            return '<div class="sk-section sk-stack">' + bar(30, 'xl') + bar(50, 'md') + '</div>' +
                '<div class="sk-section"><div class="sk-grid sk-grid-6">' +
                    repeat(6, function () { return card(bar(40, 'xl') + bar(70, 'sm')); }) +
                '</div></div>' +
                '<div class="sk-section"><div class="sk-grid sk-grid-4">' +
                    repeat(4, function () { return card(bar(60, 'md') + bar(100, 'sm') + bar(80, 'sm') + bar(40, 'btn')); }) +
                '</div></div>' +
                card(repeat(6, function () { return bar(100, 'md'); }));
        },

        forms: function () {
            return '<div class="sk-section sk-stack">' + bar(30, 'xl') +
                '<div class="sk-row">' + bar(60, 'input') + bar(20, 'input') + bar(10, 'input') + '</div></div>' +
                '<div class="sk-paper">' + bar(50, 'lg') + bar(30, 'md') +
                repeat(6, function () { return bar(30, 'sm') + bar(100, 'input'); }) + '</div>';
        },

        form: function () {
            return '<div class="sk-section sk-row">' + bar(20, 'btn') + bar(20, 'btn') + bar(20, 'btn') + '</div>' +
                '<div class="sk-paper">' + bar(60, 'lg') + bar(30, 'md') +
                repeat(5, function () { return bar(30, 'sm') + bar(100, 'input'); }) +
                repeat(2, function () { return bar(100, 'sm'); }) +
                '</div>';
        },

        generic: function () {
            return '<div class="sk-section sk-stack">' + bar(40, 'xl') + bar(60, 'md') + '</div>' +
                '<div class="sk-grid sk-grid-3">' +
                    repeat(3, function () { return card(bar(60, 'md') + bar(100, 'sm') + bar(80, 'sm')); }) +
                '</div>';
        }
    };

    function layoutFor(pathname) {
        var p = (pathname || '/').toLowerCase();
        if (p === '/') { return 'landing'; }
        if (p.indexOf('/login') === 0 || p.indexOf('/register') === 0 || p.indexOf('/logout') === 0) { return 'auth'; }
        if (p.indexOf('/user/forms/') === 0) { return 'form'; }
        if (p.indexOf('/user') === 0) { return 'student'; }
        if (p.indexOf('/admin/forms') === 0) { return 'forms'; }
        if (p.indexOf('/admin') === 0) { return 'admin'; }
        return 'generic';
    }

    function clearNavSkeleton() {
        var overlay = document.querySelector('.sk-overlay');
        if (overlay && overlay.parentNode) {
            overlay.parentNode.classList.remove('sk-host');
            overlay.parentNode.removeAttribute('aria-busy');
            overlay.parentNode.removeChild(overlay);
        }
        window.clearTimeout(navTimer);
    }

    function showNavSkeleton(pathname) {
        if (document.querySelector('.sk-overlay')) { return; }

        var kind = layoutFor(pathname);
        var fullScreen = (kind === 'auth' || kind === 'landing');
        var host = fullScreen ? null : (document.querySelector('.workspace') || document.querySelector('.dashboard-container main'));

        var overlay = document.createElement('div');
        overlay.className = 'sk-overlay' + (host ? '' : ' sk-fixed');
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML = '<div class="sk-page">' + LAYOUTS[kind]() + '</div>';

        var status = document.createElement('span');
        status.className = 'sr-only';
        status.setAttribute('role', 'status');
        status.textContent = 'Loading';

        if (host) {
            host.classList.add('sk-host');
            host.setAttribute('aria-busy', 'true');
            host.appendChild(overlay);
            host.appendChild(status);
        } else {
            document.body.appendChild(overlay);
            document.body.appendChild(status);
        }

        // If the page never changes (for example the visitor presses Esc), put things back
        navTimer = window.setTimeout(function () {
            clearNavSkeleton();
            if (status.parentNode) { status.parentNode.removeChild(status); }
        }, NAV_MAX_MS);
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) { return; }

        var link = event.target && event.target.closest ? event.target.closest('a[href]') : null;
        if (!link) { return; }
        if (link.hasAttribute('download') || link.hasAttribute('data-no-skeleton')) { return; }
        if (link.target && link.target !== '_self') { return; }

        var url;
        try { url = new URL(link.href, window.location.href); } catch (err) { return; }
        if (url.origin !== window.location.origin) { return; }
        if (!/^https?:$/.test(url.protocol)) { return; }
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) { return; }

        showNavSkeleton(url.pathname);
    });

    document.addEventListener('submit', function (event) {
        if (event.defaultPrevented) { return; }
        var form = event.target;
        if (!form || form.tagName !== 'FORM' || form.hasAttribute('data-no-skeleton')) { return; }
        if (form.target && form.target !== '_self') { return; }

        var url;
        try { url = new URL(form.getAttribute('action') || window.location.href, window.location.href); } catch (err) { return; }
        if (url.origin !== window.location.origin) { return; }

        showNavSkeleton(url.pathname);
    });

    // Coming back with the Back button should never show an old skeleton
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            clearNavSkeleton();
            var leftover = document.querySelector('[role=status].sr-only');
            if (leftover && leftover.parentNode) { leftover.parentNode.removeChild(leftover); }
            root.classList.remove('is-loading');
        }
    });
}());
