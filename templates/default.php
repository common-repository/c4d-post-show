<div class="c4d-post-show <?php echo esc_attr($params['class']) . ' layout-' .esc_attr($params['layout']); ?>" data-cols="<?php echo esc_attr($params['cols']); ?>">
	<?php while ( $q->have_posts() ) : ?>
		<?php $p = $q->the_post(); ?>
		<?php require dirname(__FILE__). '/__item.php'; ?>
	<?php endwhile; // end of the loop. ?>
</div>