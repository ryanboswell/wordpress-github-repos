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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */

	/**
	 * Generates the list of repos
	 *
	 * @since 1.0
	 */
	function rb_github_repos( $atts ) {
	
		extract( shortcode_atts( array( 'username' => '' ), $atts ) );
		
		// Check to make sure there is a username set
		if( ! empty( $username ) ) {
	
			// Check for transient just in case
			//$response = get_transient( 'rb_github_repos' );
	
			// No transient set, so get a fresh batch from GitHub
			if ( ! $response ) {
	
				$response = wp_remote_post( 'https://api.github.com/users/' . $username . '/repos',
											array( 'method' => 'GET' )
											);
	
				// Check to make sure GitHub didn't give an error
				if ( $response['response']['message'] == 'OK' ) {
					$response = json_decode( $response['body'] );
				} else {
					$response = NULL;
				}
	
				// Save repos array to a transient so we don't overload GitHub's API
				set_transient( 'rb_github_repos', $response, 21600 );
	
			}
	
			// Check that there was a response, then output the repos
			if ( $response ) {
	
				$output = '<ul class="github-repos">';
				foreach ( $response as $repo ) {
					$output .= '<li><a href="' . $repo->html_url . '">' . $repo->name . '</a> ' . $repo->description . '</li>';
				}
				$output .= '</ul>';
	
			}
		}
		
		return $output;
	}
	add_shortcode( 'github_repos', 'rb_github_repos' );