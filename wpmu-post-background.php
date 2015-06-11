<?php
/*
    Plugin Name: WPMU Post Background Image
    Plugin URI: https://github.com/belkincapital/wpmu-post-background/
    Description: Places a meta box on the posts editor screen and allows you to use a custom background image for each post or page.
    Author: Jason Jersey
    Author URI: https://www.twitter.com.com/degersey
    Version: 1.0.1
    License: GNU General Public License 2.0 
    License URI: http://www.gnu.org/licenses/gpl-2.0.txt
    
    Copyright 2015 Belkin Capital Ltd (contact: https://belkincapital.com/contact/)
    
    This plugin is opensource; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License,
    or (at your option) any later version (if applicable).
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111 USA
*/

/* Exit if accessed directly
 * Since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) die('Uh Oh!');

/**
 * Adds a meta box to the post editing screen
 */
function wpmupbi_custom_meta() {
    add_meta_box( 'wpmupbi_meta', __( 'Post Background', 'wpmupbi-textdomain' ), 'wpmupbi_meta_callback', 'post', 'side' );
    add_meta_box( 'wpmupbi_meta', __( 'Post Background', 'wpmupbi-textdomain' ), 'wpmupbi_meta_callback', 'page', 'side' );
}
add_action( 'add_meta_boxes', 'wpmupbi_custom_meta' );

/**
 * Outputs the content of the meta box
 */
function wpmupbi_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'wpmupbi_nonce' );
    $wpmupbi_stored_meta = get_post_meta( $post->ID );
    ?>
<p>
    <label for="meta-image" class="wpmupbi-row-title"><?php _e( 'Image Source URL', 'wpmupbi-textdomain' )?></label>
    <input type="text" name="meta-image" id="meta-image" value="<?php if ( isset ( $wpmupbi_stored_meta['meta-image'] ) ) echo $wpmupbi_stored_meta['meta-image'][0]; ?>" /><br />
    <br />
    <input type="button" id="meta-image-button" class="button" value="<?php _e( 'Choose or Upload Image', 'wpmupbi-textdomain' )?>" />
</p>
    <?php
}

/**
 * Saves the custom meta input
 */
function wpmupbi_meta_save( $post_id ) {
 
    /* Checks save status */
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'wpmupbi_nonce' ] ) && wp_verify_nonce( $_POST[ 'wpmupbi_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    /* Exits script depending on save status */
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
 
    /* Checks for input and sanitizes/saves if needed */
    if( isset( $_POST[ 'meta-image' ] ) ) {
        update_post_meta( $post_id, 'meta-image', sanitize_text_field( $_POST[ 'meta-image' ] ) );
    }
 
}
add_action( 'save_post', 'wpmupbi_meta_save' );

/**
 * Adds the meta box stylesheet when appropriate
 */
function wpmupbi_admin_styles(){
    global $typenow;
    if( $typenow == 'post' ) {
        wp_enqueue_style( 'wpmupbi_meta_box_styles', plugin_dir_url( __FILE__ ) . 'wpmu-post-background.css' );
    }
}
add_action( 'admin_print_styles', 'wpmupbi_admin_styles' );

/**
 * Loads the image management javascript
 */
function wpmupbi_image_enqueue() {
    global $typenow;
    if( $typenow == 'post' ) {
        wp_enqueue_media();
 
        /* Registers and enqueues the required javascript */
        wp_register_script( 'meta-box-image', plugin_dir_url( __FILE__ ) . 'wpmu-post-background.js', array( 'jquery' ) );
        wp_localize_script( 'meta-box-image', 'meta_image',
            array(
                'title' => __( 'Choose or Upload Image', 'wpmupbi-textdomain' ),
                'button' => __( 'Use this Image', 'wpmupbi-textdomain' ),
            )
        );
        wp_enqueue_script( 'meta-box-image' );
    }
}
add_action( 'admin_enqueue_scripts', 'wpmupbi_image_enqueue' );

/**
 * Adds the background image css to frontend
 */
function wpmupbi_image_frontend() {

    $meta_image = get_post_meta( get_the_ID(), 'meta-image', true );
    if( !empty( $meta_image ) ) {
        echo "<style>
        body {
              height: 100%;
              background: url('$meta_image') no-repeat center center fixed;
              -webkit-background-size: cover;
              -moz-background-size: cover;
              -o-background-size: cover;
              background-size: cover;
        }
        </style>";
    }

}
add_action('wp_head', 'wpmupbi_image_frontend');
