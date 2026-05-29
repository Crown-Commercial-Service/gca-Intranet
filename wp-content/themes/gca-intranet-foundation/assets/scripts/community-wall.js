/* global window, document, fetch, FormData, URL, URLSearchParams, confirm */
(function () {
    'use strict';

    var cfg             = window.gcaCommunityData || {};
    var restUrl         = cfg.restUrl             || '';
    var wpRestBase      = cfg.wpRestBase          || '';
    var nonce           = cfg.nonce               || '';
    var userAvatar      = cfg.currentUserAvatar   || '';
    var isCommunityHost = cfg.isCommunityHost     || false;
    var isAdmin         = cfg.isAdmin             || false;

    var CHAR_LIMIT = 500;

    // ── Utility ───────────────────────────────────────────────────────────────

    function apiFetch(method, path, body) {
        var opts = {
            method:      method,
            headers:     { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            credentials: 'same-origin',
        };
        if (body !== undefined) {
            opts.body = JSON.stringify(body);
        }
        return fetch(restUrl + path, opts).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) { throw data; }
                return data;
            });
        });
    }

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = String(str == null ? '' : str);
        return d.innerHTML;
    }

    // ── Relative time ─────────────────────────────────────────────────────────
    // < 1 min    → "just now"
    // < 1 hour   → "X mins ago"
    // < 24 hours → "X hrs ago"
    // Yesterday  → "Yesterday"
    // 2–6 days   → day name ("Tuesday")
    // 7+ days    → date ("12 Jan 2025")

    function relativeTime(iso) {
        if (!iso) { return ''; }
        var then = new Date(iso);
        var now  = new Date();
        var diff = Math.floor((now - then) / 1000);

        if (diff < 60)   { return 'just now'; }
        if (diff < 3600) {
            var m = Math.floor(diff / 60);
            return m + ' min' + (m === 1 ? '' : 's') + ' ago';
        }
        if (diff < 86400) {
            var h = Math.floor(diff / 3600);
            return h + ' hr' + (h === 1 ? '' : 's') + ' ago';
        }

        var thenDay  = new Date(then.getFullYear(), then.getMonth(), then.getDate());
        var todayDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var dayDiff  = Math.round((todayDay - thenDay) / 86400000);

        if (dayDiff === 1) { return 'Yesterday'; }
        if (dayDiff < 7)   { return then.toLocaleDateString('en-GB', { weekday: 'long' }); }

        return then.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    // Tick <time> elements every minute while they are still within 7 days
    function startTimeTicker() {
        setInterval(function () {
            document.querySelectorAll('.gca-cw-feed time[datetime]').forEach(function (el) {
                var iso = el.getAttribute('datetime');
                if (!iso) { return; }
                var diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
                if (diff < 7 * 86400) {
                    el.textContent = relativeTime(iso);
                }
            });
        }, 60000);
    }

    // ── Build gca-lc HTML ────────────────────────────────────────────────────

    function buildLcHtml(postId, likeCount, userHasLiked, commentCount) {
        var liked = userHasLiked ? 'true' : 'false';
        return (
            '<section class="gca-lc" data-post-id="' + postId + '" aria-label="Likes and comments">' +
            '<div class="govuk-visually-hidden" aria-live="polite" aria-atomic="true" id="gca-lc-status-' + postId + '"></div>' +

            '<div class="gca-lc__like-bar">' +
            '<button type="button" class="gca-lc__like-btn" aria-pressed="' + liked + '" data-action="toggle-post-like">' +
            '<span class="gca-lc__like-icon" aria-hidden="true">' +
            '<svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">' +
            '<path class="gca-lc__like-path" d="M22 9C22 7.89 21.1 7 20 7H13.68L14.64 2.43C14.66 2.33 14.67 2.22 14.67 2.11C14.67 1.7 14.5 1.32 14.23 1.05L13.17 0L6.59 6.58C6.22 6.95 6 7.45 6 8V18C6 18.5304 6.21071 19.0391 6.58579 19.4142C6.96086 19.7893 7.46957 20 8 20H17C17.83 20 18.54 19.5 18.84 18.78L21.86 11.73C21.95 11.5 22 11.26 22 11V9ZM0 20H4V8H0V20Z" fill="currentColor"/>' +
            '</svg></span>' +
            '<span class="gca-lc__like-label">' + (userHasLiked ? 'Liked' : 'Like') + '</span>' +
            '<span class="gca-lc__like-count" aria-label="likes">' + esc(likeCount) + '</span>' +
            '</button>' +

            '<button type="button" class="gca-lc__comment-toggle-btn" aria-expanded="false"' +
            ' aria-controls="gca-lc-comments-' + postId + '" data-action="toggle-comments">' +
            '<span class="gca-lc__comment-icon" aria-hidden="true">' +
            '<svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">' +
            '<path d="M11 0C4.92422 0 0 4.15625 0 9.28571C0 11.5 0.919531 13.5268 2.44922 15.1205C1.91211 17.3705 0.116016 19.375 0.0945313 19.3973C0 19.5 -0.0257812 19.6518 0.0300781 19.7857C0.0859375 19.9196 0.20625 20 0.34375 20C3.19258 20 5.32813 18.5804 6.38516 17.7054C7.79023 18.2545 9.35 18.5714 11 18.5714C17.0758 18.5714 22 14.4152 22 9.28571C22 4.15625 17.0758 0 11 0Z" fill="currentColor"/>' +
            '</svg></span>' +
            '<span class="gca-lc__comment-count-label"><span class="gca-lc__comment-count">' + esc(commentCount) + '</span> comments</span>' +
            '</button>' +

            '<button type="button" class="gca-cw-post__interact-btn gca-cw-post__bookmark-btn"' +
            ' aria-label="Bookmark (coming soon)" disabled>' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">' +
            '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg>' +
            '</button>' +
            '</div>' +

            '<div class="gca-lc__comments-panel" id="gca-lc-comments-' + postId + '" hidden>' +
            '<div class="gca-lc__form-wrap">' +
            '<div class="gca-lc__form-avatar">' +
            (userAvatar ? '<img src="' + esc(userAvatar) + '" alt="" width="40" height="40" class="gca-lc__avatar">' : '') +
            '</div>' +
            '<form class="gca-lc__form" data-action="submit-comment" novalidate>' +
            '<input type="hidden" name="parent_id" value="0">' +
            '<div class="govuk-form-group gca-lc__textarea-group">' +
            '<label class="govuk-label govuk-visually-hidden" for="gca-lc-textarea-' + postId + '">Add a comment</label>' +
            '<textarea id="gca-lc-textarea-' + postId + '" class="govuk-textarea gca-lc__textarea"' +
            ' name="content" rows="3"' +
            ' placeholder="Write a comment… Use @ to mention someone"' +
            ' aria-label="Add a comment. Use @ to mention someone" maxlength="2000"></textarea>' +
            '<ul class="gca-lc__mention-list" role="listbox" aria-label="Mention suggestions" hidden></ul>' +
            '</div>' +
            '<div class="gca-lc__form-actions">' +
            '<button type="submit" class="govuk-button govuk-button--secondary gca-lc__submit-btn">Post comment</button>' +
            '</div></form>' +
            '</div>' +
            '<div class="gca-lc__list" aria-live="polite" aria-relevant="additions" role="log">' +
            '<p class="gca-lc__loading govuk-body-s">Loading comments…</p>' +
            '</div>' +
            '</div>' +
            '</section>'
        );
    }

    // ── Render a regular post card ────────────────────────────────────────────

    function buildMediaHtml(media) {
        if (!media || !media.length) { return ''; }
        var isSingle = media.length === 1;
        var cls      = isSingle ? 'gca-cw-post__media' : 'gca-cw-post__media gca-cw-post__media--grid';
        var inner    = '';
        media.forEach(function (m) {
            if (m.type === 'video') {
                inner += '<video src="' + esc(m.url) + '" controls preload="metadata" aria-label="Post video attachment"></video>';
            } else {
                inner += '<img src="' + esc(m.url) + '" alt="' + esc(m.alt || '') + '" loading="lazy" decoding="async">';
            }
        });
        return '<div class="' + cls + '">' + inner + '</div>';
    }

    function buildMoreMenu(postId, canDelete) {
        if (!canDelete) { return ''; }
        return (
            '<div class="gca-cw-post__more-wrap">' +
            '<button type="button" class="gca-cw-post__more-btn"' +
            ' aria-label="More options" aria-expanded="false" aria-haspopup="menu"' +
            ' data-post-id="' + postId + '">' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' +
            '<circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>' +
            '</svg></button>' +
            '<ul class="gca-cw-post__dropdown" id="gca-cw-dd-' + postId + '" role="menu" hidden>' +
            '<li role="none"><button type="button" role="menuitem"' +
            ' class="gca-cw-post__dropdown-btn gca-cw-post__dropdown-btn--delete"' +
            ' data-action="delete-post" data-post-id="' + postId + '">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
            '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg> Delete post</button></li>' +
            '</ul></div>'
        );
    }

    function renderPost(post) {
        var authorHtml = post.author_profile
            ? '<a href="' + esc(post.author_profile) + '" class="gca-cw-post__author-name">' + esc(post.author_name) + '</a>'
            : '<span class="gca-cw-post__author-name">' + esc(post.author_name) + '</span>';

        var teamHtml = post.author_team
            ? '<span class="gca-cw-post__author-team">' + esc(post.author_team) + '</span>'
            : '';

        return (
            '<article class="gca-cw-post" id="gca-cw-post-' + post.id + '">' +
            '<div class="gca-cw-post__header">' +
            '<div class="gca-cw-post__author-row">' +
            (post.author_avatar ? '<img src="' + esc(post.author_avatar) + '" alt="" width="48" height="48" class="gca-cw-post__avatar">' : '') +
            '<div class="gca-cw-post__author-info">' +
            authorHtml +
            '<div class="gca-cw-post__author-meta">' +
            teamHtml +
            '<span class="gca-cw-post__meta-dot"><time datetime="' + esc(post.date_iso) + '">' + esc(relativeTime(post.date_iso)) + '</time></span>' +
            '</div></div></div>' +
            buildMoreMenu(post.id, post.can_delete) +
            '</div>' +
            '<div class="gca-cw-post__content">' + post.content_html + '</div>' +
            buildMediaHtml(post.media) +
            buildLcHtml(post.id, post.like_count, post.user_has_liked, post.comment_count) +
            '</article>'
        );
    }

    // ── Mixed feed item renderer ──────────────────────────────────────────────

    function renderItem(item) {
        switch (item.kind) {
            case 'shoutout':
                if (window.GcaShoutouts && typeof window.GcaShoutouts.renderShoutout === 'function') {
                    return window.GcaShoutouts.renderShoutout(item);
                }
                break;
            case 'poll':
                if (window.GcaPolls && typeof window.GcaPolls.renderPoll === 'function') {
                    return window.GcaPolls.renderPoll(item);
                }
                break;
            case 'qa':
                if (window.GcaQa && typeof window.GcaQa.renderAnsweredQuestion === 'function') {
                    return window.GcaQa.renderAnsweredQuestion(item);
                }
                break;
        }
        return renderPost(item);
    }

    function initItemInteractions(articleEl, item) {
        var lcEl = articleEl ? articleEl.querySelector('.gca-lc') : null;
        if (lcEl && window.GcaLc && typeof window.GcaLc.init === 'function') {
            window.GcaLc.init(lcEl);
        }
        if (item && item.kind === 'poll' && window.GcaPolls && typeof window.GcaPolls.initPollCard === 'function') {
            window.GcaPolls.initPollCard(articleEl);
        }
    }

    // ── Feed state ────────────────────────────────────────────────────────────

    var feedEl, footerEl, loadMoreBtn, endEl, loadingEl;
    var currentPage = 1;
    var totalPages  = 1;
    var isLoading   = false;

    function appendItem(item, prepend) {
        if (!feedEl) { return; }
        var html = renderItem(item);
        if (!html) { return; }

        var tmp = document.createElement('div');
        tmp.innerHTML = html;
        var articleEl = tmp.firstElementChild;
        if (!articleEl) { return; }

        if (prepend) {
            feedEl.insertBefore(articleEl, feedEl.firstChild);
        } else {
            feedEl.appendChild(articleEl);
        }

        initItemInteractions(articleEl, item);
    }

    function loadFeed(page) {
        if (isLoading) { return; }
        isLoading = true;
        if (loadMoreBtn) { loadMoreBtn.disabled = true; }

        apiFetch('GET', '/community/feed?page=' + page + '&per_page=15')
            .then(function (data) {
                totalPages  = data.total_pages || 1;
                currentPage = data.page        || page;

                if (loadingEl) { loadingEl.hidden = true; }

                if (!data.posts || !data.posts.length) {
                    if (page === 1) {
                        var empty = document.createElement('p');
                        empty.className   = 'gca-cw-feed__status';
                        empty.textContent = 'No posts yet — be the first to share an update!';
                        feedEl.appendChild(empty);
                    }
                    if (footerEl) { footerEl.hidden = true; }
                    if (endEl)    { endEl.hidden = false; }
                    return;
                }

                data.posts.forEach(function (item) { appendItem(item, false); });

                if (currentPage < totalPages) {
                    if (footerEl) { footerEl.hidden = false; }
                    if (endEl)    { endEl.hidden = true; }
                } else {
                    if (footerEl) { footerEl.hidden = true; }
                    if (endEl)    { endEl.hidden = false; }
                }
            })
            .catch(function () {
                if (loadingEl) { loadingEl.hidden = true; }
                var errEl = document.createElement('p');
                errEl.className   = 'gca-cw-feed__status';
                errEl.textContent = 'Could not load posts. Please refresh the page.';
                feedEl.appendChild(errEl);
            })
            .finally(function () {
                isLoading = false;
                if (loadMoreBtn) { loadMoreBtn.disabled = false; }
            });
    }

    // ── Post creation ─────────────────────────────────────────────────────────

    var pendingMediaIds = [];

    function setupCompose() {
        var triggerBtn = document.getElementById('gca-cw-trigger');
        var formArea   = document.getElementById('gca-cw-form-area');
        var cancelBtn  = document.getElementById('gca-cw-cancel');
        var form       = document.getElementById('gca-cw-form');
        var submitBtn  = document.getElementById('gca-cw-submit');
        var textarea   = document.getElementById('gca-cw-content');
        var charsLeft  = document.getElementById('gca-cw-chars-left');
        var charHint   = document.getElementById('gca-cw-char-hint');
        var fileInput  = document.getElementById('gca-cw-file-input');
        var mediaPrev  = document.getElementById('gca-cw-media-preview');
        var errorEl    = document.getElementById('gca-cw-error');

        if (!triggerBtn || !formArea || !form) { return; }

        function openForm() {
            triggerBtn.setAttribute('aria-expanded', 'true');
            formArea.hidden = false;
            if (textarea) { textarea.focus(); }
        }

        function closeForm() {
            triggerBtn.setAttribute('aria-expanded', 'false');
            formArea.hidden = true;
            if (form)      { form.reset(); }
            if (charsLeft) { charsLeft.textContent = String(CHAR_LIMIT); }
            if (charHint)  { charHint.classList.remove('gca-cw-compose__char-hint--warning'); }
            if (mediaPrev) { mediaPrev.innerHTML = ''; mediaPrev.hidden = true; }
            if (errorEl)   { errorEl.hidden = true; }
            pendingMediaIds = [];
            activeUploads   = 0;
            setPostBtnState();
        }

        triggerBtn.addEventListener('click', openForm);
        if (cancelBtn) { cancelBtn.addEventListener('click', closeForm); }

        // Character counter (maxlength="500" in HTML enforces the hard cap)
        if (textarea && charsLeft) {
            textarea.addEventListener('input', function () {
                var remaining = CHAR_LIMIT - textarea.value.length;
                charsLeft.textContent = remaining;
                if (charHint) {
                    charHint.classList.toggle('gca-cw-compose__char-hint--warning', remaining < 50);
                }
            });
        }

        var activeUploads = 0;

        function setPostBtnState() {
            if (!submitBtn) { return; }
            submitBtn.disabled    = activeUploads > 0;
            submitBtn.textContent = activeUploads > 0 ? 'Uploading…' : 'Post update';
        }

        if (fileInput && mediaPrev) {
            fileInput.addEventListener('change', function () {
                var files        = Array.prototype.slice.call(fileInput.files || []);
                var currentCount = pendingMediaIds.filter(function (id) { return id !== null; }).length;

                files.forEach(function (file) {
                    if (currentCount >= 4) { return; }
                    currentCount++;

                    var slotIdx = pendingMediaIds.length;
                    pendingMediaIds.push(null);

                    var item = document.createElement('div');
                    item.className      = 'gca-cw-compose__preview-item';
                    item.dataset.slot   = slotIdx;

                    var preview;
                    if (file.type.indexOf('video') === 0) {
                        preview         = document.createElement('video');
                        preview.src     = URL.createObjectURL(file);
                        preview.preload = 'metadata';
                    } else {
                        preview     = document.createElement('img');
                        preview.src = URL.createObjectURL(file);
                        preview.alt = '';
                    }
                    preview.style.width      = '120px';
                    preview.style.height     = '80px';
                    preview.style.objectFit  = 'cover';
                    preview.style.display    = 'block';
                    preview.style.flexShrink = '0';
                    item.appendChild(preview);

                    var removeBtn = document.createElement('button');
                    removeBtn.type      = 'button';
                    removeBtn.className = 'gca-cw-compose__preview-remove';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.setAttribute('aria-label', 'Remove this attachment');
                    (function (slot, el) {
                        removeBtn.addEventListener('click', function () {
                            pendingMediaIds[slot] = null;
                            el.remove();
                            if (!mediaPrev.children.length) { mediaPrev.hidden = true; }
                        });
                    }(slotIdx, item));
                    item.appendChild(removeBtn);

                    mediaPrev.appendChild(item);
                    mediaPrev.hidden = false;

                    activeUploads++;
                    setPostBtnState();

                    (function (slot, f) {
                        var safeFilename = f.name.replace(/"/g, '');
                        fetch(wpRestBase + 'wp/v2/media', {
                            method:      'POST',
                            headers:     {
                                'X-WP-Nonce':          nonce,
                                'Content-Disposition': 'attachment; filename="' + safeFilename + '"',
                                'Content-Type':        f.type || 'application/octet-stream',
                            },
                            credentials: 'same-origin',
                            body:        f,
                        })
                            .then(function (r) { return r.json().then(function (d) { if (!r.ok) { throw d; } return d; }); })
                            .then(function (media) { pendingMediaIds[slot] = media.id; })
                            .catch(function () {
                                var slot_item = mediaPrev.querySelector('[data-slot="' + slot + '"]');
                                if (slot_item) { slot_item.style.outline = '3px solid #d4351c'; }
                            })
                            .finally(function () {
                                activeUploads--;
                                setPostBtnState();
                            });
                    }(slotIdx, file));
                });

                fileInput.value = '';
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var content = textarea ? textarea.value.trim() : '';
            if (!content) { if (textarea) { textarea.focus(); } return; }

            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Posting…'; }

            var mediaIds = pendingMediaIds.filter(function (id) { return id !== null; });

            apiFetch('POST', '/community/posts', { content: content, media_ids: mediaIds })
                .then(function (post) {
                    closeForm();

                    var ph = feedEl ? feedEl.querySelector('.gca-cw-feed__status') : null;
                    if (ph) { ph.remove(); }

                    appendItem(post, true);

                    var newArticle = document.getElementById('gca-cw-post-' + post.id);
                    if (newArticle) {
                        newArticle.setAttribute('tabindex', '-1');
                        newArticle.focus();
                        newArticle.removeAttribute('tabindex');
                    }
                })
                .catch(function (err) {
                    if (errorEl) {
                        errorEl.textContent = (err && err.error) ? err.error : 'Could not post update. Please try again.';
                        errorEl.hidden = false;
                    }
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled    = false;
                        submitBtn.textContent = 'Post update';
                    }
                });
        });
    }

    // ── Feed event delegation (dropdown menu + delete) ────────────────────────

    function setupFeedEvents() {
        if (!feedEl) { return; }

        feedEl.addEventListener('click', function (e) {
            var moreBtn = e.target.closest('.gca-cw-post__more-btn');
            if (moreBtn) {
                e.stopPropagation();
                var pid      = moreBtn.dataset.postId;
                var dropdown = document.getElementById('gca-cw-dd-' + pid);
                if (!dropdown) { return; }
                var opening = dropdown.hidden;
                closeAllDropdowns();
                if (opening) {
                    dropdown.hidden = false;
                    moreBtn.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            var deleteBtn = e.target.closest('[data-action="delete-post"]');
            if (deleteBtn) {
                var postId = deleteBtn.dataset.postId;
                if (!confirm('Are you sure you want to delete this post?')) { return; }
                apiFetch('DELETE', '/community/posts/' + postId)
                    .then(function () {
                        var postEl = document.getElementById('gca-cw-post-' + postId);
                        if (postEl) { postEl.remove(); }
                    })
                    .catch(function () {
                        alert('Could not delete post. Please try again.');
                    });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.gca-cw-post__more-wrap')) {
                closeAllDropdowns();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeAllDropdowns(); }
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.gca-cw-post__dropdown').forEach(function (d) { d.hidden = true; });
        document.querySelectorAll('.gca-cw-post__more-btn').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
    }

    // ── Init ──────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        feedEl      = document.getElementById('gca-cw-feed');
        footerEl    = document.getElementById('gca-cw-footer');
        loadMoreBtn = document.getElementById('gca-cw-load-more');
        endEl       = document.getElementById('gca-cw-end');
        loadingEl   = document.getElementById('gca-cw-loading');

        if (!feedEl) { return; }

        if (isCommunityHost || isAdmin) {
            setupCompose();
        }

        setupFeedEvents();
        loadFeed(1);
        startTimeTicker();

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function () {
                loadFeed(currentPage + 1);
            });
        }
    });

    // ── Public API ────────────────────────────────────────────────────────────

    window.GcaCommunityWall = {
        appendItem:   appendItem,
        relativeTime: relativeTime,
        buildLcHtml:  buildLcHtml,
    };
}());
