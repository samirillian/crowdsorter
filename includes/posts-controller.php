<?php

class crowdsortPostsController
{
    public static function register()
    {
        $plugin = new self();

        add_shortcode('crowdsortcontainer', array( $plugin, 'create_container' ));
        add_shortcode('crowdsorter-post', array( $plugin, 'create_new_post_template' ));
        add_shortcode('edit-aggpost', array( $plugin, 'render_edit_post_container'));

        add_action('init', array( $plugin, 'generate_sorter' ));
        add_action('wp_ajax_add_entry_karma', array( $plugin, 'my_user_vote' ));
        add_action('wp_ajax_nopriv_add_entry_karma', array( $plugin, 'redirect_to_login_ajax'));
        add_filter('query_vars', array( $plugin, 'add_query_vars'));
        add_action('post_ranking_cron', array( $plugin, 'update_post_rank'));
        add_action('wp_ajax_nopriv_more_aggregator_posts', array( $plugin, 'load_more_posts'));
        add_action('wp_ajax_more_aggregator_posts', array( $plugin, 'load_more_posts'));
        add_action('wp_ajax_addComment', array( $plugin, 'add_comment'));
        add_action('wp_ajax_nopriv_addComment', array( $plugin, 'redirect_to_login_ajax'));
        add_action('wp_ajax_vote_on_comment', array( $plugin, 'vote_on_comment'));
        add_action('wp_ajax_nopriv_vote_on_comment', array( $plugin, 'redirect_to_login_ajax'));
        add_action('admin_post_submit_post', array( $plugin, 'submit_post'));
        add_action('admin_post_nopriv_submit_post', array( $plugin, 'redirect_to_login'));
        add_action('admin_post_edit_post', array( $plugin, 'edit_post'));

        add_action( 'admin_post_agg_search_posts', array( $plugin, 'agg_search_posts' ));

    }
    public function __construct()
    {
    }

    public function edit_post() {

      if (get_post_field( 'post_author', $_POST['post-id']) != get_current_user_id()) {
        wp_safe_redirect(esc_url(add_query_arg('agg_post_id', $_POST['post-id'], home_url('edit'))));
      }

      $the_post = array(
      'ID'           => $_POST['post-id'],
      'post_title'   => $_POST['post-title'],
      );

      if ($_POST['post-text-content'] != '') {
        $the_post['post_content'] = $_POST['post-text-content'];
      } else {
        update_post_meta( $_POST['post-id'], 'aggregator_entry_url', $_POST['post-url'] );
      }
      wp_set_object_terms( $_POST['post-id'], $_POST['post-type'], 'aggpost-type', false );
      wp_update_post( $the_post );

      wp_safe_redirect(home_url('fin-forum'));
    }

    public function render_edit_post_container() {
      require_once('views/edit-posts.php');
      crowdsorterEditPosts::render();
    }

    public function add_query_vars($vars)
    {
        $vars[] .= 'agg_post_id';
        $vars[] .= 'status';
        $vars[] .= 'user_id';
        $vars[] .= 'aggpost_tax';
        return $vars;
    }

    public function update_post_rank()
    {
        require_once('models/news-aggregator.php');
        newsAggregator::update_temporal_karma();
    }

    public function create_container($search_results = [])
    { 
        if (!isset($_GET['agg_query'])) {
          require_once('models/news-aggregator-posts.php');
          $query = postRankTracker::sort_posts();
          $pageposts = $query[0];
        } else {
          $pageposts = $this->agg_search_posts();
        }

        require_once('views/post-container.php');
        $content = crowdsorterContainer::render($pageposts);
        return $content;
    }

    public function agg_search_posts()
    {
      $query->query_vars['s'] = sanitize_text_field($_GET['agg_query']);
      $query->query_vars['posts_per_page'] = 30;
      $posts = [];
      foreach (relevanssi_do_query($query) as $post) {
        if ($post->post_type === 'aggregator-posts') {
          $posts[] = $post;
        }
      }
      return $posts;
    }

    public function load_more_posts()
    {
      require_once('models/news-aggregator-posts.php');
      $query = postRankTracker::sort_posts();
      $pageposts = $query[0];

      require_once('views/templates/post-template.php');
      $content = postTemplate::render($pageposts);
      return $content;
    }

    public function generate_sorter()
    {
        require_once('models/sorter-factory.php');
        $sorterFactory = new sorterFactory;
        $aggregator = $sorterFactory->get_sorter("News-Aggregator");

        //add post definition details
        $aggregator->define_post_type();
        $aggregator->define_post_meta();

        //add metadata on post creation
        //eventually add functionality to allow more vars in plugin
        add_action('load-post.php', array($aggregator, 'define_post_meta_on_load'));
        add_action('load-post-new.php', array($aggregator, 'define_post_meta_on_load'));
        add_action('publish_aggregator-posts', array($aggregator, 'define_meta_on_publish'));

    }

    public function redirect_to_login()
    {
      wp_redirect( home_url( 'member-login' ));
      die();
    }

    public function redirect_to_login_ajax()
    {
      $redirect_url = home_url( 'member-login' );
      $response[redirect] = $redirect_url;
      $response = json_encode($response);
      echo $response;
      die();
    }

    public function my_user_vote()
    {
        require_once('models/news-aggregator-posts.php');
        $karmaTracker = new postRankTracker;
        $karmaTracker->update_post_karma();
    }

    public function create_new_post_template()
    {
      require_once('views/new-post.php');
      $crowdSorterPostTemplate = new crowdsorterPostTemplate;
      $crowdSorterPostTemplate->render();
    }

    public function submit_post()
    {
      require_once('models/news-aggregator-posts.php');
      $crowdSorterPosts = new postRankTracker;
      $crowdSorterPosts->submit_post();
    }

}

crowdsortPostsController::register();
