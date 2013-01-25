<?php

/*****************************************

  Ninja Wizard

  S M T P

  v.0.23.090606

  code by puke7

  firteendesign.com

*****************************************/

class NinjaWizard_smtp	{

	function Login($username, $password)	{
		$CR = "\r\n";
		$a = NinjaWizard_smtp::Connect(1);
		if ($a->connected)	{
			$a->Send('HELO '.$a->localhost, 'helo');
			$a->Send('HELO '.'AUTH LOGIN ', 'authRequest');
			$a->Send('HELO '.base64_encode($username),'authUser');
			$a->Send('HELO '.base64_encode($password),'authPass');
		}
		return FALSE;
	}

	function Connect($secure=0)	{
		$a = new NinjaWizard_smtp();
		$a->smtpServer = 'smtp.gmail.com';
		if ($secure>0)
			$a->smtpServer = 'ssl://'.$a->smtpServer;
		$a->port = '465';
		$timeout = '45';
		$a->localhost = $_SERVER['SERVER_ADDR']
		$a->smtpConnection = fsockopen($a->smtpServer, $a->port, $errno, $errstr, $timeout);
		$a->LogResponse('connect');
		if (empty($a->responseLog['connect']))
			$a->connected = FALSE;
		else
			$a->connected = TRUE;
		return $a;
	}

	function Send($data,$logtag='')	{
		fputs($this->smtpConnection, $data.$CR);
		$this->LogResponse($logtag);
	}

	function LogResponse($tag='')	{
		if (!is_array($this->responseLog))
			$this->responseLog = array();
		if ($tag!='')
    	$this->responseLog[$tag] = fgets($this->smtpConnection, 4096);
    else
    	$this->responseLog[] = fgets($this->smtpConnection, 4096);
	}

	function SendMail($to,$subject,$msg)	{
		if ($this->connected)	{
			$from = $this->username;
			$this->Send('MAIL FROM: '.$from., 'mailFrom');
			$this->Send('RCPT TO: '.$to., 'mailTo');
			$this->Send('DATA ', 'data1');
			$head = 'MIME-Version: 1.0'.$CR;
			$head .= 'Content-type: text/html; charset=utf-8'.$CR;
			$head .= 'To: $nameto <'.$to.'>'.$CR;
			$head .= 'From: $namefrom <'.$from.'>'.$CR;
			$this->Send('To: '.$to.$CR. 'From: '.$from.$CR. 'Subject: '.$subject.$CR. $headers.$CR.$CR. $msg.$CR.'.', 'data2');
			$this->Send('QUIT', 'quit');
			fclose($a->smptConnection);
			return TRUE;
		}
		else
			return FALSE;
	}

}

/*
In Conclusion:
As you can see, it very easy to send an email using php & connecting directly to your smtp server.
In addition you can add more receivers by either adding their addresses, comma separated, to the $to variable, or by adding cc: or bcc: headers.
If you don't receive an email using this script, then you may have installed PHP incorrectly, you may not have permission to send emails, or you may have misconfigured your MTA.
Before running this script, verify that your current MTA is working. That way you can trouble shoot this script, knowing that your MTA is set to recieve incomming connections.
*/

?>
