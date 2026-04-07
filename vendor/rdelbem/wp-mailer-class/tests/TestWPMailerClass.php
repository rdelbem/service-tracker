<?php

use Mockery;
use WP_Mock;
use Rdelbem\WPMailerClass\WPMailerClass;

/**
 * 
 */
class TestWPMailerClass extends \WP_Mock\Tools\TestCase {
    
    //required by default - do not remove
    public function setUp() : void  {
        \WP_Mock::setUp();
    }

    //required by default - do not remove
    public function tearDown() : void  {
        \WP_Mock::tearDown();
    }

    public $validEmail = 'wpmailerclass@wpmailer.class';
    public $invalidEmail = 'wpmailerclass';
    public $subject = 'subject for an email';
    public $message = 'email message';
    public $userId = 1;
    public $errorMsg = 'WPMailerClass could not send email';

    /**
     * checks if the email validator is working as it should
     *
     * @return void
     */
    public function testIsValidEmail()
    {
        WP_Mock::userFunction('get_bloginfo', [
            'return'=> 'wpmaileradminemail' // returns the site name/title
        ]);

        WP_Mock::userFunction('get_option', [
            'return'=> 'wpmaileradminemail@wpmailer.com' // returns the admin email
        ]);

        $WPMailerClass = new WPMailerClass($this->validEmail, $this->subject, $this->message);

        $result = (bool) $WPMailerClass->isValidEmail($this->validEmail);
        $this->assertTrue($result);

        $result = (bool) $WPMailerClass->isValidEmail($this->invalidEmail);
        $this->assertFalse($result);
    }

    /**
     * Tests if it is returning the user email by id correctly
     *
     * @return void
     */
    public function testGetUserEmailById()
    {
        WP_Mock::userFunction('get_bloginfo', [
            'return'=> 'wpmaileradminemail' // returns the site name/title
        ]);

        WP_Mock::userFunction('get_option', [
            'return'=> 'wpmaileradminemail@wpmailer.com' // returns the admin email
        ]);

        $userInfo = new \stdClass();
        $userInfo->user_email = $this->validEmail;

        WP_Mock::userFunction('get_userdata', [
            'args' => $this->userId,
            'return'=> $userInfo // object containing our user_email
        ]);
        
        $WPMailerClass = new WPMailerClass($this->userId, $this->subject, $this->message);

        $result = $WPMailerClass->getUserEmailById();
        $this->assertEquals($this->validEmail, $result);
    }

    /**
     * This tests the actual method used to send the emails
     *
     * @return void
     */
    public function testWpMailExec()
    {
        WP_Mock::userFunction('get_bloginfo', [
            'return'=> 'wpmaileradminemail' // returns the site name/title
        ]);

        WP_Mock::userFunction('get_option', [
            'return'=> 'wpmaileradminemail@wpmailer.com' // returns the admin email
        ]);

        $WPMailerClass = new WPMailerClass($this->validEmail, $this->subject, $this->message);

        WP_Mock::userFunction('wp_mail', [
            'args' => [
                $this->validEmail,
                $this->subject,
                $this->message,
                ['Content-Type: text/html; charset=UTF-8', 'From:' . get_bloginfo() . ' <' . get_option( 'admin_email' ) . '>' ]

            ],
            'return'=> true
        ]);

        $result = (bool) $WPMailerClass->wpMailExec();
        $this->assertTrue($result);
    }

    /**
     * tests the actual sending of a msg
     *
     * @return void
     */
    public function testSendEmail()
    {
        WP_Mock::userFunction('get_bloginfo', [
            'return'=> 'wpmaileradminemail' // returns the site name/title
        ]);

        WP_Mock::userFunction('get_option', [
            'return'=> 'wpmaileradminemail@wpmailer.com' // returns the admin email
        ]);

        $WP_Error = Mockery::mock('WP_Error');

        $WPMailerClass = new WPMailerClass($this->validEmail, $this->subject, $this->message);
       
        WP_Mock::userFunction('wp_mail', [
            'args' => [
                $this->validEmail,
                $this->subject,
                $this->message,
                ['Content-Type: text/html; charset=UTF-8', 'From:' . get_bloginfo() . ' <' . get_option( 'admin_email' ) . '>' ]

            ],
            'return'=> true
        ]);

        $WPMailerClass->wpMailExec();

        $result = $WPMailerClass->sendEmail();

        $this->assertTrue($result); // return true, email was sent
    }

    /**
     * Check if it and WP_Error class is cas when wp_mail is not sent
     *
     * @return void
     */
    public function testSendEmailWithError()
    {
        WP_Mock::userFunction('get_bloginfo', [
            'return'=> 'wpmaileradminemail' // returns the site name/title
        ]);

        WP_Mock::userFunction('get_option', [
            'return'=> 'wpmaileradminemail@wpmailer.com' // returns the admin email
        ]);

        $WP_Error = Mockery::mock('WP_Error');
        $WP_Error->shouldReceive('get_error_message')->andReturn($this->errorMsg);

        $WPMailerClass = new WPMailerClass($this->validEmail, $this->subject, $this->message);
       
        WP_Mock::userFunction('wp_mail', [
            'args' => [
                $this->validEmail,
                $this->subject,
                $this->message,
                ['Content-Type: text/html; charset=UTF-8', 'From:' . get_bloginfo() . ' <' . get_option( 'admin_email' ) . '>' ]

            ],
            'return'=> $WP_Error
        ]);

        $WPMailerClass->wpMailExec();
        $result = $WPMailerClass->sendEmail();

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals($this->errorMsg, $result->get_error_message());
        $this->assertSame($WP_Error, $result);
    }
}