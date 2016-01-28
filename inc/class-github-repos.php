<?php

namespace MidnightLabs;

class Github_Repos {

	private static $shortcode_tag = 'github_repos';

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Github_Repos;
			self::$instance->register_shortcode();
		}
		return self::$instance;
	}

	public function register_shortcode() {
		add_shortcode( self::$shortcode_tag, array( $this, 'callback' ) );

		// If Shortcake is installed and activated, add support for a post element
		if ( function_exists( 'shortcode_ui_register_for_shortcode' ) ) {
			shortcode_ui_register_for_shortcode( self::$shortcode_tag, self::get_shortcode_ui_args() );
		}

	}

	public static function get_shortcode_ui_args() {
		return array(
			'label'         => esc_html__( 'Github Repos', 'wordpress-github-repos' ),
			'listItemImage' => 'dashicons-media-code',
			'attrs'         => array(
				array(
					'label'         => esc_html__( 'Github Username', 'wordpress-github-repos' ),
					'attr'          => 'username',
					'type'          => 'text',
				),
				array(
					'label'         => esc_html__( 'Use Custom CSS?', 'fusion' ),
					'description'   => esc_html__( 'If you want to use custom CSS, check this and the default styles will not be output.', 'fusion' ),
					'attr'          => 'custom_css',
					'type'          => 'checkbox',
				),
			),
		);
	}

	public function callback( $atts ) {

		// Check to make sure there is a username set
		if ( ! empty( $atts['username'] ) ) {

			// Check for transient just in case
			$response = wp_cache_get( 'ml_github_repos' );

			// No cache set, so get a fresh batch from GitHub
			if ( ! $response ) {

				$response = wp_remote_get( 'https://api.github.com/users/' . urlencode( $atts['username'] ) . '/repos' );

				// Check to make sure GitHub didn't give an error
				if ( ( $response['response']['message'] == 'OK' ) && ! empty( $response['body'] ) ) {
					$response = json_decode( $response['body'] );
				} else {
					$response = NULL;
				}

				// Cache repos array for 6 hours so we don't overload GitHub's API
				wp_cache_set( 'ml_github_repos', $response, null, 21600 );

			}

			// Check that there was a response, then output the repos
			if ( $response ) {
				if( empty( $atts['custom_css'] ) ) {
				$output = '
					<style type="text/css">
						.github-repos {
							margin: 10px;
							padding: 0px;
						}
						.github-repos li {
							list-style-type: none;
							margin: 0 0 10px 0;
							padding: 15px;
							border: 1px solid transparent;
							border-left: 1px solid #1e7ce2;
						}
						.github-repos li:hover {
							border: 1px solid #1e7ce2;
						}
						.github-repos a {
							display: block;
							line-height: 2em;
							text-decoration: none;
							font-size: 1.4em;
							font-weight: bolder;
						}
					</style>
					';
				}
				$output .= '<ul class="github-repos">';
				foreach ( $response as $repo ) {
					$output .= '<li><a href="' . $repo->html_url . '">' . $repo->name . '</a> ' . $repo->description . '</li>';
				}
				$output .= '</ul>';
				return $output;
			}
		}

		return '';
	}

}
