<?php
// Overrides Timezone for a Post
add_action( 'init' , array( 'Post_Timezone', 'init' ) );

class Post_Timezone {
	public static function init() {
		add_filter( 'get_the_date', array( 'Post_Timezone', 'get_the_date' ), 12, 2 );
		add_filter( 'get_the_time', array( 'Post_Timezone', 'get_the_time' ), 12, 2 );
		add_filter( 'get_the_modified_date' , array( 'Post_Timezone', 'get_the_date' ), 12, 2 );
		add_filter( 'get_the_modified_time' , array( 'Post_Timezone', 'get_the_time'), 12, 2);
		add_action( 'post_submitbox_misc_actions', array( 'Post_Timezone', 'post_submitbox' ) );
		add_action( 'save_post', array( 'Post_Timezone', 'postbox_save_post_meta' ) );
	}

	public static function post_submitbox() {
		global $post;
		if ( 'post' === get_post_type( $post ) ) {
			echo '<div class="misc-pub-section misc-pub-section-last">';
			wp_nonce_field( 'timezone_override_metabox', 'timezone_override_nonce' );
			$tzlist = DateTimeZone::listIdentifiers();
			$timezone = get_post_meta( $post->ID, 'geo_timezone', true );
			if ( ! $timezone ) {
				$timezone = get_post_meta( $post->ID, '_timezone', true );
				if ( $timezone ) {
					update_post_meta( $post->ID, 'geo_timezone', true );
					delete_post_meta( $post->ID, '_timezone' );
				}
			}

			?>
			<label for="override_timezone"><?php _e( 'Change Displayed Timezone', 'simple-location' ); ?></label>
		<input type="checkbox" name="override_timezone" id="override_timezone" onclick='toggle_timezone();' <?php if ( $timezone ) { echo 'checked="checked"'; } ?> />
		 <br />
		 <select name="timezone" id="timezone" width="90%" <?php if ( ! $timezone ) { echo 'hidden'; }?>>
		<?php
			if ( ! $timezone ) {
				$timezone = get_option( 'timezone_string' );
			}

			echo wp_timezone_choice( $timezone );
			echo '</select>';
			echo '</div>';
		}

	}

	/* Save the post timezone metadata. */
	public static function postbox_save_post_meta( $post_id ) {
		/*
		* We need to verify this came from our screen and with proper authorization,
		* because the save_post action can be triggered at other times.
		*/
		if ( ! isset( $_POST['timezone_override_nonce'] ) ) {
			return;
		}
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['timezone_override_nonce'], 'timezone_override_metabox' ) ) {
			return;
		}
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
		if ( isset( $_POST['override_timezone'] ) ) {
			update_post_meta( $post_id, 'geo_timezone', $_POST['timezone'] );
		} else {
			delete_post_meta( $post_id, 'geo_timezone' );
		}
	}


	public static function get_the_date($the_date, $d = '' , $post = null) {
		$post = get_post( $post );
		if ( ! $post ) {
			return $the_date;
		}
		$timezone = get_post_meta( $post->ID, 'geo_timezone', true );
		if ( ! $timezone ) {
			if ( ! $timezone = get_post_meta( $post->ID, '_timezone', true ) ) {
				return $the_date;
			}
		}
		if ( '' === $d ) {
			$d = get_option( 'date_format' );
		}
		$datetime = new DateTime( $post->post_date_gmt, new DateTimeZone( 'GMT' ) );
		$datetime->setTimezone( new DateTimeZone( $timezone ) );
		return $datetime->format( $d );
	}

	public static function get_the_time($the_time, $d = '' , $post = null) {
		$post = get_post( $post );
		if ( ! $post ) {
			return $the_time;
		}
		$timezone = get_post_meta( $post->ID, 'geo_timezone', true );
		if ( ! $timezone ) {
			if ( ! $timezone = get_post_meta( $post->ID, '_timezone', true ) ) {
				return $the_time;
			}
		}
		if ( '' === $d ) {
			$d = get_option( 'time_format' );
		}
		$datetime = new DateTime( $post->post_date_gmt, new DateTimeZone( 'GMT' ) );
		$datetime->setTimezone( new DateTimeZone( $timezone ) );
		$the_time = $datetime->format( $d );
		return $the_time;
	}

} // End Class

