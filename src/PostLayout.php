<?php

class Foxy_Post_Layout {
	public static function supported_post_layouts() {
		$supported_layouts = array(
			Foxy_Common::POST_LAYOUT_LIST_STYLE => __( 'List', 'foxy' ),
			Foxy_Common::POST_LAYOUT_CARD_STYLE => __( 'Card', 'foxy' ),
			Foxy_Common::POST_LAYOUT_LIST_TITLE_STYLE => __( 'List Only Title', 'foxy' ),
			// Foxy_Common::POST_LAYOUT_TIMELINE_STYLE   => __( 'Timeline', 'foxy' ),
			// Foxy_Common::POST_LAYOUT_SLIDE_STYLE      => __( 'Slide', 'foxy' ),
			// Foxy_Common::POST_LAYOUT_MANSORY_STYLE    => __( 'Mansory', 'foxy' ),
			// Foxy_Common::POST_LAYOUT_LARGE_TOP_STYLE  => __( 'Top large block', 'foxy' ),
			// Foxy_Common::POST_LAYOUT_LARGE_LEFT_STYLE => __( 'Left large block', 'foxy' ),
		);
		return apply_filters( 'foxy_post_layouts', $supported_layouts );
	}

	public static function column_styles() {
		return array(
			Foxy_Common::POST_LAYOUT_CARD_STYLE,
			Foxy_Common::POST_LAYOUT_MANSORY_STYLE,
			Foxy_Common::POST_LAYOUT_LARGE_TOP_STYLE,
			Foxy_Common::POST_LAYOUT_LARGE_LEFT_STYLE,
		);
	}

	public static function generate_article_tag( $post_type, $layout_args, $widget_args = null ) {
		$article_tag = array(
			'args' => array(
				'layout' => $layout_args,
				'widget' => $widget_args,
			),
		);
		if (
			empty( $layout_args['carousel'] ) &&
			in_array(
				$layout_args['style'],
				Foxy_Post_Layout::column_styles(),
				true
			) &&
			(
				empty( $widget_args ) ||
				! in_array(
					$widget_args['id'],
					array( 'primary', 'second', 'home-sidebar', 'realty-single', 'realty-archive' ),
					true
				)
			)
		) {

			$style = array_get( $layout_args, 'style', 'card' );
			$loop_content_columns = apply_filters(
				"foxy_loop_{$post_type}_{$style}_columns",
				array(
					'mobile_columns'  => 12,
					'tablet_columns'  => 6,
					'desktop_columns' => 4,
					'xtra_columns'    => 3,
				),
				$layout_args,
				$widget_args
			);
			$article_tag          = array_merge( $article_tag, $loop_content_columns );
		}
		return $article_tag;
	}

	public static function post_layout( $args = array(), $posts, $widget_args = null ) {
		if ( ! ( $posts instanceof WP_Query ) ) {
			// Check $posts variable is instance of WP_Query.
			throw new Exception( 'Argument #1 must be instance of WP_Query' );
		}
		/**
		 * Merge post layout settings with default settings
		 */
		$args = wp_parse_args(
			$args,
			array(
				'style'    => 'card',
				'carousel' => false,
			)
		);

		/**
		 * Clone style setting in argument to new variable
		 */
		$style = array_get( $args, 'style', 'list' );

		/**
		 * Generate css class for carousel
		 */
		$has_carousel = $args['carousel'] ? 'has-carousel' : 'not-carousel';

		$class_names = array(
			'post-layout',
			$has_carousel,
			sprintf( '%s-layout', $style ),
		);

		Foxy::ui()->tag(
			array(
				'class' => implode( ' ', $class_names ),
			)
		);

		$wrap_class = $args['carousel'] ? 'post-layout-wrap owl-carousel' : 'post-layout-wrap';
		if ( $posts->have_posts() ) {
			if ( $style === Foxy_Common::POST_LAYOUT_LIST_TITLE_STYLE ) {
				$tag = 'ul';
			} else {
				$tag = 'section';
			}
			Foxy::ui()->tag(
				array(
					'name'  => $tag,
					'class' => $wrap_class,
				)
			);
			do_action( 'foxy_post_layout_before_loop', $args, $widget_args );
			do_action( "foxy_post_layout_{$style}_before_loop", $args, $widget_args );
			while ( $posts->have_posts() ) {
				$posts->the_post();
				$current_post_type = get_post_type();
				if ( foxy_check_empty_hook( "foxy_{$current_post_type}_layout_{$style}_loop" ) ) {
					$template          = Foxy::search_template(
						array( $current_post_type . '/' . $style . '.php' )
					);
					if ( ! empty( $template ) ) {
						require $template;
					} else {
						foxy_detault_loop_content(
							$current_post_type,
							$style,
							self::generate_article_tag(
								$current_post_type,
								$args,
								$widget_args
							)
						);
					}
				} else {
					do_action( "foxy_{$current_post_type}_layout_{$style}_loop", $args, $widget_args );
				}
			}
			wp_reset_postdata();

			do_action( "foxy_post_layout_{$style}_end_loop", $args, $widget_args );
			do_action( 'foxy_post_layout_after_loop', $args, $widget_args );
			Foxy::ui()->tag(
				array(
					'name'  => $tag,
					'close' => 'true',
				)
			);
			echo '<div class="clearfix"></div>';
		} else {
			foxy_no_content();
		}
		Foxy::ui()->tag( array( 'close' => true ) );
	}

	public static function default_loop_layout( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'style' => 'list',
			)
		);

		$style = $args['style'];

		Foxy::ui()->tag(
			array(
				'class' => sprintf( 'post-layout post-layout-%1$s style-%1$s', $style ),
			)
		);
		if ( have_posts() ) {
			Foxy::ui()->tag(
				array(
					'name'  => 'section',
					'class' => 'post-layout-wrap row',
				)
			);
			do_action( 'foxy_post_layout_before_default_loop' );
			do_action( "foxy_post_layout_{$style}_before_loop" );
			while ( have_posts() ) {
				the_post();
				if ( foxy_check_empty_hook( "foxy_post_layout_{$style}_loop" ) ) {
					$current_post_type = get_post_type();
					$template          = Foxy::search_template(
						array()
					);
					if ( ! empty( $template ) ) {
						require $template;
					} else {
						foxy_detault_loop_content(
							$current_post_type,
							$style,
							self::generate_article_tag(
								$current_post_type,
								$args
							)
						);
					}
				} else {
					do_action( "foxy_post_layout_{$style}_loop" );
				}
			}
			wp_reset_postdata();

			do_action( "foxy_post_layout_{$style}_end_loop" );
			do_action( 'foxy_post_layout_after_default_loop' );
			Foxy::ui()->tag(
				array(
					'name'  => 'section',
					'close' => 'true',
				)
			);
		} else {
			foxy_no_content();
		}
		Foxy::ui()->tag( array( 'close' => true ) );
	}
}
