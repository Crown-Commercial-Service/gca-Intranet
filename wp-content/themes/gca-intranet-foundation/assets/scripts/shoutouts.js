/* global window, document, fetch */
(function () {
    'use strict';

    var cfg        = window.gcaCommunityData || {};
    var restUrl    = cfg.restUrl    || '';
    var nonce      = cfg.nonce      || '';
    var userAvatar = cfg.currentUserAvatar || '';

    // ── Utilities ─────────────────────────────────────────────────────────────

    function apiFetch(method, path, body) {
        var opts = {
            method:      method,
            headers:     { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            credentials: 'same-origin',
        };
        if (body !== undefined) { opts.body = JSON.stringify(body); }
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

    function confirmDelete(triggerEl, title, body) {
        return new Promise(function (resolve) {
            var previouslyFocused = document.activeElement;
            var modal = document.createElement('div');
            modal.className = 'gca-lc-delete-modal';
            modal.setAttribute('role', 'presentation');
            modal.innerHTML =
                '<div class="gca-lc-delete-modal__overlay" data-action="cancel-delete"></div>' +
                '<div class="gca-lc-delete-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="gca-so-delete-title" aria-describedby="gca-so-delete-description" tabindex="-1">' +
                '<button type="button" class="gca-lc-delete-modal__close" data-action="cancel-delete" aria-label="Close delete confirmation">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '<div class="gca-lc-delete-modal__content">' +
                '<h2 class="gca-lc-delete-modal__title" id="gca-so-delete-title">' + esc(title) + '</h2>' +
                '<p class="gca-lc-delete-modal__body" id="gca-so-delete-description">' + esc(body) + '</p>' +
                '<div class="gca-lc-delete-modal__actions">' +
                '<button type="button" class="gca-lc-delete-modal__confirm" data-action="confirm-delete">Yes, delete it</button>' +
                '<button type="button" class="gca-lc-delete-modal__cancel" data-action="cancel-delete">Cancel</button>' +
                '</div>' +
                '</div>' +
                '</div>';
            function close(confirmed) {
                document.removeEventListener('keydown', onKeydown);
                modal.remove();
                if (previouslyFocused && typeof previouslyFocused.focus === 'function') {
                    previouslyFocused.focus();
                } else if (triggerEl && typeof triggerEl.focus === 'function') {
                    triggerEl.focus();
                }
                resolve(confirmed);
            }
            function onKeydown(e) {
                if (e.key === 'Escape') { close(false); return; }
                if (e.key !== 'Tab') { return; }
                var focusable = modal.querySelectorAll('button');
                var first = focusable[0]; var last = focusable[focusable.length - 1];
                if (!first || !last) { return; }
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
            modal.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-action]');
                if (!btn) { return; }
                if (btn.dataset.action === 'confirm-delete') { close(true); }
                if (btn.dataset.action === 'cancel-delete')  { close(false); }
            });
            document.body.appendChild(modal);
            document.addEventListener('keydown', onKeydown);
            var cancelBtn = modal.querySelector('[data-action="cancel-delete"].gca-lc-delete-modal__cancel');
            if (cancelBtn) { setTimeout(function () { cancelBtn.focus(); }, 50); }
        });
    }

    function relativeTime(iso) {
        if (!iso) { return ''; }
        var then = new Date(iso);
        var now  = new Date();
        var diff = Math.floor((now - then) / 1000);
        if (diff < 60)    { return 'just now'; }
        if (diff < 3600)  { var m = Math.floor(diff / 60);   return m + ' min' + (m === 1 ? '' : 's') + ' ago'; }
        if (diff < 86400) { var h = Math.floor(diff / 3600); return h + ' hr'  + (h === 1 ? '' : 's') + ' ago'; }
        var thenDay  = new Date(then.getFullYear(), then.getMonth(), then.getDate());
        var todayDay = new Date(now.getFullYear(),  now.getMonth(),  now.getDate());
        var dayDiff  = Math.round((todayDay - thenDay) / 86400000);
        if (dayDiff === 1) { return 'Yesterday'; }
        if (dayDiff < 7)   { return then.toLocaleDateString('en-GB', { weekday: 'long' }); }
        return then.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function buildLcHtml(postId, likeCount, userHasLiked, commentCount) {
        var liked = userHasLiked ? 'true' : 'false';
        return (
            '<section class="gca-lc" data-post-id="' + postId + '" aria-label="Likes and comments, post ' + postId + '">' +
            '<div class="govuk-visually-hidden" aria-live="polite" aria-atomic="true" id="gca-lc-status-' + postId + '"></div>' +
            '<div class="gca-lc__like-bar">' +
            '<button type="button" class="gca-lc__like-btn" aria-pressed="' + liked + '" data-action="toggle-post-like">' +
            '<span class="gca-lc__like-icon" aria-hidden="true">' +
            '<svg width="22" height="20" viewBox="0 0 22 20" fill="none" focusable="false">' +
            '<path class="gca-lc__like-path" d="M22 9C22 7.89 21.1 7 20 7H13.68L14.64 2.43C14.66 2.33 14.67 2.22 14.67 2.11C14.67 1.7 14.5 1.32 14.23 1.05L13.17 0L6.59 6.58C6.22 6.95 6 7.45 6 8V18C6 18.5304 6.21071 19.0391 6.58579 19.4142C6.96086 19.7893 7.46957 20 8 20H17C17.83 20 18.54 19.5 18.84 18.78L21.86 11.73C21.95 11.5 22 11.26 22 11V9ZM0 20H4V8H0V20Z" fill="currentColor"/>' +
            '</svg></span>' +
            '<span class="gca-lc__like-label">' + (userHasLiked ? 'Liked' : 'Like') + '</span>' +
            '<span class="gca-lc__like-count" aria-label="likes">' + esc(likeCount) + '</span>' +
            '</button>' +
            '<button type="button" class="gca-lc__comment-toggle-btn" aria-expanded="false"' +
            ' aria-controls="gca-lc-comments-' + postId + '" data-action="toggle-comments">' +
            '<span class="gca-lc__comment-icon" aria-hidden="true">' +
            '<svg width="22" height="20" viewBox="0 0 22 20" fill="none" focusable="false">' +
            '<path d="M11 0C4.92422 0 0 4.15625 0 9.28571C0 11.5 0.919531 13.5268 2.44922 15.1205C1.91211 17.3705 0.116016 19.375 0.0945313 19.3973C0 19.5 -0.0257812 19.6518 0.0300781 19.7857C0.0859375 19.9196 0.20625 20 0.34375 20C3.19258 20 5.32813 18.5804 6.38516 17.7054C7.79023 18.2545 9.35 18.5714 11 18.5714C17.0758 18.5714 22 14.4152 22 9.28571C22 4.15625 17.0758 0 11 0Z" fill="currentColor"/>' +
            '</svg></span>' +
            '<span class="gca-lc__comment-count-label"><span class="gca-lc__comment-count">' + esc(commentCount) + '</span> comments</span>' +
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
            ' name="content" rows="3" placeholder="Write a comment…" aria-label="Add a comment" maxlength="2000"></textarea>' +
            '<ul class="gca-lc__mention-list" role="listbox" aria-label="Mention suggestions" hidden></ul>' +
            '</div>' +
            '<div class="gca-lc__form-actions">' +
            '<button type="submit" class="govuk-button govuk-button--secondary gca-lc__submit-btn">Post comment</button>' +
            '</div></form></div>' +
            '<div class="gca-lc__list" aria-live="polite" aria-relevant="additions" role="log">' +
            '<p class="gca-lc__loading govuk-body-s">Loading comments…</p>' +
            '</div></div></section>'
        );
    }

    // ── Award icon (replaces profile avatar in shoutout cards) ────────────────

    var SHOUTOUT_ICON_HTML = (
        '<div class="gca-shoutout__icon" aria-hidden="true">' +
        '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M12 3.5l2.47 5.01 5.53.8-4 3.9.94 5.51L12 16.98l-4.94 2.74.94-5.51-4-3.9 5.53-.8L12 3.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="none"/>' +
        '</svg>' +
        '</div>'
    );

    // ── Shoutout card renderer ─────────────────────────────────────────────────

    function renderShoutout(s) {
        var giverHtml = s.giver_profile
            ? '<a href="' + esc(s.giver_profile) + '" class="gca-cw-post__author-name">' + esc(s.giver_name) + '</a>'
            : '<span class="gca-cw-post__author-name">' + esc(s.giver_name) + '</span>';

        var recipientHtml = s.recipient_profile
            ? '<a href="' + esc(s.recipient_profile) + '" class="gca-shoutout__recipient-name">' + esc(s.recipient_name) + '</a>'
            : '<strong class="gca-shoutout__recipient-name">' + esc(s.recipient_name) + '</strong>';

        var categoryHtml = s.category
            ? '<span class="gca-shoutout__category-tag">' + esc(s.category) + '</span>'
            : '';

        var moreHtml = '';
        if (s.can_delete) {
            moreHtml = (
                '<div class="gca-cw-post__more-wrap">' +
                '<button type="button" class="gca-cw-post__more-btn" aria-label="More options"' +
                ' aria-expanded="false" aria-haspopup="menu" data-shoutout-id="' + s.id + '">' +
                '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' +
                '<circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>' +
                '</svg></button>' +
                '<ul class="gca-cw-post__dropdown" id="gca-shoutout-dd-' + s.id + '" role="menu" hidden>' +
                '<li role="none"><button type="button" role="menuitem"' +
                ' class="gca-cw-post__dropdown-btn gca-cw-post__dropdown-btn--delete"' +
                ' data-shoutout-action="delete" data-shoutout-id="' + s.id + '">' +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg> Delete shout-out</button></li>' +
                '</ul></div>'
            );
        }

        return (
            '<article class="gca-cw-post gca-shoutout" id="gca-shoutout-' + s.id + '">' +
            '<div class="gca-cw-post__header">' +
            '<div class="gca-cw-post__author-row">' +
            SHOUTOUT_ICON_HTML +
            '<div class="gca-cw-post__author-info">' +
            '<div class="gca-cw-post__author-name-row">' +
            giverHtml +
            '<span class="gca-shoutout__shouted-out"> shouted out </span>' +
            recipientHtml +
            '</div>' +
            '<div class="gca-cw-post__author-meta">' + categoryHtml +
            '<span class="gca-cw-post__meta-dot"><time datetime="' + esc(s.date_iso) + '">' + esc(relativeTime(s.date_iso)) + '</time></span>' +
            '</div></div></div>' +
            moreHtml +
            '</div>' +
            '<div class="gca-cw-post__content">' + s.content_html + '</div>' +
            buildLcHtml(s.id, s.like_count, s.user_has_liked, s.comment_count) +
            '</article>'
        );
    }

    function initShoutoutCard(articleEl) {
        if (!articleEl) { return; }
        // Three-dot menu and delete — delegated via body listener set up in init
    }

    // ── Shoutouts tab – feed ───────────────────────────────────────────────────

    var shoutoutFeedEl, shoutoutFooterEl, shoutoutLoadMoreBtn, shoutoutEndEl, shoutoutLoadingEl;
    var shoutoutPage       = 1;
    var shoutoutTotalPages = 1;
    var shoutoutLoading    = false;
    var shoutoutsLoaded    = false;

    function loadShoutouts(page) {
        if (shoutoutLoading) { return; }
        shoutoutLoading = true;
        if (shoutoutLoadMoreBtn) { shoutoutLoadMoreBtn.disabled = true; }

        apiFetch('GET', '/shoutouts?page=' + page + '&per_page=15')
            .then(function (data) {
                shoutoutTotalPages = data.total_pages || 1;
                shoutoutPage       = data.page || page;

                if (shoutoutLoadingEl) { shoutoutLoadingEl.hidden = true; }

                if (!data.shoutouts || !data.shoutouts.length) {
                    if (page === 1) {
                        var empty = document.createElement('p');
                        empty.className = 'gca-cw-feed__status';
                        empty.textContent = 'No shout-outs yet — be the first to celebrate a colleague!';
                        shoutoutFeedEl.appendChild(empty);
                    }
                    if (shoutoutFooterEl) { shoutoutFooterEl.hidden = true; }
                    if (shoutoutEndEl)    { shoutoutEndEl.hidden = false; }
                    return;
                }

                data.shoutouts.forEach(function (s) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = renderShoutout(s);
                    var el = tmp.firstElementChild;
                    if (!el) { return; }
                    shoutoutFeedEl.appendChild(el);
                    if (window.GcaLc && typeof window.GcaLc.init === 'function') {
                        var lcEl = el.querySelector('.gca-lc');
                        if (lcEl) { window.GcaLc.init(lcEl); }
                    }
                });

                if (shoutoutPage < shoutoutTotalPages) {
                    if (shoutoutFooterEl) { shoutoutFooterEl.hidden = false; }
                    if (shoutoutEndEl)    { shoutoutEndEl.hidden = true; }
                } else {
                    if (shoutoutFooterEl) { shoutoutFooterEl.hidden = true; }
                    if (shoutoutEndEl)    { shoutoutEndEl.hidden = false; }
                }
            })
            .catch(function () {
                if (shoutoutLoadingEl) { shoutoutLoadingEl.hidden = true; }
                var errEl = document.createElement('p');
                errEl.className = 'gca-cw-feed__status';
                errEl.textContent = 'Could not load shout-outs. Please refresh.';
                if (shoutoutFeedEl) { shoutoutFeedEl.appendChild(errEl); }
            })
            .finally(function () {
                shoutoutLoading = false;
                if (shoutoutLoadMoreBtn) { shoutoutLoadMoreBtn.disabled = false; }
            });
    }

    function onTabActivate() {
        if (!shoutoutsLoaded) {
            shoutoutsLoaded = true;
            loadShoutouts(1);
        }
    }

    // ── Recipient autocomplete ─────────────────────────────────────────────────

    function setupRecipientAutocomplete(searchInput, suggestionsEl, hiddenIdInput) {
        if (!searchInput || !suggestionsEl || !hiddenIdInput) { return; }

        var debounceTimer = null;
        var selectedUser  = null;

        function clearSelection() {
            selectedUser = null;
            hiddenIdInput.value = '';
        }

        function selectUser(user) {
            selectedUser        = user;
            hiddenIdInput.value = user.id;
            searchInput.value   = user.name;
            suggestionsEl.hidden = true;
            suggestionsEl.innerHTML = '';
            searchInput.setAttribute('aria-expanded', 'false');
        }

        function showSuggestions(users) {
            suggestionsEl.innerHTML = '';

            if (!users.length) {
                var none = document.createElement('li');
                none.className = 'gca-shoutout-compose__suggestion gca-shoutout-compose__suggestion--empty';
                none.textContent = 'No colleagues found';
                none.setAttribute('role', 'option');
                suggestionsEl.appendChild(none);
            } else {
                users.forEach(function (user) {
                    var li = document.createElement('li');
                    li.className = 'gca-shoutout-compose__suggestion';
                    li.setAttribute('role', 'option');
                    li.setAttribute('tabindex', '0');
                    li.dataset.userId = user.id;
                    li.innerHTML = (
                        (user.avatar ? '<img src="' + esc(user.avatar) + '" alt="" width="36" height="36" class="gca-shoutout-compose__suggestion-avatar">' : '') +
                        '<span class="gca-shoutout-compose__suggestion-info">' +
                        '<span class="gca-shoutout-compose__suggestion-name">' + esc(user.name) + '</span>' +
                        (user.team ? '<span class="gca-shoutout-compose__suggestion-team">' + esc(user.team) + '</span>' : '') +
                        '</span>'
                    );

                    function choose() { selectUser(user); }
                    li.addEventListener('click', choose);
                    li.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); choose(); }
                        if (e.key === 'ArrowDown') { var next = li.nextElementSibling; if (next) { next.focus(); } }
                        if (e.key === 'ArrowUp')   { var prev = li.previousElementSibling; if (prev) { prev.focus(); } else { searchInput.focus(); } }
                        if (e.key === 'Escape')    { suggestionsEl.hidden = true; searchInput.focus(); }
                    });
                    suggestionsEl.appendChild(li);
                });
            }

            suggestionsEl.hidden = false;
            searchInput.setAttribute('aria-expanded', 'true');
        }

        searchInput.addEventListener('input', function () {
            clearSelection();
            clearTimeout(debounceTimer);
            var val = searchInput.value.trim();

            if (val.length < 2) {
                suggestionsEl.hidden = true;
                suggestionsEl.innerHTML = '';
                searchInput.setAttribute('aria-expanded', 'false');
                return;
            }

            debounceTimer = setTimeout(function () {
                apiFetch('GET', '/shoutouts/users?search=' + encodeURIComponent(val))
                    .then(showSuggestions)
                    .catch(function () { suggestionsEl.hidden = true; });
            }, 250);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' && !suggestionsEl.hidden) {
                var first = suggestionsEl.querySelector('[role="option"]');
                if (first) { e.preventDefault(); first.focus(); }
            }
            if (e.key === 'Escape') { suggestionsEl.hidden = true; }
        });

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsEl.contains(e.target)) {
                suggestionsEl.hidden = true;
                // Restore display name if a user was selected and then field was changed
                if (selectedUser && searchInput.value !== selectedUser.name) {
                    clearSelection();
                }
            }
        });

        return {
            getSelectedId: function () { return hiddenIdInput.value ? parseInt(hiddenIdInput.value, 10) : null; },
            reset: function () { clearSelection(); searchInput.value = ''; suggestionsEl.hidden = true; suggestionsEl.innerHTML = ''; },
        };
    }

    // ── Compose form ──────────────────────────────────────────────────────────

    var autocomplete;

    function setupShoutoutCompose() {
        var triggerBtn     = document.getElementById('gca-shoutout-trigger');
        var formArea       = document.getElementById('gca-shoutout-form-area');
        var cancelBtn      = document.getElementById('gca-shoutout-cancel');
        var form           = document.getElementById('gca-shoutout-form');
        var submitBtn      = document.getElementById('gca-shoutout-submit');
        var searchInput    = document.getElementById('gca-shoutout-recipient-search');
        var hiddenId       = document.getElementById('gca-shoutout-recipient-id');
        var suggestions    = document.getElementById('gca-shoutout-suggestions');
        var textarea       = document.getElementById('gca-shoutout-content');
        var charsLeft      = document.getElementById('gca-shoutout-chars-left');
        var errorEl        = document.getElementById('gca-shoutout-error');
        var categoryGroup  = document.getElementById('gca-shoutout-category-group');
        var categorySelect = document.getElementById('gca-shoutout-category');

        if (!form) { return; }

        // Populate category dropdown
        apiFetch('GET', '/shoutouts/categories').then(function (categories) {
            if (!categorySelect) { return; }
            categorySelect.innerHTML = '<option value="">Select a category</option>';
            if (categories && categories.length) {
                categories.forEach(function (cat) {
                    var opt = document.createElement('option');
                    opt.value = cat.id;
                    opt.textContent = cat.name;
                    categorySelect.appendChild(opt);
                });
            }
            categorySelect.disabled = false;
        });

        autocomplete = setupRecipientAutocomplete(searchInput, suggestions, hiddenId);

        function openForm() {
            if (triggerBtn) { triggerBtn.setAttribute('aria-expanded', 'true'); }
            if (formArea)   { formArea.hidden = false; }
            if (searchInput) { searchInput.focus(); }
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', function () {
                categorySelect.classList.toggle('is-filled', !!categorySelect.value);
            });
        }

        function closeForm() {
            if (triggerBtn)    { triggerBtn.setAttribute('aria-expanded', 'false'); }
            if (formArea)      { formArea.hidden = true; }
            if (form)          { form.reset(); }
            if (charsLeft)     { charsLeft.textContent = '500'; }
            if (errorEl)       { errorEl.hidden = true; }
            if (categorySelect){ categorySelect.value = ''; categorySelect.classList.remove('is-filled'); categorySelect.removeAttribute('aria-invalid'); }
            if (autocomplete)  { autocomplete.reset(); }
            if (searchInput)   { searchInput.removeAttribute('aria-invalid'); }
            if (textarea)      { textarea.removeAttribute('aria-invalid'); }
        }

        if (triggerBtn) { triggerBtn.addEventListener('click', openForm); }
        if (cancelBtn)  { cancelBtn.addEventListener('click', closeForm); }

        // Sidebar button and "Shout-out a colleague" in compose footer
        var sidebarBtn = document.querySelector('.gca-cw__sidebar-btn--shoutout');
        if (sidebarBtn) {
            sidebarBtn.addEventListener('click', function () {
                var tabBtn = document.querySelector('.gca-cw-tabs__btn[data-tab="shoutouts"]');
                if (tabBtn && !tabBtn.disabled) { tabBtn.click(); }
                setTimeout(openForm, 50);
            });
        }

        if (textarea && charsLeft) {
            textarea.addEventListener('input', function () {
                charsLeft.textContent = 500 - textarea.value.length;
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var recipientId = autocomplete ? autocomplete.getSelectedId() : null;
            if (!recipientId) {
                if (errorEl) {
                    errorEl.textContent = 'Please select a colleague to shout out.';
                    errorEl.hidden = false;
                }
                if (searchInput) { searchInput.setAttribute('aria-invalid', 'true'); searchInput.focus(); }
                return;
            }
            if (searchInput) { searchInput.removeAttribute('aria-invalid'); }

            var content    = textarea ? textarea.value.trim() : '';
            if (!content) {
                if (textarea) { textarea.setAttribute('aria-invalid', 'true'); textarea.focus(); }
                return;
            }
            if (textarea) { textarea.removeAttribute('aria-invalid'); }

            var categoryId = categorySelect ? parseInt(categorySelect.value, 10) || 0 : 0;
            if (!categoryId) {
                if (errorEl) {
                    errorEl.textContent = 'Please select a category.';
                    errorEl.hidden = false;
                }
                if (categorySelect) { categorySelect.setAttribute('aria-invalid', 'true'); categorySelect.focus(); }
                return;
            }
            if (categorySelect) { categorySelect.removeAttribute('aria-invalid'); }

            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Posting…'; }
            if (errorEl)   { errorEl.hidden = true; }

            apiFetch('POST', '/shoutouts', { recipient_id: recipientId, content: content, category_id: categoryId })
                .then(function (shoutout) {
                    closeForm();

                    function prependTo(feedContainerEl) {
                        if (!feedContainerEl) { return; }
                        var ph = feedContainerEl.querySelector('.gca-cw-feed__status');
                        if (ph) { ph.remove(); }
                        var tmp = document.createElement('div');
                        tmp.innerHTML = renderShoutout(shoutout);
                        var el = tmp.firstElementChild;
                        if (!el) { return; }
                        feedContainerEl.insertBefore(el, feedContainerEl.firstChild);
                        if (window.GcaLc && typeof window.GcaLc.init === 'function') {
                            var lcEl = el.querySelector('.gca-lc');
                            if (lcEl) { window.GcaLc.init(lcEl); }
                        }
                    }

                    prependTo(shoutoutFeedEl);

                    // Also push to main feed if it's currently visible
                    var mainFeedPanel = document.getElementById('gca-panel-feed');
                    if (mainFeedPanel && !mainFeedPanel.hidden) {
                        prependTo(document.getElementById('gca-cw-feed'));
                    }
                })
                .catch(function (err) {
                    if (errorEl) {
                        errorEl.textContent = (err && err.error) ? err.error : 'Could not post shout-out. Please try again.';
                        errorEl.hidden = false;
                        errorEl.focus();
                    }
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Post shout-out';
                    }
                });
        });
    }

    // ── Event delegation (delete, dropdowns) ──────────────────────────────────

    function setupEvents() {
        document.body.addEventListener('click', function (e) {
            var deleteBtn = e.target.closest('[data-shoutout-action="delete"]');
            if (!deleteBtn) { return; }
            var shoutoutId = deleteBtn.dataset.shoutoutId;
            confirmDelete(deleteBtn, 'Are you sure you want to delete this shout-out?', 'This will permanently delete the shout-out. You cannot undo this action.')
                .then(function (confirmed) {
                    if (!confirmed) { return; }
                    var positions = [];
                    document.querySelectorAll('#gca-shoutout-' + shoutoutId).forEach(function (el) {
                        positions.push({ el: el, parent: el.parentNode, next: el.nextSibling });
                        el.remove();
                    });
                    if (window.gcaShowDeleteToast) { window.gcaShowDeleteToast('Shout-out deleted successfully.'); }
                    apiFetch('DELETE', '/shoutouts/' + shoutoutId)
                        .catch(function () {
                            positions.forEach(function (p) { if (p.parent) { p.parent.insertBefore(p.el, p.next); } });
                            alert('Could not delete. Please try again.');
                        });
                });
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('[id^="gca-shoutout-dd-"]').forEach(function (d) { d.hidden = true; });
        document.querySelectorAll('.gca-cw-post__more-btn[data-shoutout-id]').forEach(function (b) {
            b.setAttribute('aria-expanded', 'false');
        });
    }

    // ── Expose globally ───────────────────────────────────────────────────────

    window.GcaShoutouts = {
        renderShoutout:    renderShoutout,
        initShoutoutCard:  initShoutoutCard,
        onTabActivate:     onTabActivate,
    };

    // ── Init ──────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        shoutoutFeedEl      = document.getElementById('gca-shoutout-feed');
        shoutoutFooterEl    = document.getElementById('gca-shoutout-footer');
        shoutoutLoadMoreBtn = document.getElementById('gca-shoutout-load-more');
        shoutoutEndEl       = document.getElementById('gca-shoutout-end');
        shoutoutLoadingEl   = document.getElementById('gca-shoutout-loading');

        if (!shoutoutFeedEl) { return; }

        setupShoutoutCompose();
        setupEvents();

        if (shoutoutLoadMoreBtn) {
            shoutoutLoadMoreBtn.addEventListener('click', function () {
                loadShoutouts(shoutoutPage + 1);
            });
        }
    });
}());
