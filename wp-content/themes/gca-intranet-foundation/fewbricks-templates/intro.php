<?php
/**
 * Intro Component (Media & Text)
 * Filename: intro.php
 */

// 1. Map data from the $row variable (as seen in your print_r)
$heading = $row['intro_intro_heading'] ?? '';
$content = $row['intro_intro_content'] ?? '';
$flip    = $row['intro_intro_flip_media_text'] ?? 'image_right';
$media   = $row['intro_intro_media'] ?? []; // This is an array of sub-rows

// 2. Logic for Media Column
ob_start();
if (!empty($media)) :
    foreach ($media as $item) :
        $img = $item['image_image_image'] ?? null;
        if ($img) : ?>
            <figure class="image-wrapper">
                <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
            </figure>
        <?php endif;
    endforeach;
endif;
$media_html = ob_get_clean();

// 3. Logic for Text Column
ob_start(); ?>
    <?php if ($heading) : ?>
        <h2 class="govuk-heading-m"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <div class="wysiwyg-content">
        <?php echo wp_kses_post($content); ?>
    </div>
<?php $text_html = ob_get_clean(); ?>

<div class="govuk-width-container govuk-!-margin-top-6">
    <div class="govuk-grid-row">
        <?php if ($flip === 'image_left') : ?>
            <div class="govuk-grid-column-one-half"><?php echo $media_html; ?></div>
            <div class="govuk-grid-column-one-half"><?php echo $text_html; ?></div>
        <?php else : ?>
            <div class="govuk-grid-column-one-half"><?php echo $text_html; ?></div>
            <div class="govuk-grid-column-one-half"><?php echo $media_html; ?></div>
        <?php endif; ?>
    </div>
</div>