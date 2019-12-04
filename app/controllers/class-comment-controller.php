<?php

/**
 *
 * @package Ifmer
 */
require_once( IFM_APP . 'models/class-comments.php' );
require_once( IFM_APP . 'views/class-comment-container.php' );



class IfmCommentController {

	public static function register() {
		$plugin = new self();

		add_shortcode( 'custom-comments', array( $plugin, 'show' ) );

		add_action( 'wp_ajax_addComment', array( $plugin, 'comment_on_post' ) );
		add_action( 'wp_ajax_nopriv_addComment', array( $plugin, 'redirect_to_login' ) );
		add_action( 'wp_ajax_vote_on_comment', array( $plugin, 'vote_on_comment' ) );
		add_action( 'wp_ajax_nopriv_vote_on_comment', array( $plugin, 'redirect_to_login' ) );
		add_action( 'wp_ajax_reply_to_comment', array( $plugin, 'comment_on_comment' ) );
		add_action( 'wp_ajax_nopriv_replyToComment', array( $plugin, 'redirect_to_login' ) );

	}
	public function __construct() {
	}

	public function show() {
		$crowd_comments = new IfmComments;
		$comment_query  = $crowd_comments->query_comments();
		$comment_array  = json_decode( json_encode( $comment_query ), true );

		return IfmCommentContainer::render( $comment_array );
	}

	public function vote_on_comment() {
		$crowd_comments = new IfmComments;
		$crowd_comments->update_comment_karma();

	}

	public function comment_on_post() {
		$crowd_comments = new IfmComments;
		$crowd_comments->add_comment_to_post( $postID );
	}

	public function comment_on_comment() {
		$crowd_comments = new IfmComments;
		$crowd_comments->comment_on_comment();
	}

	public function redirect_to_login() {
		$redirect_url         = home_url( 'member-login' );
		$response[ redirect ] = $redirect_url;
		$response             = json_encode( $response );
		echo $response;
		die();
	}
}

IfmCommentController::register();
