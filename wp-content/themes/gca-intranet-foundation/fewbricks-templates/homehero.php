<?php
/**
 * Fewbricks Template: Homepage Hero
 * Fields: homehero_homehero_kicker, homehero_homehero_title, homehero_homehero_image
 */
$kicker = $row['homehero_homehero_kicker'] ?? 'Welcome to our';
$title  = $row['homehero_homehero_title']  ?? 'GCA Intranet';
$image  = $row['homehero_homehero_image']  ?? [];

$img_url = !empty($image['url']) ? $image['url'] : esc_url( get_template_directory_uri() . '/assets/img/hero.png' );
$img_alt = !empty($image['alt']) ? $image['alt'] : '';
?>

<section class="gca-home-hero" aria-label="Homepage hero" data-testid="home-hero">
    <div class="govuk-width-container" data-testid="home-hero-container">
        <div class="gca-home-hero-inner" data-testid="home-hero-inner">
            <div class="gca-home-hero-content" data-testid="home-hero-content">
                <?php if ( $kicker ) : ?>
                    <p class="govuk-body gca-home-hero-kicker" data-testid="home-hero-kicker">
                        <?php echo esc_html( $kicker ); ?>
                    </p>
                <?php endif; ?>
                <h1 class="govuk-body gca-home-hero-title" data-testid="home-hero-title">
                    <?php echo esc_html( $title ); ?>
                </h1>
            </div>
        </div>
        <div class="gca-home-hero__media" data-testid="home-hero-media" aria-hidden="true">
            <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>">
        </div>
    </div>
</section>
