<?php
//Last updated Posts Filters Without GIF
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
            $output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
        }

        $output .= '</ul>';

        wp_reset_postdata();

        return $output;
    }
}

add_shortcode('psf-updated-nogif', 'psf_last_updated_posts_shortcode');