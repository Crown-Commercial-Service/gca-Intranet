<?php
/**
 * Staff profile card.
 *
 * Args:
 *   user         (WP_User)
 *   display_name (string)
 *   email        (string)
 *   business_title    (string)
 *   team         (string)
 *   avatar_url   (string)  local Google profile picture URL, may be empty
 */

/** @var WP_User $user */
$user         = $args['user']         ?? null;
$display_name = $args['display_name'] ?? '';
$email        = $args['email']        ?? '';
$business_title    = $args['business_title']    ?? '';
$team         = $args['team']         ?? '';
$avatar_url   = $args['avatar_url']   ?? '';

if (!$user instanceof WP_User) {
    return;
}

// Build role/team line – only include parts that have data.
$role_parts = array_filter([$business_title, $team]);
$role_line  = implode(' | ', $role_parts);

// Fallback to default avatar if no Google picture is stored.
if (empty($avatar_url)) {
    $avatar_url = get_avatar_url($user->ID, ['size' => 120]);
}
?>

<div class="gca-profile-card govuk-!-margin-bottom-6">

  <div class="gca-profile-card__inner">

    <div class="gca-profile-card__avatar" aria-hidden="true">
      <img
        src="<?php echo esc_url($avatar_url); ?>"
        alt=""
        class="gca-profile-card__avatar-img"
        width="80"
        height="80"
      >
    </div>

    <div class="gca-profile-card__body">

      <h2 class="govuk-heading-m gca-profile-card__name">
        <?php echo esc_html($display_name); ?>
      </h2>

      <?php if ($role_line) : ?>
        <p class="govuk-body-s gca-profile-card__role">
          <?php echo esc_html($role_line); ?>
        </p>
      <?php endif; ?>

      <?php if ($email) : ?>
        <ul class="govuk-list gca-profile-card__contact">
          <li class="gca-profile-card__contact-item">
            <svg class="gca-profile-card__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
              <path d="M20 4H4C2.9 4 2 4.9 2 6v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
            </svg>
            <a href="mailto:<?php echo esc_attr($email); ?>" class="govuk-link">
              <?php echo esc_html($email); ?>
            </a>
          </li>
        </ul>
      <?php endif; ?>

    </div>

  </div>

</div>
