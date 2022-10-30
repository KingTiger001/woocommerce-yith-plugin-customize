<?php
/**
 * Implements Update Points email class
 *
 * @class   YITH_WC_Points_Rewards
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_YWPAR_Update_Points' ) ) {

	/**
	 * YITH_YWPAR_Expiration
	 *
	 * @since 1.0.0
	 */
	class YITH_YWPAR_Update_Points extends WC_Email {

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->id          = 'ywpar_update_points';
			$this->title       = esc_html__( 'Update Points', 'yith-woocommerce-points-and-rewards' );
			$this->description = esc_html__( 'This email is sent when the number of user-points change', 'yith-woocommerce-points-and-rewards' );

			$this->heading = esc_html__( 'Your Points in ', 'yith-woocommerce-points-and-rewards' ) . get_option( 'blogname', '' );
			$this->subject = esc_html__( '[Points updated]', 'yith-woocommerce-points-and-rewards' );

			$this->template_base  = YITH_YWPAR_TEMPLATE_PATH . '/';
			$this->template_html  = 'emails/update-points.php';
			$this->template_plain = 'emails/plain/update-points.php';

			// Triggers for this email.
			add_action( 'update_points_mail_notification', array( $this, 'trigger' ), 15, 1 );

			// Call parent constructor.
			parent::__construct();

			$this->customer_email = true;

			// Other settings.
			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

			$this->enable_cc = $this->get_option( 'enable_cc' );
			$this->enable_cc = 'yes' === $this->enable_cc;
		}

		/**
		 * Method triggered to send email
		 *
		 * @param int $args Arguments.
		 *
		 * @return void
		 * @since  1.0
		 */
		public function trigger( $args ) {
			$this->email_content = $args['email_content'];
			$this->recipient     = $args['user_email'];
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}



		/**
		 * Get HTML content for the mail
		 *
		 * @return string HTML content of the mail
		 * @since  1.0
		 */
		public function get_content_html() {
			ob_start();

			wc_get_template(
				$this->template_html,
				array(
					'email_content' => $this->email_content,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				),
				false,
				$this->template_base
			);
			return ob_get_clean();
		}

		/**
		 * Get plain text content of the mail
		 *
		 * @return string Plain text content of the mail
		 * @since  1.0
		 */
		public function get_content_plain() {
			ob_start();
			wc_get_template(
				$this->template_plain,
				array(
					'email_content' => $this->email_content,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				),
				false,
				$this->template_base
			);
			return ob_get_clean();
		}

		/**
		 * Return the attachments
		 *
		 * @return array
		 */
		public function get_attachments() {
			$attachments = array();
			if ( ! empty( $file ) && file_exists( $file['file'] ) ) {
				$attachments[] = $file['file'];
			}
			return $attachments;
		}

		/**
		 * Get from name for email.
		 *
		 * @param string $from_name From name.
		 *
		 * @return string
		 */
		public function get_from_name( $from_name = '' ) {
			$email_from_name = ( isset( $this->email_from_name ) && '' !== $this->email_from_name ) ? $this->email_from_name : get_option( 'woocommerce_email_from_name' );
			return wp_specialchars_decode( esc_html( $email_from_name ), ENT_QUOTES );
		}

		/**
		 * Get from email address.
		 *
		 * @param string $from_email From email.
		 *
		 * @return string
		 */
		public function get_from_address( $from_email = '' ) {
			$email_from_email = ( isset( $this->email_from_email ) && '' !== $this->email_from_email ) ? $this->email_from_email : get_option( 'woocommerce_email_from_address' );
			return sanitize_email( $email_from_email );
		}

		/**
		 * Init form fields to display in WC admin pages
		 *
		 * @return void
		 * @since  1.0
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'           => array(
					'title'   => esc_html__( 'Enable/Disable', 'yith-woocommerce-points-and-rewards' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable this email notification', 'yith-woocommerce-points-and-rewards' ),
					'default' => 'yes',
				),
				'email_from_name'   => array(
					'title'       => esc_html__( 'From (Name)', 'yith-woocommerce-points-and-rewards' ),
					'type'        => 'text',
					'description' => '',
					'placeholder' => '',
					'default'     => get_option( 'woocommerce_email_from_name' ),
				),
				'email_from_email'  => array(
					'title'       => esc_html__( 'From (Email Address)', 'yith-woocommerce-points-and-rewards' ),
					'type'        => 'text',
					'description' => '',
					'placeholder' => '',
					'default'     => get_option( 'woocommerce_email_from_address' ),
				),
				'subject'           => array(
					'title'       => esc_html__( 'Subject', 'yith-woocommerce-points-and-rewards' ),
					'type'        => 'text',
					// translators: Placeholder: subject.
					'description' => sprintf( _x( 'This field lets you edit the email subject line. Leave it blank to use the default subject text: <code>%s</code>.', 'Placeholder: subject', 'yith-woocommerce-points-and-rewards' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
				),
				'recipient'         => array(
					'title'       => __( 'Recipient(s)', 'yith-woocommerce-points-and-rewards' ),
					'type'        => 'text',
					// translators: Placeholder: admin email.
					'description' => sprintf( _x( 'Enter recipients (separated by commas) for this email. Defaults to <code>%s</code>', 'Placeholder: admin email', 'yith-woocommerce-points-and-rewards' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => '',
				),
				'heading'           => array(
					'title'       => __( 'Email Heading', 'yith-woocommerce-points-and-rewards' ),
					'type'        => 'text',
					// translators: Placeholder: email heading.
					'description' => sprintf( _x( 'This field lets you change the main heading in email notification. Leave it blank to use the default heading type: <code>%s</code>.', 'Placeholder: email heading', 'yith-woocommerce-points-and-rewards' ), $this->heading ),
					'placeholder' => '',
					'default'     => '',
				),

				'email-description' => array(
					'title'       => __( 'Email Description', 'yith-woocommerce-points-and-rewards' ),
					'type'        => 'textarea',
					'placeholder' => '',
					'default'     => '',
				),

				'email_type'        => array(
					'title'       => __( 'Email type', 'yith-woocommerce-points-and-rewards' ),
					'type'        => 'select',
					'description' => __( 'Choose the format of the email that has to be sent.', 'yith-woocommerce-points-and-rewards' ),
					'default'     => 'html',
					'class'       => 'email_type',
					'options'     => array(
						'plain'     => __( 'Plain text', 'yith-woocommerce-points-and-rewards' ),
						'html'      => __( 'HTML', 'yith-woocommerce-points-and-rewards' ),
						'multipart' => __( 'Multipart', 'yith-woocommerce-points-and-rewards' ),
					),
				),
			);
		}
	}
}


// returns instance of the mail on file include.
return new YITH_YWPAR_Update_Points();
