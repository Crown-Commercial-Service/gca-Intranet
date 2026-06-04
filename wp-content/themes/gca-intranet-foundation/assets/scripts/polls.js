/* global window, document, fetch, confirm */
(function () {
    'use strict';

    var cfg         = window.gcaCommunityData || {};
    var restUrl     = cfg.restUrl    || '';
    var nonce       = cfg.nonce      || '';
    var userAvatar  = cfg.currentUserAvatar || '';

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
            '<section class="gca-lc" data-post-id="' + postId + '" aria-label="Likes and comments">' +
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

    // ── Poll card renderer ─────────────────────────────────────────────────────

    function renderPollOptions(poll) {
        var showResults = !poll.is_open || poll.user_vote !== null || poll.is_own || poll.can_see_voters;
        var html = '<div class="gca-poll__options" data-poll-id="' + poll.id + '"' +
            (poll.is_open ? '' : ' data-closed="true"') + '>';

        poll.options.forEach(function (opt) {
            var isVoted  = poll.user_vote === opt.index;
            var cls      = 'gca-poll__option' + (isVoted ? ' gca-poll__option--voted' : '');
            var pctStyle = showResults ? 'width:' + opt.pct + '%' : 'width:0%';
            var tag      = poll.is_open && !showResults ? 'button type="button"' : (poll.is_open ? 'button type="button"' : 'div');
            var closeTag = (tag.indexOf('button') === 0) ? 'button' : 'div';

            html += '<' + tag + ' class="' + cls + '"' +
                (tag.indexOf('button') === 0 ? ' data-poll-vote="' + opt.index + '"' : '') + '>' +
                '<div class="gca-poll__option-fill" style="' + pctStyle + '" aria-hidden="true"></div>' +
                '<span class="gca-poll__option-text">' + esc(opt.text) + '</span>';

            if (showResults) {
                html += '<span class="gca-poll__option-pct">' + esc(opt.pct) + '%</span>';
            }

            html += '</' + closeTag + '>';
        });

        html += '</div>';
        return html;
    }

    function renderPoll(poll) {
        var authorHtml = poll.author_profile
            ? '<a href="' + esc(poll.author_profile) + '" class="gca-cw-post__author-name">' + esc(poll.author_name) + '</a>'
            : '<span class="gca-cw-post__author-name">' + esc(poll.author_name) + '</span>';

        var displayTeam = poll.author_team || '';
        var teamHtml = displayTeam
            ? '<span class="gca-cw-post__author-team">' + esc(displayTeam) + '</span>'
            : '';

        var badgeCls = poll.is_open ? 'gca-poll-badge gca-poll-badge--live' : 'gca-poll-badge gca-poll-badge--closed';
        var badgeLabel = poll.is_open ? 'Live poll' : 'Poll closed';

        var moreHtml = '';
        if (poll.can_delete) {
            moreHtml = (
                '<div class="gca-cw-post__more-wrap">' +
                '<button type="button" class="gca-cw-post__more-btn" aria-label="More options"' +
                ' aria-expanded="false" aria-haspopup="menu" data-poll-id="' + poll.id + '">' +
                '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' +
                '<circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>' +
                '</svg></button>' +
                '<ul class="gca-cw-post__dropdown" id="gca-poll-dd-' + poll.id + '" role="menu" hidden>' +
                '<li role="none"><button type="button" role="menuitem"' +
                ' class="gca-cw-post__dropdown-btn gca-cw-post__dropdown-btn--delete"' +
                ' data-poll-action="delete" data-poll-id="' + poll.id + '">' +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg> Delete poll</button></li>' +
                '</ul></div>'
            );
        }

        var footerParts = [];
        if (poll.total_votes > 0) {
            footerParts.push(esc(poll.total_votes) + ' vote' + (poll.total_votes !== 1 ? 's' : ''));
        } else {
            footerParts.push('No votes yet');
        }
        if (poll.time_left) {
            footerParts.push(esc(poll.time_left));
        }

        var votersHtml = '';

        return (
            '<article class="gca-cw-post gca-poll" id="gca-poll-' + poll.id + '">' +
            '<div class="gca-cw-post__header">' +
            '<div class="gca-cw-post__author-row">' +
            (poll.author_avatar ? '<img src="' + esc(poll.author_avatar) + '" alt="" width="48" height="48" class="gca-cw-post__avatar">' : '') +
            '<div class="gca-cw-post__author-info">' +
            authorHtml +
            '<div class="gca-cw-post__author-meta">' + teamHtml +
            '<span class="gca-cw-post__meta-dot"><time datetime="' + esc(poll.date_iso) + '">' + esc(relativeTime(poll.date_iso)) + '</time></span>' +
            '</div></div></div>' +
            '<span class="' + badgeCls + '">' + badgeLabel + '</span>' +
            moreHtml +
            '</div>' +
            (poll.poll_title ? '<p class="gca-poll__title">' + esc(poll.poll_title) + '</p>' : '') +
            '<p class="gca-poll__question">' + esc(poll.question) + '</p>' +
            renderPollOptions(poll) +
            '<div class="gca-poll__footer">' + footerParts.join(' &bull; ') + '</div>' +
            votersHtml +
            buildLcHtml(poll.id, poll.like_count, poll.user_has_liked, poll.comment_count) +
            '</article>'
        );
    }

    // ── Update poll card in place after a vote ─────────────────────────────────

    function updatePollCard(articleEl, poll) {
        // Replace options section
        var oldOpts = articleEl.querySelector('.gca-poll__options');
        if (oldOpts) {
            var tmp = document.createElement('div');
            tmp.innerHTML = renderPollOptions(poll);
            articleEl.replaceChild(tmp.firstElementChild, oldOpts);
        }

        // Update footer text
        var footerEl = articleEl.querySelector('.gca-poll__footer');
        if (footerEl) {
            var parts = [];
            parts.push(poll.total_votes + ' vote' + (poll.total_votes !== 1 ? 's' : ''));
            if (poll.time_left) { parts.push(poll.time_left); }
            footerEl.innerHTML = parts.join(' &bull; ');
        }

        // Wire up new vote buttons
        initPollCard(articleEl);
    }

    // ── Wire event handlers on a poll card ────────────────────────────────────

    function initPollCard(articleEl) {
        if (!articleEl) { return; }

        // Vote buttons
        var optBtns = articleEl.querySelectorAll('[data-poll-vote]');
        optBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var pollId  = articleEl.querySelector('.gca-poll__options').dataset.pollId;
                var optIdx  = parseInt(btn.dataset.pollVote, 10);
                var isClosed = articleEl.querySelector('.gca-poll__options').dataset.closed === 'true';
                if (isClosed) { return; }

                btn.disabled = true;
                apiFetch('POST', '/polls/' + pollId + '/vote', { option: optIdx })
                    .then(function (updated) {
                        updatePollCard(articleEl, updated);
                    })
                    .catch(function (err) {
                        btn.disabled = false;
                        alert((err && err.error) ? err.error : 'Could not record vote. Please try again.');
                    });
            });
        });
    }

    // ── Three-dot menu ────────────────────────────────────────────────────────

    function setupPollMenus(containerEl) {
        containerEl.addEventListener('click', function (e) {
            var moreBtn = e.target.closest('.gca-cw-post__more-btn[data-poll-id]');
            if (moreBtn) {
                e.stopPropagation();
                var pid = moreBtn.dataset.pollId;
                var dd  = document.getElementById('gca-poll-dd-' + pid);
                if (!dd) { return; }
                var opening = dd.hidden;
                closeAllPollDropdowns();
                if (opening) {
                    dd.hidden = false;
                    moreBtn.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            // Delete poll
            var deleteBtn = e.target.closest('[data-poll-action="delete"]');
            if (deleteBtn) {
                var pollId = deleteBtn.dataset.pollId;
                if (!confirm('Delete this poll? This cannot be undone.')) { return; }
                apiFetch('DELETE', '/polls/' + pollId)
                    .then(function () {
                        var el = document.getElementById('gca-poll-' + pollId);
                        if (el) { el.remove(); }
                    })
                    .catch(function () {
                        alert('Could not delete poll. Please try again.');
                    });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.gca-cw-post__more-wrap')) {
                closeAllPollDropdowns();
            }
        });
    }

    function closeAllPollDropdowns() {
        document.querySelectorAll('[id^="gca-poll-dd-"]').forEach(function (d) { d.hidden = true; });
        document.querySelectorAll('.gca-cw-post__more-btn[data-poll-id]').forEach(function (b) {
            b.setAttribute('aria-expanded', 'false');
        });
    }

    // ── Polls tab – feed ───────────────────────────────────────────────────────

    var pollFeedEl, pollFooterEl, pollLoadMoreBtn, pollEndEl, pollLoadingEl;
    var pollPage       = 1;
    var pollTotalPages = 1;
    var pollLoading    = false;
    var pollsLoaded    = false;

    function loadPolls(page) {
        if (pollLoading) { return; }
        pollLoading = true;
        if (pollLoadMoreBtn) { pollLoadMoreBtn.disabled = true; }

        apiFetch('GET', '/polls?page=' + page + '&per_page=15')
            .then(function (data) {
                pollTotalPages = data.total_pages || 1;
                pollPage       = data.page || page;

                if (pollLoadingEl) { pollLoadingEl.hidden = true; }

                if (!data.polls || !data.polls.length) {
                    if (page === 1) {
                        var empty = document.createElement('p');
                        empty.className = 'gca-cw-feed__status';
                        empty.textContent = 'No polls yet — create the first one!';
                        pollFeedEl.appendChild(empty);
                    }
                    if (pollFooterEl) { pollFooterEl.hidden = true; }
                    if (pollEndEl)    { pollEndEl.hidden = false; }
                    return;
                }

                data.polls.forEach(function (poll) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = renderPoll(poll);
                    var el = tmp.firstElementChild;
                    if (!el) { return; }
                    pollFeedEl.appendChild(el);
                    initPollCard(el);
                    if (window.GcaLc && typeof window.GcaLc.init === 'function') {
                        var lcEl = el.querySelector('.gca-lc');
                        if (lcEl) { window.GcaLc.init(lcEl); }
                    }
                });

                if (pollPage < pollTotalPages) {
                    if (pollFooterEl) { pollFooterEl.hidden = false; }
                    if (pollEndEl)    { pollEndEl.hidden = true; }
                } else {
                    if (pollFooterEl) { pollFooterEl.hidden = true; }
                    if (pollEndEl)    { pollEndEl.hidden = false; }
                }
            })
            .catch(function () {
                if (pollLoadingEl) { pollLoadingEl.hidden = true; }
                var errEl = document.createElement('p');
                errEl.className = 'gca-cw-feed__status';
                errEl.textContent = 'Could not load polls. Please refresh.';
                if (pollFeedEl) { pollFeedEl.appendChild(errEl); }
            })
            .finally(function () {
                pollLoading = false;
                if (pollLoadMoreBtn) { pollLoadMoreBtn.disabled = false; }
            });
    }

    function onTabActivate() {
        if (!pollsLoaded) {
            pollsLoaded = true;
            loadPolls(1);
        }
    }

    // ── Poll compose form ──────────────────────────────────────────────────────

    function setupPollCompose() {
        var triggerBtn  = document.getElementById('gca-poll-trigger');
        var formArea    = document.getElementById('gca-poll-form-area');
        var cancelBtn   = document.getElementById('gca-poll-cancel');
        var form        = document.getElementById('gca-poll-form');
        var submitBtn   = document.getElementById('gca-poll-submit');
        var questionEl  = document.getElementById('gca-poll-question');
        var optionsList = document.getElementById('gca-poll-options-list');
        var addOptBtn   = document.getElementById('gca-poll-add-option');
        var errorEl     = document.getElementById('gca-poll-error');

        if (!form) { return; }

        function openForm() {
            if (triggerBtn) { triggerBtn.setAttribute('aria-expanded', 'true'); }
            if (formArea)   { formArea.hidden = false; }
            if (questionEl) { questionEl.focus(); }
        }

        function closeForm() {
            if (triggerBtn)  { triggerBtn.setAttribute('aria-expanded', 'false'); }
            if (formArea)    { formArea.hidden = true; }
            if (form)        { form.reset(); }
            if (errorEl)     { errorEl.hidden = true; }
            if (questionEl)  { questionEl.removeAttribute('aria-invalid'); }
            // Reset options to 2
            resetOptions();
        }

        function resetOptions() {
            if (!optionsList) { return; }
            var inputs = optionsList.querySelectorAll('.gca-poll-compose__option-input');
            inputs.forEach(function (inp, idx) {
                if (idx >= 2) {
                    inp.closest('.gca-poll-compose__option-row').remove();
                } else {
                    inp.value = '';
                }
            });
            updateAddOptionBtn();
        }

        var MAX_OPTIONS = 5;

        function updateAddOptionBtn() {
            if (!addOptBtn || !optionsList) { return; }
            var count = optionsList.querySelectorAll('.gca-poll-compose__option-input').length;
            addOptBtn.disabled = count >= MAX_OPTIONS;
        }

        if (triggerBtn) { triggerBtn.addEventListener('click', openForm); }
        if (cancelBtn)  { cancelBtn.addEventListener('click', closeForm); }

        // Also wire the "Create poll" button in the compose footer of the feed tab
        var cwPollBtn = document.getElementById('gca-cw-poll-btn');
        if (cwPollBtn) {
            cwPollBtn.addEventListener('click', function () {
                // Switch to polls tab
                var pollsTabBtn = document.querySelector('.gca-cw-tabs__btn[data-tab="polls"]');
                if (pollsTabBtn && !pollsTabBtn.disabled) {
                    pollsTabBtn.click();
                }
                // Slight delay to let the panel appear, then open compose
                setTimeout(openForm, 50);
            });
        }

        if (addOptBtn) {
            addOptBtn.addEventListener('click', function () {
                if (!optionsList) { return; }
                var count = optionsList.querySelectorAll('.gca-poll-compose__option-input').length;
                if (count >= MAX_OPTIONS) { return; }

                var idx = count + 1;
                var row = document.createElement('div');
                row.className = 'gca-poll-compose__option-row';
                row.innerHTML = (
                    '<input type="text" class="govuk-input gca-poll-compose__option-input"' +
                    ' placeholder="Option ' + idx + '" maxlength="100" aria-label="Option ' + idx + '">' +
                    '<button type="button" class="gca-poll-compose__option-remove" aria-label="Remove option">&times;</button>'
                );
                row.querySelector('.gca-poll-compose__option-remove').addEventListener('click', function () {
                    row.remove();
                    // Re-number placeholders
                    optionsList.querySelectorAll('.gca-poll-compose__option-input').forEach(function (inp, i) {
                        inp.placeholder = 'Option ' + (i + 1);
                        inp.setAttribute('aria-label', 'Option ' + (i + 1));
                    });
                    updateAddOptionBtn();
                });
                optionsList.appendChild(row);
                updateAddOptionBtn();
                row.querySelector('input').focus();
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var question = questionEl ? questionEl.value.trim() : '';
            if (!question) {
                if (questionEl) { questionEl.setAttribute('aria-invalid', 'true'); questionEl.focus(); }
                return;
            }
            if (questionEl) { questionEl.removeAttribute('aria-invalid'); }

            var options = [];
            if (optionsList) {
                optionsList.querySelectorAll('.gca-poll-compose__option-input').forEach(function (inp) {
                    var v = inp.value.trim();
                    if (v) { options.push(v); }
                });
            }

            if (options.length < 2) {
                if (errorEl) {
                    errorEl.textContent = 'Please add at least 2 options.';
                    errorEl.hidden = false;
                    errorEl.focus();
                }
                return;
            }

            var deadlineInput  = document.getElementById('gca-poll-deadline');
            var titleInput     = document.getElementById('gca-poll-title');
            var teamInput      = document.getElementById('gca-poll-team');
            var deadline   = deadlineInput && deadlineInput.value ? new Date(deadlineInput.value).toISOString() : null;
            var poll_title = titleInput  ? titleInput.value.trim()  : '';
            var poll_team  = teamInput   ? teamInput.value.trim()   : '';

            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Creating…'; }
            if (errorEl)   { errorEl.hidden = true; }

            apiFetch('POST', '/polls', { question: question, options: options, deadline: deadline, poll_title: poll_title, poll_team: poll_team })
                .then(function (poll) {
                    closeForm();

                    // Add to polls tab feed
                    if (pollFeedEl) {
                        var ph = pollFeedEl.querySelector('.gca-cw-feed__status');
                        if (ph) { ph.remove(); }

                        var tmp = document.createElement('div');
                        tmp.innerHTML = renderPoll(poll);
                        var el = tmp.firstElementChild;
                        if (el) {
                            pollFeedEl.insertBefore(el, pollFeedEl.firstChild);
                            initPollCard(el);
                            if (window.GcaLc && typeof window.GcaLc.init === 'function') {
                                var lcEl = el.querySelector('.gca-lc');
                                if (lcEl) { window.GcaLc.init(lcEl); }
                            }
                        }
                    }

                    // Also inject into main feed if visible
                    var mainFeed = document.getElementById('gca-cw-feed');
                    if (mainFeed && !document.getElementById('gca-panel-feed').hidden) {
                        var tmp2 = document.createElement('div');
                        tmp2.innerHTML = renderPoll(poll);
                        var el2 = tmp2.firstElementChild;
                        if (el2) {
                            mainFeed.insertBefore(el2, mainFeed.firstChild);
                            initPollCard(el2);
                            if (window.GcaLc && typeof window.GcaLc.init === 'function') {
                                var lcEl2 = el2.querySelector('.gca-lc');
                                if (lcEl2) { window.GcaLc.init(lcEl2); }
                            }
                        }
                    }
                })
                .catch(function (err) {
                    if (errorEl) {
                        errorEl.textContent = (err && err.error) ? err.error : 'Could not create poll. Please try again.';
                        errorEl.hidden = false;
                        errorEl.focus();
                    }
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Create poll';
                    }
                });
        });
    }

    // ── Expose globally (used by community-wall.js appendItem) ────────────────

    window.GcaPolls = {
        renderPoll:    renderPoll,
        initPollCard:  initPollCard,
        onTabActivate: onTabActivate,
    };

    // ── Init ──────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        pollFeedEl      = document.getElementById('gca-poll-feed');
        pollFooterEl    = document.getElementById('gca-poll-footer');
        pollLoadMoreBtn = document.getElementById('gca-poll-load-more');
        pollEndEl       = document.getElementById('gca-poll-end');
        pollLoadingEl   = document.getElementById('gca-poll-loading');

        if (!pollFeedEl) { return; }

        setupPollCompose();
        setupPollMenus(document.body);

        if (pollLoadMoreBtn) {
            pollLoadMoreBtn.addEventListener('click', function () {
                loadPolls(pollPage + 1);
            });
        }
    });
}());
