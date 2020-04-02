<?php

namespace IFM;

class View_Profile
{

	public static function render()
	{
		$user_id      = get_query_var('user_id');
		$current_user = get_user_by('id', $user_id);
		echo '<div class="user-account-details">';
		echo '<h5>Username: ' . $current_user->user_nicename . '</h5>';
		$user_karma = Model_User::calculate_user_karma($user_id);
		echo '<h5>User Karma: ' . $user_karma . '</h5>';
		echo '<h5>User Since: ' . human_time_diff(strtotime($current_user->user_registered), current_time('timestamp', 1)) . ' ago</h5>';
		if (get_user_meta($user_id, 'about_user', true)) {
			echo '<h5>About:</h5><div class="ifm-about-user">' . stripslashes(get_user_meta($user_id, 'about_user', true)) . '</div>';
		}
		if (count_user_posts($user_id, 'aggregator-posts')) {
			echo "<a class='btn btn-default view-user-posts' href='" . add_query_arg('user_id', $user_id, home_url(IFM_ROUTE_FORUM)) . "'>" . $current_user->user_nicename . "'s posts</a>";
		}
		if (get_current_user_id() === (int) get_query_var('user_id')) {
			echo "<div class='ifm-user-edit'><a href='" . home_url(IFM_ROUTE_MY_ACCOUNT) . "'>Edit Your Profile</a></div>";
		}
		echo '</div>';
	}
}
