<?php

class Hasher {

	public function __construct() {

		// Hooks
		self::actions();
		self::filters();

	}

	protected function actions() {

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'schedule_event' ) );
		add_action( 'hasher_scheduled_event', array( $this, 'get_tweets' ) );

	}

	protected function filters() {

		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
	}

	public function register_post_types() {

		register_extended_post_type(
			'tweet',
			array(
				'public' => false,
				'supports' => array(
					'editor', // The tweet text
					'thumbnail', // Tweet picture (if applicable)
					'author', // User who tweeted
				),
			),
			array(
				'singular' => __('Tweet'),
				'plural' => __('Tweets'),
				'slug' => 'tweet',
			)
		);

	}

	public function cron_schedules( $schedules ) {

		$schedules['thirty_seconds'] = array(
			'interval' => 30,
			'display' => __('30 Seconds'),
		);

		$schedules['minutes'] = array(
			'interval' => 60,
			'display' => __('Every minute'),
		);

		return $schedules;

	}

	public function schedule_event() {

		wp_schedule_event(
			time(),
			'thirty_seconds',
			'hasher_scheduled_event'
		);

	}

	public function get_tweets() {

		// Set up the Tweet Fetcher
		$fetcher = new FooTweetFetcher();
		$args = array(
			'limit' => 10, // get 10 tweets please
			'include_rts' => false, // do not include retweets
			'exclude_replies' => true // exclude replies
		);

		// Get the tweets
		$tweets = $fetcher->get_search( '#qanda', $args );

		if ( $tweets !== false && is_array( $tweets ) && (count( $tweets ) > 0) ) {
			//loop through each tweet
			foreach ( $tweets as $tweet ) {

				// Check if the tweet has already been imported
				$post_id = get_option( 'tweet_' . $tweet->id, null );

				// Import the tweet
				$this->import_tweet( $tweet, $post_id );

			}
		}

	}

	protected function import_tweet( $tweet, $post_id = null ) {

		// Prepare post data
		$post_data = array(
			'post_type' => 'tweet',
			'status' => 'publish', // @todo filter this
			'ping_status' => 'closed',
			'post_date' => $fetcher->get_wp_time( $tweet->created_at ),
			'comment_status' => 'closed',
		);

		// We might be updating an existing post
		if ( ! is_null( $post_id ) )
			$post_data['ID'] = intval( $post_id );

		// Convert all URLs, mentions, hashtags, media to clickable links
		$post_data['post_content'] = $fetcher->make_clickable( $tweet );

		// @todo Set post author

		// @todo Add hashtags as custom taxonomy

		// @todo Add source as custom taxonomy

		if ( is_null( $post_id ) ) {

			// Create the post
			$post_id = wp_insert_post( $post_data, true );

			// Set an option linking this tweet with the post for easier checking later on
			add_option( 'tweet_' . $tweet->id, $post_id );

		} else {

			// Update the post
			$post_id = wp_update_post( $post_data, true );

			// Set an option linking this tweet with the post for easier checking later on
			update_option( 'tweet_' . $tweet->id, $post_id );

		}

		// Set some custom fields

		// @todo "id"

		// @todo "in_reply" => reply info

		// @todo "retweet_count"

	}

}

$hasher = new Hasher();