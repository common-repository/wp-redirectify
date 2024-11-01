<?php
/**
 * Plugin Name: Redirectify
 * Plugin URI: http://wordpress.org/plugins/wp-redirectify/
 * Description: A plugin that redirects posts and pages to specified url.
 * Version: 2.2.1
 * Author: Philip Rabbett
 * Author URI: http://www.rabbett.co.uk
 * Text Domain: wp-redirectify
 * Domain Path: /lang
 */

/*
    Copyright 2014  Rabbett Designs  (email : info@rabbettdesigns.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Redirectify {
  /**
   * __construct 
   * 
   * @since 2.0
   */
  function __construct() {
    if ( is_admin() ) :
      //load language files
      add_action( 'admin_init', array( $this, 'admin_init' ) );
      //display redirectify
      add_action( 'post_submitbox_misc_actions', array( $this, 'display' ) );
      //save redirectify
      add_action( 'save_post', array( $this, 'save' ), 10, 2 );
      //filter post states
      add_filter( 'display_post_states', array( $this, 'post_state' ) );
      //filter edit post list
      add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
      //loading script on post add/edit page
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
      //add help tab
      add_action( 'admin_head-options-reading.php', array( $this, 'add_help_tab' ) );
    else :
      //perform redirectify
      add_action( 'template_redirect', array( $this, 'redirect' ) );
    endif;
  }

  /**
   * used to load language files for localisation
   *
   * @since 2.0
   */
  function admin_init() {
    load_plugin_textdomain( 'wp-redirectify', false, dirname(plugin_basename(__FILE__)) . '/lang' );

    register_setting( 'reading', 'redirectify' );
    add_settings_field(
      'wp-redirectify-filter',
      '<label for="redirectify_filter">' . __( 'Redirectify URL Display' , 'wp-redirectify' ) . '</label>',
      array( &$this, 'redirectify_filter' ),
      'reading'
    );
  }

  /**
   * used to display redirectify form field
   *
   * @since 2.0
   */
  function display() {
    $redirect_url = $this->get_redirectified(); ?>
<div class="misc-pub-section">
  <label for="redirectify"><? _e( 'Redirection:', 'wp-redirectify' ); ?></label>
  <strong><?php ( !empty( $redirect_url ) ) ? _e( 'Enabled', 'wp-redirectify' ) : _e( 'Disabled', 'wp-redirectify' ); ?></strong>
  <a href="#edit_redirectify" class="edit-redirectify hide-if-no-js"><?php _e( 'Edit', 'wp-redirectify' ); ?></a>
  <div id="redirectifydiv" class="hide-if-js">
    <input type="hidden" name="hidden_redirectify" id="hidden-redirectify" value="<?php echo ( ! empty( $redirect_url ) ) ? $redirect_url : ''; ?>" />
    <?php wp_nonce_field( plugin_basename( __FILE__ ), '_redirect_wpnonce', false, true ); ?>
    <input type="text" id="redirectify" name="redirect" value="<?php echo ( !empty( $redirect_url ) ) ? $redirect_url : ''; ?>" />
    <a href="#edit_redirectify" class="save-post-status save-redirectify hide-if-no-js button"><?php _e( 'OK', 'wp-redirectify' ); ?></a>
    <a href="#edit_redirectify" class="cancel-post-status cancel-redirectify hide-if-no-js button-cancel"><?php _e( 'Cancel', 'wp-redirectify' ); ?></a>
    <br /><em class="howto"><?php _e( 'Input a URL to be redirected to.', 'wp-redirectify' ); ?></em>
  </div>
</div>
<?php }

  /**
   * used to save redirectify form field
   *
   * @since 2.0
   */
  function save( $post_id, $post ) {
    if ( ! user_can_save( $post_id, $post, '_redirect_wpnonce' ) ) :
      return $post_id;
    endif;

    $old = $this->get_redirectified( get_queried_object_id( $post_id ) );
    $new = esc_url_raw( $_POST[ 'redirect' ] );
    if ( $new && $new != $old ) :
      update_post_meta( $post_id, '_redirect', $new );
    elseif ( '' == $new && $old ) :
      delete_post_meta( $post_id, '_redirect', $old );
    endif;
  }

  /**
   * used to modify layout for display
   *
   * @since 2.0
   */
  function enqueue_script( $hook_suffix ) {
    if ( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) :
      wp_enqueue_script( 'jsRedirectify', plugins_url( 'js/redirectify.min.js', __FILE__ ), array( 'jquery' ), '1.0', true );
    endif;
  }

  /**
   * used to filter permalinks with redirectified urls
   *
   * @since 2.1
   */
  function redirectify_filter() {
    $redirectify_opts = get_option( 'redirectify' );

    // print the final result
    echo '<fieldset><legend class="screen-reader-text"><span>' . __( 'Redirectify URL Display', 'wp-redirectify' ) . '</span></legend><label for="redirectify_filter"><input type="checkbox" name="redirectify[filter]" id="redirectify_filter" value="1" ' . checked( '1', ( isset( $redirectify_opts['filter'] ) ? $redirectify_opts['filter'] : 0 ), false ) . '/> ' . __( 'WordPress should display redirectify url as post permalink instead', 'wp-redirectify' ) . '</label></fieldset>';
  }

  /**
   * used to redirect posts & pages and whether or not to filter permalinks
   *
   * @since 2.0
   */
  function redirect() {
    if ( is_singular() && ( $redirect = $this->get_redirectified( get_queried_object_id() ) ) ) :
      wp_redirect( $redirect, 301 );
      exit;
    endif;

    $redirectify_opts = get_option( 'redirectify' );
    if ( ! ( isset( $redirectify_opts['filter'] ) ? $redirectify_opts['filter'] : 0 ) && apply_filters( 'redirectify_bail_on_permalink_filter', 'is_feed' ) )
      return;

    $filters = array(
      'post_link',
      'post_type_link',
      'page_link'
    );
    foreach ( apply_filters( 'redirectify_permalink_filter', $filters ) as $filter ) {
      add_filter( $filter, array( $this, 'filter_urls' ) );
    }
  }

  /**
   *used to filter the redirection url as the permalink
   *
   * @since 2.1
   */
  function filter_urls( $url, $post = null, $leavename = false ) {
    if ( $redirect = $this->get_redirectified($id) )
      return $redirect;

    return $url;
  }

  /**
   *used to get the redirection url
   *
   * @since 2.1
   */
  function get_redirectified( $postid = null ) {
    if ( $url = esc_url_raw( get_post_meta( ( $postid ) ? $postid : get_the_ID(), '_redirect', true ) ) )
      return $url;
    return;
  }

  /**
   * used to display post state notifying what posts are redirection posts
   *
   * @since 2.0
   */
  function post_state( $states ) {
    if ( $this->get_redirectified() ) :
      $states[ 'redirect' ] = __( 'Redirectify', 'wp-redirectify' );
    endif;

    return $states;
  }

  /**
   * used to filter edit post list
   *
   * @since 2.0
   */
  function pre_get_posts( $wp_query ) {

    // Bail if not a category, not a query or not main query
    if ( ! $wp_query->is_admin || ! is_a( $wp_query, 'WP_Query' ) || ! $wp_query->is_main_query() )
      return;

    $post_type = get_current_post_type();
    add_filter( "views_edit-{$post_type}", array( $this, 'views_edit' ) );

    global $pagenow;

    if ( $pagenow == 'edit.php' && isset( $_REQUEST[ 'redirectify' ] ) ) :
      $wp_query->set( 'meta_key', '_redirect' );
    endif;

  }

  /**
   * adds extra menu item to post views
   *
   * @since 2.0
   */
  function views_edit( $views ) {
    $post_type = get_current_post_type();
    $query = array(
      'post_type' => $post_type,
      'meta_key' => '_redirect'
    );
    $result = new WP_Query( $query );
    if ( $result->have_posts() ) :
      $class = ( $_REQUEST[ 'redirectify' ] ) ? ' class="current"' : '';
      if ( !empty( $class ) ) :
        $views[ 'all' ] = str_replace( 'class="current"', '', $views[ 'all' ] );
      endif;
      $views[ 'redirectify' ] = sprintf( '<a href="%s"'. $class .'>' . __( 'Redirectify', 'wp-redirectify' ) . '<span class="count">(%d)</span></a>', admin_url( 'edit.php?post_type=$post_type&redirectify=1' ), $result->found_posts );
    endif;

    return $views;
  }

  /**
   * add helpful information to options page
   *
   * @since 2.1
   */
  function add_help_tab() {
    $screen = get_current_screen();
    $screen->add_help_tab( array(
      'id'      => 'wp-redirectify-filter',
      'title'   => __( 'Redirectify URL Display' , 'wp-redirectify' ),
      'content' => '<p>' . __( 'You can choose whether or not your site will display the redirectify URL or your normal individual post or page URL. If you want to display the redirectify URL, click the checkbox next to &#8220;WordPress should display redirectify url as post permalink instead&#8221; and click the Save Changes button at the bottom of the screen.' , 'wp-redirectify') . '</p>',
    ) );
    $screen->set_help_sidebar(
      $screen->get_help_sidebar() . '<p><a href="https://wordpress.org/support/plugin/wp-redirectify" target="_blank">' . __( 'Redirectify Support', 'wp-redirectify' ) . '</a></p>'
    );
  }
}
$redirectify = new Redirectify();

