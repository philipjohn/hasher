<?php

class Hasher {

	public function __construct() {

		// Hooks
		self::actions();

	}

	protected function actions() {

		add_action( 'init', array( $this, 'register_post_types' ) );

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

}