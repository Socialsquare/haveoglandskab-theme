<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;
use WP_Query;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');



/**
 * Have&Landskap custom
 */

// Sort Udstillere overview alphabetically
add_action('pre_get_posts', __NAMESPACE__ . '\\udstiller_default_order');
function udstiller_default_order( $query ){
  if( 'udstillere' == $query->get('post_type') ){
  // Disable pagination
  $query->set('nopaging', 1);
  if( $query->get('orderby') == '' )
     $query->set('orderby','title');
  if( $query->get('order') == '' )
     $query->set('order','ASC');
  }
}

// Fjerne "arkiver:" label fra get_the_archive_title
function my_theme_archive_title( $title ) {
  if ( is_category() ) {
    $title = single_cat_title( '', false );
  } elseif ( is_tag() ) {
    $title = single_tag_title( '', false );
  } elseif ( is_author() ) {
    $title = '<span class="vcard">' . get_the_author() . '</span>';
  } elseif ( is_post_type_archive() ) {
    $title = post_type_archive_title( '', false );
  } elseif ( is_tax() ) {
    $title = single_term_title( '', false );
  }
  return $title;
}
add_filter( 'get_the_archive_title', __NAMESPACE__ . '\\my_theme_archive_title' );

// Allow shortcodes in widgets
add_filter('widget_text', 'do_shortcode');

// Button
add_shortcode( 'button', __NAMESPACE__ . '\\button' );
function button( $atts, $content = null ) {
  $classes = 'btn btn-white btn-lg m-r-1 m-b-1';
  if(isset($atts['href'])) {
    $href = $atts['href'];
    return "<a class='$classes' href='$href'>$content</a>";
  } else {
    return "<div class='$classes'>$content</div>";
  }
}

// Columns
add_shortcode( 'column', __NAMESPACE__ . '\\column' );
function column( $atts, $content = null ) {
	return "<div class='col-sm-6'>" . do_shortcode($content) . "</div>";
}

add_shortcode( 'column-wide', __NAMESPACE__ . '\\columnWide' );
function columnWide( $atts, $content = null ) {
	return "<div class='col-xs-12'>" . do_shortcode($content) . "</div>";
}

// Social Icons
add_shortcode( 'icon', __NAMESPACE__ . '\\icon' );
function icon( $atts ) {
  $a = shortcode_atts( array(
    'logo' => '',
    'url' => '#',
  ), $atts );
	return "<a href='{$a['url']}' target='_blank' class='social-icon'><img class='social-icon__img' src='". get_template_directory_uri() . "/dist/images/{$a['logo']}.svg'/></a>";
}

// Logo
add_shortcode( 'logo', __NAMESPACE__ . '\\logo' );
function logo( $atts ) {
  $a = shortcode_atts( array(
    'logo' => '',
    'url' => '#',
  ), $atts );
	return "<a href='{$a['url']}' target='_blank' class='social-icon'><img class='social-icon__img-big m-r-1' src='". get_template_directory_uri() . "/dist/images/{$a['logo']}.svg'/></a>";
}

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function udstiller_login_redirect( $redirect_to, $request, $user ) {
	// is there a user to check?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		// check for admins
		if ( in_array( 'author', $user->roles ) ) {
      // Determine if this author has authored an "udstiller"
      $udstiller_query = new WP_Query(array(
        'author' => $user->ID,
        'post_type' => 'udstillere'
      ));
      if($udstiller_query->have_posts()) {
  			$udstiller_query->the_post();
        return get_permalink($udstiller_query->ID);
      } else {
  			// redirect them to the default place
			  return $redirect_to;
      }
		} else {
			// redirect them to the default place
      return $redirect_to;
		}
	} else {
		return $redirect_to;
	}
}

add_filter( 'login_redirect', __NAMESPACE__ . '\\udstiller_login_redirect', 10, 3 );

function haveoglandskab_login_logo() {
  $logo_url = get_stylesheet_directory_uri() . '/dist/images/haveoglandskab.svg';
  ?>
  <style type="text/css">
  #login h1 a, .login h1 a {
    background-image: url('<?=$logo_url?>');
  }
  </style>
  <?php
}
add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\haveoglandskab_login_logo' );
