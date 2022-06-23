<?php
/**
 * The initation loader for CMB2, and the main plugin file.
 *
 * @category     WordPress_Plugin
 * @package      Yours59
 * @author       larslo
 * @license      GNU GPLv3
 * @link         https://larslo.de
 *
 * Plugin Name:  Yours59 5-Star Rating Block
 * Plugin URI:   https://github.com/larsloQ/5-star-rating-gutenberg-block
 * Description:  a Server-Side-Render block showing 5-star-rating (procentual)
 * Author:       larslo
 * Author URI:   https://larslo.de
 *
 * Version:      1
 *
 * Text Domain:  yours59
 *
 *
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/*
outline:
- get google reviews and saves it to a custom post type
- allows to select certain reviews for showing on your website
- allows you to show the overall rating
- custom post-type (google_reviews): you are not allowed to change title or content of this CPT


requirements:
- setup my business at google, allow google apis to access mybusiness (in google api console)
- an oauth2 setup, so you need an gmail-account which is allowed to access your my business account
- no docs included about this process

remember:
- does not update automatically: you need to go to wp-admin/options-general.php?page=google_reviews_settings
and update it manually.

 */
namespace Yours59StarsRating;

$yours59_stars_rating = Yours59StarsRating::getInstance();

class Yours59StarsRating {

	// static $saved_options;
	protected static $instance = null;
	private static $name       = 'Yours59 Stars Rating';


