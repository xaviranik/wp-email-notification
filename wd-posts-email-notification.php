<?php

/**
 * Plugin Name:       WeDevs Posts Email Notification
 * Plugin URI:        https://zabiranik.me
 * Description:       Sends email to admin when a post gets published.
 * Version:           1.1.0
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
    const version = '1.1.0';

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
        $this->register_email_service();
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
     * Registers email service
     *
     * @return void
     */
    protected function register_email_service() {
        add_action( 'phpmailer_init', 'phpmailer_init' );
    }

    /**
     * Registers post publish notification
     *
     * @return void
     */
    public function register_post_published_notification() {
        add_action( 'publish_post', [ $this, 'notify_for_published_post' ], 10, 2 );
    }

    /**
     * Sends email to author and site managers when a post gets published
     *
     * @return void
     */
    public function notify_for_published_post($post_id, $post) {
        $author       = $post->post_author;
        $author_name  = get_the_author_meta('display_name', $author );
        $author_email = get_the_author_meta('user_email', $author );
        $title        = $post->post_title;
        $to[]         = $author_email;
        $subject      = sprintf( 'Post published: %s', $title );
        $message      = sprintf( 'Author        : %s, Post Name: "%s" has been published. View post: %s' . "\n\n", $author_name, $title, get_permalink( $post ) );

        add_filter( 'get_site_manager_email_list', [ $this, 'get_site_manager_email_list' ] );
        $to = apply_filters( 'get_site_manager_email_list', $to );

        wp_mail( implode(", ", $to), $subject, $message );
    }

    public function get_site_manager_email_list( $emails ) {
        $managers = get_users( [
             'role__in' => [ 'administrator', 'editor' ]
        ] );

        foreach ( $managers as $manager ) {
            if ( is_email( $manager->user_email ) ) {
                $emails[] = $manager->user_email;
            }
        }
        return array_unique( $emails );
    }

    /**
     * PHPMailer initialization
     *
     * @param PHPMailer $phpmailer
     * @return void
     */
    function phpmailer_init(PHPMailer $phpmailer) {
        $phpmailer->Host       = 'smtp.test.net';
        $phpmailer->Port       = 465;
        $phpmailer->Username   = '';
        $phpmailer->Password   = '';
        $phpmailer->SMTPAuth   = true;
        $phpmailer->SMTPSecure = 'ssl';
        $phpmailer->IsSMTP();
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
