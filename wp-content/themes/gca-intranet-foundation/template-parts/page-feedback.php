<?php
/**
 * Page Feedback Widget
 *
 * Rendered above the footer on all singular pages when the 'page-feedback'
 * feature flag is enabled. Embeds three Gravity Forms (Yes / No / Report)
 * created programmatically on first use.
 */

if (!gca_flag_enabled('page-feedback')) {
    return;
}

if (!function_exists('gravity_form')) {
    return;
}

$yes_form_id    = (int) get_option('gca_pf_yes_form_id', 0);
$no_form_id     = (int) get_option('gca_pf_no_form_id', 0);
$report_form_id = (int) get_option('gca_pf_report_form_id', 0);

if (!$yes_form_id || !$no_form_id || !$report_form_id) {
    return;
}
?>

<div class="gca-page-useful" id="gca-page-useful">

  <?php /* ── Initial bar ─────────────────────────────────────────────── */ ?>
  <div class="gca-pf-bar" id="gca-pf-bar">
    <div class="govuk-width-container gca-pf-bar-inner">

      <div class="gca-pf-bar-left">
        <p class="gca-pf-question">Is this page useful?</p>
        <div class="gca-pf-yesno">
          <button class="gca-pf-btn-yesno" type="button" data-pf-panel="yes">Yes</button>
          <button class="gca-pf-btn-yesno" type="button" data-pf-panel="no">No</button>
        </div>
      </div>

      <button class="gca-pf-btn-report" type="button" data-pf-panel="report">
        Report a problem with this page
      </button>

    </div>
  </div>

  <?php /* ── Yes panel ───────────────────────────────────────────────── */ ?>
  <div class="gca-page-useful__panel" id="gca-pf-panel-yes" hidden aria-hidden="true">
    <div class="govuk-width-container">
      <hr class="govuk-section-break govuk-section-break--visible gca-pf-separator">
      <h2 class="gca-pf-panel-heading">Thanks for letting us know</h2>
      <?php gravity_form($yes_form_id, false, false, false, null, true, 1); ?>
    </div>
  </div>

  <?php /* ── No panel ────────────────────────────────────────────────── */ ?>
  <div class="gca-page-useful__panel" id="gca-pf-panel-no" hidden aria-hidden="true">
    <div class="govuk-width-container">
      <hr class="govuk-section-break govuk-section-break--visible gca-pf-separator">
      <h2 class="gca-pf-panel-heading">Thanks for letting us know</h2>
      <?php gravity_form($no_form_id, false, false, false, null, true, 10); ?>
    </div>
  </div>

  <?php /* ── Report panel ────────────────────────────────────────────── */ ?>
  <div class="gca-page-useful__panel" id="gca-pf-panel-report" hidden aria-hidden="true">
    <div class="govuk-width-container">
      <hr class="govuk-section-break govuk-section-break--visible gca-pf-separator">
      <h2 class="gca-pf-panel-heading">Help us improve our intranet</h2>
      <p class="gca-pf-report-hint">
        Don&rsquo;t include personal or financial information like your National Insurance number or credit card details.
      </p>
      <?php gravity_form($report_form_id, false, false, false, null, true, 20); ?>
    </div>
  </div>

  <?php /* ── Thank-you state ─────────────────────────────────────────── */ ?>
  <div class="gca-pf-thankyou" id="gca-pf-thankyou" hidden aria-hidden="true">
    <div class="govuk-width-container gca-pf-thankyou-inner">
      <p class="gca-pf-thankyou-text">Thank you for your feedback</p>
    </div>
  </div>

</div>

<script>
(function () {
  var widget   = document.getElementById('gca-page-useful');
  if (!widget) { return; }

  var bar      = document.getElementById('gca-pf-bar');
  var thankyou = document.getElementById('gca-pf-thankyou');
  var panels   = widget.querySelectorAll('.gca-page-useful__panel');

  function show(el) { el.style.display = ''; el.removeAttribute('hidden'); el.removeAttribute('aria-hidden'); }
  function hide(el) { el.style.display = 'none'; el.setAttribute('hidden', ''); el.setAttribute('aria-hidden', 'true'); }
  function hideAllPanels() { panels.forEach(hide); }

  function reset() {
    hideAllPanels();
    hide(thankyou);
    show(bar);
  }

  function openPanel(id) {
    hideAllPanels();
    hide(thankyou);
    hide(bar);
    var target = document.getElementById('gca-pf-panel-' + id);
    if (target) {
      show(target);
      var first = target.querySelector('input,textarea,button');
      if (first) { first.focus(); }
    }
  }

  // Trigger buttons (Yes / No / Report)
  widget.querySelectorAll('[data-pf-panel]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openPanel(this.getAttribute('data-pf-panel'));
    });
  });

  // Inject Cancel button into each GF form footer
  function injectCancel(panel) {
    var footer = panel.querySelector('.gform_footer, .gform_page_footer');
    if (!footer || footer.querySelector('.gca-pf-cancel')) { return; }
    var btn = document.createElement('button');
    btn.type        = 'button';
    btn.className   = 'gca-pf-cancel';
    btn.textContent = 'Cancel';
    btn.addEventListener('click', reset);
    footer.appendChild(btn);
  }

  panels.forEach(injectCancel);

  // Re-inject cancel whenever GF re-renders a panel's contents (e.g. after validation failure)
  panels.forEach(function (panel) {
    new MutationObserver(function () {
      injectCancel(panel);
    }).observe(panel, { childList: true, subtree: true });
  });

  function onConfirmation() {
    hideAllPanels();
    hide(bar);
    show(thankyou);
  }

  // Poll the active panel until GF shows its confirmation (wrapper added or fields gone)
  function startConfirmationPoll(activePanel) {
    var hadFields = !!activePanel.querySelector('.gfield');
    var attempts = 0;
    var check = setInterval(function () {
      attempts++;
      var hasConfirmation = !!activePanel.querySelector('.gform_confirmation_wrapper');
      var fieldsGone = hadFields && !activePanel.querySelector('.gfield');
      if (hasConfirmation || fieldsGone) {
        clearInterval(check);
        onConfirmation();
      } else if (attempts > 100) { // 10s timeout
        clearInterval(check);
      }
    }, 100);
  }

  // Catch native form submit (standard AJAX)
  widget.addEventListener('submit', function (e) {
    panels.forEach(function (p) { if (p.contains(e.target)) { startConfirmationPoll(p); } });
  });

  // Catch GF submit-button clicks (some GF versions bypass the submit event)
  widget.addEventListener('click', function (e) {
    var btn = e.target.closest
      ? e.target.closest('[id^="gform_submit_button_"], input[type="submit"]')
      : null;
    if (!btn) { return; }
    panels.forEach(function (p) { if (p.contains(btn)) { startConfirmationPoll(p); } });
  });

  if (window.jQuery) {
    // Belt-and-suspenders: GF's own confirmation event
    jQuery(document).on('gform_confirmation_loaded', onConfirmation);
  }
}());
</script>
