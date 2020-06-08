<?php

/**
 * Post Controller Class
 *
 * @package IFM
 */

namespace IFM;


class Controller_Forum
{
	/**
	 * Define Posts Per Page for Pagination. Eventually set in WordPress Admin.
	 *
	 * @var integer
	 */
	private $posts_per_page = 30;

	/**
	 * Registration function.
	 */
	public static function register()
	{
		$forum_ctrl = new self();

		add_shortcode('edit-aggpost', array($forum_ctrl, 'render_edit_post_container'));

		add_action('wp_ajax_add_entry_karma', array($forum_ctrl, 'my_user_vote'));
		add_action('wp_ajax_nopriv_add_entry_karma', array($forum_ctrl, 'redirect_to_login_ajax'));
		add_action('admin_post_submit_post', array($forum_ctrl, 'submit_post'));
		add_action('admin_post_nopriv_submit_post', array($forum_ctrl, 'redirect_to_login'));
		add_action('admin_post_edit_post', array($forum_ctrl, 'edit_post'));

		// Limit media library access

		add_filter('ajax_query_attachments_args', array($forum_ctrl, 'show_current_user_attachments'));

		// Limit media library access
		// add_action('wp_ajax_nopriv_more_ifm_posts', array($plugin, 'load_more_posts'));
		// add_action('wp_ajax_more_ifm_posts', array($plugin, 'load_more_posts'));
		// add_action('wp_ajax_addComment', array($plugin, 'add_comment'));
		// add_action('wp_ajax_nopriv_addComment', array($plugin, 'redirect_to_login_ajax'));
		// add_action('wp_ajax_vote_on_comment', array($plugin, 'vote_on_comment'));
		// add_action('wp_ajax_nopriv_vote_on_comment', array($plugin, 'redirect_to_login_ajax'));
		// add_filter('ajax_query_attachments_args', array($plugin, 'ifm_limit_media_upload_to_user'));
	}

	/**
	 * Returns Forum function
	 *
	 * @param array $search_results
	 * @return void
	 */
	public function main()
	{

		// define initial args
		$args = array(
			'posts_per_page' => 20
		);

		if (get_query_var('ifm_query')) {
			$args['s'] = sanitize_text_field(get_query_var('ifm_query'));
			$query = $this->ifm_search_posts($args);
		} else {
			$query = new Model_Query($args);
		}

		return view('forum/main', $query);
	}

	public function show_current_user_attachments()
	{
		$user_id = get_current_user_id();
		if ($user_id && !current_user_can('activate_plugins') && !current_user_can('edit_others_posts
')) {
			$query['author'] = $user_id;
		}
		return $query;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function submit()
	{
		return view('forum/submit-post');
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function edit_post()
	{

		if (get_post_field('post_author', $_POST['post-id']) !== get_current_user_id()) {
			wp_safe_redirect(esc_url(add_query_arg('ifm_post_id', $_POST['post-id'], home_url('edit'))));
		}

		$the_post = array(
			'ID'         => $_POST['post-id'],
			'post_title' => $_POST['post-title'],
		);

		if ('' !== $_POST['post-text-content']) {
			$the_post['post_content'] = $_POST['post-text-content'];
		} else {
			update_post_meta($_POST['post-id'], 'ifm_entry_url', $_POST['post-url']);
		}
		wp_set_object_terms($_POST['post-id'], $_POST['post-type'], IFM_POST_TAXONOMY_NAME, false);
		wp_update_post($the_post);

		wp_safe_redirect(home_url(IFM_ROUTE_FORUM));
	}

	/**
	 * Limit media upload options on the frontend visual editor to the user's personal media.
	 */
	function ifm_limit_media_upload_to_user($query)
	{
		$user_id = get_current_user_id();
		if ($user_id && !current_user_can('activate_plugins') && !current_user_can('edit_others_posts')) {
			$query['author'] = $user_id;
		}
		return $query;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function render_edit_post_container()
	{
		return view('forum/edit-post');
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function ifm_search_posts($args)
	{
		$query = new Model_Query($args);
		// $query->parse_query($args);

		relevanssi_do_query($query);
		return $query;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function redirect_to_login()
	{
		wp_redirect(home_url(IFM_NAMESPACE . '/login'));
		die();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function redirect_to_login_ajax()
	{
		$redirect_url         = home_url(IFM_NAMESPACE . '/login');
		$response['redirect'] = $redirect_url;
		$response             = json_encode($response);
		echo $response;
		die();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function my_user_vote()
	{
		$karma_tracker = new Model_Post;
		$karma_tracker->update_post_karma();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function submit_post()
	{
		Model_Post::store();
	}
}

Controller_Forum::register();
