<?php

namespace Rdelbem\WPMailerClass;

/**
 * This class provides an easy way to call wp_mail anywhere it may be needed.
 * NOTICE - The function wp_mail is only available after wp_load is rendered.
 * So, it may not work depending on WHEN your application is hooked on.
 */
class WPMailerClass
{

	/**
	 * User id always expected to be an int.
	 *
	 * @var int $id
	 */
	public int $id;

	/**
	 * User id or e-mail. It Can be either an int or a string.
	 *
	 * @var mixed $to
	 */
	public mixed $to;

	/**
	 * Title of the e-mail.
	 *
	 * @var string $subject
	 */
	public string $subject;

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
	public string $message;

	/**
	 * Constructor method.
	 * It will receive the parameters and equate then to the class properties.
	 *
	 * @param string | int  $to
	 * @param string $subject
	 * @param string $message
	 */
	public function __construct(string|int $to, string $subject, string $message)
	{
		if (is_int($to) || ctype_digit($to)) {
			$this->id = (int) $to;
			$this->to = $this->getUserEmailById();
		}

		if (is_string($to) && !ctype_digit($to) && $this->isValidEmail($to)) {
			$this->to = $to;
		}

		$this->subject = $subject;
		$this->headers = ['Content-Type: text/html; charset=UTF-8', 'From:' . get_bloginfo() . ' <' . get_option('admin_email') . '>'];
		$this->message = $message;
	}

	/**
	 * If only user id is available,
	 * this function will retrieve the user email by the provided id.
	 *
	 * @return string
	 */
	public function getUserEmailById()
	{
		$userInfo = get_userdata($this->id);
		$userEmail = $userInfo->user_email;
		return $userEmail;
	}

	/**
	 * This will try to sendo the email but fail silently if unsuccessful
	 * It will show on logs though
	 * 
	 * It is possible to get the error message by using it like:
	 * 
	 * $mail->sendEmail()->get_error_message();
	 * 
	 * This is possible because, in case of an error, this method will
	 * return an instance of the class WP_Error, therefore, all its methods
	 * are available to us
	 *
	 * @return mixed bolean | string containing an error message
	 */
	public function sendEmail()
	{
		$result = $this->wpMailExec();
		if (!$result) {
			return new \WP_Error('WPMailerClass', 'WPMailerClass could not send email');
		}
		return $result;
	}

	public function isValidEmail($email)
	{
		$regex = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
		return preg_match($regex, $email);
	}

	/**
	 * This calls the wp_mail function.
	 *
	 * @return boolean
	 */
	public function wpMailExec()
	{
		return wp_mail($this->to, $this->subject, $this->message, $this->headers);
	}
}