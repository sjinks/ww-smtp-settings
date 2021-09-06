<?php

use WildWolf\WordPress\SMTP\Admin;
use WildWolf\WordPress\SMTP\AdminSettings;

defined( 'ABSPATH' ) || die();
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
	<?php
		settings_fields( AdminSettings::OPTION_GROUP );
		do_settings_sections( Admin::OPTIONS_MENU_SLUG );
		submit_button( __( 'Save settings', 'ww-smtp' ) );
	?>
	</form>

	<form class="hide-if-no-js">
		<hr/>

		<h2 id="smtp-test-heading"><?php esc_html_e( 'Test Your Settings', 'ww-smtp' ); ?></h2>

		<p id="smtp-test-description"><?php esc_html_e( 'Send a test email using the saved settings.', 'ww-smtp' ); ?></p>

		<table class="form-table" aria-labelledby="smtp-test-heading" aria-describedby="smtp-test-description" role="presentation" id="smtp-test-table">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'To', 'ww-smtp' ); ?></th>
					<td><input type="email" id="smtp_to" value="" placeholder="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>"/></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Subject', 'ww-smtp' ); ?></th>
					<td><input type="text" id="smtp_subject" value="" placeholder="<?php echo esc_attr( $default_subject ); ?>"/></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Message', 'ww-smtp' ); ?></th>
					<td><textarea id="smtp_message" rows="5" columns="30"><?php echo esc_textarea( $default_message ); ?></textarea></td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<button type="button" id="smtp_test" class="button button-primary"><?php esc_html_e( 'Send Test Email', 'ww-smtp' ); ?></button>
		</p>
	</form>
</div>
