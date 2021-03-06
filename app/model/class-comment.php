<?php

/**
 * Undocumented class
 */

namespace IFM;

// Cannot Extend WP_Comment at this time, since WP_Comment is final class
class Model_Comment
{
	// Information needed for creating the plugin's pages
	public function query_comments()
	{
		global $wpdb;
		$querystr = "
      SELECT
        $wpdb->comments.*,
        CASE
          WHEN ROUND(POW((TIMESTAMPDIFF( MINUTE, $wpdb->comments.comment_date_gmt, UTC_TIMESTAMP())/60), 1.8), 2) = 0
          THEN .01
          ELSE
          ROUND(POW((TIMESTAMPDIFF( MINUTE, $wpdb->comments.comment_date_gmt, UTC_TIMESTAMP())/60), 1.8), 2)
          END as karma_divisor,
        (SELECT count(*) FROM wp_commentmeta WHERE comment_id=$wpdb->comments.comment_ID AND meta_key='user_upvote_id') as karma
      FROM $wpdb->comments
        WHERE $wpdb->comments.comment_post_ID=" . get_query_var('ifm_post_id') . '
        ORDER BY  (
                karma/karma_divisor
                ) DESC';

		$rankedcomments = $wpdb->get_results($querystr, OBJECT);
		return $rankedcomments;
	}

	public function create()
	{
		if (!wp_verify_nonce($_REQUEST['comment_nonce'], 'comment_nonce')) {
			exit('No naughty business please');
		}

		$comment_parent = 0;

		if (isset($_REQUEST['comment_parent'])) {
			$comment_parent = $_REQUEST['comment_parent'];
		}


		$comment = wp_insert_comment(
			array(
				'comment_parent'       => $comment_parent,
				'user_id'              => get_current_user_id(),
				'comment_content'      => $_REQUEST['replyContent'],
				'comment_post_ID'      => $_REQUEST['post_id'],
				'comment_author'       => wp_get_current_user()->display_name,
				'comment_author_email' => wp_get_current_user()->user_email,
			)
		);

		if (0 === $comment_parent) {

			$post_author_id = get_post_field( 'post_author', $_REQUEST['post_id'] );
			$post_author = new Model_User($post_author_id);

			if ($post_author->get('comment_on_post') && $post_author->get('email_verified')) {
				wp_mail($post_author->user_email, 'New comment on post', 'There was a new comment on your post');
			}

		} else {

			$comment_author_id = get_comment_author($comment_parent);
			$comment_author = new Model_User($comment_author_id);

			if ($comment_author->get('comment_on_comment') && $comment_author->get('email_verified')) {
				wp_mail($comment_author->user_email, 'New comment on one of your comments', 'There was a new comment on your comment');
			}
		}

		global $wpdb;
		$firstvote = $wpdb->insert(
			$wpdb->commentmeta,
			array(
				'comment_id' => $comment,
				'meta_key'   => 'user_upvote_id',
				'meta_value' => get_current_user_id(),
			),
			array('%d', '%s', '%d')
		);
	}

	public function update_comment_get_karma()
	{
		// if (!wp_verify_nonce($_REQUEST['nonce'], "comment_nonce")) {
		// exit("No naughty business please");
		// }
		global $wpdb;
		$userid     = get_current_user_id();
		$comment_id = $_REQUEST['comment_id'];
		$voted      = $wpdb->get_var(
			$wpdb->prepare(
				"
        SELECT count(1)
        FROM $wpdb->commentmeta
        WHERE comment_ID=%d
        AND meta_key='user_upvote_id'
        AND meta_value=%d
      ",
				$comment_id,
				$userid
			)
		);
		if ($voted >= 1) {
			$vote    = $wpdb->delete(
				$wpdb->commentmeta,
				array(
					'comment_id' => $comment_id,
					'meta_key'   => 'user_upvote_id',
					'meta_value' => $userid,
				),
				array('%d', '%s', '%d')
			);
			$upvoted = false;
		} else {
			$vote    = $wpdb->insert(
				$wpdb->commentmeta,
				array(
					'comment_id' => $comment_id,
					'meta_key'   => 'user_upvote_id',
					'meta_value' => $userid,
				),
				array('%d', '%s', '%d')
			);
			$upvoted = true;
		}

		$entry_karma = $wpdb->get_var(
			$wpdb->prepare(
				"
        SELECT count(*)
        FROM $wpdb->commentmeta
        WHERE comment_id=%d
        AND meta_key='user_upvote_id'
      ",
				$comment_id
			)
		);

		if (false === $voted) {
			$result['type']        = 'error';
			$result['entry_karma'] = $entry_karma;
			$result['redirect']    = 'wat';
		} else {
			$result['upvoted']     = $upvoted;
			$result['type']        = 'success';
			$result['entry_karma'] = $entry_karma;
		}

		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$result = json_encode($result);
			echo $result;
		} else {
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		}

		die();
	}
}
