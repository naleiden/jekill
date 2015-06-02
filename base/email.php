<?php

set_include_path(get_include_path() . PATH_SEPARATOR . "..");

require_once("vendor/phpmailer/phpmailer/class.phpmailer.php");

require_once("util.php");

class Email {

	private /* PHPMailer */ $mail;
	private $body;

	function __construct ($address, $subject, $body, $from, $reply_to="", $from_name="") {
		$this->mail = new PHPMailer();
		$this->mail->IsSendmail(); // telling the class to use SendMail transport

		if (!$reply_to)
			$reply_to = $from;

		$this->add_recipient($address);
		$this->set_from($from, $from_name);
		$this->set_reply_to($reply_to, $from_name);
		$this->set_subject($subject);
		$this->set_body($body);

		$template_dir = "templates/email/neuroanatomy-2014/";
		$this->set_template("{$template_dir}header.php", "{$template_dir}footer.php");
	}

	function add_recipient ($address, $recipient_name="") {
		if (!$recipient_name) $recipient_name = $address;
		$this->mail->AddAddress($address, $recipient_name);
	}

	function bcc ($address, $recipient_name="") {
		if (!$recipient_name) $recipient_name = $address;
		$this->mail->AddBCC($address, $recipient_name);
	}

	function cc ($address, $recipient_name="") {
		if (!$recipient_name) $recipient_name = $address;
		$this->mail->AddCC($address, $recipient_name);
	}

	function get_body () {
		return $this->body;
	}

	function get_errors () {
		return $this->mail->ErrorInfo;
	}

	function set_from ($from_address, $from_name="") {
		if (!$from_name)
			$from_name = $from_address;

		$this->mail->SetFrom($from_address, $from_name);
	}

	function set_reply_to ($reply_to, $reply_to_name) {
		if (!$reply_to_name)
			$reply_to_name = $reply_to;

		$this->mail->AddReplyTo($reply_to, $reply_to_name);
	}

	function set_subject ($subject) {
		$this->mail->Subject = $subject;
	}

	// TODO: $template
	function set_body ($body, $template="") {
		// Save this internally so we can change the header.
		$this->body = $body;
		$this->mail->MsgHTML($body);
		// TODO: Replace <br/>, <p> with \n
		$this->mail->AltBody = strip_tags($body);
	}

	function add_bcc ($email) {
		$this->bcc[] = $email;
	}

	function attach_file ($filename) {
		$this->mail->AddAttachment($filename);
	}

	function attach_PDF (PDF $pdf, $filename="") {
		$this->attach_file_data($pdf->get_data(), "application/pdf", $filename);
	}

	function attach_PDF_data ($data, $filename="") {
		$this->attach_file_data($data, "application/pdf", $filename);
	}

	function attach_file_data ($data, $file_type, $filename="") {
		if (!$filename) {
			$file_type_parts = explode("/", $file_type);
			$filename = "attachment.{$file_type_parts[1]}";
		}

		$this->mail->AddStringAttachment($data, $filename, "base64", $file_type);
	}

	function send () {
		return $this->mail->Send();
	}

	function set_template ($template_header, $template_footer) {
        if ($template_header) {
            $this->template_header = include_capture($template_header);
        }
        else $this->template_header = "";

        if ($template_footer) {
            $this->template_footer = include_capture($template_footer);
        }
        else $this->template_footer = "";

		$this->mail->MsgHTML($this->template_header . $this->body . $this->template_footer);
	}

}

?>