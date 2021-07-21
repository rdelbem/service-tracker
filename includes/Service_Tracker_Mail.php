<?php
namespace ServiceTracker\includes;

class Service_Tracker_Mail {
	/**
	 * User id always expected to be an int.
	 *
	 * @var int $id
	 */
	public $id;

	/**
	 * User id or email. It Can be either an int or a string.
	 *
	 * @var mixed $to
	 */
	public $to;

	/**
	 * Title of the e-mail.
	 *
	 * @var string $subject
	 */
	public $subject;

	/**
	 * Headers of the e-mail.
	 *
	 * @var array $headers
	 */
	public $headers;

	/**
	 * Body of the email.
	 *
	 * @var string $message
	 */
	public $message;

	/**
	 * Constructor method.
	 * It will receive the parameters and equate then to the class properties.
	 *
	 * @param mixed  $to
	 * @param string $subject
	 * @param string $message
	 */
	public function __construct( $to, $subject, $message ) {
		if ( is_int( $to ) || ctype_digit( $to ) ) {
			$this->id = (int) $to;
			$this->to = $this->getUserEmailById();
		}

		if ( is_string( $to ) && ! ctype_digit( $to ) && filter_var( $to, FILTER_VALIDATE_EMAIL ) ) {
			$this->to = $to;
		}

		$this->subject = $subject;
		$this->headers = array( 'Content-Type: text/html; charset=UTF-8', 'From:' . get_bloginfo() . ' <' . get_option( 'admin_email' ) . '>' );
		$this->message = $message;

		$this->sendEmail();
	}

	/**
	 * If only user id is available,
	 * this function will retrieve the user email by the provided id.
	 *
	 * @return string
	 */
	public function getUserEmailById() {
		$userInfo  = get_userdata( $this->id );
		$userEmail = $userInfo->user_email;
		return $userEmail;
	}

	/**
	 * This calls the wp_mail function.
	 *
	 * @return void
	 */
	public function sendEmail() {
		wp_mail( $this->to, $this->subject, $this->message, $this->headers );
	}
}
