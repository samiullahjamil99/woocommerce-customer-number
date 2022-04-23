<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer username */ ?>
<p><?php printf( esc_html__( 'Hallo %s,', 'woocommerce' ), esc_html( $user_login ) ); ?></p>
<?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
<p><?php printf( esc_html__( 'Vielen Dank für die Erstellung eines Kontos auf %1$s. Ihr Benutzername ist %2$s. Sie können auf Ihren Kontobereich zugreifen, um Bestellungen einzusehen, Ihr Passwort zu ändern und vieles mehr unter: %3$s', 'woocommerce' ), esc_html( $blogname ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<?php if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $password_generated && $set_password_url ) : ?>
	<?php // If the password has not been set by the user during the sign up process, send them a link to set a new password ?>
	<p><a href="<?php echo esc_attr( $set_password_url ); ?>"><?php printf( esc_html__( 'Klicken Sie hier, um Ihr neues Passwort festzulegen.', 'woocommerce' ) ); ?></a></p>
<?php endif; ?>
<p><?php printf( esc_html__( 'Ihre Kundennummer ist %s, bitte merken Sie sich die Nummer gut, dies dient unserer Kommunikation und bewahren Sie die Email mit der Nummer gut auf.' ), '<strong>' . esc_html( $customer_number ) . '</strong>' ); ?></p>
<p>To verify, please share your customer number via Whatsapp to the following number: 0176 2424 5365</p>
<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
