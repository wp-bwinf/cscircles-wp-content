<?php
/**
** Special Mail Tags
** https://contactform7.com/special-mail-tags/
**/

add_filter( 'wpcf7_special_mail_tags', 'wpcf7_special_mail_tag', 10, 3 );

function wpcf7_special_mail_tag( $output, $name, $html ) {
	$name = preg_replace( '/^wpcf7\./', '_', $name ); // for back-compat

	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
		return $output;
	}

	if ( '_remote_ip' == $name ) {
		if ( $remote_ip = $submission->get_meta( 'remote_ip' ) ) {
			return $remote_ip;
		} else {
			return '';
		}
	}

	if ( '_user_agent' == $name ) {
		if ( $user_agent = $submission->get_meta( 'user_agent' ) ) {
			return $html ? esc_html( $user_agent ) : $user_agent;
		} else {
			return '';
		}
	}

	if ( '_url' == $name ) {
		if ( $url = $submission->get_meta( 'url' ) ) {
			return esc_url( $url );
		} else {
			return '';
		}
	}

	if ( '_date' == $name
	or '_time' == $name ) {
		if ( $timestamp = $submission->get_meta( 'timestamp' ) ) {
			if ( '_date' == $name ) {
				return date_i18n( get_option( 'date_format' ), $timestamp );
			}

			if ( '_time' == $name ) {
				return date_i18n( get_option( 'time_format' ), $timestamp );
			}
		}

		return '';
	}

	if ( '_invalid_fields' == $name ) {
		return count( $submission->get_invalid_fields() );
	}

	return $output;
}

add_filter( 'wpcf7_special_mail_tags', 'wpcf7_post_related_smt', 10, 3 );

function wpcf7_post_related_smt( $output, $name, $html ) {
	if ( '_post_' != substr( $name, 0, 6 ) ) {
		return $output;
	}

	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
		return $output;
	}

	$post_id = (int) $submission->get_meta( 'container_post_id' );

	if ( ! $post_id
	or ! $post = get_post( $post_id ) ) {
		return '';
	}

	if ( '_post_id' == $name ) {
		return (string) $post->ID;
	}

	if ( '_post_name' == $name ) {
		return $post->post_name;
	}

	if ( '_post_title' == $name ) {
		return $html ? esc_html( $post->post_title ) : $post->post_title;
	}

	if ( '_post_url' == $name ) {
		return get_permalink( $post->ID );
	}

	$user = new WP_User( $post->post_author );

	if ( '_post_author' == $name ) {
		return $user->display_name;
	}

	if ( '_post_author_email' == $name ) {
		return $user->user_email;
	}

	return $output;
}

add_filter( 'wpcf7_special_mail_tags', 'wpcf7_site_related_smt', 10, 3 );

function wpcf7_site_related_smt( $output, $name, $html ) {
	$filter = $html ? 'display' : 'raw';

	if ( '_site_title' == $name ) {
		return get_bloginfo( 'name', $filter );
	}

	if ( '_site_description' == $name ) {
		return get_bloginfo( 'description', $filter );
	}

	if ( '_site_url' == $name ) {
		return get_bloginfo( 'url', $filter );
	}

	if ( '_site_admin_email' == $name ) {
		return get_bloginfo( 'admin_email', $filter );
	}

	return $output;
}

add_filter( 'wpcf7_special_mail_tags', 'wpcf7_user_related_smt', 10, 3 );

function wpcf7_user_related_smt( $output, $name, $html ) {
	if ( '_user_' != substr( $name, 0, 6 )
	or '_user_agent' == $name ) {
		return $output;
	}

	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
		return $output;
	}

	$user_id = (int) $submission->get_meta( 'current_user_id' );

        if ( '_user_info' == $name ) {
          if ($user_id == 0) return 'User not logged in.';
          $user = new WP_User( $user_id );
	  return '#' . $user->ID . ' ' . $user->user_login
	  . " <" . $user->user_firstname . ' ' . $user->user_lastname . ">"
	  . " " . $user->user_email;
        }

	if ( ! $user_id ) {
		return '';
	}

	$primary_props = array( 'user_login', 'user_email', 'user_url' );
	$opt = ltrim( $name, '_' );
	$opt = in_array( $opt, $primary_props ) ? $opt : substr( $opt, 5 );

	$user = new WP_User( $user_id );

	if ( $user->has_prop( $opt ) ) {
		return $user->get( $opt );
	}

	return '';
}
