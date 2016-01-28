<?php
/**
 * Plugin Name: WordPress GitHub Repos
 * Plugin URI: https://github.com/ryanboswell/wordpress-github-repos/
 * Description: A simple WordPress plugin that adds a shortcode to WordPress to allow you to list your GitHub repos on a page or in a post. Just use: [github_repos username="YOUR_GITHUB_USERNAME"]
 * Version: 1.0.0
 * Author: Ryan Boswell / Ambit Studios
 * Author URI: http://ryanboswell.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 */

	/**
	 * Generates the list of repos
	 *
	 * @since 1.0
	 */
	function rb_github_repos( $args ) {
		$defaults = array(
			'username' => '',
			'custom_css' => 'false'
		);
		wp_parse_args( $args, $defaults );

		// Check to make sure there is a username set
		if ( ! empty( $args['username'] ) ) {

			// Check for transient just in case
			$response = wp_cache_get( 'rb_github_repos' );

			// No cache set, so get a fresh batch from GitHub
			if ( ! $response ) {

				$response = wp_remote_get( 'https://api.github.com/users/' . urlencode( $args['username'] ) . '/repos' );

				// Check to make sure GitHub didn't give an error
				if ( ( $response['response']['message'] == 'OK' ) && ! empty( $response['body'] ) ) {
					$response = json_decode( $response['body'] );
				} else {
					$response = NULL;
				}

				// Cache repos array for 6 hours so we don't overload GitHub's API
				wp_cache_set( 'rb_github_repos', $response, null, 21600 );

			}

			// Check that there was a response, then output the repos
			if ( $response ) {
				if( $args['custom_css'] == 'false' ) {
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
	add_shortcode( 'github_repos', 'rb_github_repos' );
