<?php
/*
Plugin Name: Comment Author URL Stripper
Plugin URI: https://wordpress.org/plugins/comment-author-url-stripper/
Description: Removes the comment author URL if it contains any of the specified words. <a href="options-general.php?page=comment-author-url-stripper.php">Configuration</a>.
Version: 1.1
Author: Nick Momrik
Author URI: http://nickmomrik.com/

*/ 

add_action( 'admin_menu', 'mdv_caus_add_pages' );
add_filter( 'pre_comment_author_url', 'mdv_caus' );
register_activation_hook( __FILE__, 'set_mdv_caus_options' );

function mdv_caus_add_pages() {
	add_options_page( 'Comment Author URL Stripper Options', 'CAU Stripper', 8, __FILE__, 'mdv_caus_options_page' );
}

function set_mdv_caus_options() {
	add_option( 'mdv_caus_keys', '' );
}

function update_mdv_caust_options() {
	$updated = false;
	
	if ( $_REQUEST['mdv_caus_keys'] ) {
		update_option( 'mdv_caus_keys', $_REQUEST['mdv_caus_keys'] );
		$updated = true;
	}
	
	if ( $updated ) {
		?>
		<div id="message" class="updated fade">
			<p>Options saved.</p>
		</div>
		<?php
	} else {
		?>
		<div id="message" class="failed fade">
			<p>Failed to update options.</p>
		</div>
		<?php
	}
}

function mdv_caus_options_page() {
?>
	<div class="wrap">
	<h2>Comment Author URL Stripper Options</h2>
<?php
	if ( $_REQUEST['submit'] ) {
		update_mdv_caus_options();
	}
	$mdv_caus_keys = get_option( 'mdv_caus_keys' );
?>
	<form method="post" action="options.php">
		<?php wp_nonce_field( 'update-options' ); ?>

		<fieldset class="options">
			<p><?php _e( 'When a comment author URL contains any of these words, the URL will be removed. One word per line.' ); ?></p>
			<p>
				<textarea name="mdv_caus_keys" cols="60" rows="4" id="moderation_keys" style="width: 98%; font-size: 12px;" class="code"><?php echo esc_html( $mdv_caus_keys ); ?></textarea>
			</p>
		</fieldset>

		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="mdv_caus_keys" />
			<input type="submit" name="Submit" value="<?php _e( 'Update Options &raquo;' ); ?>" />
		</p>
	</form>
	</div>
<?php
}

function mdv_caus( $url ) {
	if ( ! mdv_caus_ok( $url ) ) {
		$url = '';
	}

	return $url;
}

function mdv_caus_ok( $url ) {
	$mdv_caus_keys = trim( get_option( 'mdv_caus_keys' ) );
	if ( ! empty( $mdv_caus_keys ) ) {
		$words = explode( "\n", $mdv_caus_keys );

		foreach ( $words as $word ) {
			$word = trim( $word );

			// Skip empty lines
			if ( empty( $word ) ) {
				continue;
			}

			// Do some escaping magic so that '#' chars in the
			// spam words don't break things:
			$word = preg_quote( $word, '#' );

			$pattern = "#$word#i";
			if ( preg_match( $pattern, $url ) ) {
				return false;
			}
		}
	}
	
	return true;
}
