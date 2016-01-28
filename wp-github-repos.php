<?php
 /**
  * Plugin Name: WordPress GitHub Repos
  * Plugin URI: https://github.com/ryanboswell/wordpress-github-repos/
  * Description: Extends Shortcake and Shortcake Bakery to add a Github repos shortcode and post element.
  * Version: 2.0
  * Author: Ryan Boswell / MidnightLabs
  * Author URI: http://ryanboswell.com
  * Plugin URI: http://ryanboswell.com
  */

  /**
   * Initializes the WordPress Github Repos plugin
   */
  function wordpress_github_repos_load() {
		require_once dirname( __FILE__ ) . '/inc/class-github-repos.php';
    MidnightLabs\Github_Repos::get_instance();
  }
  add_action( 'after_setup_theme', 'wordpress_github_repos_load' );