/**
 * gets the current post type in the WordPress Admin
 */
if( ! function_exists( 'get_current_post_type' ) ) :
  function get_current_post_type() {
    global $post, $typenow, $current_screen;

    //we have a post so we can just get the post type from that
    if ( $post && $post->post_type )
      return $post->post_type;

    //check the global $typenow - set in admin.php
    elseif( $typenow )
      return $typenow;

    //check the global $current_screen object - set in sceen.php
    elseif( $current_screen && $current_screen->post_type )
      return $current_screen->post_type;

    //lastly check the post_type querystring
    elseif( isset( $_REQUEST[ 'post_type' ] ) )
      return sanitize_key( $_REQUEST[ 'post_type' ] );

    //we do not know the post type!
    return null;
  }
endif;

if( ! function_exists( 'user_can_save' ) ) :
  /**
   * Check if the current user can save metadata
   *
   * @param   int       $post_id    The post ID.
   * @param   post    $post         The post object
   * @param   string  $nonce      The post nonce.
   * @return    bool
   */
  function user_can_save( $post_id, $post, $nonce ) {
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) );

    $can_edit = true;
    if ( 'page' == $post->post_type ) :
      if ( ! current_user_can( 'edit_page', $post_id ) ) :
        $can_edit = false;
      endif;
    elseif ( ! current_user_can( 'edit_post', $post_id ) ) :
      $can_edit = false;
    endif;

    return ! ( $is_autosave || $is_revision ) && $is_valid_nonce && $can_edit;
  }
endif;