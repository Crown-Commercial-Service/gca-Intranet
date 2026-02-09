<?php get_header(); ?>

<div class="container-xxl py-5">
  <h1>Search results for: <?php echo esc_html(get_search_query()); ?></h1>

  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article class="mb-4">
        <h2 class="h4"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <?php the_excerpt(); ?>
      </article>
    <?php endwhile; ?>

    <?php the_posts_pagination(); ?>

  <?php else : ?>
    <p>No results found.</p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
