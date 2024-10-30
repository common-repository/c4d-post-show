<div <?php post_class('item'); ?>>
	<div class="item-inner">
		<div class="post-image">
			<a href="<?php esc_attr(the_permalink()); ?>">
				<?php the_post_thumbnail($params['image_size']); ?>
			</a>
		</div>		
		<div class="post-info">
			<a href="<?php esc_attr(the_permalink()); ?>">
				<?php the_title('<h3 class="title">', '</h3>'); ?>
			</a>
			<div class="meta">
				<?php if ($params['display_date']): ?>
					<div class="date">
						<?php if ($params['human_date']): ?>
							<?php echo human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' ' . esc_html__('ago', 'c4d-post-show'); ?>
						<?php else:?>
							<?php echo get_the_date(); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ($params['display_category']): ?>
					<?php the_category(); ?>	
				<?php endif; ?>

				<?php if ($params['display_views']): ?>
					<?php if (function_exists('c4d_post_views_number')): ?>
						<span class="views"><i class="pe-7s-look"></i> <?php echo c4d_post_views_number(get_the_ID()); ?></span> 
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php if ($params['display_content']): ?>
				<div class="desc"><?php the_content(''); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>