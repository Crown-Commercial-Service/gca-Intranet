/* global window, document, fetch */
(function () {
    'use strict';

    var cfg           = window.gcaQaData || {};
    var restUrl       = cfg.restUrl           || '';
    var nonce         = cfg.nonce             || '';
    var userAvatar    = cfg.currentUserAvatar || '';
    var currentUserId = cfg.currentUserId     || 0;

    // ── Utilities ─────────────────────────────────────────────────────────────

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

    // ── LC HTML builder (mirrors community-wall.js) ───────────────────────────

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
            ' name="content" rows="3" placeholder="Write a comment… Use @ to mention someone"' +
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

    // ── Render functions ───────────────────────────────────────────────────────

    function renderAnsweredQuestion(q) {
        var answererNameHtml = q.answerer_profile
            ? '<a href="' + esc(q.answerer_profile) + '" class="gca-qa-card__answerer-name">' + esc(q.answerer_name) + '</a>'
            : '<span class="gca-qa-card__answerer-name">' + esc(q.answerer_name) + '</span>';

        var teamHtml = q.answerer_team
            ? '<span class="gca-qa-card__answerer-team">' + esc(q.answerer_team) + '</span>'
            : '';

        var moreMenuHtml = '';
        if (q.can_moderate || q.is_own) {
            moreMenuHtml = (
                '<div class="gca-cw-post__more-wrap">' +
                '<button type="button" class="gca-cw-post__more-btn" aria-label="More options"' +
                ' aria-expanded="false" aria-haspopup="menu" data-qa-id="' + q.id + '">' +
                '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' +
                '<circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>' +
                '</svg></button>' +
                '<ul class="gca-cw-post__dropdown" id="gca-qa-dd-' + q.id + '" role="menu" hidden>' +
                (q.can_moderate
                    ? '<li role="none"><button type="button" role="menuitem" class="gca-cw-post__dropdown-btn"' +
                      ' data-qa-action="edit-answer" data-qa-id="' + q.id + '">Edit answer</button></li>'
                    : '') +
                '<li role="none"><button type="button" role="menuitem"' +
                ' class="gca-cw-post__dropdown-btn gca-cw-post__dropdown-btn--delete"' +
                ' data-qa-action="delete-question" data-qa-id="' + q.id + '">Delete question</button></li>' +
                '</ul></div>'
            );
        }

        return (
            '<article class="gca-qa-card gca-qa-card--answered" id="gca-qa-q-' + q.id + '">' +
            '<div class="gca-qa-card__header">' +
            '<div class="gca-qa-card__answerer-row">' +
            (q.answerer_avatar ? '<img src="' + esc(q.answerer_avatar) + '" alt="" width="48" height="48" class="gca-qa-card__avatar">' : '') +
            '<div class="gca-qa-card__answerer-info">' +
            '<div class="gca-qa-card__answerer-title">' + answererNameHtml + '<span class="gca-qa-card__answered-label"> answered a question</span></div>' +
            '<div class="gca-qa-card__meta">' + teamHtml +
            '<span class="gca-cw-post__meta-dot"><time datetime="' + esc(q.answered_at_iso) + '">' + esc(relativeTime(q.answered_at_iso)) + '</time></span>' +
            '</div></div></div>' +
            '<span class="gca-qa-badge gca-qa-badge--official">Official answer</span>' +
            moreMenuHtml +
            '</div>' +

            '<div class="gca-qa-card__question-block">' +
            '<p class="gca-qa-card__question-label">Question from employee:</p>' +
            '<p class="gca-qa-card__question-text">“' + q.question_html + '”</p>' +
            '</div>' +

            '<div class="gca-qa-card__answer-block">' + q.answer_html + '</div>' +

            buildLcHtml(q.id, q.like_count, q.user_has_liked, q.comment_count) +
            '</article>'
        );
    }

    function renderPendingCard(q) {
        return (
            '<div class="gca-qa-pending-card" id="gca-qa-pending-' + q.id + '">' +
            '<div class="gca-qa-pending-card__header">' +
            '<span class="gca-qa-badge gca-qa-badge--pending">UNDER REVIEW</span>' +
            '<time class="gca-qa-pending-card__date" datetime="' + esc(q.date_iso) + '">' + esc(relativeTime(q.date_iso)) + '</time>' +
            '</div>' +
            '<p class="gca-qa-pending-card__question">' + esc(q.question_raw) + '</p>' +
            '</div>'
        );
    }

    function renderSidebarPendingItem(q) {
        return (
            '<li class="gca-qa-sidebar-pending__item">' +
            '<div class="gca-qa-sidebar-pending__meta">' +
            '<span class="gca-qa-badge gca-qa-badge--pending gca-qa-badge--sm">UNDER REVIEW</span>' +
            '<time class="gca-qa-sidebar-pending__date" datetime="' + esc(q.date_iso) + '">' + esc(relativeTime(q.date_iso)) + '</time>' +
            '</div>' +
            '<p class="gca-qa-sidebar-pending__text">' + esc(q.question_raw) + '</p>' +
            '</li>'
        );
    }

    // ── Feed state ─────────────────────────────────────────────────────────────

    var qaLoaded      = false;
    var qaPage        = 1;
    var qaTotalPages  = 1;
    var qaIsLoading   = false;

    var qaFeedEl, qaFooterEl, qaLoadMoreBtn, qaEndEl, qaLoadingEl;
    var qaPendingBannerEl, qaPendingListEl;
    var sidebarPendingEl, sidebarPendingListEl;

    function initLcForQuestion(articleEl) {
        var lcEl = articleEl ? articleEl.querySelector('.gca-lc') : null;
        if (lcEl && window.GcaLc && typeof window.GcaLc.init === 'function') {
            window.GcaLc.init(lcEl);
        }
    }

    function appendQuestion(q) {
        var tmp = document.createElement('div');
        tmp.innerHTML = renderAnsweredQuestion(q);
        var articleEl = tmp.firstElementChild;
        if (!articleEl || !qaFeedEl) { return; }
        qaFeedEl.appendChild(articleEl);
        initLcForQuestion(articleEl);
    }

    function populatePending(myPending) {
        if (!myPending || !myPending.length) { return; }

        if (qaPendingBannerEl) { qaPendingBannerEl.hidden = false; }
        if (qaPendingListEl) {
            qaPendingListEl.innerHTML = myPending.map(renderPendingCard).join('');
        }

        if (sidebarPendingEl) { sidebarPendingEl.hidden = false; }
        if (sidebarPendingListEl) {
            sidebarPendingListEl.innerHTML = myPending.slice(0, 3).map(renderSidebarPendingItem).join('');
        }
    }

    function loadQaQuestions(page) {
        if (qaIsLoading) { return; }
        qaIsLoading = true;
        if (qaLoadMoreBtn) { qaLoadMoreBtn.disabled = true; }

        apiFetch('GET', '/qa/questions?page=' + page + '&per_page=15')
            .then(function (data) {
                qaTotalPages = data.total_pages || 1;
                qaPage       = data.page        || page;

                if (qaLoadingEl) { qaLoadingEl.hidden = true; }

                if (page === 1) { populatePending(data.my_pending); }

                if (!data.answered || !data.answered.length) {
                    if (page === 1) {
                        var empty = document.createElement('p');
                        empty.className = 'gca-cw-feed__status';
                        empty.textContent = 'No answered questions yet — be the first to ask!';
                        if (qaFeedEl) { qaFeedEl.appendChild(empty); }
                    }
                    if (qaFooterEl) { qaFooterEl.hidden = true; }
                    if (qaEndEl)    { qaEndEl.hidden = false; }
                    return;
                }

                data.answered.forEach(appendQuestion);

                if (qaPage < qaTotalPages) {
                    if (qaFooterEl) { qaFooterEl.hidden = false; }
                    if (qaEndEl)    { qaEndEl.hidden = true; }
                } else {
                    if (qaFooterEl) { qaFooterEl.hidden = true; }
                    if (qaEndEl)    { qaEndEl.hidden = false; }
                }
            })
            .catch(function () {
                if (qaLoadingEl) { qaLoadingEl.hidden = true; }
                var errEl = document.createElement('p');
                errEl.className = 'gca-cw-feed__status';
                errEl.textContent = 'Could not load questions. Please refresh the page.';
                if (qaFeedEl) { qaFeedEl.appendChild(errEl); }
            })
            .finally(function () {
                qaIsLoading = false;
                if (qaLoadMoreBtn) { qaLoadMoreBtn.disabled = false; }
            });
    }

    // ── Tab switching ──────────────────────────────────────────────────────────

    function switchToTab(tabName) {
        var feedPanel      = document.getElementById('gca-panel-feed');
        var qaPanel        = document.getElementById('gca-panel-qa');
        var pollsPanel     = document.getElementById('gca-panel-polls');
        var shoutoutsPanel = document.getElementById('gca-panel-shoutouts');

        document.querySelectorAll('.gca-cw-tabs__btn').forEach(function (b) {
            var isTarget = b.dataset.tab === tabName;
            b.classList.toggle('gca-cw-tabs__btn--active', isTarget);
            b.setAttribute('aria-pressed', isTarget ? 'true' : 'false');
        });

        if (feedPanel)      { feedPanel.hidden      = tabName !== 'feed'; }
        if (qaPanel)        { qaPanel.hidden        = tabName !== 'qa'; }
        if (pollsPanel)     { pollsPanel.hidden     = tabName !== 'polls'; }
        if (shoutoutsPanel) { shoutoutsPanel.hidden = tabName !== 'shoutouts'; }

        if (tabName === 'qa' && !qaLoaded) {
            qaLoaded = true;
            loadQaQuestions(1);
        }

        if (tabName === 'polls' && window.GcaPolls && typeof window.GcaPolls.onTabActivate === 'function') {
            window.GcaPolls.onTabActivate();
        }

        if (tabName === 'shoutouts' && window.GcaShoutouts && typeof window.GcaShoutouts.onTabActivate === 'function') {
            window.GcaShoutouts.onTabActivate();
        }
    }

    function initTabs() {
        document.querySelectorAll('.gca-cw-tabs__btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.disabled) { return; }
                switchToTab(btn.dataset.tab);
            });
        });
    }

    // ── Ask question form ──────────────────────────────────────────────────────

    var openAskForm, closeAskForm;

    function setupAskForm() {
        var triggerBtn = document.getElementById('gca-qa-trigger');
        var formArea   = document.getElementById('gca-qa-form-area');
        var cancelBtn  = document.getElementById('gca-qa-cancel');
        var form       = document.getElementById('gca-qa-form');
        var submitBtn  = document.getElementById('gca-qa-submit');
        var textarea   = document.getElementById('gca-qa-content');
        var charsLeft  = document.getElementById('gca-qa-chars-left');
        var charHint   = document.getElementById('gca-qa-char-hint');
        var errorEl    = document.getElementById('gca-qa-error');

        if (!form) { return; }

        openAskForm = function () {
            if (triggerBtn) { triggerBtn.setAttribute('aria-expanded', 'true'); }
            if (formArea)   { formArea.hidden = false; }
            if (textarea)   { textarea.focus(); }
        };

        closeAskForm = function () {
            if (triggerBtn) { triggerBtn.setAttribute('aria-expanded', 'false'); }
            if (formArea)   { formArea.hidden = true; }
            if (form)       { form.reset(); }
            if (charsLeft)  { charsLeft.textContent = '500'; }
            if (charHint)   { charHint.classList.remove('gca-cw-compose__char-hint--warning'); }
            if (errorEl)    { errorEl.hidden = true; }
        };

        if (triggerBtn) { triggerBtn.addEventListener('click', openAskForm); }
        if (cancelBtn)  { cancelBtn.addEventListener('click', closeAskForm); }

        if (textarea && charsLeft) {
            textarea.addEventListener('input', function () {
                var remaining = 500 - textarea.value.length;
                charsLeft.textContent = remaining;
                if (charHint) {
                    charHint.classList.toggle('gca-cw-compose__char-hint--warning', remaining < 50);
                }
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var content = textarea ? textarea.value.trim() : '';
            if (!content) { if (textarea) { textarea.focus(); } return; }

            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Submitting…'; }

            apiFetch('POST', '/qa/questions', { content: content })
                .then(function (question) {
                    closeAskForm();

                    // Show pending banner and add new card
                    if (qaPendingBannerEl) { qaPendingBannerEl.hidden = false; }
                    if (qaPendingListEl) {
                        var tmp = document.createElement('div');
                        tmp.innerHTML = renderPendingCard(question);
                        qaPendingListEl.insertBefore(tmp.firstElementChild, qaPendingListEl.firstChild);
                    }

                    // Add to sidebar
                    if (sidebarPendingEl) { sidebarPendingEl.hidden = false; }
                    if (sidebarPendingListEl) {
                        var stmp = document.createElement('div');
                        stmp.innerHTML = renderSidebarPendingItem(question);
                        sidebarPendingListEl.insertBefore(stmp.firstElementChild, sidebarPendingListEl.firstChild);
                    }
                })
                .catch(function () {
                    if (errorEl) {
                        errorEl.textContent = 'Could not submit question. Please try again.';
                        errorEl.hidden = false;
                    }
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit question';
                    }
                });
        });
    }

    // ── Feed event delegation (dropdown menu, delete, edit-answer) ─────────────

    function closeAllQaDropdowns() {
        document.querySelectorAll('[id^="gca-qa-dd-"]').forEach(function (d) { d.hidden = true; });
        document.querySelectorAll('[data-qa-id]').forEach(function (b) {
            if (b.classList.contains('gca-cw-post__more-btn')) { b.setAttribute('aria-expanded', 'false'); }
        });
    }

    function setupQaFeedEvents() {
        if (!qaFeedEl) { return; }

        qaFeedEl.addEventListener('click', function (e) {

            // Three-dot toggle
            var moreBtn = e.target.closest('.gca-cw-post__more-btn[data-qa-id]');
            if (moreBtn) {
                e.stopPropagation();
                var qid      = moreBtn.dataset.qaId;
                var dropdown = document.getElementById('gca-qa-dd-' + qid);
                if (!dropdown) { return; }
                var opening = dropdown.hidden;
                closeAllQaDropdowns();
                if (opening) {
                    dropdown.hidden = false;
                    moreBtn.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            // Delete question
            var deleteBtn = e.target.closest('[data-qa-action="delete-question"]');
            if (deleteBtn) {
                var delId = deleteBtn.dataset.qaId;
                if (!confirm('Are you sure you want to delete this question?')) { return; }
                apiFetch('DELETE', '/qa/questions/' + delId)
                    .then(function () {
                        var cardEl = document.getElementById('gca-qa-q-' + delId);
                        if (cardEl) { cardEl.remove(); }
                    })
                    .catch(function () {
                        alert('Could not delete question. Please try again.');
                    });
                return;
            }

            // Edit answer (moderator inline edit)
            var editBtn = e.target.closest('[data-qa-action="edit-answer"]');
            if (editBtn) {
                closeAllQaDropdowns();
                var editId  = editBtn.dataset.qaId;
                var cardEl  = document.getElementById('gca-qa-q-' + editId);
                if (!cardEl) { return; }

                var answerBlock = cardEl.querySelector('.gca-qa-card__answer-block');
                if (!answerBlock) { return; }

                var currentText = answerBlock.innerText || '';

                // Replace answer block with inline edit form
                var editForm = document.createElement('div');
                editForm.className = 'gca-qa-edit-form';
                editForm.innerHTML = (
                    '<textarea class="govuk-textarea gca-qa-edit-form__textarea" rows="6" maxlength="5000">' + esc(currentText.trim()) + '</textarea>' +
                    '<div class="gca-qa-edit-form__actions gca-lc-delete-modal__actions">' +
                    '<button type="button" class="gca-lc-delete-modal__confirm" data-qa-edit-save="' + editId + '">Save answer</button>' +
                    '<button type="button" class="gca-lc-delete-modal__cancel" data-qa-edit-cancel="' + editId + '">Cancel</button>' +
                    '</div>'
                );
                answerBlock.replaceWith(editForm);
                editForm.querySelector('textarea').focus();
            }
        });

        // Edit answer – save / cancel (delegated on document since editForm is created dynamically)
        document.addEventListener('click', function (e) {
            var saveBtn = e.target.closest('[data-qa-edit-save]');
            if (saveBtn) {
                var saveId   = saveBtn.dataset.qaEditSave;
                var editForm = saveBtn.closest('.gca-qa-edit-form');
                var textarea = editForm ? editForm.querySelector('textarea') : null;
                var newText  = textarea ? textarea.value.trim() : '';

                if (!newText) { return; }

                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving…';

                apiFetch('POST', '/qa/questions/' + saveId + '/answer', { answer: newText })
                    .then(function (q) {
                        var cardEl = document.getElementById('gca-qa-q-' + saveId);
                        if (!cardEl) { return; }
                        // Rebuild the answer block
                        var newAnswerBlock = document.createElement('div');
                        newAnswerBlock.className = 'gca-qa-card__answer-block';
                        newAnswerBlock.innerHTML = q.answer_html;
                        if (editForm) { editForm.replaceWith(newAnswerBlock); }
                    })
                    .catch(function () {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Save answer';
                        alert('Could not save answer. Please try again.');
                    });
                return;
            }

            var cancelBtn = e.target.closest('[data-qa-edit-cancel]');
            if (cancelBtn) {
                var cancelId  = cancelBtn.dataset.qaEditCancel;
                var editForm2 = cancelBtn.closest('.gca-qa-edit-form');
                if (!editForm2) { return; }
                // Restore original answer (reload the card's answer block from the DOM we already have)
                // Simplest: reload question from API
                apiFetch('GET', '/qa/questions?per_page=1&page=1').then(function () {
                    // noop — just close the form, user can refresh for latest text
                }).catch(function () {});
                // Replace edit form with a placeholder telling user to refresh
                var restoredBlock = document.createElement('div');
                restoredBlock.className = 'gca-qa-card__answer-block';
                restoredBlock.innerHTML = '<p style="color:#666;font-style:italic">Reload to see current answer.</p>';
                editForm2.replaceWith(restoredBlock);
            }
        });

        // Close dropdowns on outside click / Escape
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.gca-cw-post__more-wrap')) { closeAllQaDropdowns(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeAllQaDropdowns(); }
        });
    }

    // ── Sidebar wiring ─────────────────────────────────────────────────────────

    function setupSidebar() {
        // "Ask a question" sidebar button → switch to Q&A tab + open form
        var sidebarAskBtn = document.querySelector('.gca-cw__sidebar-btn--question');
        if (sidebarAskBtn) {
            sidebarAskBtn.addEventListener('click', function () {
                switchToTab('qa');
                if (typeof openAskForm === 'function') {
                    openAskForm();
                    var composeEl = document.getElementById('gca-qa-compose');
                    if (composeEl) { composeEl.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                }
            });
        }

        // "View all my submissions" link → switch to Q&A tab
        var viewAllBtn = document.getElementById('gca-qa-view-all-btn');
        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', function () {
                switchToTab('qa');
                var pendingBanner = document.getElementById('gca-qa-pending-banner');
                if (pendingBanner) { pendingBanner.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
            });
        }
    }

    // ── Expose globally (used by community-wall.js mixed feed) ───────────────

    window.GcaQa = {
        renderAnsweredQuestion: renderAnsweredQuestion,
    };

    // ── Init ──────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        qaFeedEl          = document.getElementById('gca-qa-feed');
        qaFooterEl        = document.getElementById('gca-qa-footer');
        qaLoadMoreBtn     = document.getElementById('gca-qa-load-more');
        qaEndEl           = document.getElementById('gca-qa-end');
        qaLoadingEl       = document.getElementById('gca-qa-loading');
        qaPendingBannerEl = document.getElementById('gca-qa-pending-banner');
        qaPendingListEl   = document.getElementById('gca-qa-pending-list');
        sidebarPendingEl      = document.getElementById('gca-qa-sidebar-pending');
        sidebarPendingListEl  = document.getElementById('gca-qa-sidebar-pending-list');

        if (!qaFeedEl) { return; }

        initTabs();
        setupAskForm();
        setupQaFeedEvents();
        setupSidebar();

        if (qaLoadMoreBtn) {
            qaLoadMoreBtn.addEventListener('click', function () {
                loadQaQuestions(qaPage + 1);
            });
        }
    });
}());