	// Method to get the unique instance.
	// Singleton
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self(); }
		self::$instance->init();

		return self::$instance;
	}

	/**
	 * is not allowed to call from outside to prevent from creating multiple instances,
	 * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
	 */
	private function __construct() {}

	/**
	 * prevent the instance from being cloned (which would create a second instance of it)
	 */
	private function __clone() {}

	/**
	 * prevent from being unserialized (which would create a second instance of it)
	 */
	public function __wakeup() {
		throw new Exception( 'Cannot unserialize' );
	}

	public function init() {

		/* add block category, sorry its not necessary, its promo */
		add_action(
			'block_categories',
			function( $categories ) {
				return array_merge(
					$categories,
					array(
						array(
							'slug'  => 'yours59',
							'title' => __( 'yours59', 'yours59' ),
						),
					)
				);
			}
		);
		/* enqueue block-editor js and css */
		add_action(
			'enqueue_block_editor_assets',
			function() {
				$file = '/build/js/editor.five-star-block.js';
				$name = 'yours59_stars_rating';
				wp_enqueue_script(
					$name,
					plugins_url( $file, __FILE__ ),
					array( 'wp-blocks', 'wp-element', 'wp-edit-post' )
				);
				$editor_css = '/build/css/editor-5-star-rating.css';
				wp_enqueue_style(
					$editor_css,
					plugins_url( $editor_css, __FILE__ ),
					array(),
					false,
					'all'
				);
			}
		);

		/* we use the same css for frontend and editor 
		 enqueue for frontend*/
		add_action(
			'wp_enqueue_scripts',
			function() {

				wp_enqueue_style(
					'google_reviews_css',
					plugin_dir_url( __FILE__ ) . 'build/css/editor-5-star-rating.css',
				);
			}
		);
		

		$this->register_blocks();

	}


	/**
	 * see https://developer.wordpress.org/reference/classes/wp_block_type/__construct/ especially for
	 * block api version 2
	 */

	private function register_blocks() {

		$blockName = 'yours59/yours59-stars-rating';
		register_block_type(
			$blockName,
			array(
				'render_callback'   => array( $this, 'block_render_callback' ),
				'api_version'       => 2,
				'skip_inner_blocks' => true,
				'supports'          => array(
					'color'   => true,
					'spacing' => array(
						'margin'  => true,
						'padding' => true,
					),
				),
				/* double check the that the values here are the same as in JS */
				'attributes'        => array(
					'rating'    => array(
						'type'    => 'number',
						'default' => 3,
					),
					'scale'     => array(
						'type'    => 'number',
						'default' => 1,
					),

					// * style contains spacings etc
					// * so if you add supports:{
					// spacing} in JS this comes as styles object (inline styles)

					'style'     => array(
						'type' => 'object',
					),
					'className' => array(
						'type' => 'string',
					),
				),
			)
		);
	}



	/**
	 * to have this working in editor
	 * make sure that attributes in JS and PHP (here) are the same
	 * if not so, you will see an http error in console (400 Bad Request)
	 * and a React Error saying ...
	 * Uncaught Error: Objects are not valid as a React child (found: object with keys {error, errorMsg}). If you meant to render a collection of children, use an array instead.
	 * some more info here
	 * This has been supported for some time. ServerSideRender accepts an httpMethod prop that can be used to switch to a POST request: https://github.com/WordPress/gutenberg/tree/trunk/packages/server-side-render
	 *
	 * @param      <type>  $attributes  The block attributes
	 * @param      <type>  $content           The content
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function block_render_callback( $attributes, $content ) {
		$wrapper_attributes = get_block_wrapper_attributes();
		
		$stars = '';
		$dark  = $attributes['dark_stars'] ? true : false;

		$color = '';
		if ($attributes['textColor']) {
			$hex = self::yours59_color_value_from_theme_json($attributes['textColor']);
			if ($hex !== '') {
				$color = $hex;
			}
		}
		$rating = $attributes['rating'] ? $attributes['rating'] : 0;
		$scale  = $attributes['scale'] ? $attributes['scale'] : 1;
		$stars  = $this->rating_to_stars( $rating, $dark, $scale, $color );

		/* most outer div is required by blockeditor, react all needs to be wrapped into one element*/
		$out      = sprintf( '<div %s>', $wrapper_attributes );
		$scaled   = ( 60 * $scale );
		$width    = ( 5 * $scaled ) . 'px';
		$out     .= '<div style="width:' . $width . '">';
			$out .= $stars;
		$out     .= '</div>';
		$out     .= '</div>';

		return $out;
	}

	private function one_star( $size, $color ) {
		$pixeled = $size . 'px';
		if($color != '') {
			$color = 'fill="' . $color . '"';
		} 
		return '<div class="google-review__svg-wrapper" style="width:' . $pixeled . '; height:' . $pixeled . '">
			<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 21 21" preserveAspectRatio="none">
			<path ' . $color . ' d="M21.947 9.179a1.001 1.001 0 0 0-.868-.676l-5.701-.453-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4 4.536-4.082c.297-.268.406-.686.278-1.065z">
			</path>
			</svg>
			</div>';
	}

	private function rating_to_stars( $rating, $dark = false, $scale = 1, $color='' ) {
		$as_num = $rating;

		$cover_width = strval( 100 - ( ( $as_num / 5 ) * 100 ) ) . '%';

		if ( ! $as_num ) {
			return;
		}
		$dark   = $dark ? '--dark' : '';
		$scaled = ( 60 * $scale );
		$width  = ( 5 * $scaled ) . 'px';
		$stars  = sprintf( '<div class="google-review__stars %s" style="width:%s">', $dark, $width );
		for ( $i = 0; $i < 5; $i++ ) {
			$stars .= $this->one_star( $scaled,  $color );
		}
		$stars .= '</div>';
		/* most outer div is required by blockeditor, react all needs to be wrapped into one element*/
		// $out .= sprintf( '<div class="google-review__stars-transform" style="transform: scale(%s);">', $scale );
		$out  = '<div class="google-review__stars-wrapper">';
		$out .= $stars;
		$out .= sprintf( '<div class="google-review__stars-cover" style="width: %s;"></div>', $cover_width );
		$out .= '</div>';
		return $out;
	}

	/**
 * get a color value defined in theme.json from a color slug
 *
 * @param      string  $colorslug  The colorslug, i.e. 'primary'
 *
 * @return     string  usually a hex color value
 */
	private static function yours59_color_value_from_theme_json( string $colorslug ) {
		/* strangely \WP_Theme_JSON_Resolver::get_theme_data does not give us "standard" colors, only theme colors (defined in theme.json) */
		$themedata = \WP_Theme_JSON_Resolver::get_theme_data( array(), array( 'with_supports' => false ) );
		$data      = reset( $themedata );
		try {
			$colors = $data['settings']['color']['palette']['theme'];
			foreach ( $colors as $color ) {
				if ( $color['slug'] === $colorslug ) {
					return $color['color'];
				}
			}
		} catch ( Exception $e ) {
			return '';
		}
	}


} // class



