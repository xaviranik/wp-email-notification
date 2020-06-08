<?php

/**
 * Plugin Name:       WeDevs Posts Email Notification
 * Plugin URI:        https://zabiranik.me
 * Description:       Sends email to admin when a post gets published.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Zabir Anik
 * Author URI:        https://zabiranik.me
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wd-posts-email-notificaition
 */

if (!defined('ABSPATH')) exit;

/**
 * Main plugin class
 */
final class WD_Posts_Email_Notification {

    /**
     * WD Posts Email Notification Version
     * @var string
     */
    const version = '1.0.0';

    /**
     * Class Constructor
     */
    public function __construct() {

        $this->define_constants();

        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        $this->init_plugin();
    }

    /**
     * Initializes a Singleton
     * @return \WD_Posts_Email_Notification
     */
    public static function init() {

        static $instance = false;

        if ( ! $instance ) {
            $instance = new Self();
        }

        return $instance;
    }

    /**
     * Defines plugin constants
     * @return void
     */
    public function define_constants() {
        define( 'WD_POSTS_EMAIL_NOTIFICATION_VERSION', self::version );
    }

    /**
     * Plugin init
     * @return void
     */
    public function init_plugin() {
        $this->register_post_published_notification();
    }

    /**
     * Executes on plugin activation
     * @return void
     */
    public function activate() {
        $installed = get_option( 'wd_posts_email_notification_installed' );

        if ( ! $installed ) {
            update_option( 'wd_posts_email_notification_installed', time() );
        }

        update_option( 'wd_posts_email_notification_version', WD_POSTS_EMAIL_NOTIFICATION_VERSION );
    }

    /**
     * Registers post publish notification
     *
     * @return void
     */
    public function register_post_published_notification() {
        add_action( 'publish_post', [ $this, 'send_email_to_admin' ], 10, 2 );
    }

    /**
     * Sends email to admin when a post gets published
     *
     * @return void
     */
    public function send_email_to_admin($post_id, $post) {
        $author  = $post->post_author;
        $name    = get_the_author_meta('display_name', $author);
        $email   = get_the_author_meta('user_email', $author);
        $title   = $post->post_title;
        $to[]    = sprintf( '%s <%s>', $name, $email );
        $subject = sprintf( 'Published: %s', $title );
        $message = sprintf( 'Hello, %s! Your post "%s" has been published.' . "\n\n", $name, $title );
        wp_mail( $to, $subject, $message );
    }
}

/**
 * WD Posts Email Notification Instance init
 * @return \WD_Posts_Email_Notification
 */
function WD_posts_email_notification_init() {
    return WD_Posts_Email_Notification::init();
}

// Turn on the plugin
WD_posts_email_notification_init();
