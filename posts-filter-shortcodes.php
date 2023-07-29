<?php
/*
 * Plugin Name: Posts Filter Shortcodes
 * Plugin URI: https://wordpress.org/plugins/Posts-Filter-Shortcodes/
 * Description: Post Filters Shortcodes is a powerful WordPress plugin that allows effortless post filtering through user-friendly shortcodes.
 * Version: 1.0
 * Requires at least: 4.7
 * Tested up to: 6.2
 * Author: Numan Rasheed 
 * Author URI: https://www.numanrki.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

 if ( ! defined( 'WPINC' ) ) {
    die;
  }

  define( 'Posts_Filter_Shortcodes', '1.0' );

// Enqueue the custom CSS file
function psf_enqueue_custom_css() {
    // Get the path to the CSS file
    $css_file_path = plugin_dir_url( __FILE__ ) . '/psf-includes/psf-main.css';

    // Enqueue the CSS file
    wp_enqueue_style( 'psf-main-css', $css_file_path );
}
add_action( 'wp_enqueue_scripts', 'psf_enqueue_custom_css' );


// Last updated Posts Filters
function psf_last_updated_posts_shortcode($atts) {
    $atts = shortcode_atts( array(
        'show' => '',
        'hide' => '',
        'num_posts' => -1, // Default to show all posts
    ), $atts );

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $atts['num_posts'],
        'orderby' => 'modified',
        'order' => 'DESC',
    );

    // Include specific categories
    if ( ! empty( $atts['show'] ) && $atts['show'] !== 'all' ) {
        $category_slugs = explode( ',', $atts['show'] );
        $category_ids = array_map( 'get_category_by_slug', $category_slugs );
        $args['category__in'] = wp_list_pluck( $category_ids, 'term_id' );
    }

    // Exclude specific categories
    if ( ! empty( $atts['hide'] ) ) {
        $category_slugs = explode( ',', $atts['hide'] );
        $category_ids = array_map( 'get_category_by_slug', $category_slugs );
        $args['category__not_in'] = wp_list_pluck( $category_ids, 'term_id' );
    }

    $last_updated_posts = new WP_Query($args);

    if ($last_updated_posts->have_posts()) {
        $output = '<ul class="psf-last-updated-posts">'; // Add the custom class here

        while ($last_updated_posts->have_posts()) {
            $last_updated_posts->the_post();
            $output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '<img src="' . plugin_dir_url( __FILE__ ) . 'assets/gifs/new.gif" alt="New" class="psf-new-gif" width="24" height="24" /></a></li>';
        }

        $output .= '</ul>';

        wp_reset_postdata();

        return $output;
    }
}

add_shortcode('psf-updated', 'psf_last_updated_posts_shortcode');




//PSF Trending Posts Show
// Shortcode callback function
add_shortcode('psf-trending', 'psf_trending_posts');
function psf_trending_posts($atts) {
    // Extract attributes from the shortcode
    $atts = shortcode_atts(array(
        'show' => '',    // Comma-separated category slugs
        'hide' => '',    // Comma-separated category slugs to hide
        'posts' => 5,    // Number of posts to display
    ), $atts);

    // Get the trending posts based on the shortcode attributes
    $trending_posts = psf_get_trending_posts($atts['show'], $atts['hide'], $atts['posts']);

    // Start building the output
    $output = '<ul class="psf-trending-posts">';

    foreach ($trending_posts as $post) {
        $output .= '<li><a href="' . get_permalink($post->ID) . '">' . get_the_title($post->ID) . '</a></li>';
    }

    $output .= '</ul>';

    return $output;
}
function psf_get_trending_posts($show_categories, $hide_categories, $num_posts) {
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $num_posts,
        'meta_key' => 'post_views_count', // Replace 'post_views_count' with your post view count meta key
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    );

    // If 'show' attribute is provided and not 'all', include specified categories
    if ($show_categories && $show_categories !== 'all') {
        $args['category_name'] = $show_categories;
    }

    // If 'hide' attribute is provided, exclude specified categories
    if ($hide_categories) {
        $args['category__not_in'] = explode(',', $hide_categories);
    }

    $trending_query = new WP_Query($args);
    return $trending_query->posts;
}
