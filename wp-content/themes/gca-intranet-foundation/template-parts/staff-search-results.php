<?php
/**
 * Staff search results block.
 *
 * Included at the top of search.php when the staff-profiles flag is on.
 * Renders matching users in the same visual style as post search results.
 */

// Results are pre-computed in search.php and passed via $args to avoid a duplicate query.
$staff_results = $args['staff_results'] ?? [];

if (empty($staff_results)) {
    return;
}
?>

<?php foreach ($staff_results as $i => $result) :
    $role_parts = array_filter([$result->job_title, $result->team]);
    $role_line  = implode(' | ', $role_parts);
?>

  <article class="gca-search-result gca-staff-result govuk-!-margin-bottom-0">

    <h2 class="govuk-heading-m govuk-!-margin-bottom-2">
      <span class="gca-search-result__type">Staff profile - </span><a
        href="<?php echo esc_url($result->profile_url); ?>"
        class="govuk-link"
      ><?php echo esc_html($result->display_name); ?></a>
    </h2>

    <div class="gca-staff-result__detail">

      <div class="gca-staff-result__avatar" aria-hidden="true">
        <img
          src="<?php echo esc_url($result->avatar_url); ?>"
          alt=""
          class="gca-staff-result__avatar-img"
          width="48"
          height="48"
        >
      </div>

      <div class="gca-staff-result__body">
        <?php if ($role_line) : ?>
          <p class="govuk-body govuk-!-margin-bottom-1">
            <?php echo esc_html($role_line); ?>
          </p>
        <?php endif; ?>
        <?php if ($result->email) : ?>
          <p class="govuk-body-s govuk-!-margin-bottom-0" style="color:#505a5f;">
            <?php echo esc_html($result->email); ?>
          </p>
        <?php endif; ?>
      </div>

    </div>

  </article>

  <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">

<?php endforeach; ?>
<?php
// If there are post results following, the last <hr> above acts as the divider between staff and posts.
// If this is the last result overall, WordPress's normal flow handles the rest.
?>
