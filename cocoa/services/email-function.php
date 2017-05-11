<?php

require_once("xpm/SMTP.php");

function connectEmail($loginEmail, $password)
{
  $c = fsockopen('tls://smtp.gmail.com', 465, $errno, $errstr, 10);
  if(!$c) 
  {
    echo $errstr;
  }
  else
  // expect response code '220'
  if (!SMTP::recv($c, 220)) 
  {
    print_r($_RESULT);
  }
  else
  // EHLO/HELO
  if (!SMTP::ehlo($c, 'localhost') and !SMTP::helo($c, 'localhost'))
  {
    print_r($_RESULT);
  }
  else
  // AUTH LOGIN/PLAIN
  if (!SMTP::auth($c, $loginEmail, $password, 'login') and !SMTP::auth($c, $loginEmail, $password, 'plain'))
  {
    print_r($_RESULT);
  }
  else
  {
    return $c;
  }
  return null;
}

function disconnectEmail($c)
{
  // QUIT
  SMTP::quit($c);

  // close connection
  @fclose($c);
}

function getAddress($email)
{
  if(substr_count($email, "<") > 0)
  {
    $address = substr($email, strpos($email, "<")+1);
    $address = substr($address, 0, strpos($address, ">"));
  }
  else
  {
    $address = $email;
  }
  return $address;
}

//TODO: handle attachments
function sendEmail($c, $from, $to, $subject, $body, $attachment=null)
{ 
  if($c == null)
    return false;
  $fromAddress = getAddress($from);
  $toAddress = getAddress($to);

  // standard mail message RFC2822
  $message = 'From: '.$from."\r\n".
    'To: '.$to."\r\n".
    'Subject: '.$subject."\r\n".
    'Content-Type: text/html'."\r\n\r\n".
    $body;

  $email_failed = false;
  // MAIL FROM
  if(!SMTP::from($c, $fromAddress))
  {
    print_r($_RESULT);
    $email_failed = true;
  }
  else
  // RCPT TO
  if(!SMTP::to($c, $toAddress))
  {
    print_r($_RESULT);
	print_r($toAddress);
    $email_failed = true;
  }
  else
  // DATA
  if(!SMTP::data($c, $message))
  {
    print_r($_RESULT);
    $email_failed = true;
  }
  
  // RSET, optional if you need to send another mail using this connection '$c'
  if(!SMTP::rset($c)) 
  {
    print_r($_RESULT);
  }

  return !$email_failed;
}

?>