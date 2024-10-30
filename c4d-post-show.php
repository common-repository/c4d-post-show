<?php
/*
Plugin Name: C4D Post Show
Plugin URI: http://coffee4dev.com/
Description: List posts with grid, slider, mansory. Please install C4D Plugin Manager and Redux Framework to enable all features.
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-post-show
Version: 2.0.3
*/

define('C4DPOSTSHOW_PLUGIN_URI', plugins_url('', __FILE__));

add_action('wp_enqueue_scripts', 'c4d_post_show_safely_add_stylesheet_to_frontsite');
add_shortcode('c4d-post-show', 'c4d_post_show');
add_filter( 'plugin_row_meta', 'c4d_post_show_plugin_row_meta', 10, 2 );

function c4d_post_show_plugin_row_meta( $links, $file ) {
    if ( strpos( $file, basename(__FILE__) ) !== false ) {
        $new_links = array(
            'visit' => '<a href="http://coffee4dev.com">Visit Plugin Site</<a>',
            'forum' => '<a href="http://coffee4dev.com/forums/">Forum</<a>',
            'redux' => '<a href="https://wordpress.org/plugins/redux-framework/">Redux Framework</<a>',
            'c4dpluginmanager' => '<a href="https://wordpress.org/plugins/c4d-plugin-manager/">C4D Plugin Manager</a>'
        );
        
        $links = array_merge( $links, $new_links );
    }
    
    return $links;
}

function c4d_post_show_safely_add_stylesheet_to_frontsite() {
	if(!defined('C4DPLUGINMANAGER_OFF_JS_CSS')) {
		wp_enqueue_style( 'c4d-post-show-frontsite-style', C4DPOSTSHOW_PLUGIN_URI.'/assets/default.css' );
		wp_enqueue_script( 'c4d-post-show-frontsite-plugin-js', C4DPOSTSHOW_PLUGIN_URI.'/assets/default.js', array( 'jquery' ), false, true ); 
	}
	
    wp_localize_script( 'jquery', 'c4d_post_show',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

function c4d_post_show($params){
	$html = '';
	if ($params == '') $params = array();
	$html = c4d_post_show_main_query($params);
	return $html;
}

function c4d_post_show_main_query($params) {
	$default = array(
		'count' => 10,
		'page' => 0,
		'class' => '',
		'image_size' => 'thumbnail',
		'loadmore' => 0,
		'loadmore_text' => 'More Product',
		'cols' => 1,
		'category' => '',
		'layout' => false,
		'display_date' => 0,
		'display_category' => 1,
		'display_content' => 1,
		'display_views' => 1,
		'human_date' => 0
	);
	$params = array_merge($default, $params);
	$ajax = false;
	try {
		if (isset($_REQUEST['c4dajaxgp'])) {
			$params['category'] = isset($_REQUEST['category']) ? esc_sql($_REQUEST['category']) : '';
			$params['count'] = isset($_REQUEST['count']) ? esc_sql($_REQUEST['count']) : 3;

			if (isset($_REQUEST['loadmore']) && $_REQUEST['loadmore'] == 1) {
				if (isset($_REQUEST['page'])) {
					$params['page'] = esc_sql($_REQUEST['page']);
				}
			}
		}

		$args = array(
	        'posts_per_page' 	=> isset($params['count']) ? esc_sql($params['count']) : 10 ,
	        'paged'				=> isset($params['page']) ? esc_sql($params['page']) : 0,
	        'post_type' 		=> 'post',
	        'orderby'   		=> 'date',
        	'order'     		=> 'desc',
	        'post_status'       => 'publish'
		);

		$categories = $params['category'] ? explode(',', esc_sql($params['category'])) : array();
		if (count($categories) > 0) {
			$args['category__in'] = $categories;
		}

		if (isset($params['order'])) {
			$orderby = $params['order'];

			if ($orderby == 'featured') {
				$args =  array_merge($args, array( 'post__in' => get_option( 'sticky_posts' ), 'ignore_sticky_posts' => 1 ));
			}

			if ($orderby == 'popular') { // we need c4d-post-views plugin
				$args =  array_merge($args, array( 'meta_key' => 'c4d_post_views_count', 'orderby' => 'meta_value_num'));
			}

			if ($orderby == 'comment') {
				$args['orderby'] = 'comment_count';
			}
	 	}

	   	$q = new WP_Query( $args );
		
		if (!$q->have_posts()) {
			$html = '<div class="c4d-post-show__noti">'.esc_html__('No Post!', 'c4d-post-show').'</div>';
			throw new Exception($html);
		}

		ob_start();
		$template = get_template_directory() .'/c4d-post-show/templates/'.$params['layout'].'.php';
		if ($params['layout'] && $template && file_exists($template)) {
			require $template;
		} else {
			require dirname(__FILE__). '/templates/default.php';
		}
		$html = ob_get_contents();
		$html = do_shortcode($html);
		ob_end_clean();
		wp_reset_query();
		wp_reset_postdata();

		throw new Exception($html);
	} catch(Exception $e) {
		if (isset($_REQUEST['c4dajaxgp'])) {
			echo $e->getMessage(); wp_die();
		}
		return $e->getMessage();
	}
}