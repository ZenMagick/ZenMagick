<?php /* .tmp\flat\AbstractSmtpTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\AbstractSmtpTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport.php';
//@require 'Swift/Transport/IoBuffer.php';
//@require 'Swift/Transport/CommandSentException.php';
//@require 'Swift/TransportException.php';
//@require 'Swift/Mime/Message.php';
//@require 'Swift/Events/EventDispatcher.php';
//@require 'Swift/Events/EventListener.php';

/**
 * Sends Messages over SMTP.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
abstract class Swift_Transport_AbstractSmtpTransport
  implements Swift_Transport
{
  
  /** Input-Output buffer for sending/receiving SMTP commands and responses */
  protected $_buffer;
  
  /** Connection status */
  protected $_started = false;
  
  /** The domain name to use in HELO command */
  protected $_domain = '[127.0.0.1]';
  
  /** The event dispatching layer */
  protected $_eventDispatcher;
  
  /** Return an array of params for the Buffer */
  abstract protected function _getBufferParams();
  
  /**
   * Creates a new EsmtpTransport using the given I/O buffer.
   * 
   * @param Swift_Transport_IoBuffer $buf
   * @param Swift_Events_EventDispatcher $dispatcher
   */
  public function __construct(Swift_Transport_IoBuffer $buf,
    Swift_Events_EventDispatcher $dispatcher)
  {
    $this->_eventDispatcher = $dispatcher;
    $this->_buffer = $buf;
    $this->_lookupHostname();
  }
  
  /**
   * Set the name of the local domain which Swift will identify itself as.
   * This should be a fully-qualified domain name and should be truly the domain
   * you're using.  If your server doesn't have a domain name, use the IP in square
   * brackets (i.e. [127.0.0.1]).
   * 
   * @param string $domain
   */
  public function setLocalDomain($domain)
  {
    $this->_domain = $domain;
    return $this;
  }
  
  /**
   * Get the name of the domain Swift will identify as.
   * 
   * @return string
   */
  public function getLocalDomain()
  {
    return $this->_domain;
  }
  
  /**
   * Start the SMTP connection.
   */
  public function start()
  {
    if (!$this->_started)
    {
      if ($evt = $this->_eventDispatcher->createTransportChangeEvent($this))
      {
        $this->_eventDispatcher->dispatchEvent($evt, 'beforeTransportStarted');
        if ($evt->bubbleCancelled())
        {
          return;
        }
      }
      
      try
      {
        $this->_buffer->initialize($this->_getBufferParams());
      }
      catch (Swift_TransportException $e)
      {
        $this->_throwException($e);
      }
      $this->_readGreeting();
      $this->_doHeloCommand();
      
      if ($evt)
      {
        $this->_eventDispatcher->dispatchEvent($evt, 'transportStarted');
      }
      
      $this->_started = true;
    }
  }
  
  /**
   * Test if an SMTP connection has been established.
   * 
   * @return boolean
   */
  public function isStarted()
  {
    return $this->_started;
  }
  
  /**
   * Send the given Message.
   * 
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $sent = 0;
    $failedRecipients = (array) $failedRecipients;
    
    if (!$reversePath = $this->_getReversePath($message))
    {
      throw new Swift_TransportException(
        'Cannot send message without a sender address'
        );
    }
    
    if ($evt = $this->_eventDispatcher->createSendEvent($this, $message))
    {
      $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
      if ($evt->bubbleCancelled())
      {
        return 0;
      }
    }
    
    $to = (array) $message->getTo();
    $cc = (array) $message->getCc();
    $bcc = (array) $message->getBcc();
    
    $message->setBcc(array());
    
    try
    {
      $sent += $this->_sendTo($message, $reversePath, $to, $failedRecipients);
      $sent += $this->_sendCc($message, $reversePath, $cc, $failedRecipients);
      $sent += $this->_sendBcc($message, $reversePath, $bcc, $failedRecipients);
    }
    catch (Exception $e)
    {
      $message->setBcc($bcc);
      throw $e;
    }
    
    $message->setBcc($bcc);
    
    if ($evt)
    {
      if ($sent == count($to) + count($cc) + count($bcc))
      {
        $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
      }
      elseif ($sent > 0)
      {
        $evt->setResult(Swift_Events_SendEvent::RESULT_TENTATIVE);
      }
      else
      {
        $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
      }
      $evt->setFailedRecipients($failedRecipients);
      $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
    }
    
    $message->generateId(); //Make sure a new Message ID is used
    
    return $sent;
  }
  
  /**
   * Stop the SMTP connection.
   */
  public function stop()
  {
    if ($this->_started)
    {
      if ($evt = $this->_eventDispatcher->createTransportChangeEvent($this))
      {
        $this->_eventDispatcher->dispatchEvent($evt, 'beforeTransportStopped');
        if ($evt->bubbleCancelled())
        {
          return;
        }
      }
      
      try
      {
        $this->executeCommand("QUIT\r\n", array(221));
      }
      catch (Swift_TransportException $e) {}
      
      try
      {
        $this->_buffer->terminate();
      
        if ($evt)
        {
          $this->_eventDispatcher->dispatchEvent($evt, 'transportStopped');
        }
      }
      catch (Swift_TransportException $e)
      {
        $this->_throwException($e);
      }
    }
    $this->_started = false;
  }
  
  /**
   * Register a plugin.
   * 
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->_eventDispatcher->bindEventListener($plugin);
  }
  
  /**
   * Reset the current mail transaction.
   */
  public function reset()
  {
    $this->executeCommand("RSET\r\n", array(250));
  }
  
  /**
   * Get the IoBuffer where read/writes are occurring.
   * 
   * @return Swift_Transport_IoBuffer
   */
  public function getBuffer()
  {
    return $this->_buffer;
  }
  
  /**
   * Run a command against the buffer, expecting the given response codes.
   * 
   * If no response codes are given, the response will not be validated.
   * If codes are given, an exception will be thrown on an invalid response.
   * 
   * @param string $command
   * @param int[] $codes
   * @param string[] &$failures
   * @return string
   */
  public function executeCommand($command, $codes = array(), &$failures = null)
  {
    $failures = (array) $failures;
    $seq = $this->_buffer->write($command);
    $response = $this->_getFullResponse($seq);
    if ($evt = $this->_eventDispatcher->createCommandEvent($this, $command, $codes))
    {
      $this->_eventDispatcher->dispatchEvent($evt, 'commandSent');
    }
    $this->_assertResponseCode($response, $codes);
    return $response;
  }
  
  // -- Protected methods
  
  /** Read the opening SMTP greeting */
  protected function _readGreeting()
  {
    $this->_assertResponseCode($this->_getFullResponse(0), array(220));
  }
  
  /** Send the HELO welcome */
  protected function _doHeloCommand()
  {
    $this->executeCommand(
      sprintf("HELO %s\r\n", $this->_domain), array(250)
      );
  }
  
  /** Send the MAIL FROM command */
  protected function _doMailFromCommand($address)
  {
    $this->executeCommand(
      sprintf("MAIL FROM: <%s>\r\n", $address), array(250)
      );
  }
  
  /** Send the RCPT TO command */
  protected function _doRcptToCommand($address)
  {
    $this->executeCommand(
      sprintf("RCPT TO: <%s>\r\n", $address), array(250, 251, 252)
      );
  }
  
  /** Send the DATA command */
  protected function _doDataCommand()
  {
    $this->executeCommand("DATA\r\n", array(354));
  }
  
  /** Stream the contents of the message over the buffer */
  protected function _streamMessage(Swift_Mime_Message $message)
  {
    $this->_buffer->setWriteTranslations(array("\r\n." => "\r\n.."));
    try
    {
      $message->toByteStream($this->_buffer);
      $this->_buffer->flushBuffers();
    }
    catch (Swift_TransportException $e)
    {
      $this->_throwException($e);
    }
    $this->_buffer->setWriteTranslations(array());
    $this->executeCommand("\r\n.\r\n", array(250));
  }
  
  /** Determine the best-use reverse path for this message */
  protected function _getReversePath(Swift_Mime_Message $message)
  {
    $return = $message->getReturnPath();
    $sender = $message->getSender();
    $from = $message->getFrom();
    $path = null;
    if (!empty($return))
    {
      $path = $return;
    }
    elseif (!empty($sender))
    {
      // Don't use array_keys
      reset($sender); // Reset Pointer to first pos
      $path = key($sender); // Get key
    }
    elseif (!empty($from))
    {
      reset($from); // Reset Pointer to first pos
      $path = key($from); // Get key
    }
    return $path;
  }
  
  /** Throw a TransportException, first sending it to any listeners */
  protected function _throwException(Swift_TransportException $e)
  {
    if ($evt = $this->_eventDispatcher->createTransportExceptionEvent($this, $e))
    {
      $this->_eventDispatcher->dispatchEvent($evt, 'exceptionThrown');
      if (!$evt->bubbleCancelled())
      {
        throw $e;
      }
    }
    else
    {
      throw $e;
    }
  }
  
  /** Throws an Exception if a response code is incorrect */
  protected function _assertResponseCode($response, $wanted)
  {
    list($code, $separator, $text) = sscanf($response, '%3d%[ -]%s');
    $valid = (empty($wanted) || in_array($code, $wanted));
    
    if ($evt = $this->_eventDispatcher->createResponseEvent($this, $response,
      $valid))
    {
      $this->_eventDispatcher->dispatchEvent($evt, 'responseReceived');
    }
    
    if (!$valid)
    {
      $this->_throwException(
        new Swift_TransportException(
          'Expected response code ' . implode('/', $wanted) . ' but got code ' .
          '"' . $code . '", with message "' . $response . '"'
          )
        );
    }
  }
  
  /** Get an entire multi-line response using its sequence number */
  protected function _getFullResponse($seq)
  {
    $response = '';
    try
    {
      do
      {
        $line = $this->_buffer->readLine($seq);
        $response .= $line;
      }
      while (null !== $line && false !== $line && ' ' != $line{3});
    }
    catch (Swift_TransportException $e)
    {
      $this->_throwException($e);
    }
    return $response;
  }
  
  // -- Private methods
  
  /** Send an email to the given recipients from the given reverse path */
  private function _doMailTransaction($message, $reversePath,
    array $recipients, array &$failedRecipients)
  {
    $sent = 0;
    $this->_doMailFromCommand($reversePath);
    foreach ($recipients as $forwardPath)
    {
      try
      {
        $this->_doRcptToCommand($forwardPath);
        $sent++;
      }
      catch (Swift_TransportException $e)
      {
        $failedRecipients[] = $forwardPath;
      }
    }
    
    if ($sent != 0)
    {
      $this->_doDataCommand();
      $this->_streamMessage($message);
    }
    else
    {
      $this->reset();
    }
    
    return $sent;
  }
  
  /** Send a message to the given To: recipients */
  private function _sendTo(Swift_Mime_Message $message, $reversePath,
    array $to, array &$failedRecipients)
  {
    if (empty($to))
    {
      return 0;
    }
    return $this->_doMailTransaction($message, $reversePath, array_keys($to),
      $failedRecipients);
  }
  
  /** Send a message to the given Cc: recipients */
  private function _sendCc(Swift_Mime_Message $message, $reversePath,
    array $cc, array &$failedRecipients)
  {
    if (empty($cc))
    {
      return 0;
    }
    return $this->_doMailTransaction($message, $reversePath, array_keys($cc),
      $failedRecipients);
  }
  
  /** Send a message to all Bcc: recipients */
  private function _sendBcc(Swift_Mime_Message $message, $reversePath,
    array $bcc, array &$failedRecipients)
  {
    $sent = 0;
    foreach ($bcc as $forwardPath => $name)
    {
      $message->setBcc(array($forwardPath => $name));
      $sent += $this->_doMailTransaction(
        $message, $reversePath, array($forwardPath), $failedRecipients
        );
    }
    return $sent;
  }
  
  /** Try to determine the hostname of the server this is run on */
  private function _lookupHostname()
  {
    if (!empty($_SERVER['SERVER_NAME'])
      && $this->_isFqdn($_SERVER['SERVER_NAME']))
    {
      $this->_domain = $_SERVER['SERVER_NAME'];
    }
    elseif (!empty($_SERVER['SERVER_ADDR']))
    {
      $this->_domain = sprintf('[%s]', $_SERVER['SERVER_ADDR']);
    }
  }
  
  /** Determine is the $hostname is a fully-qualified name */
  private function _isFqdn($hostname)
  {
    //We could do a really thorough check, but there's really no point
    if (false !== $dotPos = strpos($hostname, '.'))
    {
      return ($dotPos > 0) && ($dotPos != strlen($hostname) - 1);
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Destructor.
   */
  public function __destruct()
  {
    $this->stop();
  }
  
}
 /* .tmp\flat\Authenticator.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Authenticator.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/SmtpAgent.php';

/**
 * An Authentication mechanism.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport_Esmtp_Authenticator
{
  
  /**
   * Get the name of the AUTH mechanism this Authenticator handles.
   * @return string
   */
  public function getAuthKeyword();
  
  /**
   * Try to authenticate the user with $username and $password.
   * @param Swift_Transport_SmtpAgent $agent
   * @param string $username
   * @param string $password
   * @return boolean
   */
  public function authenticate(Swift_Transport_SmtpAgent $agent,
    $username, $password);
  
}
 /* .tmp\flat\AuthHandler.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\AuthHandler.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/TransportException.php';
//@require 'Swift/Transport/EsmtpHandler.php';
//@require 'Swift/Transport/SmtpAgent.php';

/**
 * An ESMTP handler for AUTH support.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Esmtp_AuthHandler implements Swift_Transport_EsmtpHandler
{
  
  /**
   * Authenticators available to process the request.
   * @var Swift_Transport_Esmtp_Authenticator[]
   * @access private
   */
  private $_authenticators = array();
  
  /**
   * The username for authentication.
   * @var string
   * @access private
   */
  private $_username;
  
  /**
   * The password for authentication.
   * @var string
   * @access private
   */
  private $_password;
  
  /**
   * The auth mode for authentication.
   * @var string
   * @access private
   */
  private $_auth_mode;
  
  /**
   * The ESMTP AUTH parameters available.
   * @var string[]
   * @access private
   */
  private $_esmtpParams = array();
  
  /**
   * Create a new AuthHandler with $authenticators for support.
   * @param Swift_Transport_Esmtp_Authenticator[] $authenticators
   */
  public function __construct(array $authenticators)
  {
    $this->setAuthenticators($authenticators);
  }
  
  /**
   * Set the Authenticators which can process a login request.
   * @param Swift_Transport_Esmtp_Authenticator[] $authenticators
   */
  public function setAuthenticators(array $authenticators)
  {
    $this->_authenticators = $authenticators;
  }
  
  /**
   * Get the Authenticators which can process a login request.
   * @return Swift_Transport_Esmtp_Authenticator[]
   */
  public function getAuthenticators()
  {
    return $this->_authenticators;
  }
  
  /**
   * Set the username to authenticate with.
   * @param string $username
   */
  public function setUsername($username)
  {
    $this->_username = $username;
  }
  
  /**
   * Get the username to authenticate with.
   * @return string
   */
  public function getUsername()
  {
    return $this->_username;
  }
  
  /**
   * Set the password to authenticate with.
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->_password = $password;
  }
  
  /**
   * Get the password to authenticate with.
   * @return string
   */
  public function getPassword()
  {
    return $this->_password;
  }
  
  /**
   * Set the auth mode to use to authenticate.
   * @param string $mode
   */
  public function setAuthMode($mode)
  {
    $this->_auth_mode = $mode;
  }
  
  /**
   * Get the auth mode to use to authenticate.
   * @return string
   */
  public function getAuthMode()
  {
    return $this->_auth_mode;
  }
  
  /**
   * Get the name of the ESMTP extension this handles.
   * @return boolean
   */
  public function getHandledKeyword()
  {
    return 'AUTH';
  }
  
  /**
   * Set the parameters which the EHLO greeting indicated.
   * @param string[] $parameters
   */
  public function setKeywordParams(array $parameters)
  {
    $this->_esmtpParams = $parameters;
  }
  
  /**
   * Runs immediately after a EHLO has been issued.
   * @param Swift_Transport_SmtpAgent $agent to read/write
   */
  public function afterEhlo(Swift_Transport_SmtpAgent $agent)
  {
    if ($this->_username)
    {
      $count = 0;
      foreach ($this->_getAuthenticatorsForAgent() as $authenticator)
      {
        if (in_array(strtolower($authenticator->getAuthKeyword()),
          array_map('strtolower', $this->_esmtpParams)))
        {
          $count++;
          if ($authenticator->authenticate($agent, $this->_username, $this->_password))
          {
            return;
          }
        }
      }
      throw new Swift_TransportException(
        'Failed to authenticate on SMTP server with username "' .
        $this->_username . '" using ' . $count . ' possible authenticators'
        );
    }
  }
  
  /**
   * Not used.
   */
  public function getMailParams()
  {
    return array();
  }
  
  /**
   * Not used.
   */
  public function getRcptParams()
  {
    return array();
  }
  
  /**
   * Not used.
   */
  public function onCommand(Swift_Transport_SmtpAgent $agent,
    $command, $codes = array(), &$failedRecipients = null, &$stop = false)
  {
  }
    
  /**
   * Returns +1, -1 or 0 according to the rules for usort().
   * This method is called to ensure extensions can be execute in an appropriate order.
   * @param string $esmtpKeyword to compare with
   * @return int
   */
  public function getPriorityOver($esmtpKeyword)
  {
    return 0;
  }
  
  /**
   * Returns an array of method names which are exposed to the Esmtp class.
   * @return string[]
   */
  public function exposeMixinMethods()
  {
    return array('setUsername', 'getUsername', 'setPassword', 'getPassword', 'setAuthMode', 'getAuthMode');
  }
  
  /**
   * Not used.
   */
  public function resetState()
  {
  }
  
  // -- Protected methods
  
  /**
   * Returns the authenticator list for the given agent.
   * @param  Swift_Transport_SmtpAgent $agent
   * @return array
   * @access protected
   */
  protected function _getAuthenticatorsForAgent()
  {
    if (!$mode = strtolower($this->_auth_mode))
    {
      return $this->_authenticators;
    }

    foreach ($this->_authenticators as $authenticator)
    {
      if (strtolower($authenticator->getAuthKeyword()) == $mode)
      {
        return array($authenticator);
      }
    }

    throw new Swift_TransportException('Auth mode '.$mode.' is invalid');
  }
}
 /* .tmp\flat\CharacterReader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\CharacterReader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Analyzes characters for a specific character set.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 * @author Xavier De Cock <xdecock@gmail.com>
 */
interface Swift_CharacterReader
{
  const MAP_TYPE_INVALID = 0x01;
  const MAP_TYPE_FIXED_LEN = 0x02;
  const MAP_TYPE_POSITIONS = 0x03;
  
  /**
   * Returns the complete charactermap
   *
   * @param string $string
   * @param int $startOffset
   * @param array $currentMap
   * @param mixed $ignoredChars
   * @return int
   */
  public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars);
  
  /**
   * Returns mapType
   * @int mapType
   */
  public function getMapType();
  
  /**
   * Returns an integer which specifies how many more bytes to read.
   * A positive integer indicates the number of more bytes to fetch before invoking
   * this method again.
   * A value of zero means this is already a valid character.
   * A value of -1 means this cannot possibly be a valid character.
   * @param int[] $bytes
   * @return int
   */
  public function validateByteSequence($bytes, $size);

  /**
   * Returns the number of bytes which should be read to start each character.
   * For fixed width character sets this should be the number of
   * octets-per-character. For multibyte character sets this will probably be 1.
   * @return int
   */
  public function getInitialByteSize();

}
 /* .tmp\flat\CharacterReaderFactory.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\CharacterReaderFactory.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/CharacterReader.php';

/**
 * A factory for creating CharacterReaders.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
interface Swift_CharacterReaderFactory
{

  /**
   * Returns a CharacterReader suitable for the charset applied.
   * @param string $charset
   * @return Swift_CharacterReader
   */
  public function getReaderFor($charset);
  
}
 /* .tmp\flat\CharacterStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\CharacterStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//require_once dirname(__FILE__) . '/OutputByteStream.php';
//require_once dirname(__FILE__) . '/CharacterReaderFactory.php';


/**
 * An abstract means of reading and writing data in terms of characters as opposed
 * to bytes.
 * Classes implementing this interface may use a subsystem which requires less
 * memory than working with large strings of data.
 * @package Swift
 * @subpackage CharacterStream
 * @author Chris Corbyn
 */
interface Swift_CharacterStream
{

  /**
   * Set the character set used in this CharacterStream.
   * @param string $charset
   */
  public function setCharacterSet($charset);
  
  /**
   * Set the CharacterReaderFactory for multi charset support.
   * @param Swift_CharacterReaderFactory $factory
   */
  public function setCharacterReaderFactory(
    Swift_CharacterReaderFactory $factory);
  
  /**
   * Overwrite this character stream using the byte sequence in the byte stream.
   * @param Swift_OutputByteStream $os output stream to read from
   */
  public function importByteStream(Swift_OutputByteStream $os);
  
  /**
   * Import a string a bytes into this CharacterStream, overwriting any existing
   * data in the stream.
   * @param string $string
   */
  public function importString($string);
  
  /**
   * Read $length characters from the stream and move the internal pointer
   * $length further into the stream.
   * @param int $length
   * @return string
   */
  public function read($length);
  
  /**
   * Read $length characters from the stream and return a 1-dimensional array
   * containing there octet values.
   * @param int $length
   * @return int[]
   */
  public function readBytes($length);
  
  /**
   * Write $chars to the end of the stream.
   * @param string $chars
   */
  public function write($chars);
  
  /**
   * Move the internal pointer to $charOffset in the stream.
   * @param int $charOffset
   */
  public function setPointer($charOffset);
  
  /**
   * Empty the stream and reset the internal pointer.
   */
  public function flushContents();
  
}
 /* .tmp\flat\CharsetObserver.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\CharsetObserver.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Observes changes in an Mime entity's character set.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_CharsetObserver
{
  
  /**
   * Notify this observer that the entity's charset has changed.
   * @param string $charset
   */
  public function charsetChanged($charset);
  
}
 /* .tmp\flat\CramMd5Authenticator.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\CramMd5Authenticator.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/Esmtp/Authenticator.php';
//@require 'Swift/Transport/SmtpAgent.php';
//@require 'Swift/TransportException.php';

/**
 * Handles CRAM-MD5 authentication.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Esmtp_Auth_CramMd5Authenticator
  implements Swift_Transport_Esmtp_Authenticator
{
  
  /**
   * Get the name of the AUTH mechanism this Authenticator handles.
   * @return string
   */
  public function getAuthKeyword()
  {
    return 'CRAM-MD5';
  }
  
  /**
   * Try to authenticate the user with $username and $password.
   * @param Swift_Transport_SmtpAgent $agent
   * @param string $username
   * @param string $password
   * @return boolean
   */
  public function authenticate(Swift_Transport_SmtpAgent $agent,
    $username, $password)
  {
    try
    {
      $challenge = $agent->executeCommand("AUTH CRAM-MD5\r\n", array(334));
      $challenge = base64_decode(substr($challenge, 4));
      $message = base64_encode(
        $username . ' ' . $this->_getResponse($password, $challenge)
        );
      $agent->executeCommand(sprintf("%s\r\n", $message), array(235));
      return true;
    }
    catch (Swift_TransportException $e)
    {
      $agent->executeCommand("RSET\r\n", array(250));
      return false;
    }
  }
  
  /**
   * Generate a CRAM-MD5 response from a server challenge.
   * @param string $secret
   * @param string $challenge
   * @return string
   */
  private function _getResponse($secret, $challenge)
  {
    if (strlen($secret) > 64)
    {
      $secret = pack('H32', md5($secret));
    }
    
    if (strlen($secret) < 64)
    {
      $secret = str_pad($secret, 64, chr(0));
    }
    
    $k_ipad = substr($secret, 0, 64) ^ str_repeat(chr(0x36), 64);
    $k_opad = substr($secret, 0, 64) ^ str_repeat(chr(0x5C), 64);

    $inner  = pack('H32', md5($k_ipad . $challenge));
    $digest = md5($k_opad . $inner);

    return $digest;
  }
  
}
 /* .tmp\flat\DependencyContainer.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\DependencyContainer.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/DependencyException.php';

/**
 * Dependency Injection container.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_DependencyContainer
{
  
  /** Constant for literal value types */
  const TYPE_VALUE = 0x0001;
  
  /** Constant for new instance types */
  const TYPE_INSTANCE = 0x0010;
  
  /** Constant for shared instance types */
  const TYPE_SHARED = 0x0100;
  
  /** Constant for aliases */
  const TYPE_ALIAS = 0x1000;
  
  /** Singleton instance */
  private static $_instance = null;
  
  /** The data container */
  private $_store = array();
  
  /** The current endpoint in the data container */
  private $_endPoint;
  
  /**
   * Constructor should not be used.
   * Use {@link getInstance()} instead.
   */
  public function __construct() { }
  
  /**
   * Returns a singleton of the DependencyContainer.
   * @return Swift_DependencyContainer
   */
  public static function getInstance()
  {
    if (!isset(self::$_instance))
    {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
  
  /**
   * List the names of all items stored in the Container.
   * @return array
   */
  public function listItems()
  {
    return array_keys($this->_store);
  }
  
  /**
   * Test if an item is registered in this container with the given name.
   * @param string $itemName
   * @return boolean
   * @see register()
   */
  public function has($itemName)
  {
    return array_key_exists($itemName, $this->_store)
      && isset($this->_store[$itemName]['lookupType']);
  }
  
  /**
   * Lookup the item with the given $itemName.
   * @param string $itemName
   * @return mixed
   * @throws Swift_DependencyException If the dependency is not found
   * @see register()
   */
  public function lookup($itemName)
  {
    if (!$this->has($itemName))
    {
      throw new Swift_DependencyException(
        'Cannot lookup dependency "' . $itemName . '" since it is not registered.'
        );
    }
    
    switch ($this->_store[$itemName]['lookupType'])
    {
      case self::TYPE_ALIAS:
        return $this->_createAlias($itemName);
      case self::TYPE_VALUE:
        return $this->_getValue($itemName);
      case self::TYPE_INSTANCE:
        return $this->_createNewInstance($itemName);
      case self::TYPE_SHARED:
        return $this->_createSharedInstance($itemName);
    }
  }
  
  /**
   * Create an array of arguments passed to the constructor of $itemName.
   * @param string $itemName
   * @return array
   */
  public function createDependenciesFor($itemName)
  {
    $args = array();
    if (isset($this->_store[$itemName]['args']))
    {
      $args = $this->_resolveArgs($this->_store[$itemName]['args']);
    }
    return $args;
  }
  
  /**
   * Register a new dependency with $itemName.
   * This method returns the current DependencyContainer instance because it
   * requires the use of the fluid interface to set the specific details for the
   * dependency.
   *
   * @param string $itemName
   * @return Swift_DependencyContainer
   * @see asNewInstanceOf(), asSharedInstanceOf(), asValue()
   */
  public function register($itemName)
  {
    $this->_store[$itemName] = array();
    $this->_endPoint =& $this->_store[$itemName];
    return $this;
  }
  
  /**
   * Specify the previously registered item as a literal value.
   * {@link register()} must be called before this will work.
   *
   * @param mixed $value
   * @return Swift_DependencyContainer
   */
  public function asValue($value)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['lookupType'] = self::TYPE_VALUE;
    $endPoint['value'] = $value;
    return $this;
  }
  
  /**
   * Specify the previously registered item as an alias of another item.
   * @param string $lookup
   * @return Swift_DependencyContainer
   */
  public function asAliasOf($lookup)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['lookupType'] = self::TYPE_ALIAS;
    $endPoint['ref'] = $lookup;
    return $this;
  }
  
  /**
   * Specify the previously registered item as a new instance of $className.
   * {@link register()} must be called before this will work.
   * Any arguments can be set with {@link withDependencies()},
   * {@link addConstructorValue()} or {@link addConstructorLookup()}.
   *
   * @param string $className
   * @return Swift_DependencyContainer
   * @see withDependencies(), addConstructorValue(), addConstructorLookup()
   */
  public function asNewInstanceOf($className)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['lookupType'] = self::TYPE_INSTANCE;
    $endPoint['className'] = $className;
    return $this;
  }
  
  /**
   * Specify the previously registered item as a shared instance of $className.
   * {@link register()} must be called before this will work.
   * @param string $className
   * @return Swift_DependencyContainer
   */
  public function asSharedInstanceOf($className)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['lookupType'] = self::TYPE_SHARED;
    $endPoint['className'] = $className;
    return $this;
  }
  
  /**
   * Specify a list of injected dependencies for the previously registered item.
   * This method takes an array of lookup names.
   * 
   * @param array $lookups
   * @return Swift_DependencyContainer
   * @see addConstructorValue(), addConstructorLookup()
   */
  public function withDependencies(array $lookups)
  {
    $endPoint =& $this->_getEndPoint();
    $endPoint['args'] = array();
    foreach ($lookups as $lookup)
    {
      $this->addConstructorLookup($lookup);
    }
    return $this;
  }
  
  /**
   * Specify a literal (non looked up) value for the constructor of the
   * previously registered item.
   * 
   * @param mixed $value
   * @return Swift_DependencyContainer
   * @see withDependencies(), addConstructorLookup()
   */
  public function addConstructorValue($value)
  {
    $endPoint =& $this->_getEndPoint();
    if (!isset($endPoint['args']))
    {
      $endPoint['args'] = array();
    }
    $endPoint['args'][] = array('type' => 'value', 'item' => $value);
    return $this;
  }
  
  /**
   * Specify a dependency lookup for the constructor of the previously
   * registered item.
   * 
   * @param string $lookup
   * @return Swift_DependencyContainer
   * @see withDependencies(), addConstructorValue()
   */
  public function addConstructorLookup($lookup)
  {
    $endPoint =& $this->_getEndPoint();
    if (!isset($this->_endPoint['args']))
    {
      $endPoint['args'] = array();
    }
    $endPoint['args'][] = array('type' => 'lookup', 'item' => $lookup);
    return $this;
  }
  
  // -- Private methods
  
  /** Get the literal value with $itemName */
  private function _getValue($itemName)
  {
    return $this->_store[$itemName]['value'];
  }
  
  /** Resolve an alias to another item */
  private function _createAlias($itemName)
  {
    return $this->lookup($this->_store[$itemName]['ref']);
  }
  
  /** Create a fresh instance of $itemName */
  private function _createNewInstance($itemName)
  {
    $reflector = new ReflectionClass($this->_store[$itemName]['className']);
    if ($reflector->getConstructor())
    {
      return $reflector->newInstanceArgs(
        $this->createDependenciesFor($itemName)
        );
    }
    else
    {
      return $reflector->newInstance();
    }
  }
  
  /** Create and register a shared instance of $itemName */
  private function _createSharedInstance($itemName)
  {
    if (!isset($this->_store[$itemName]['instance']))
    {
      $this->_store[$itemName]['instance'] = $this->_createNewInstance($itemName);
    }
    return $this->_store[$itemName]['instance'];
  }
  
  /** Get the current endpoint in the store */
  private function &_getEndPoint()
  {
    if (!isset($this->_endPoint))
    {
      throw new BadMethodCallException(
        'Component must first be registered by calling register()'
        );
    }
    return $this->_endPoint;
  }
  
  /** Get an argument list with dependencies resolved */
  private function _resolveArgs(array $args)
  {
    $resolved = array();
    foreach ($args as $argDefinition)
    {
      switch ($argDefinition['type'])
      {
        case 'lookup':
          $resolved[] = $this->_lookupRecursive($argDefinition['item']);
          break;
        case 'value':
          $resolved[] = $argDefinition['item'];
          break;
      }
    }
    return $resolved;
  }
  
  /** Resolve a single dependency with an collections */
  private function _lookupRecursive($item)
  {
    if (is_array($item))
    {
      $collection = array();
      foreach ($item as $k => $v)
      {
        $collection[$k] = $this->_lookupRecursive($v);
      }
      return $collection;
    }
    else
    {
      return $this->lookup($item);
    }
  }
  
}
 /* .tmp\flat\Encoding.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Encoding.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/DependencyContainer.php';

/**
 * Provides quick access to each encoding type.
 * 
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoding
{
  
  /**
   * Get the Encoder that provides 7-bit encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function get7BitEncoding()
  {
    return self::_lookup('mime.7bitcontentencoder');
  }
  
  /**
   * Get the Encoder that provides 8-bit encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function get8BitEncoding()
  {
    return self::_lookup('mime.8bitcontentencoder');
  }
  
  /**
   * Get the Encoder that provides Quoted-Printable (QP) encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function getQpEncoding()
  {
    return self::_lookup('mime.qpcontentencoder');
  }
  
  /**
   * Get the Encoder that provides Base64 encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function getBase64Encoding()
  {
    return self::_lookup('mime.base64contentencoder');
  }
  
  // -- Private Static Methods
  
  private static function _lookup($key)
  {
    return Swift_DependencyContainer::getInstance()->lookup($key);
  }
  
}
 /* .tmp\flat\EncodingObserver.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\EncodingObserver.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/ContentEncoder.php';

/**
 * Observes changes for a Mime entity's ContentEncoder.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_EncodingObserver
{
  
  /**
   * Notify this observer that the observed entity's ContentEncoder has changed.
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function encoderChanged(Swift_Mime_ContentEncoder $encoder);
  
}
 /* .tmp\flat\EsmtpHandler.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\EsmtpHandler.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/EsmtpBufferWrapper.php';

/**
 * An ESMTP handler.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport_EsmtpHandler
{
  
  /**
   * Get the name of the ESMTP extension this handles.
   * @return boolean
   */
  public function getHandledKeyword();
  
  /**
   * Set the parameters which the EHLO greeting indicated.
   * @param string[] $parameters
   */
  public function setKeywordParams(array $parameters);
  
  /**
   * Runs immediately after a EHLO has been issued.
   * @param Swift_Transport_SmtpAgent $agent to read/write
   */
  public function afterEhlo(Swift_Transport_SmtpAgent $agent);
  
  /**
   * Get params which are appended to MAIL FROM:<>.
   * @return string[]
   */
  public function getMailParams();
  
  /**
   * Get params which are appended to RCPT TO:<>.
   * @return string[]
   */
  public function getRcptParams();
  
  /**
   * Runs when a command is due to be sent.
   * @param Swift_Transport_SmtpAgent $agent to read/write
   * @param string $command to send
   * @param int[] $codes expected in response
   * @param string[] &$failedRecipients
   * @param boolean &$stop to be set true if the command is now sent
   */
  public function onCommand(Swift_Transport_SmtpAgent $agent,
    $command, $codes = array(), &$failedRecipients = null, &$stop = false);
    
  /**
   * Returns +1, -1 or 0 according to the rules for usort().
   * This method is called to ensure extensions can be execute in an appropriate order.
   * @param string $esmtpKeyword to compare with
   * @return int
   */
  public function getPriorityOver($esmtpKeyword);
  
  /**
   * Returns an array of method names which are exposed to the Esmtp class.
   * @return string[]
   */
  public function exposeMixinMethods();
  
  /**
   * Tells this handler to clear any buffers and reset its state.
   */
  public function resetState();
  
}
 /* .tmp\flat\Event.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Event.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The minimum interface for an Event.
 * 
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_Event
{
  
  /**
   * Get the source object of this event.
   * @return object
   */
  public function getSource();
  
  /**
   * Prevent this Event from bubbling any further up the stack.
   * @param boolean $cancel, optional
   */
  public function cancelBubble($cancel = true);
  
  /**
   * Returns true if this Event will not bubble any further up the stack.
   * @return boolean
   */
  public function bubbleCancelled();
  
}
 /* .tmp\flat\EventDispatcher.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\EventDispatcher.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Event.php';

/**
 * Interface for the EventDispatcher which handles the event dispatching layer.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_EventDispatcher
{
  
  /**
   * Create a new SendEvent for $source and $message.
   * @param Swift_Transport $source
   * @param Swift_Mime_Message
   * @return Swift_Events_SendEvent
   */
  public function createSendEvent(Swift_Transport $source,
    Swift_Mime_Message $message);
  
  /**
   * Create a new CommandEvent for $source and $command.
   * @param Swift_Transport $source
   * @param string $command That will be executed
   * @param array $successCodes That are needed
   * @return Swift_Events_CommandEvent
   */
  public function createCommandEvent(Swift_Transport $source,
    $command, $successCodes = array());
  
  /**
   * Create a new ResponseEvent for $source and $response.
   * @param Swift_Transport $source
   * @param string $response
   * @param boolean $valid If the response is valid
   * @return Swift_Events_ResponseEvent
   */
  public function createResponseEvent(Swift_Transport $source,
    $response, $valid);
  
  /**
   * Create a new TransportChangeEvent for $source.
   * @param Swift_Transport $source
   * @return Swift_Events_TransportChangeEvent
   */
  public function createTransportChangeEvent(Swift_Transport $source);
  
  /**
   * Create a new TransportExceptionEvent for $source.
   * @param Swift_Transport $source
   * @param Swift_TransportException $ex
   * @return Swift_Events_TransportExceptionEvent
   */
  public function createTransportExceptionEvent(Swift_Transport $source,
    Swift_TransportException $ex);
  
  /**
   * Bind an event listener to this dispatcher.
   * @param Swift_Events_EventListener $listener
   */
  public function bindEventListener(Swift_Events_EventListener $listener);
  
  /**
   * Dispatch the given Event to all suitable listeners.
   * @param Swift_Events_EventObject $evt
   * @param string $target method
   */
  public function dispatchEvent(Swift_Events_EventObject $evt, $target);
  
}
 /* .tmp\flat\EventListener.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\EventListener.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An identity interface which all EventListeners must extend.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_EventListener
{  
}
 /* .tmp\flat\Filterable.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Filterable.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/StreamFilter.php';

/**
 * Allows StreamFilters to operate on a stream.
 * @package Swift
 * @author Chris Corbyn
 */
interface Swift_Filterable
{
  
  /**
   * Add a new StreamFilter, referenced by $key.
   * @param Swift_StreamFilter $filter
   * @param string $key
   */
  public function addFilter(Swift_StreamFilter $filter, $key);
  
  /**
   * Remove an existing filter using $key.
   * @param string $key
   */
  public function removeFilter($key);
  
}
 /* .tmp\flat\Header.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Header.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_Header
{
  
  /** Text headers */
  const TYPE_TEXT = 2;
  
  /** Parameterized headers (text + params) */
  const TYPE_PARAMETERIZED = 6;

  /** Mailbox and address headers */
  const TYPE_MAILBOX = 8;
  
  /** Date and time headers */
  const TYPE_DATE = 16;
  
  /** Identification headers */
  const TYPE_ID = 32;
  
  /** Address path headers */
  const TYPE_PATH = 64;
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType();
  
  /**
   * Set the model for the field body.
   * The actual types needed will vary depending upon the type of Header.
   * @param mixed $model
   */
  public function setFieldBodyModel($model);
  
  /**
   * Set the charset used when rendering the Header.
   * @param string $charset
   */
  public function setCharset($charset);
  
  /**
   * Get the model for the field body.
   * The return type depends on the specifics of the Header.
   * @return mixed
   */
  public function getFieldBodyModel();
  
  /**
   * Get the name of this header (e.g. Subject).
   * The name is an identifier and as such will be immutable.
   * @return string
   */
  public function getFieldName();
  
  /**
   * Get the field body, prepared for folding into a final header value.
   * @return string
   */
  public function getFieldBody();
  
  /**
   * Get this Header rendered as a compliant string.
   * @return string
   */
  public function toString();
  
}
 /* .tmp\flat\InputByteStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\InputByteStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An abstract means of writing data.
 * Classes implementing this interface may use a subsystem which requires less
 * memory than working with large strings of data.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
interface Swift_InputByteStream
{
  
  /**
   * Writes $bytes to the end of the stream.
   * 
   * Writing may not happen immediately if the stream chooses to buffer.  If
   * you want to write these bytes with immediate effect, call {@link commit()}
   * after calling write().
   * 
   * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
   * second, etc etc).
   *
   * @param string $bytes
   * @return int
   * @throws Swift_IoException
   */
  public function write($bytes);
  
  /**
   * For any bytes that are currently buffered inside the stream, force them
   * off the buffer.
   * 
   * @throws Swift_IoException
   */
  public function commit();
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   * 
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is);
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   * 
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is);
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   * @throws Swift_IoException
   */
  public function flushBuffers();
  
}
 /* .tmp\flat\KeyCache.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\KeyCache.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Provides a mechanism for storing data using two keys.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
interface Swift_KeyCache
{
  
  /** Mode for replacing existing cached data */
  const MODE_WRITE = 1;
  
  /** Mode for appending data to the end of existing cached data */
  const MODE_APPEND = 2;
  
  /**
   * Set a string into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param string $string
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function setString($nsKey, $itemKey, $string, $mode);
  
  /**
   * Set a ByteStream into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_OutputByteStream $os
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os,
    $mode);
  
  /**
   * Provides a ByteStream which when written to, writes data to $itemKey.
   * NOTE: The stream will always write in append mode.
   * If the optional third parameter is passed all writes will go through $is.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_InputByteStream $is, optional
   * @return Swift_InputByteStream
   */
  public function getInputByteStream($nsKey, $itemKey,
    Swift_InputByteStream $is = null);
  
  /**
   * Get data back out of the cache as a string.
   * @param string $nsKey
   * @param string $itemKey
   * @return string
   */
  public function getString($nsKey, $itemKey);
  
  /**
   * Get data back out of the cache as a ByteStream.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_InputByteStream $is to write the data to
   */
  public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is);
  
  /**
   * Check if the given $itemKey exists in the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @return boolean
   */
  public function hasKey($nsKey, $itemKey);
  
  /**
   * Clear data for $itemKey in the namespace $nsKey if it exists.
   * @param string $nsKey
   * @param string $itemKey
   */
  public function clearKey($nsKey, $itemKey);
  
  /**
   * Clear all data in the namespace $nsKey if it exists.
   * @param string $nsKey
   */
  public function clearAll($nsKey);
  
}
 /* .tmp\flat\Logger.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Logger.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs events in the Transport system.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Plugins_Logger
{
  
  /**
   * Add a log entry.
   * @param string $entry
   */
  public function add($entry);
  
  /**
   * Clear the log contents.
   */
  public function clear();
  
  /**
   * Get this log as a string.
   * @return string
   */
  public function dump();
  
}
 /* .tmp\flat\LoginAuthenticator.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\LoginAuthenticator.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/Esmtp/Authenticator.php';
//@require 'Swift/Transport/SmtpAgent.php';
//@require 'Swift/TransportException.php';

/**
 * Handles LOGIN authentication.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Esmtp_Auth_LoginAuthenticator
  implements Swift_Transport_Esmtp_Authenticator
{
  
  /**
   * Get the name of the AUTH mechanism this Authenticator handles.
   * @return string
   */
  public function getAuthKeyword()
  {
    return 'LOGIN';
  }
  
  /**
   * Try to authenticate the user with $username and $password.
   * @param Swift_Transport_SmtpAgent $agent
   * @param string $username
   * @param string $password
   * @return boolean
   */
  public function authenticate(Swift_Transport_SmtpAgent $agent,
    $username, $password)
  {
    try
    {
      $agent->executeCommand("AUTH LOGIN\r\n", array(334));
      $agent->executeCommand(sprintf("%s\r\n", base64_encode($username)), array(334));
      $agent->executeCommand(sprintf("%s\r\n", base64_encode($password)), array(235));
      return true;
    }
    catch (Swift_TransportException $e)
    {
      $agent->executeCommand("RSET\r\n", array(250));
      return false;
    }
  }
  
}
 /* .tmp\flat\Mailer.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Mailer.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport.php';
//@require 'Swift/Mime/Message.php';
//@require 'Swift/Mailer/RecipientIterator.php';
//@require 'Swift/Events/EventListener.php';

/**
 * Swift Mailer class.
 * 
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_Mailer
{
  
  /** The Transport used to send messages */
  private $_transport;
  
  /**
   * Create a new Mailer using $transport for delivery.
   * 
   * @param Swift_Transport $transport
   */
  public function __construct(Swift_Transport $transport)
  {
    $this->_transport = $transport;
  }

  /**
   * Create a new Mailer instance.
   * 
   * @param Swift_Transport $transport
   * @return Swift_Mailer
   */
  public static function newInstance(Swift_Transport $transport)
  {
    return new self($transport);
  }
  
  /**
   * Send the given Message like it would be sent in a mail client.
   * 
   * All recipients (with the exception of Bcc) will be able to see the other
   * recipients this message was sent to.
   * 
   * If you need to send to each recipient without disclosing details about the
   * other recipients see {@link batchSend()}.
   * 
   * Recipient/sender data will be retreived from the Message object.
   * 
   * The return value is the number of recipients who were accepted for
   * delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param array &$failedRecipients, optional
   * @return int
   * @see batchSend()
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $failedRecipients = (array) $failedRecipients;
    
    if (!$this->_transport->isStarted())
    {
      $this->_transport->start();
    }
    
    return $this->_transport->send($message, $failedRecipients);
  }
  
  /**
   * Send the given Message to all recipients individually.
   * 
   * This differs from {@link send()} in the way headers are presented to the
   * recipient.  The only recipient in the "To:" field will be the individual
   * recipient it was sent to.
   * 
   * If an iterator is provided, recipients will be read from the iterator
   * one-by-one, otherwise recipient data will be retreived from the Message
   * object.
   * 
   * Sender information is always read from the Message object.
   * 
   * The return value is the number of recipients who were accepted for
   * delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param array &$failedRecipients, optional
   * @param Swift_Mailer_RecipientIterator $it, optional
   * @return int
   * @see send()
   */
  public function batchSend(Swift_Mime_Message $message,
    &$failedRecipients = null,
    Swift_Mailer_RecipientIterator $it = null)
  {
    $failedRecipients = (array) $failedRecipients;
    
    $sent = 0;
    $to = $message->getTo();
    $cc = $message->getCc();
    $bcc = $message->getBcc();
    
    if (!empty($cc))
    {
      $message->setCc(array());
    }
    if (!empty($bcc))
    {
      $message->setBcc(array());
    }
    
    //Use an iterator if set
    if (isset($it))
    {
      while ($it->hasNext())
      {
        $message->setTo($it->nextRecipient());
        $sent += $this->send($message, $failedRecipients);
      }
    }
    else
    {
      foreach ($to as $address => $name)
      {
        $message->setTo(array($address => $name));
        $sent += $this->send($message, $failedRecipients);
      }
    }
    
    $message->setTo($to);
    
    if (!empty($cc))
    {
      $message->setCc($cc);
    }
    if (!empty($bcc))
    {
      $message->setBcc($bcc);
    }
    
    return $sent;
  }
  
  /**
   * Register a plugin using a known unique key (e.g. myPlugin).
   * 
   * @param Swift_Events_EventListener $plugin
   * @param string $key
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->_transport->registerPlugin($plugin);
  }
  
  /**
   * The Transport used to send messages.
   * @return Swift_Transport
   */
  public function getTransport()
  {
    return $this->_transport;
  }
}
 /* .tmp\flat\MailInvoker.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\MailInvoker.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This interface intercepts calls to the mail() function.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport_MailInvoker
{
  
  /**
   * Send mail via the mail() function.
   * 
   * This method takes the same arguments as PHP mail().
   * 
   * @param string $to
   * @param string $subject
   * @param string $body
   * @param string $headers
   * @param string $extraParams
   * 
   * @return boolean
   */
  public function mail($to, $subject, $body, $headers = null, $extraParams = null);
  
}
 /* .tmp\flat\OutputByteStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\OutputByteStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An abstract means of reading data.
 * Classes implementing this interface may use a subsystem which requires less
 * memory than working with large strings of data.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
interface Swift_OutputByteStream
{
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   * @throws Swift_IoException
   */
  public function read($length);
  
  /**
   * Move the internal read pointer to $byteOffset in the stream.
   * @param int $byteOffset
   * @return boolean
   * @throws Swift_IoException
   */
  public function setReadPointer($byteOffset);
  
}
 /* .tmp\flat\PlainAuthenticator.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\PlainAuthenticator.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/Esmtp/Authenticator.php';
//@require 'Swift/Transport/SmtpAgent.php';
//@require 'Swift/TransportException.php';

/**
 * Handles PLAIN authentication.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Esmtp_Auth_PlainAuthenticator
  implements Swift_Transport_Esmtp_Authenticator
{
  
  /**
   * Get the name of the AUTH mechanism this Authenticator handles.
   * @return string
   */
  public function getAuthKeyword()
  {
    return 'PLAIN';
  }
  
  /**
   * Try to authenticate the user with $username and $password.
   * @param Swift_Transport_SmtpAgent $agent
   * @param string $username
   * @param string $password
   * @return boolean
   */
  public function authenticate(Swift_Transport_SmtpAgent $agent,
    $username, $password)
  {
    try
    {
      $message = base64_encode($username . chr(0) . $username . chr(0) . $password);
      $agent->executeCommand(sprintf("AUTH PLAIN %s\r\n", $message), array(235));
      return true;
    }
    catch (Swift_TransportException $e)
    {
      $agent->executeCommand("RSET\r\n", array(250));
      return false;
    }
  }
  
}
 /* .tmp\flat\Pop3Connection.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Pop3Connection.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pop3Connection interface for connecting and disconnecting to a POP3 host.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
interface Swift_Plugins_Pop_Pop3Connection
{
  
  /**
   * Connect to the POP3 host and throw an Exception if it fails.
   * 
   * @throws Swift_Plugins_Pop_Pop3Exception
   */
  public function connect();
  
  /**
   * Disconnect from the POP3 host and throw an Exception if it fails.
   * 
   * @throws Swift_Plugins_Pop_Pop3Exception
   */
  public function disconnect();
  
}
 /* .tmp\flat\Preferences.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Preferences.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/DependencyContainer.php';

/**
 * Changes some global preference settings in Swift Mailer.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_Preferences
{
  
  /** Singleton instance */
  private static $_instance = null;
  
  /** Constructor not to be used */
  private function __construct() { }
  
  /**
   * Get a new instance of Preferences.
   * @return Swift_Preferences
   */
  public static function getInstance()
  {
    if (!isset(self::$_instance))
    {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
  
  /**
   * Set the default charset used.
   * @param string
   * @return Swift_Preferences
   */
  public function setCharset($charset)
  {
    Swift_DependencyContainer::getInstance()
      ->register('properties.charset')->asValue($charset);
    return $this;
  }
  
  /**
   * Set the directory where temporary files can be saved.
   * @param string $dir
   * @return Swift_Preferences
   */
  public function setTempDir($dir)
  {
    Swift_DependencyContainer::getInstance()
      ->register('tempdir')->asValue($dir);
    return $this;
  }
  
  /**
   * Set the type of cache to use (i.e. "disk" or "array").
   * @param string $type
   * @return Swift_Preferences
   */
  public function setCacheType($type)
  {
    Swift_DependencyContainer::getInstance()
      ->register('cache')->asAliasOf(sprintf('cache.%s', $type));
    return $this;
  }
  
}
 /* .tmp\flat\RecipientIterator.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\RecipientIterator.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides an abstract way of specifying recipients for batch sending.
 * @package Swift
 * @subpackage Mailer
 * @author Chris Corbyn
 */
interface Swift_Mailer_RecipientIterator
{
  
  /**
   * Returns true only if there are more recipients to send to.
   * @return boolean
   */
  public function hasNext();
  
  /**
   * Returns an array where the keys are the addresses of recipients and the
   * values are the names.
   * e.g. ('foo@bar' => 'Foo') or ('foo@bar' => NULL)
   * @return array
   */
  public function nextRecipient();
  
}
 /* .tmp\flat\ReplacementFilterFactory.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\ReplacementFilterFactory.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates StreamFilters.
 * @package Swift
 * @author Chris Corbyn
 */
interface Swift_ReplacementFilterFactory
{
  
  /**
   * Create a filter to replace $search with $replace.
   * @param mixed $search
   * @param mixed $replace
   * @return Swift_StreamFilter
   */
  public function createFilter($search, $replace);
  
}
 /* .tmp\flat\Replacements.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Replacements.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Allows customization of Messages on-the-fly.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
interface Swift_Plugins_Decorator_Replacements
{
  
  /**
   * Return the array of replacements for $address.
   * 
   * This method is invoked once for every single recipient of a message.
   * 
   * If no replacements can be found, an empty value (NULL) should be returned
   * and no replacements will then be made on the message.
   * 
   * @param string $address
   * 
   * @return array
   */
  public function getReplacementsFor($address);
  
}
 /* .tmp\flat\Reporter.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Reporter.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Message.php';

/**
 * The Reporter plugin sends pass/fail notification to a Reporter.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
interface Swift_Plugins_Reporter
{
  
  /** The recipient was accepted for delivery */
  const RESULT_PASS = 0x01;
  
  /** The recipient could not be accepted */
  const RESULT_FAIL = 0x10;
  
  /**
   * Notifies this ReportNotifier that $address failed or succeeded.
   * @param Swift_Mime_Message $message
   * @param string $address
   * @param int $result from {@link RESULT_PASS, RESULT_FAIL}
   */
  public function notify(Swift_Mime_Message $message, $address, $result);
  
}
 /* .tmp\flat\Sleeper.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Sleeper.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sleeps for a duration of time.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
interface Swift_Plugins_Sleeper
{
  
  /**
   * Sleep for $seconds.
   * @param int $seconds
   */
  public function sleep($seconds);
  
}
 /* .tmp\flat\SmtpAgent.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\SmtpAgent.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Wraps an IoBuffer to send/receive SMTP commands/responses.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport_SmtpAgent
{
  
  /**
   * Get the IoBuffer where read/writes are occurring.
   * @return Swift_Transport_IoBuffer
   */
  public function getBuffer();
  
  /**
   * Run a command against the buffer, expecting the given response codes.
   * If no response codes are given, the response will not be validated.
   * If codes are given, an exception will be thrown on an invalid response.
   * @param string $command
   * @param int[] $codes
   * @param string[] &$failures
   */
  public function executeCommand($command, $codes = array(), &$failures = null);
  
}
 /* .tmp\flat\StreamFilter.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\StreamFilter.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Processes bytes as they pass through a stream and performs filtering.
 * @package Swift
 * @author Chris Corbyn
 */
interface Swift_StreamFilter
{
  
  /**
   * Based on the buffer given, this returns true if more buffering is needed.
   * @param mixed $buffer
   * @return boolean
   */
  public function shouldBuffer($buffer);
  
  /**
   * Filters $buffer and returns the changes.
   * @param mixed $buffer
   * @return mixed
   */
  public function filter($buffer);
  
}
 /* .tmp\flat\Swift.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Swift.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * General utility class in Swift Mailer, not to be instantiated.
 * 
 * @package Swift
 * 
 * @author Chris Corbyn
 */
abstract class Swift
{
  
  /** Swift Mailer Version number generated during dist release process */
  const VERSION = '4.0.6';
  
  /**
   * Internal autoloader for spl_autoload_register().
   * 
   * @param string $class
   */
  public static function autoload($class)
  {
    //Don't interfere with other autoloaders
    if (0 !== strpos($class, 'Swift'))
    {
      return false;
    }

    $path = dirname(__FILE__).'/'.str_replace('_', '/', $class).'.php';

    if (!file_exists($path))
    {
      return false;
    }

//    require_once $path;
  }
  
  /**
   * Configure autoloading using Swift Mailer.
   * 
   * This is designed to play nicely with other autoloaders.
   */
  public static function registerAutoload()
  {
    spl_autoload_register(array('Swift', 'autoload'));
  }
  
}
 /* .tmp\flat\SwiftException.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\SwiftException.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base Exception class.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_SwiftException extends Exception
{
  
  /**
   * Create a new SwiftException with $message.
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
 /* .tmp\flat\Timer.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Timer.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides timestamp data.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
interface Swift_Plugins_Timer
{
  
  /**
   * Get the current UNIX timestamp.
   * @return int
   */
  public function getTimestamp();
  
}
 /* .tmp\flat\Transport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\Transport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Message.php';
//@require 'Swift/Events/EventListener.php';

/**
 * Sends Messages via an abstract Transport subsystem.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport
{

  /**
   * Test if this Transport mechanism has started.
   * 
   * @return boolean
   */
  public function isStarted();
  
  /**
   * Start this Transport mechanism.
   */
  public function start();
  
  /**
   * Stop this Transport mechanism.
   */
  public function stop();
  
  /**
   * Send the given Message.
   * 
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null);
  
  /**
   * Register a plugin in the Transport.
   * 
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin);
  
}
 /* .tmp\flat\1\AbstractFilterableInputStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\AbstractFilterableInputStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/InputByteStream.php';
//@require 'Swift/Filterable.php';
//@require 'Swift/StreamFilter.php';

/**
 * Provides the base functionality for an InputStream supporting filters.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
abstract class Swift_ByteStream_AbstractFilterableInputStream
  implements Swift_InputByteStream, Swift_Filterable
{
  
  /** Write sequence */
  private $_sequence = 0;
  
  /** StreamFilters */
  private $_filters = array();
  
  /** A buffer for writing */
  private $_writeBuffer = '';
  
  /** Bound streams */
  private $_mirrors = array();
  
  /**
   * Commit the given bytes to the storage medium immediately.
   * @param string $bytes
   * @access protected
   */
  abstract protected function _commit($bytes);
  
  /**
   * Flush any buffers/content with immediate effect.
   * @access protected
   */
  abstract protected function _flush();
  
  /**
   * Add a StreamFilter to this InputByteStream.
   * @param Swift_StreamFilter $filter
   * @param string $key
   */
  public function addFilter(Swift_StreamFilter $filter, $key)
  {
    $this->_filters[$key] = $filter;
  }
  
  /**
   * Remove an already present StreamFilter based on its $key.
   * @param string $key
   */
  public function removeFilter($key)
  {
    unset($this->_filters[$key]);
  }
  
  /**
   * Writes $bytes to the end of the stream.
   * @param string $bytes
   * @throws Swift_IoException
   */
  public function write($bytes)
  {
    $this->_writeBuffer .= $bytes;
    foreach ($this->_filters as $filter)
    {
      if ($filter->shouldBuffer($this->_writeBuffer))
      {
        return;
      }
    }
    $this->_doWrite($this->_writeBuffer);
    return ++$this->_sequence;
  }
  
  /**
   * For any bytes that are currently buffered inside the stream, force them
   * off the buffer.
   * 
   * @throws Swift_IoException
   */
  public function commit()
  {
    $this->_doWrite($this->_writeBuffer);
  }
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   * 
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is)
  {
    $this->_mirrors[] = $is;
  }
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   * 
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is)
  {
    foreach ($this->_mirrors as $k => $stream)
    {
      if ($is === $stream)
      {
        if ($this->_writeBuffer !== '')
        {
          $stream->write($this->_filter($this->_writeBuffer));
        }
        unset($this->_mirrors[$k]);
      }
    }
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   * @throws Swift_IoException
   */
  public function flushBuffers()
  {
    if ($this->_writeBuffer !== '')
    {
      $this->_doWrite($this->_writeBuffer);
    }
    $this->_flush();
    
    foreach ($this->_mirrors as $stream)
    {
      $stream->flushBuffers();
    }
  }
  
  // -- Private methods
  
  /** Run $bytes through all filters */
  private function _filter($bytes)
  {
    foreach ($this->_filters as $filter)
    {
      $bytes = $filter->filter($bytes);
    }
    return $bytes;
  }
  
  /** Just write the bytes to the stream */
  private function _doWrite($bytes)
  {
    $this->_commit($this->_filter($bytes));
    
    foreach ($this->_mirrors as $stream)
    {
      $stream->write($bytes);
    }
    
    $this->_writeBuffer = '';
  }
  
}
 /* .tmp\flat\1\AbstractHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\AbstractHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Header.php';
//@require 'Swift/Mime/HeaderEncoder.php';
//@require 'Swift/RfcComplianceException.php';

/**
 * An abstract base MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
abstract class Swift_Mime_Headers_AbstractHeader implements Swift_Mime_Header
{
  
  /**
   * Special characters used in the syntax which need to be escaped.
   * @var string[]
   * @access private
   */
  private $_specials = array();
  
  /**
   * Tokens defined in RFC 2822 (and some related RFCs).
   * @var string[]
   * @access private
   */
  private $_grammar = array();
  
  /**
   * The name of this Header.
   * @var string
   * @access private
   */
  private $_name;
  
  /**
   * The Encoder used to encode this Header.
   * @var Swift_Encoder
   * @access private
   */
  private $_encoder;
  
  /**
   * The maximum length of a line in the header.
   * @var int
   * @access private
   */
  private $_lineLength = 78;
  
  /**
   * The language used in this Header.
   * @var string
   */
  private $_lang;
  
  /**
   * The character set of the text in this Header.
   * @var string
   * @access private
   */
  private $_charset = 'utf-8';
  
  /**
   * The value of this Header, cached.
   * @var string
   * @access private
   */
  private $_cachedValue = null;
  
  /**
   * Set the character set used in this Header.
   * @param string $charset
   */
  public function setCharset($charset)
  {
    $this->clearCachedValueIf($charset != $this->_charset);
    $this->_charset = $charset;
    if (isset($this->_encoder))
    {
      $this->_encoder->charsetChanged($charset);
    }
  }
  
  /**
   * Get the character set used in this Header.
   * @return string
   */
  public function getCharset()
  {
    return $this->_charset;
  }
  
  /**
   * Set the language used in this Header.
   * For example, for US English, 'en-us'.
   * This can be unspecified.
   * @param string $lang
   */
  public function setLanguage($lang)
  {
    $this->clearCachedValueIf($this->_lang != $lang);
    $this->_lang = $lang;
  }
  
  /**
   * Get the language used in this Header.
   * @return string
   */
  public function getLanguage()
  {
    return $this->_lang;
  }
  
  /**
   * Set the encoder used for encoding the header.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setEncoder(Swift_Mime_HeaderEncoder $encoder)
  {
    $this->_encoder = $encoder;
    $this->setCachedValue(null);
  }
  
  /**
   * Get the encoder used for encoding this Header.
   * @return Swift_Mime_HeaderEncoder
   */
  public function getEncoder()
  {
    return $this->_encoder;
  }
  
  /**
   * Get the name of this header (e.g. charset).
   * @return string
   */
  public function getFieldName()
  {
    return $this->_name;
  }
  
  /**
   * Set the maximum length of lines in the header (excluding EOL).
   * @param int $lineLength
   */
  public function setMaxLineLength($lineLength)
  {
    $this->clearCachedValueIf($this->_lineLength != $lineLength);
    $this->_lineLength = $lineLength;
  }
  
  /**
   * Get the maximum permitted length of lines in this Header.
   * @return int
   */
  public function getMaxLineLength()
  {
    return $this->_lineLength;
  }
  
  /**
   * Get this Header rendered as a RFC 2822 compliant string.
   * @return string
   * @throws Swift_RfcComplianceException
   */
  public function toString()
  {
    return $this->_tokensToString($this->toTokens());
  }
  
  /**
   * Returns a string representation of this object.
   *
   * @return string
   *
   * @see toString()
   */
  public function __toString()
  {
    return $this->toString();
  }
  
  // -- Points of extension
  
  /**
   * Set the name of this Header field.
   * @param string $name
   * @access protected
   */
  protected function setFieldName($name)
  {
    $this->_name = $name;
  }
  
  /**
   * Initialize some RFC 2822 (and friends) ABNF grammar definitions.
   * @access protected
   */
  protected function initializeGrammar()
  {
    $this->_specials = array(
      '(', ')', '<', '>', '[', ']',
      ':', ';', '@', ',', '.', '"'
      );
    
    /*** Refer to RFC 2822 for ABNF grammar ***/
    
    //All basic building blocks
    $this->_grammar['NO-WS-CTL'] = '[\x01-\x08\x0B\x0C\x0E-\x19\x7F]';
    $this->_grammar['WSP'] = '[ \t]';
    $this->_grammar['CRLF'] = '(?:\r\n)';
    $this->_grammar['FWS'] = '(?:(?:' . $this->_grammar['WSP'] . '*' .
        $this->_grammar['CRLF'] . ')?' . $this->_grammar['WSP'] . ')';
    $this->_grammar['text'] = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
    $this->_grammar['quoted-pair'] = '(?:\\\\' . $this->_grammar['text'] . ')';
    $this->_grammar['ctext'] = '(?:' . $this->_grammar['NO-WS-CTL'] .
        '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
    //Uses recursive PCRE (?1) -- could be a weak point??
    $this->_grammar['ccontent'] = '(?:' . $this->_grammar['ctext'] . '|' .
        $this->_grammar['quoted-pair'] . '|(?1))';
    $this->_grammar['comment'] = '(\((?:' . $this->_grammar['FWS'] . '|' .
        $this->_grammar['ccontent']. ')*' . $this->_grammar['FWS'] . '?\))';
    $this->_grammar['CFWS'] = '(?:(?:' . $this->_grammar['FWS'] . '?' .
        $this->_grammar['comment'] . ')*(?:(?:' . $this->_grammar['FWS'] . '?' .
        $this->_grammar['comment'] . ')|' . $this->_grammar['FWS'] . '))';
    $this->_grammar['qtext'] = '(?:' . $this->_grammar['NO-WS-CTL'] .
        '|[\x21\x23-\x5B\x5D-\x7E])';
    $this->_grammar['qcontent'] = '(?:' . $this->_grammar['qtext'] . '|' .
        $this->_grammar['quoted-pair'] . ')';
    $this->_grammar['quoted-string'] = '(?:' . $this->_grammar['CFWS'] . '?"' .
        '(' . $this->_grammar['FWS'] . '?' . $this->_grammar['qcontent'] . ')*' .
        $this->_grammar['FWS'] . '?"' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['atext'] = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
    $this->_grammar['atom'] = '(?:' . $this->_grammar['CFWS'] . '?' .
        $this->_grammar['atext'] . '+' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['dot-atom-text'] = '(?:' . $this->_grammar['atext'] . '+' .
        '(\.' . $this->_grammar['atext'] . '+)*)';
    $this->_grammar['dot-atom'] = '(?:' . $this->_grammar['CFWS'] . '?' .
        $this->_grammar['dot-atom-text'] . '+' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['word'] = '(?:' . $this->_grammar['atom'] . '|' .
        $this->_grammar['quoted-string'] . ')';
    $this->_grammar['phrase'] = '(?:' . $this->_grammar['word'] . '+?)';
    $this->_grammar['no-fold-quote'] = '(?:"(?:' . $this->_grammar['qtext'] .
        '|' . $this->_grammar['quoted-pair'] . ')*")';
    $this->_grammar['dtext'] = '(?:' . $this->_grammar['NO-WS-CTL'] .
        '|[\x21-\x5A\x5E-\x7E])';
    $this->_grammar['no-fold-literal'] = '(?:\[(?:' . $this->_grammar['dtext'] .
        '|' . $this->_grammar['quoted-pair'] . ')*\])';
    
    //Message IDs
    $this->_grammar['id-left'] = '(?:' . $this->_grammar['dot-atom-text'] . '|' .
        $this->_grammar['no-fold-quote'] . ')';
    $this->_grammar['id-right'] = '(?:' . $this->_grammar['dot-atom-text'] . '|' .
        $this->_grammar['no-fold-literal'] . ')';
    
    //Addresses, mailboxes and paths
    $this->_grammar['local-part'] = '(?:' . $this->_grammar['dot-atom'] . '|' .
        $this->_grammar['quoted-string'] . ')';
    $this->_grammar['dcontent'] = '(?:' . $this->_grammar['dtext'] . '|' .
        $this->_grammar['quoted-pair'] . ')';
    $this->_grammar['domain-literal'] = '(?:' . $this->_grammar['CFWS'] . '?\[(' .
        $this->_grammar['FWS'] . '?' . $this->_grammar['dcontent'] . ')*?' .
        $this->_grammar['FWS'] . '?\]' . $this->_grammar['CFWS'] . '?)';
    $this->_grammar['domain'] = '(?:' . $this->_grammar['dot-atom'] . '|' .
        $this->_grammar['domain-literal'] . ')';
    $this->_grammar['addr-spec'] = '(?:' . $this->_grammar['local-part'] . '@' .
        $this->_grammar['domain'] . ')';
  }
  
  /**
   * Get the grammar defined for $name token.
   * @param string $name execatly as written in the RFC
   * @return string
   */
  protected function getGrammar($name)
  {
    if (array_key_exists($name, $this->_grammar))
    {
      return $this->_grammar[$name];
    }
    else
    {
      throw new Swift_RfcComplianceException(
        "No such grammar '" . $name . "' defined."
        );
    }
  }
  
  /**
   * Escape special characters in a string (convert to quoted-pairs).
   * @param string $token
   * @param string[] $include additonal chars to escape
   * @param string[] $exclude chars from escaping
   * @return string
   */
  protected function escapeSpecials($token, $include = array(),
    $exclude = array())
  {
    foreach (
      array_merge(array('\\'), array_diff($this->_specials, $exclude), $include) as $char)
    {
      $token = str_replace($char, '\\' . $char, $token);
    }
    return $token;
  }
  
  /**
   * Produces a compliant, formatted RFC 2822 'phrase' based on the string given.
   * @param Swift_Mime_Header $header
   * @param string $string as displayed
   * @param string $charset of the text
   * @param Swift_Mime_HeaderEncoder $encoder
   * @param boolean $shorten the first line to make remove for header name
   * @return string
   */
  protected function createPhrase(Swift_Mime_Header $header, $string, $charset,
    Swift_Mime_HeaderEncoder $encoder = null, $shorten = false)
  {
    //Treat token as exactly what was given
    $phraseStr = $string;
    //If it's not valid
    if (!preg_match('/^' . $this->_grammar['phrase'] . '$/D', $phraseStr))
    {
      // .. but it is just ascii text, try escaping some characters
      // and make it a quoted-string
      if (preg_match('/^' . $this->_grammar['text'] . '*$/D', $phraseStr))
      {
        $phraseStr = $this->escapeSpecials(
          $phraseStr, array('"'), $this->_specials
          );
        $phraseStr = '"' . $phraseStr . '"';
      }
      else // ... otherwise it needs encoding
      {
        //Determine space remaining on line if first line
        if ($shorten)
        {
          $usedLength = strlen($header->getFieldName() . ': ');
        }
        else
        {
          $usedLength = 0;
        }
        $phraseStr = $this->encodeWords($header, $string, $usedLength);
      }
    }
    
    return $phraseStr;
  }
  
  /**
   * Encode needed word tokens within a string of input.
   * @param string $input
   * @param string $usedLength, optional
   * @return string
   */
  protected function encodeWords(Swift_Mime_Header $header, $input,
    $usedLength = -1)
  {
    $value = '';
    
    $tokens = $this->getEncodableWordTokens($input);
    
    foreach ($tokens as $token)
    {
      //See RFC 2822, Sect 2.2 (really 2.2 ??)
      if ($this->tokenNeedsEncoding($token))
      {
        //Don't encode starting WSP
        $firstChar = substr($token, 0, 1);
        switch($firstChar)
        {
          case ' ':
          case "\t":
            $value .= $firstChar;
            $token = substr($token, 1);
        }
        
        if (-1 == $usedLength)
        {
          $usedLength = strlen($header->getFieldName() . ': ') + strlen($value);
        }
        $value .= $this->getTokenAsEncodedWord($token, $usedLength);
        
        $header->setMaxLineLength(76); //Forefully override
      }
      else
      {
        $value .= $token;
      }
    }
    
    return $value;
  }
  
  /**
   * Test if a token needs to be encoded or not.
   * @param string $token
   * @return boolean
   */
  protected function tokenNeedsEncoding($token)
  {
    return preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $token);
  }
  
  /**
   * Splits a string into tokens in blocks of words which can be encoded quickly.
   * @param string $string
   * @return string[]
   */
  protected function getEncodableWordTokens($string)
  {
    $tokens = array();
    
    $encodedToken = '';
    //Split at all whitespace boundaries
    foreach (preg_split('~(?=[\t ])~', $string) as $token)
    {
      if ($this->tokenNeedsEncoding($token))
      {
        $encodedToken .= $token;
      }
      else
      {
        if (strlen($encodedToken) > 0)
        {
          $tokens[] = $encodedToken;
          $encodedToken = '';
        }
        $tokens[] = $token;
      }
    }
    if (strlen($encodedToken))
    {
      $tokens[] = $encodedToken;
    }
    
    return $tokens;
  }
  
  /**
   * Get a token as an encoded word for safe insertion into headers.
   * @param string $token to encode
   * @param int $firstLineOffset, optional
   * @return string
   */
  protected function getTokenAsEncodedWord($token, $firstLineOffset = 0)
  {
    //Adjust $firstLineOffset to account for space needed for syntax
    $charsetDecl = $this->_charset;
    if (isset($this->_lang))
    {
      $charsetDecl .= '*' . $this->_lang;
    }
    $encodingWrapperLength = strlen(
      '=?' . $charsetDecl . '?' . $this->_encoder->getName() . '??='
      );
    
    if ($firstLineOffset >= 75) //Does this logic need to be here?
    {
      $firstLineOffset = 0;
    }
    
    $encodedTextLines = explode("\r\n",
      $this->_encoder->encodeString(
        $token, $firstLineOffset, 75 - $encodingWrapperLength
        )
      );
    
    foreach ($encodedTextLines as $lineNum => $line)
    {
      $encodedTextLines[$lineNum] = '=?' . $charsetDecl .
        '?' . $this->_encoder->getName() .
        '?' . $line . '?=';
    }
    
    return implode("\r\n ", $encodedTextLines);
  }
  
  /**
   * Generates tokens from the given string which include CRLF as individual tokens.
   * @param string $token
   * @return string[]
   * @access protected
   */
  protected function generateTokenLines($token)
  {
    return preg_split('~(\r\n)~', $token, -1, PREG_SPLIT_DELIM_CAPTURE);
  }
  
  /**
   * Set a value into the cache.
   * @param string $value
   * @access protected
   */
  protected function setCachedValue($value)
  {
    $this->_cachedValue = $value;
  }
  
  /**
   * Get the value in the cache.
   * @return string
   * @access protected
   */
  protected function getCachedValue()
  {
    return $this->_cachedValue;
  }
  
  /**
   * Clear the cached value if $condition is met.
   * @param boolean $condition
   * @access protected
   */
  protected function clearCachedValueIf($condition)
  {
    if ($condition)
    {
      $this->setCachedValue(null);
    }
  }
  
  // -- Private methods
  
  /**
   * Generate a list of all tokens in the final header.
   * @param string $string input, optional
   * @return string[]
   * @access private
   */
  protected function toTokens($string = null)
  {
    if (is_null($string))
    {
      $string = $this->getFieldBody();
    }
    
    $tokens = array();
    
    //Generate atoms; split at all invisible boundaries followed by WSP
    foreach (preg_split('~(?=[ \t])~', $string) as $token)
    {
      $tokens = array_merge($tokens, $this->generateTokenLines($token));
    }
    
    return $tokens;
  }
  
  /**
   * Takes an array of tokens which appear in the header and turns them into
   * an RFC 2822 compliant string, adding FWSP where needed.
   * @param string[] $tokens
   * @return string
   * @access private
   */
  private function _tokensToString(array $tokens)
  {
    $lineCount = 0;
    $headerLines = array();
    $headerLines[] = $this->_name . ': ';
    $currentLine =& $headerLines[$lineCount++];
    
    //Build all tokens back into compliant header
    foreach ($tokens as $i => $token)
    {
      //Line longer than specified maximum or token was just a new line
      if (("\r\n" == $token) ||
        ($i > 0 && strlen($currentLine . $token) > $this->_lineLength)
        && 0 < strlen($currentLine))
      {
        $headerLines[] = '';
        $currentLine =& $headerLines[$lineCount++];
      }
      
      //Append token to the line
      if ("\r\n" != $token)
      {
        $currentLine .= $token;
      }
    }
    
    //Implode with FWS (RFC 2822, 2.2.3)
    return implode("\r\n", $headerLines) . "\r\n";
  }
  
}
 /* .tmp\flat\1\ArrayByteStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ArrayByteStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Allows reading and writing of bytes to and from an array.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
class Swift_ByteStream_ArrayByteStream
  implements Swift_InputByteStream, Swift_OutputByteStream
{

  /**
   * The internal stack of bytes.
   * @var string[]
   * @access private
   */
  private $_array = array();

  /**
   * The size of the stack
   * @var int
   * @access private
   */
  private $_arraySize = 0;

  /**
   * The internal pointer offset.
   * @var int
   * @access private
   */
  private $_offset = 0;
  
  /** Bound streams */
  private $_mirrors = array();
  
  /**
   * Create a new ArrayByteStream.
   * If $stack is given the stream will be populated with the bytes it contains.
   * @param mixed $stack of bytes in string or array form, optional
   */
  public function __construct($stack = null)
  {
    if (is_array($stack))
    {
      $this->_array = $stack;
      $this->_arraySize = count($stack);
    }
    elseif (is_string($stack))
    {
      $this->write($stack);
    }
    else
    {
      $this->_array = array();
    }
  }

  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   */
  public function read($length)
  {
    if ($this->_offset == $this->_arraySize)
    {
      return false;
    }

    // Don't use array slice
    $end = $length + $this->_offset;
    $end = $this->_arraySize<$end
      ?$this->_arraySize
      :$end;
    $ret = '';
    for (; $this->_offset < $end; ++$this->_offset)
    {
      $ret .= $this->_array[$this->_offset];
    }
    return $ret;
  }

  /**
   * Writes $bytes to the end of the stream.
   * @param string $bytes
   */
  public function write($bytes)
  {
    $to_add = str_split($bytes);
    foreach ($to_add as $value)
    {
      $this->_array[] = $value;
    }
    $this->_arraySize = count($this->_array);
    
    foreach ($this->_mirrors as $stream)
    {
      $stream->write($bytes);
    }
  }
  
  /**
   * Not used.
   */
  public function commit()
  {
  }
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   *
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is)
  {
    $this->_mirrors[] = $is;
  }
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   *
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is)
  {
    foreach ($this->_mirrors as $k => $stream)
    {
      if ($is === $stream)
      {
        unset($this->_mirrors[$k]);
      }
    }
  }

  /**
   * Move the internal read pointer to $byteOffset in the stream.
   * @param int $byteOffset
   * @return boolean
   */
  public function setReadPointer($byteOffset)
  {
    if ($byteOffset > $this->_arraySize)
    {
      $byteOffset = $this->_arraySize;
    }
    elseif ($byteOffset < 0)
    {
      $byteOffset = 0;
    }

    $this->_offset = $byteOffset;
  }

  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   */
  public function flushBuffers()
  {
    $this->_offset = 0;
    $this->_array = array();
    $this->_arraySize = 0;
    
    foreach ($this->_mirrors as $stream)
    {
      $stream->flushBuffers();
    }
  }

}
 /* .tmp\flat\1\ArrayCharacterStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ArrayCharacterStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/CharacterStream.php';
//@require 'Swift/OutputByteStream.php';


/**
 * A CharacterStream implementation which stores characters in an internal array.
 * @package Swift
 * @subpackage CharacterStream
 * @author Chris Corbyn
 */
class Swift_CharacterStream_ArrayCharacterStream
  implements Swift_CharacterStream
{
  
  /** A map of byte values and their respective characters */
  private static $_charMap;
  
  /** A map of characters and their derivative byte values */
  private static $_byteMap;
  
  /** The char reader (lazy-loaded) for the current charset */
  private $_charReader;

  /** A factory for creatiing CharacterReader instances */
  private $_charReaderFactory;

  /** The character set this stream is using */
  private $_charset;

  /** Array of characters */
  private $_array = array();

  /** Size of the array of character */
  private $_array_size = array();

  /** The current character offset in the stream */
  private $_offset = 0;

  /**
   * Create a new CharacterStream with the given $chars, if set.
   * @param Swift_CharacterReaderFactory $factory for loading validators
   * @param string $charset used in the stream
   */
  public function __construct(Swift_CharacterReaderFactory $factory,
    $charset)
  {
    self::_initializeMaps();
    $this->setCharacterReaderFactory($factory);
    $this->setCharacterSet($charset);
  }

  /**
   * Set the character set used in this CharacterStream.
   * @param string $charset
   */
  public function setCharacterSet($charset)
  {
    $this->_charset = $charset;
    $this->_charReader = null;
  }

  /**
   * Set the CharacterReaderFactory for multi charset support.
   * @param Swift_CharacterReaderFactory $factory
   */
  public function setCharacterReaderFactory(
    Swift_CharacterReaderFactory $factory)
  {
    $this->_charReaderFactory = $factory;
  }

  /**
   * Overwrite this character stream using the byte sequence in the byte stream.
   * @param Swift_OutputByteStream $os output stream to read from
   */
  public function importByteStream(Swift_OutputByteStream $os)
  {
    if (!isset($this->_charReader))
    {
      $this->_charReader = $this->_charReaderFactory
        ->getReaderFor($this->_charset);
    }

    $startLength = $this->_charReader->getInitialByteSize();
    while (false !== $bytes = $os->read($startLength))
    {
      $c = array();
      for ($i = 0, $len = strlen($bytes); $i < $len; ++$i)
      {
        $c[] = self::$_byteMap[$bytes[$i]];
      }
      $size = count($c);
      $need = $this->_charReader
        ->validateByteSequence($c, $size);
      if ($need > 0 &&
        false !== $bytes = $os->read($need))
      {
        for ($i = 0, $len = strlen($bytes); $i < $len; ++$i)
        {
          $c[] = self::$_byteMap[$bytes[$i]];
        }
      }
      $this->_array[] = $c;
      ++$this->_array_size;
    }
  }

  /**
   * Import a string a bytes into this CharacterStream, overwriting any existing
   * data in the stream.
   * @param string $string
   */
  public function importString($string)
  {
    $this->flushContents();
    $this->write($string);
  }

  /**
   * Read $length characters from the stream and move the internal pointer
   * $length further into the stream.
   * @param int $length
   * @return string
   */
  public function read($length)
  {
    if ($this->_offset == $this->_array_size)
    {
      return false;
    }

    // Don't use array slice
    $arrays = array();
    $end = $length + $this->_offset;
    for ($i = $this->_offset; $i < $end; ++$i)
    {
      if (!isset($this->_array[$i]))
      {
        break;
      }
      $arrays[] = $this->_array[$i];
    }
    $this->_offset += $i - $this->_offset; // Limit function calls
    $chars = false;
    foreach ($arrays as $array)
    {
      $chars .= implode('', array_map('chr', $array));
    }
    return $chars;
  }

  /**
   * Read $length characters from the stream and return a 1-dimensional array
   * containing there octet values.
   * @param int $length
   * @return int[]
   */
  public function readBytes($length)
  {
    if ($this->_offset == $this->_array_size)
    {
      return false;
    }
    $arrays = array();
    $end = $length + $this->_offset;
    for ($i = $this->_offset; $i < $end; ++$i)
    {
      if (!isset($this->_array[$i]))
      {
        break;
      }
      $arrays[] = $this->_array[$i];
    }
    $this->_offset += ($i - $this->_offset); // Limit function calls
    return call_user_func_array('array_merge', $arrays);
  }

  /**
   * Write $chars to the end of the stream.
   * @param string $chars
   */
  public function write($chars)
  {
    if (!isset($this->_charReader))
    {
      $this->_charReader = $this->_charReaderFactory->getReaderFor(
        $this->_charset);
    }

    $startLength = $this->_charReader->getInitialByteSize();

    $fp = fopen('php://memory', 'w+b');
    fwrite($fp, $chars);
    unset($chars);
    fseek($fp, 0, SEEK_SET);

    $buffer = array(0);
    $buf_pos = 1;
    $buf_len = 1;
    $has_datas = true;
    do
    {
      $bytes = array();
      // Buffer Filing
      if ($buf_len - $buf_pos < $startLength)
      {
        $buf = array_splice($buffer, $buf_pos);
        $new = $this->_reloadBuffer($fp, 100);
        if ($new)
        {
          $buffer = array_merge($buf, $new);
          $buf_len = count($buffer);
          $buf_pos = 0;
        }
        else
        {
          $has_datas = false;
        }
      }
      if ($buf_len - $buf_pos > 0)
      {
        $size = 0;
        for ($i = 0; $i < $startLength && isset($buffer[$buf_pos]); ++$i)
        {
          ++$size;
          $bytes[] = $buffer[$buf_pos++];
        }
        $need = $this->_charReader->validateByteSequence(
          $bytes, $size);
        if ($need > 0)
        {
          if ($buf_len - $buf_pos < $need)
          {
            $new = $this->_reloadBuffer($fp, $need);
            
            if ($new)
            {
              $buffer = array_merge($buffer, $new);
              $buf_len = count($buffer);
            }
          }
          for ($i = 0; $i < $need && isset($buffer[$buf_pos]); ++$i)
          {
            $bytes[] = $buffer[$buf_pos++];
          }
        }
        $this->_array[] = $bytes;
        ++$this->_array_size;
      }
    }
    while ($has_datas);
    
    fclose($fp);
  }

  /**
   * Move the internal pointer to $charOffset in the stream.
   * @param int $charOffset
   */
  public function setPointer($charOffset)
  {
    if ($charOffset > $this->_array_size)
    {
      $charOffset = $this->_array_size;
    }
    elseif ($charOffset < 0)
    {
      $charOffset = 0;
    }
    $this->_offset = $charOffset;
  }

  /**
   * Empty the stream and reset the internal pointer.
   */
  public function flushContents()
  {
    $this->_offset = 0;
    $this->_array = array();
    $this->_array_size = 0;
  }
  
  private function _reloadBuffer($fp, $len)
  {
    if (!feof($fp) && ($bytes = fread($fp, $len)) !== false)
    {
      $buf = array();
      for ($i = 0, $len = strlen($bytes); $i < $len; ++$i)
      {
        $buf[] = self::$_byteMap[$bytes[$i]];
      }
      return $buf;
    }
    return false;
  }
  
  private static function _initializeMaps()
  {
    if (!isset(self::$_charMap))
    {
      self::$_charMap = array();
      for ($byte = 0; $byte < 256; ++$byte)
      {
        self::$_charMap[$byte] = chr($byte);
      }
      self::$_byteMap = array_flip(self::$_charMap);
    }
  }
}
 /* .tmp\flat\1\ArrayKeyCache.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ArrayKeyCache.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/KeyCache.php';
//@require 'Swift/KeyCacheInputStream.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStrean.php';
//@require 'Swift/SwiftException.php';

/**
 * A basic KeyCache backed by an array.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
class Swift_KeyCache_ArrayKeyCache implements Swift_KeyCache
{
  
  /**
   * Cache contents.
   * @var array
   * @access private
   */
  private $_contents = array();
  
  /**
   * An InputStream for cloning.
   * @var Swift_KeyCache_KeyCacheInputStream
   * @access private
   */
  private $_stream;
  
  /**
   * Create a new ArrayKeyCache with the given $stream for cloning to make
   * InputByteStreams.
   * @param Swift_KeyCache_KeyCacheInputStream $stream
   */
  public function __construct(Swift_KeyCache_KeyCacheInputStream $stream)
  {
    $this->_stream = $stream;
  }
  
  /**
   * Set a string into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param string $string
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function setString($nsKey, $itemKey, $string, $mode)
  {
    $this->_prepareCache($nsKey);
    switch ($mode)
    {
      case self::MODE_WRITE:
        $this->_contents[$nsKey][$itemKey] = $string;
        break;
      case self::MODE_APPEND:
        if (!$this->hasKey($nsKey, $itemKey))
        {
          $this->_contents[$nsKey][$itemKey] = '';
        }
        $this->_contents[$nsKey][$itemKey] .= $string;
        break;
      default:
        throw new Swift_SwiftException(
          'Invalid mode [' . $mode . '] used to set nsKey='.
          $nsKey . ', itemKey=' . $itemKey
          );
    }
  }
  
  /**
   * Set a ByteStream into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_OutputByteStream $os
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os,
    $mode)
  {
    $this->_prepareCache($nsKey);
    switch ($mode)
    {
      case self::MODE_WRITE:
        $this->clearKey($nsKey, $itemKey);
      case self::MODE_APPEND:
        if (!$this->hasKey($nsKey, $itemKey))
        {
          $this->_contents[$nsKey][$itemKey] = '';
        }
        while (false !== $bytes = $os->read(8192))
        {
          $this->_contents[$nsKey][$itemKey] .= $bytes;
        }
        break;
      default:
        throw new Swift_SwiftException(
          'Invalid mode [' . $mode . '] used to set nsKey='.
          $nsKey . ', itemKey=' . $itemKey
          );
    }
  }
  
  /**
   * Provides a ByteStream which when written to, writes data to $itemKey.
   * NOTE: The stream will always write in append mode.
   * @param string $nsKey
   * @param string $itemKey
   * @return Swift_InputByteStream
   */
  public function getInputByteStream($nsKey, $itemKey,
    Swift_InputByteStream $writeThrough = null)
  {
    $is = clone $this->_stream;
    $is->setKeyCache($this);
    $is->setNsKey($nsKey);
    $is->setItemKey($itemKey);
    if (isset($writeThrough))
    {
      $is->setWriteThroughStream($writeThrough);
    }
    return $is;
  }
  
  /**
   * Get data back out of the cache as a string.
   * @param string $nsKey
   * @param string $itemKey
   * @return string
   */
  public function getString($nsKey, $itemKey)
  {
    $this->_prepareCache($nsKey);
    if ($this->hasKey($nsKey, $itemKey))
    {
      return $this->_contents[$nsKey][$itemKey];
    }
  }
  
  /**
   * Get data back out of the cache as a ByteStream.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_InputByteStream $is to write the data to
   */
  public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is)
  {
    $this->_prepareCache($nsKey);
    $is->write($this->getString($nsKey, $itemKey));
  }
  
  /**
   * Check if the given $itemKey exists in the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @return boolean
   */
  public function hasKey($nsKey, $itemKey)
  {
    $this->_prepareCache($nsKey);
    return array_key_exists($itemKey, $this->_contents[$nsKey]);
  }
  
  /**
   * Clear data for $itemKey in the namespace $nsKey if it exists.
   * @param string $nsKey
   * @param string $itemKey
   */
  public function clearKey($nsKey, $itemKey)
  {
    unset($this->_contents[$nsKey][$itemKey]);
  }
  
  /**
   * Clear all data in the namespace $nsKey if it exists.
   * @param string $nsKey
   */
  public function clearAll($nsKey)
  {
    unset($this->_contents[$nsKey]);
  }
  
  // -- Private methods
  
  /**
   * Initialize the namespace of $nsKey if needed.
   * @param string $nsKey
   * @access private
   */
  private function _prepareCache($nsKey)
  {
    if (!array_key_exists($nsKey, $this->_contents))
    {
      $this->_contents[$nsKey] = array();
    }
  }
  
}
 /* .tmp\flat\1\ArrayLogger.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ArrayLogger.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs to an Array backend.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Plugins_Loggers_ArrayLogger implements Swift_Plugins_Logger
{
  
  /**
   * The log contents.
   * @var array
   * @access private
   */
  private $_log = array();
  
  /**
   * Max size of the log.
   * @var int
   * @access private
   */
  private $_size = 0;
  
  /**
   * Create a new ArrayLogger with a maximum of $size entries.
   * @var int $size
   */
  public function __construct($size = 50)
  {
    $this->_size = $size;
  }
  
  /**
   * Add a log entry.
   * @param string $entry
   */
  public function add($entry)
  {
    $this->_log[] = $entry;
    while (count($this->_log) > $this->_size)
    {
      array_shift($this->_log);
    }
  }
  
  /**
   * Clear the log contents.
   */
  public function clear()
  {
    $this->_log = array();
  }
  
  /**
   * Get this log as a string.
   * @return string
   */
  public function dump()
  {
    return implode(PHP_EOL, $this->_log);
  }
  
}
 /* .tmp\flat\1\ArrayRecipientIterator.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ArrayRecipientIterator.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mailer/RecipientIterator.php';

/**
 * Wraps a standard PHP array in an interator.
 * @package Swift
 * @subpackage Mailer
 * @author Chris Corbyn
 */
class Swift_Mailer_ArrayRecipientIterator
  implements Swift_Mailer_RecipientIterator
{
  
  /**
   * The list of recipients.
   * @var array
   * @access private
   */
  private $_recipients = array();
  
  /**
   * Create a new ArrayRecipientIterator from $recipients.
   * @param array $recipients
   */
  public function __construct(array $recipients)
  {
    $this->_recipients = $recipients;
  }
  
  /**
   * Returns true only if there are more recipients to send to.
   * @return boolean
   */
  public function hasNext()
  {
    return !empty($this->_recipients);
  }
  
  /**
   * Returns an array where the keys are the addresses of recipients and the
   * values are the names.
   * e.g. ('foo@bar' => 'Foo') or ('foo@bar' => NULL)
   * @return array
   */
  public function nextRecipient()
  {
    return array_splice($this->_recipients, 0, 1);
  }
  
}
 /* .tmp\flat\1\ByteArrayReplacementFilter.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ByteArrayReplacementFilter.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/StreamFilter.php';

/**
 * Processes bytes as they pass through a buffer and replaces sequences in it.
 * This stream filter deals with Byte arrays rather than simple strings.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_StreamFilters_ByteArrayReplacementFilter
  implements Swift_StreamFilter
{

  /** The needle(s) to search for */
  private $_search;

  /** The replacement(s) to make */
  private $_replace;

  /** The Index for searching */
  private $_index;

  /** The Search Tree */
  private $_tree = array();

  /**  Gives the size of the largest search */
  private $_treeMaxLen = 0;
  
  private $_repSize;

  /**
   * Create a new ByteArrayReplacementFilter with $search and $replace.
   * @param array $search
   * @param array $replace
   */
  public function __construct($search, $replace)
  {
    $this->_search = $search;
    $this->_index = array();
    $this->_tree = array();
    $this->_replace = array();
    $this->_repSize = array();
    
    $tree = null;
    $i = null;
    $last_size = $size = 0;
    foreach ($search as $i => $search_element)
    {
      if ($tree !== null)
      {
        $tree[-1] = min (count($replace) - 1, $i - 1);
        $tree[-2] = $last_size;
      }
      $tree = &$this->_tree;
      if (is_array ($search_element))
      {
        foreach ($search_element as $k => $char)
        {
          $this->_index[$char] = true;
          if (!isset($tree[$char]))
          {
            $tree[$char] = array();
          }
          $tree = &$tree[$char];
        }
        $last_size = $k+1;
        $size = max($size, $last_size);
      }
      else
      {
        $last_size = 1;
        if (!isset($tree[$search_element]))
        {
          $tree[$search_element] = array();
        }
        $tree = &$tree[$search_element];
        $size = max($last_size, $size);
        $this->_index[$search_element] = true;
      }
    }
    if ($i !== null)
    {
      $tree[-1] = min (count ($replace) - 1, $i);
      $tree[-2] = $last_size;
      $this->_treeMaxLen = $size;
    }
    foreach ($replace as $rep)
    {
      if (!is_array($rep))
      {
        $rep = array ($rep);
      }
      $this->_replace[] = $rep;
    }
    for ($i = count($this->_replace) - 1; $i >= 0; --$i)
    {
      $this->_replace[$i] = $rep = $this->filter($this->_replace[$i], $i);
      $this->_repSize[$i] = count($rep);
    }
  }

  /**
   * Returns true if based on the buffer passed more bytes should be buffered.
   * @param array $buffer
   * @return boolean
   */
  public function shouldBuffer($buffer)
  {
    $endOfBuffer = end($buffer);
    return isset ($this->_index[$endOfBuffer]);
  }

  /**
   * Perform the actual replacements on $buffer and return the result.
   * @param array $buffer
   * @return array
   */
  public function filter($buffer, $_minReplaces = -1)
  {
    if ($this->_treeMaxLen == 0)
    {
      return $buffer;
    }
    
    $newBuffer = array();
    $buf_size = count($buffer);
    for ($i = 0; $i < $buf_size; ++$i)
    {
      $search_pos = $this->_tree;
      $last_found = PHP_INT_MAX;
      // We try to find if the next byte is part of a search pattern
      for ($j = 0; $j <= $this->_treeMaxLen; ++$j)
      {
        // We have a new byte for a search pattern
        if (isset ($buffer [$p = $i + $j]) && isset($search_pos[$buffer[$p]]))
        {
          $search_pos = $search_pos[$buffer[$p]];
          // We have a complete pattern, save, in case we don't find a better match later
          if (isset($search_pos[- 1]) && $search_pos[-1] < $last_found
            && $search_pos[-1] > $_minReplaces)
          {
            $last_found = $search_pos[-1];
            $last_size = $search_pos[-2];
          }
        }
        // We got a complete pattern
        elseif ($last_found !== PHP_INT_MAX)
        {
          // Adding replacement datas to output buffer
          $rep_size = $this->_repSize[$last_found];
          for ($j = 0; $j < $rep_size; ++$j)
          {
            $newBuffer[] = $this->_replace[$last_found][$j];
          }
          // We Move cursor forward
          $i += $last_size - 1;
          // Edge Case, last position in buffer
          if ($i >= $buf_size)
          {
            $newBuffer[] = $buffer[$i];
          }
          
          // We start the next loop
          continue 2;
        }
        else
        {
          // this byte is not in a pattern and we haven't found another pattern
          break;
        }
      }
      // Normal byte, move it to output buffer
      $newBuffer[] = $buffer[$i];
    }
    
    return $newBuffer;
  }

}
 /* .tmp\flat\1\CommandListener.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\CommandListener.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Events/CommandEvent.php';

/**
 * Listens for Transports to send commands to the server.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_CommandListener extends Swift_Events_EventListener
{
  
  /**
   * Invoked immediately following a command being sent.
   * @param Swift_Events_ResponseEvent $evt
   */
  public function commandSent(Swift_Events_CommandEvent $evt);
  
}
 /* .tmp\flat\1\DependencyException.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\DependencyException.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/SwiftException.php';

/**
 * DependencyException thrown when a requested dependeny is missing.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_DependencyException extends Swift_SwiftException
{
  
  /**
   * Create a new DependencyException with $message.
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
 /* .tmp\flat\1\DiskKeyCache.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\DiskKeyCache.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/KeyCache.php';
//@require 'Swift/KeyCacheInputStream.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStrean.php';
//@require 'Swift/SwiftException.php';
//@require 'Swift/IoException.php';

/**
 * A KeyCache which streams to and from disk.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
class Swift_KeyCache_DiskKeyCache implements Swift_KeyCache
{

  /** Signal to place pointer at start of file */
  const POSITION_START = 0;

  /** Signal to place pointer at end of file */
  const POSITION_END = 1;

  /**
   * An InputStream for cloning.
   * @var Swift_KeyCache_KeyCacheInputStream
   * @access private
   */
  private $_stream;

  /**
   * A path to write to.
   * @var string
   * @access private
   */
  private $_path;

  /**
   * Stored keys.
   * @var array
   * @access private
   */
  private $_keys = array();

  /**
   * Will be true if magic_quotes_runtime is turned on.
   * @var boolean
   * @access private
   */
  private $_quotes = false;

  /**
   * Create a new DiskKeyCache with the given $stream for cloning to make
   * InputByteStreams, and the given $path to save to.
   * @param Swift_KeyCache_KeyCacheInputStream $stream
   * @param string $path to save to
   */
  public function __construct(Swift_KeyCache_KeyCacheInputStream $stream, $path)
  {
    $this->_stream = $stream;
    $this->_path = $path;
    $this->_quotes = get_magic_quotes_runtime();
  }

  /**
   * Set a string into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param string $string
   * @param int $mode
   * @throws Swift_IoException
   * @see MODE_WRITE, MODE_APPEND
   */
  public function setString($nsKey, $itemKey, $string, $mode)
  {
    $this->_prepareCache($nsKey);
    switch ($mode)
    {
      case self::MODE_WRITE:
        $fp = $this->_getHandle($nsKey, $itemKey, self::POSITION_START);
        break;
      case self::MODE_APPEND:
        $fp = $this->_getHandle($nsKey, $itemKey, self::POSITION_END);
        break;
      default:
        throw new Swift_SwiftException(
          'Invalid mode [' . $mode . '] used to set nsKey='.
          $nsKey . ', itemKey=' . $itemKey
          );
        break;
    }
    fwrite($fp, $string);
  }

  /**
   * Set a ByteStream into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_OutputByteStream $os
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   * @throws Swift_IoException
   */
  public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os,
    $mode)
  {
    $this->_prepareCache($nsKey);
    switch ($mode)
    {
      case self::MODE_WRITE:
        $fp = $this->_getHandle($nsKey, $itemKey, self::POSITION_START);
        break;
      case self::MODE_APPEND:
        $fp = $this->_getHandle($nsKey, $itemKey, self::POSITION_END);
        break;
      default:
        throw new Swift_SwiftException(
          'Invalid mode [' . $mode . '] used to set nsKey='.
          $nsKey . ', itemKey=' . $itemKey
          );
        break;
    }
    while (false !== $bytes = $os->read(8192))
    {
      fwrite($fp, $bytes);
    }
  }

  /**
   * Provides a ByteStream which when written to, writes data to $itemKey.
   * NOTE: The stream will always write in append mode.
   * @param string $nsKey
   * @param string $itemKey
   * @return Swift_InputByteStream
   */
  public function getInputByteStream($nsKey, $itemKey,
    Swift_InputByteStream $writeThrough = null)
  {
    $is = clone $this->_stream;
    $is->setKeyCache($this);
    $is->setNsKey($nsKey);
    $is->setItemKey($itemKey);
    if (isset($writeThrough))
    {
      $is->setWriteThroughStream($writeThrough);
    }
    return $is;
  }

  /**
   * Get data back out of the cache as a string.
   * @param string $nsKey
   * @param string $itemKey
   * @return string
   * @throws Swift_IoException
   */
  public function getString($nsKey, $itemKey)
  {
    $this->_prepareCache($nsKey);
    if ($this->hasKey($nsKey, $itemKey))
    {
      $fp = $this->_getHandle($nsKey, $itemKey, self::POSITION_START);
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(0);
      }
      $str = '';
      while (!feof($fp) && false !== $bytes = fread($fp, 8192))
      {
        $str .= $bytes;
      }
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(1);
      }
      return $str;
    }
  }

  /**
   * Get data back out of the cache as a ByteStream.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_InputByteStream $is to write the data to
   */
  public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is)
  {
    if ($this->hasKey($nsKey, $itemKey))
    {
      $fp = $this->_getHandle($nsKey, $itemKey, self::POSITION_START);
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(0);
      }
      while (!feof($fp) && false !== $bytes = fread($fp, 8192))
      {
        $is->write($bytes);
      }
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(1);
      }
    }
  }

  /**
   * Check if the given $itemKey exists in the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @return boolean
   */
  public function hasKey($nsKey, $itemKey)
  {
    return is_file($this->_path . '/' . $nsKey . '/' . $itemKey);
  }

  /**
   * Clear data for $itemKey in the namespace $nsKey if it exists.
   * @param string $nsKey
   * @param string $itemKey
   */
  public function clearKey($nsKey, $itemKey)
  {
    if ($this->hasKey($nsKey, $itemKey))
    {
      $fp = $this->_getHandle($nsKey, $itemKey, self::POSITION_END);
      fclose($fp);
      unlink($this->_path . '/' . $nsKey . '/' . $itemKey);
    }
    unset($this->_keys[$nsKey][$itemKey]);
  }

  /**
   * Clear all data in the namespace $nsKey if it exists.
   * @param string $nsKey
   */
  public function clearAll($nsKey)
  {
    if (array_key_exists($nsKey, $this->_keys))
    {
      foreach ($this->_keys[$nsKey] as $itemKey=>$null)
      {
        $this->clearKey($nsKey, $itemKey);
      }
      rmdir($this->_path . '/' . $nsKey);
      unset($this->_keys[$nsKey]);
    }
  }

  // -- Private methods

  /**
   * Initialize the namespace of $nsKey if needed.
   * @param string $nsKey
   * @access private
   */
  private function _prepareCache($nsKey)
  {
    $cacheDir = $this->_path . '/' . $nsKey;
    if (!is_dir($cacheDir))
    {
      if (!mkdir($cacheDir))
      {
        throw new Swift_IoException('Failed to create cache directory ' . $cacheDir);
      }
      $this->_keys[$nsKey] = array();
    }
  }

  /**
   * Get a file handle on the cache item.
   * @param string $nsKey
   * @param string $itemKey
   * @param int $position
   * @return resource
   * @access private
   */
  private function _getHandle($nsKey, $itemKey, $position)
  {
    if (!isset($this->_keys[$nsKey]) || !array_key_exists($itemKey, $this->_keys[$nsKey]))
    {
      $fp = fopen($this->_path . '/' . $nsKey . '/' . $itemKey, 'w+b');
      $this->_keys[$nsKey][$itemKey] = $fp;
    }
    if (self::POSITION_START == $position)
    {
      fseek($this->_keys[$nsKey][$itemKey], 0, SEEK_SET);
    }
    else
    {
      fseek($this->_keys[$nsKey][$itemKey], 0, SEEK_END);
    }
    return $this->_keys[$nsKey][$itemKey];
  }

  /**
   * Destructor.
   */
  public function __destruct()
  {
    foreach ($this->_keys as $nsKey=>$null)
    {
      $this->clearAll($nsKey);
    }
  }

}
 /* .tmp\flat\1\EchoLogger.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\EchoLogger.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Prints all log messages in real time.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Plugins_Loggers_EchoLogger implements Swift_Plugins_Logger
{
  
  /** Whether or not HTML should be output */
  private $_isHtml;
  
  /**
   * Create a new EchoLogger.
   * 
   * @param boolean $isHtml
   */
  public function __construct($isHtml = true)
  {
    $this->_isHtml = $isHtml;
  }
  
  /**
   * Add a log entry.
   * @param string $entry
   */
  public function add($entry)
  {
    if ($this->_isHtml)
    {
      printf('%s%s%s', htmlspecialchars($entry, ENT_QUOTES), '<br />', PHP_EOL);
    }
    else
    {
      printf('%s%s', $entry, PHP_EOL);
    }
  }
  
  /**
   * Not implemented.
   */
  public function clear()
  {
  }
  
  /**
   * Not implemented.
   */
  public function dump()
  {
  }
  
}
 /* .tmp\flat\1\Encoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\Encoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/CharsetObserver.php';

/**
 * Interface for all Encoder schemes.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
interface Swift_Encoder extends Swift_Mime_CharsetObserver
{
  
  /**
   * Encode a given string to produce an encoded string.
   * @param string $string
   * @param int $firstLineOffset if first line needs to be shorter
   * @param int $maxLineLength - 0 indicates the default length for this encoding
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0);
  
}
 /* .tmp\flat\1\EsmtpTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\EsmtpTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/AbstractSmtpTransport.php';
//@require 'Swift/Transport/EsmtpHandler.php';
//@require 'Swift/Transport/IoBuffer.php';
//@require 'Swift/Transport/SmtpAgent.php';
//@require 'Swift/TransportException.php';
//@require 'Swift/Mime/Message.php';
//@require 'Swift/Events/EventDispatcher.php';

/**
 * Sends Messages over SMTP with ESMTP support.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_EsmtpTransport
  extends Swift_Transport_AbstractSmtpTransport
  implements Swift_Transport_SmtpAgent
{
  
  /**
   * ESMTP extension handlers.
   * @var Swift_Transport_EsmtpHandler[]
   * @access private
   */
  private $_handlers = array();
  
  /**
   * ESMTP capabilities.
   * @var string[]
   * @access private
   */
  private $_capabilities = array();
  
  /**
   * Connection buffer parameters.
   * @var array
   * @access protected
   */
  private $_params = array(
    'protocol' => 'tcp',
    'host' => 'localhost',
    'port' => 25,
    'timeout' => 30,
    'blocking' => 1,
    'type' => Swift_Transport_IoBuffer::TYPE_SOCKET
    );
  
  /**
   * Creates a new EsmtpTransport using the given I/O buffer.
   * @param Swift_Transport_IoBuffer $buf
   * @param Swift_Transport_EsmtpHandler[] $extensionHandlers
   * @param Swift_Events_EventDispatcher $dispatcher
   */
  public function __construct(Swift_Transport_IoBuffer $buf,
    array $extensionHandlers, Swift_Events_EventDispatcher $dispatcher)
  {
    parent::__construct($buf, $dispatcher);
    $this->setExtensionHandlers($extensionHandlers);
  }
  
  /**
   * Set the host to connect to.
   * @param string $host
   */
  public function setHost($host)
  {
    $this->_params['host'] = $host;
    return $this;
  }
  
  /**
   * Get the host to connect to.
   * @return string
   */
  public function getHost()
  {
    return $this->_params['host'];
  }
  
  /**
   * Set the port to connect to.
   * @param int $port
   */
  public function setPort($port)
  {
    $this->_params['port'] = (int) $port;
    return $this;
  }
  
  /**
   * Get the port to connect to.
   * @return int
   */
  public function getPort()
  {
    return $this->_params['port'];
  }
  
  /**
   * Set the connection timeout.
   * @param int $timeout seconds
   */
  public function setTimeout($timeout)
  {
    $this->_params['timeout'] = (int) $timeout;
    return $this;
  }
  
  /**
   * Get the connection timeout.
   * @return int
   */
  public function getTimeout()
  {
    return $this->_params['timeout'];
  }
  
  /**
   * Set the encryption type (tls or ssl)
   * @param string $encryption
   */
  public function setEncryption($enc)
  {
    $this->_params['protocol'] = $enc;
    return $this;
  }
  
  /**
   * Get the encryption type.
   * @return string
   */
  public function getEncryption()
  {
    return $this->_params['protocol'];
  }
  
  /**
   * Set ESMTP extension handlers.
   * @param Swift_Transport_EsmtpHandler[] $handlers
   */
  public function setExtensionHandlers(array $handlers)
  {
    $assoc = array();
    foreach ($handlers as $handler)
    {
      $assoc[$handler->getHandledKeyword()] = $handler;
    }
    uasort($assoc, array($this, '_sortHandlers'));
    $this->_handlers = $assoc;
    $this->_setHandlerParams();
    return $this;
  }
  
  /**
   * Get ESMTP extension handlers.
   * @return Swift_Transport_EsmtpHandler[]
   */
  public function getExtensionHandlers()
  {
    return array_values($this->_handlers);
  }
  
  /**
   * Run a command against the buffer, expecting the given response codes.
   * If no response codes are given, the response will not be validated.
   * If codes are given, an exception will be thrown on an invalid response.
   * @param string $command
   * @param int[] $codes
   * @param string[] &$failures
   * @return string
   */
  public function executeCommand($command, $codes = array(), &$failures = null)
  {
    $failures = (array) $failures;
    $stopSignal = false;
    $response = null;
    foreach ($this->_getActiveHandlers() as $handler)
    {
      $response = $handler->onCommand(
        $this, $command, $codes, $failures, $stopSignal
        );
      if ($stopSignal)
      {
        return $response;
      }
    }
    return parent::executeCommand($command, $codes, $failures);
  }
  
  // -- Mixin invocation code
  
  /** Mixin handling method for ESMTP handlers */
  public function __call($method, $args)
  {
    foreach ($this->_handlers as $handler)
    {
      if (in_array(strtolower($method),
        array_map('strtolower', (array) $handler->exposeMixinMethods())
        ))
      {
        $return = call_user_func_array(array($handler, $method), $args);
        //Allow fluid method calls
        if (is_null($return) && substr($method, 0, 3) == 'set')
        {
          return $this;
        }
        else
        {
          return $return;
        }
      }
    }
    trigger_error('Call to undefined method ' . $method, E_USER_ERROR);
  }
  
  // -- Protected methods
  
  /** Get the params to initialize the buffer */
  protected function _getBufferParams()
  {
    return $this->_params;
  }
  
  /** Overridden to perform EHLO instead */
  protected function _doHeloCommand()
  {
    try
    {
      $response = $this->executeCommand(
        sprintf("EHLO %s\r\n", $this->_domain), array(250)
        );
    }
    catch (Swift_TransportException $e)
    {
      return parent::_doHeloCommand();
    }

    $this->_capabilities = $this->_getCapabilities($response);
    $this->_setHandlerParams();
    foreach ($this->_getActiveHandlers() as $handler)
    {
      $handler->afterEhlo($this);
    }
  }
  
  /** Overridden to add Extension support */
  protected function _doMailFromCommand($address)
  {
    $handlers = $this->_getActiveHandlers();
    $params = array();
    foreach ($handlers as $handler)
    {
      $params = array_merge($params, (array) $handler->getMailParams());
    }
    $paramStr = !empty($params) ? ' ' . implode(' ', $params) : '';
    $this->executeCommand(
      sprintf("MAIL FROM: <%s>%s\r\n", $address, $paramStr), array(250)
      );
  }
  
  /** Overridden to add Extension support */
  protected function _doRcptToCommand($address)
  {
    $handlers = $this->_getActiveHandlers();
    $params = array();
    foreach ($handlers as $handler)
    {
      $params = array_merge($params, (array) $handler->getRcptParams());
    }
    $paramStr = !empty($params) ? ' ' . implode(' ', $params) : '';
    $this->executeCommand(
      sprintf("RCPT TO: <%s>%s\r\n", $address, $paramStr), array(250, 251, 252)
      );
  }
  
  // -- Private methods
  
  /** Determine ESMTP capabilities by function group */
  private function _getCapabilities($ehloResponse)
  {
    $capabilities = array();
    $ehloResponse = trim($ehloResponse);
    $lines = explode("\r\n", $ehloResponse);
    array_shift($lines);
    foreach ($lines as $line)
    {
      if (preg_match('/^[0-9]{3}[ -]([A-Z0-9-]+)((?:[ =].*)?)$/Di', $line, $matches))
      {
        $keyword = strtoupper($matches[1]);
        $paramStr = strtoupper(ltrim($matches[2], ' ='));
        $params = !empty($paramStr) ? explode(' ', $paramStr) : array();
        $capabilities[$keyword] = $params;
      }
    }
    return $capabilities;
  }
  
  /** Set parameters which are used by each extension handler */
  private function _setHandlerParams()
  {
    foreach ($this->_handlers as $keyword => $handler)
    {
      if (array_key_exists($keyword, $this->_capabilities))
      {
        $handler->setKeywordParams($this->_capabilities[$keyword]);
      }
    }
  }
  
  /** Get ESMTP handlers which are currently ok to use */
  private function _getActiveHandlers()
  {
    $handlers = array();
    foreach ($this->_handlers as $keyword => $handler)
    {
      if (array_key_exists($keyword, $this->_capabilities))
      {
        $handlers[] = $handler;
      }
    }
    return $handlers;
  }
  
  /** Custom sort for extension handler ordering */
  private function _sortHandlers($a, $b)
  {
    return $a->getPriorityOver($b->getHandledKeyword());
  }
  
}
 /* .tmp\flat\1\EventObject.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\EventObject.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/Event.php';

/**
 * A base Event which all Event classes inherit from.
 * 
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_EventObject implements Swift_Events_Event
{
  
  /** The source of this Event */
  private $_source;
  
  /** The state of this Event (should it bubble up the stack?) */
  private $_bubbleCancelled = false;
  
  /**
   * Create a new EventObject originating at $source.
   * @param object $source
   */
  public function __construct($source)
  {
    $this->_source = $source;
  }
  
  /**
   * Get the source object of this event.
   * @return object
   */
  public function getSource()
  {
    return $this->_source;
  }
  
  /**
   * Prevent this Event from bubbling any further up the stack.
   * @param boolean $cancel, optional
   */
  public function cancelBubble($cancel = true)
  {
    $this->_bubbleCancelled = $cancel;
  }
  
  /**
   * Returns true if this Event will not bubble any further up the stack.
   * @return boolean
   */
  public function bubbleCancelled()
  {
    return $this->_bubbleCancelled;
  }
  
}
 /* .tmp\flat\1\FileStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\FileStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/OutputByteStream.php';

/**
 * An OutputByteStream which specifically reads from a file.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
interface Swift_FileStream extends Swift_OutputByteStream
{
  
  /**
   * Get the complete path to the file.
   * @return string
   */
  public function getPath();
  
}
 /* .tmp\flat\1\GenericFixedWidthReader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\GenericFixedWidthReader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/CharacterReader.php';

/**
 * Provides fixed-width byte sizes for reading fixed-width character sets.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 * @author Xavier De Cock <xdecock@gmail.com>
 */
class Swift_CharacterReader_GenericFixedWidthReader
  implements Swift_CharacterReader
{
   
  /**
   * The number of bytes in a single character.
   * @var int
   * @access private
   */
  private $_width;
	
  /**
   * Creates a new GenericFixedWidthReader using $width bytes per character.
   * @param int $width
   */
  public function __construct($width)
  {
    $this->_width = $width;
  }

  /**
   * Returns the complete charactermap
   *
   * @param string $string
   * @param int $startOffset
   * @param array $currentMap
   * @param mixed $ignoredChars
   * @return $int
   */
  public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars)
  {
  	$strlen = strlen($string);
  	// % and / are CPU intensive, so, maybe find a better way
  	$ignored = $strlen%$this->_width;
  	$ignoredChars = substr($string, - $ignored);
  	$currentMap = $this->_width;
  	return ($strlen - $ignored)/$this->_width;
  	
  }
  
  /**
   * Returns mapType
   * @int mapType
   */
  public function getMapType()
  {
  	return self::MAP_TYPE_FIXED_LEN;
  }

  /**
   * Returns an integer which specifies how many more bytes to read.
   * A positive integer indicates the number of more bytes to fetch before invoking
   * this method again.
   * A value of zero means this is already a valid character.
   * A value of -1 means this cannot possibly be a valid character.
   * @param string $bytes
   * @return int
   */
  public function validateByteSequence($bytes, $size)
  {
    $needed = $this->_width - $size;
    return ($needed > -1)
      ? $needed
      : -1
      ;
  }

  /**
   * Returns the number of bytes which should be read to start each character.
   * @return int
   */
  public function getInitialByteSize()
  {
    return $this->_width;
  }

}
 /* .tmp\flat\1\HeaderFactory.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\HeaderFactory.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/CharsetObserver.php';

/**
 * Creates MIME headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_HeaderFactory extends Swift_Mime_CharsetObserver
{
  
  /**
   * Create a new Mailbox Header with a list of $addresses.
   * @param string $name
   * @param array|string $addresses
   * @return Swift_Mime_Header
   */
  public function createMailboxHeader($name, $addresses = null);
  
  /**
   * Create a new Date header using $timestamp (UNIX time).
   * @param string $name
   * @param int $timestamp
   * @return Swift_Mime_Header
   */
  public function createDateHeader($name, $timestamp = null);
  
  /**
   * Create a new basic text header with $name and $value.
   * @param string $name
   * @param string $value
   * @return Swift_Mime_Header
   */
  public function createTextHeader($name, $value = null);
  
  /**
   * Create a new ParameterizedHeader with $name, $value and $params.
   * @param string $name
   * @param string $value
   * @param array $params
   * @return Swift_Mime_ParameterizedHeader
   */
  public function createParameterizedHeader($name, $value = null,
    $params = array());
  
  /**
   * Create a new ID header for Message-ID or Content-ID.
   * @param string $name
   * @param string|array $ids
   * @return Swift_Mime_Header
   */
  public function createIdHeader($name, $ids = null);
  
  /**
   * Create a new Path header with an address (path) in it.
   * @param string $name
   * @param string $path
   * @return Swift_Mime_Header
   */
  public function createPathHeader($name, $path = null);
  
}
 /* .tmp\flat\1\HeaderSet.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\HeaderSet.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/CharsetObserver.php';

/**
 * A collection of MIME headers.
 * 
 * @package Swift
 * @subpackage Mime
 * 
 * @author Chris Corbyn
 */
interface Swift_Mime_HeaderSet extends Swift_Mime_CharsetObserver
{
  
  /**
   * Add a new Mailbox Header with a list of $addresses.
   * 
   * @param string $name
   * @param array|string $addresses
   */
  public function addMailboxHeader($name, $addresses = null);
  
  /**
   * Add a new Date header using $timestamp (UNIX time).
   * 
   * @param string $name
   * @param int $timestamp
   */
  public function addDateHeader($name, $timestamp = null);
  
  /**
   * Add a new basic text header with $name and $value.
   * 
   * @param string $name
   * @param string $value
   */
  public function addTextHeader($name, $value = null);
  
  /**
   * Add a new ParameterizedHeader with $name, $value and $params.
   * 
   * @param string $name
   * @param string $value
   * @param array $params
   */
  public function addParameterizedHeader($name, $value = null,
    $params = array());
  
  /**
   * Add a new ID header for Message-ID or Content-ID.
   * 
   * @param string $name
   * @param string|array $ids
   */
  public function addIdHeader($name, $ids = null);
  
  /**
   * Add a new Path header with an address (path) in it.
   * 
   * @param string $name
   * @param string $path
   */
  public function addPathHeader($name, $path = null);
  
  /**
   * Returns true if at least one header with the given $name exists.
   * 
   * If multiple headers match, the actual one may be specified by $index.
   * 
   * @param string $name
   * @param int $index
   * 
   * @return boolean
   */
  public function has($name, $index = 0);
  
  /**
   * Set a header in the HeaderSet.
   * 
   * The header may be a previously fetched header via {@link get()} or it may
   * be one that has been created separately.
   * 
   * If $index is specified, the header will be inserted into the set at this
   * offset.
   * 
   * @param Swift_Mime_Header $header
   * @param int $index
   */
  public function set(Swift_Mime_Header $header, $index = 0);
  
  /**
   * Get the header with the given $name.
   * If multiple headers match, the actual one may be specified by $index.
   * Returns NULL if none present.
   * 
   * @param string $name
   * @param int $index
   * 
   * @return Swift_Mime_Header
   */
  public function get($name, $index = 0);
  
  /**
   * Get all headers with the given $name.
   * 
   * @param string $name
   * 
   * @return array
   */
  public function getAll($name = null);
  
  /**
   * Remove the header with the given $name if it's set.
   * 
   * If multiple headers match, the actual one may be specified by $index.
   * 
   * @param string $name
   * @param int $index
   */
  public function remove($name, $index = 0);
  
  /**
   * Remove all headers with the given $name.
   * 
   * @param string $name
   */
  public function removeAll($name);
  
  /**
   * Create a new instance of this HeaderSet.
   * 
   * @return Swift_Mime_HeaderSet
   */
  public function newInstance();
  
  /**
   * Define a list of Header names as an array in the correct order.
   * 
   * These Headers will be output in the given order where present.
   * 
   * @param array $sequence
   */
  public function defineOrdering(array $sequence);
  
  /**
   * Set a list of header names which must always be displayed when set.
   * 
   * Usually headers without a field value won't be output unless set here.
   * 
   * @param array $names
   */
  public function setAlwaysDisplayed(array $names);
  
  /**
   * Returns a string with a representation of all headers.
   * 
   * @return string
   */
  public function toString();
  
}
 /* .tmp\flat\1\HitReporter.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\HitReporter.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Plugins/Reporter.php';
//@require 'Swift/Mime/Message.php';

/**
 * A reporter which "collects" failures for the Reporter plugin.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_Reporters_HitReporter implements Swift_Plugins_Reporter
{
  
  /**
   * The list of failures.
   * @var array
   * @access private
   */
  private $_failures = array();
  private $_failures_cache = array();
  
  /**
   * Notifies this ReportNotifier that $address failed or succeeded.
   * @param Swift_Mime_Message $message
   * @param string $address
   * @param int $result from {@link RESULT_PASS, RESULT_FAIL}
   */
  public function notify(Swift_Mime_Message $message, $address, $result)
  {
    if (self::RESULT_FAIL == $result && !isset($this->_failures_cache[$address]))
    {
      $this->_failures[] = $address;
      $this->_failures_cache[$address] = true;
    }
  }
  
  /**
   * Get an array of addresses for which delivery failed.
   * @return array
   */
  public function getFailedRecipients()
  {
    return $this->_failures;
  }
  
  /**
   * Clear the buffer (empty the list).
   */
  public function clear()
  {
    $this->_failures = $this->_failures_cache = array();
  }
  
}
 /* .tmp\flat\1\HtmlReporter.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\HtmlReporter.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Plugins/Reporter.php';
//@require 'Swift/Mime/Message.php';

/**
 * A HTML output reporter for the Reporter plugin.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_Reporters_HtmlReporter implements Swift_Plugins_Reporter
{
  
  /**
   * Notifies this ReportNotifier that $address failed or succeeded.
   * @param Swift_Mime_Message $message
   * @param string $address
   * @param int $result from {@link RESULT_PASS, RESULT_FAIL}
   */
  public function notify(Swift_Mime_Message $message, $address, $result)
  {
    if (self::RESULT_PASS == $result)
    {
      echo "<div style=\"color: #fff; background: #006600; padding: 2px; margin: 2px;\">" . PHP_EOL;
      echo "PASS " . $address . PHP_EOL;
      echo "</div>" . PHP_EOL;
      flush();
    }
    else
    {
      echo "<div style=\"color: #fff; background: #880000; padding: 2px; margin: 2px;\">" . PHP_EOL;
      echo "FAIL " . $address . PHP_EOL;
      echo "</div>" . PHP_EOL;
      flush();
    }
  }
  
}
 /* .tmp\flat\1\IoBuffer.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\IoBuffer.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Buffers input and output to a resource.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport_IoBuffer
  extends Swift_InputByteStream, Swift_OutputByteStream
{
  
  /** A socket buffer over TCP */
  const TYPE_SOCKET = 0x0001;
  
  /** A process buffer with I/O support */
  const TYPE_PROCESS = 0x0010;
  
  /**
   * Perform any initialization needed, using the given $params.
   * Parameters will vary depending upon the type of IoBuffer used.
   * @param array $params
   */
  public function initialize(array $params);
  
  /**
   * Set an individual param on the buffer (e.g. switching to SSL).
   * @param string $param
   * @param mixed $value
   */
  public function setParam($param, $value);
  
  /**
   * Perform any shutdown logic needed.
   */
  public function terminate();
  
  /**
   * Set an array of string replacements which should be made on data written
   * to the buffer.  This could replace LF with CRLF for example.
   * @param string[] $replacements
   */
  public function setWriteTranslations(array $replacements);
  
  /**
   * Get a line of output (including any CRLF).
   * The $sequence number comes from any writes and may or may not be used
   * depending upon the implementation.
   * @param int $sequence of last write to scan from
   * @return string
   */
  public function readLine($sequence);
  
}
 /* .tmp\flat\1\IoException.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\IoException.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/SwiftException.php';

/**
 * I/O Exception class.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_IoException extends Swift_SwiftException
{
  
  /**
   * Create a new IoException with $message.
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
 /* .tmp\flat\1\KeyCacheInputStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\KeyCacheInputStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/KeyCache.php';
//@require 'Swift/InputByteStream.php';

/**
 * Writes data to a KeyCache using a stream.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
interface Swift_KeyCache_KeyCacheInputStream extends Swift_InputByteStream
{
  
  /**
   * Set the KeyCache to wrap.
   * @param Swift_KeyCache $keyCache
   */
  public function setKeyCache(Swift_KeyCache $keyCache);
  
  /**
   * Set the nsKey which will be written to.
   * @param string $nsKey
   */
  public function setNsKey($nsKey);
  
  /**
   * Set the itemKey which will be written to.
   * @param string $itemKey
   */
  public function setItemKey($itemKey);
  
  /**
   * Specify a stream to write through for each write().
   * @param Swift_InputByteStream $is
   */
  public function setWriteThroughStream(Swift_InputByteStream $is);
  
  /**
   * Any implementation should be cloneable, allowing the clone to access a
   * separate $nsKey and $itemKey.
   */
  public function __clone();
  
}
 /* .tmp\flat\1\LoadBalancedTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\LoadBalancedTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport.php';
//@require 'Swift/Mime/Message.php';
//@require 'Swift/Events/EventListener.php';

/**
 * Redudantly and rotationally uses several Transports when sending.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_LoadBalancedTransport implements Swift_Transport
{
  
  /** Transports which are deemed useless */
  private $_deadTransports = array();
  
  /**
   * The Transports which are used in rotation.
   * 
   * @var array Swift_Transport
   * @access protected
   */
  protected $_transports = array();
  
  /**
   * Creates a new LoadBalancedTransport.
   */
  public function __construct()
  {
  }
  
  /**
   * Set $transports to delegate to.
   * 
   * @param array $transports Swift_Transport
   */
  public function setTransports(array $transports)
  {
    $this->_transports = $transports;
    $this->_deadTransports = array();
  }
  
  /**
   * Get $transports to delegate to.
   * 
   * @return array Swift_Transport
   */
  public function getTransports(array $transports)
  {
    return array_merge($this->_transports, $this->_deadTransports);
  }
  
  /**
   * Test if this Transport mechanism has started.
   * 
   * @return boolean
   */
  public function isStarted()
  {
    return count($this->_transports) > 0;
  }
  
  /**
   * Start this Transport mechanism.
   */
  public function start()
  {
    $this->_transports = array_merge($this->_transports, $this->_deadTransports);
  }
  
  /**
   * Stop this Transport mechanism.
   */
  public function stop()
  {
    foreach ($this->_transports as $transport)
    {
      $transport->stop();
    }
  }
  
  /**
   * Send the given Message.
   * 
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $maxTransports = count($this->_transports);
    $sent = 0;
    
    for ($i = 0; $i < $maxTransports
      && $transport = $this->_getNextTransport(); ++$i)
    {
      try
      {
        if (!$transport->isStarted())
        {
          $transport->start();
        }
        if ($sent = $transport->send($message, $failedRecipients))
        {
          break;
        }
      }
      catch (Swift_TransportException $e)
      {
        $this->_killCurrentTransport();
      }
    }
    
    if (count($this->_transports) == 0)
    {
      throw new Swift_TransportException(
        'All Transports in LoadBalancedTransport failed, or no Transports available'
        );
    }
    
    return $sent;
  }
  
  /**
   * Register a plugin.
   * 
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    foreach ($this->_transports as $transport)
    {
      $transport->registerPlugin($plugin);
    }
  }
  
  // -- Protected methods
  
  /**
   * Rotates the transport list around and returns the first instance.
   * 
   * @return Swift_Transport
   * @access protected
   */
  protected function _getNextTransport()
  {
    if ($next = array_shift($this->_transports))
    {
      $this->_transports[] = $next;
    }
    return $next;
  }
  
  /**
   * Tag the currently used (top of stack) transport as dead/useless.
   * 
   * @access protected
   */
  protected function _killCurrentTransport()
  {
    if ($transport = array_pop($this->_transports))
    {
      try
      {
        $transport->stop();
      }
      catch (Exception $e)
      {
      }
      $this->_deadTransports[] = $transport;
    }
  }
  
}
 /* .tmp\flat\1\MailTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\MailTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport.php';
//@require 'Swift/Transport/MailInvoker.php';
//@require 'Swift/Mime/Message.php';
//@require 'Swift/Events/EventListener.php';

/**
 * Sends Messages using the mail() function.
 * 
 * It is advised that users do not use this transport if at all possible
 * since a number of plugin features cannot be used in conjunction with this
 * transport due to the internal interface in PHP itself.
 * 
 * The level of error reporting with this transport is incredibly weak, again
 * due to limitations of PHP's internal mail() function.  You'll get an
 * all-or-nothing result from sending.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_MailTransport implements Swift_Transport
{

  /** Addtional parameters to pass to mail() */
  private $_extraParams = '-f%s';
  
  /** The event dispatcher from the plugin API */
  private $_eventDispatcher;
  
  /** An invoker that calls the mail() function */
  private $_invoker;
  
  /**
   * Create a new MailTransport with the $log.
   * @param Swift_Transport_Log $log
   */
  public function __construct(Swift_Transport_MailInvoker $invoker,
    Swift_Events_EventDispatcher $eventDispatcher)
  {
    $this->_invoker = $invoker;
    $this->_eventDispatcher = $eventDispatcher;
  }
  
  /**
   * Not used.
   */
  public function isStarted()
  {
    return false;
  }
  
  /**
   * Not used.
   */
  public function start()
  {
  }
  
  /**
   * Not used.
   */
  public function stop()
  {
  }
  
  /**
   * Set the additional parameters used on the mail() function.
   * 
   * This string is formatted for sprintf() where %s is the sender address.
   * 
   * @param string $params
   */
  public function setExtraParams($params)
  {
    $this->_extraParams = $params;
    return $this;
  }
  
  /**
   * Get the additional parameters used on the mail() function.
   * 
   * This string is formatted for sprintf() where %s is the sender address.
   * 
   * @return string
   */
  public function getExtraParams()
  {
    return $this->_extraParams;
  }
  
  /**
   * Send the given Message.
   * 
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $failedRecipients = (array) $failedRecipients;
    
    if ($evt = $this->_eventDispatcher->createSendEvent($this, $message))
    {
      $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
      if ($evt->bubbleCancelled())
      {
        return 0;
      }
    }
    
    $count = (
      count((array) $message->getTo())
      + count((array) $message->getCc())
      + count((array) $message->getBcc())
      );
    
    $toHeader = $message->getHeaders()->get('To');
    $subjectHeader = $message->getHeaders()->get('Subject');
    
    $to = $toHeader->getFieldBody();
    $subject = $subjectHeader->getFieldBody();
    
    $reversePath = $this->_getReversePath($message);
    
    //Remove headers that would otherwise be duplicated
    $message->getHeaders()->remove('To');
    $message->getHeaders()->remove('Subject');
    
    $messageStr = $message->toString();
    
    $message->getHeaders()->set($toHeader);
    $message->getHeaders()->set($subjectHeader);
    
    //Separate headers from body
    if (false !== $endHeaders = strpos($messageStr, "\r\n\r\n"))
    {
      $headers = substr($messageStr, 0, $endHeaders) . "\r\n"; //Keep last EOL
      $body = substr($messageStr, $endHeaders + 4);
    }
    else
    {
      $headers = $messageStr . "\r\n";
      $body = '';
    }
    
    unset($messageStr);
    
    if ("\r\n" != PHP_EOL) //Non-windows (not using SMTP)
    {
      $headers = str_replace("\r\n", PHP_EOL, $headers);
      $body = str_replace("\r\n", PHP_EOL, $body);
    }
    else //Windows, using SMTP
    {
      $headers = str_replace("\r\n.", "\r\n..", $headers);
      $body = str_replace("\r\n.", "\r\n..", $body);
    }
    
    if ($this->_invoker->mail($to, $subject, $body, $headers,
      sprintf($this->_extraParams, $reversePath)))
    {
      if ($evt)
      {
        $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
        $evt->setFailedRecipients($failedRecipients);
        $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
      }
    }
    else
    {
      $failedRecipients = array_merge(
        $failedRecipients,
        array_keys((array) $message->getTo()),
        array_keys((array) $message->getCc()),
        array_keys((array) $message->getBcc())
        );
      
      if ($evt)
      {
        $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
        $evt->setFailedRecipients($failedRecipients);
        $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
      }
      
      $message->generateId();
      
      $count = 0;
    }
    
    return $count;
  }
  
  /**
   * Register a plugin.
   * 
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->_eventDispatcher->bindEventListener($plugin);
  }
  
  // -- Private methods
  
  /** Determine the best-use reverse path for this message */
  private function _getReversePath(Swift_Mime_Message $message)
  {
    $return = $message->getReturnPath();
    $sender = $message->getSender();
    $from = $message->getFrom();
    $path = null;
    if (!empty($return))
    {
      $path = $return;
    }
    elseif (!empty($sender))
    {
      $keys = array_keys($sender);
      $path = array_shift($keys);
    }
    elseif (!empty($from))
    {
      $keys = array_keys($from);
      $path = array_shift($keys);
    }
    return $path;
  }
  
}
 /* .tmp\flat\1\MimeEntity.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\MimeEntity.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/InputByteStream.php';
//@require 'Swift/Mime/EncodingObserver.php';
//@require 'Swift/Mime/CharsetObserver.php';

/**
 * A MIME entity, such as an attachment.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_MimeEntity
  extends Swift_Mime_CharsetObserver, Swift_Mime_EncodingObserver
{
  
  /** Main message document; there can only be one of these */
  const LEVEL_TOP = 16;
  
  /** An entity which nests with the same precedence as an attachment */
  const LEVEL_MIXED = 256;
  
  /** An entity which nests with the same precedence as a mime part */
  const LEVEL_ALTERNATIVE = 4096;
  
  /** An entity which nests with the same precedence as embedded content */
  const LEVEL_RELATED = 65536;
  
  /**
   * Get the level at which this entity shall be nested in final document.
   * The lower the value, the more outermost the entity will be nested.
   * @return int
   * @see LEVEL_TOP, LEVEL_MIXED, LEVEL_RELATED, LEVEL_ALTERNATIVE
   */
  public function getNestingLevel();
  
  /**
   * Get the qualified content-type of this mime entity.
   * @return string
   */
  public function getContentType();
  
  /**
   * Returns a unique ID for this entity.
   * For most entities this will likely be the Content-ID, though it has
   * no explicit semantic meaning and can be considered an identifier for
   * programming logic purposes.
   * If a Content-ID header is present, this value SHOULD match the value of
   * the header.
   * @return string
   */
  public function getId();
  
  /**
   * Get all children nested inside this entity.
   * These are not just the immediate children, but all children.
   * @return Swift_Mime_MimeEntity[]
   */
  public function getChildren();
  
  /**
   * Set all children nested inside this entity.
   * This includes grandchildren.
   * @param Swift_Mime_MimeEntity[] $children
   */
  public function setChildren(array $children);
  
  /**
   * Get the collection of Headers in this Mime entity.
   * @return Swift_Mime_Header[]
   */
  public function getHeaders();
  
  /**
   * Get the body content of this entity as a string.
   * Returns NULL if no body has been set.
   * @return string
   */
  public function getBody();
  
  /**
   * Set the body content of this entity as a string.
   * @param string $body
   * @param string $contentType optional
   */
  public function setBody($body, $contentType = null);
  
  /**
   * Get this entire entity in its string form.
   * @return string
   */
  public function toString();
  
  /**
   * Get this entire entity as a ByteStream.
   * @param Swift_InputByteStream $is to write to
   */
  public function toByteStream(Swift_InputByteStream $is);
  
}
 /* .tmp\flat\1\NgCharacterStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\NgCharacterStream.php */ ?>
<?php

/*
 CharacterStream implementation using an array in Swift Mailer.

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

//@require 'Swift/CharacterStream.php';
//@require 'Swift/OutputByteStream.php';


/**
 * A CharacterStream implementation which stores characters in an internal array.
 * @package Swift
 * @subpackage CharacterStream
 * @author Xavier De Cock <xdecock@gmail.com>
 */

Class Swift_CharacterStream_NgCharacterStream
  implements Swift_CharacterStream
{

  /**
   * The char reader (lazy-loaded) for the current charset.
   * @var Swift_CharacterReader
   * @access private
   */
  private $_charReader;

  /**
   * A factory for creatiing CharacterReader instances.
   * @var Swift_CharacterReaderFactory
   * @access private
   */
  private $_charReaderFactory;

  /**
   * The character set this stream is using.
   * @var string
   * @access private
   */
  private $_charset;
  
  /**
   * The datas stored as is
   *
   * @var string
   */
  private $_datas = "";
  
  /**
   * Number of bytes in the stream
   *
   * @var int
   */
  private $_datasSize = 0;
  
  /**
   * Map
   *
   * @var mixed
   */
  private $_map;
  
  /**
   * Map Type
   *
   * @var int
   */
  private $_mapType = 0;
  
  /**
   * Number of characters in the stream
   *
   * @var int
   */
  private $_charCount = 0;
  
  /**
   * Position in the stream
   *
   * @var unknown_type
   */
  private $_currentPos = 0;
  
  /**
   * The constructor
   *
   * @param Swift_CharacterReaderFactory $factory
   * @param unknown_type $charset
   */
  public function __construct(Swift_CharacterReaderFactory $factory,
    $charset)
  {
    $this->setCharacterReaderFactory($factory);
    $this->setCharacterSet($charset);
  }
  
  /* -- Changing parameters of the stream -- */

  /**
   * Set the character set used in this CharacterStream.
   * @param string $charset
   */
  public function setCharacterSet($charset)
  {
    $this->_charset = $charset;
    $this->_charReader = null;
  	$this->_mapType = 0;
  }

  /**
   * Set the CharacterReaderFactory for multi charset support.
   * @param Swift_CharacterReaderFactory $factory
   */
  public function setCharacterReaderFactory(
    Swift_CharacterReaderFactory $factory)
  {
    $this->_charReaderFactory = $factory;
  }

  /**
   * @see Swift_CharacterStream::flushContents()
   *
   */
  public function flushContents()
  {
  	$this->_datas = null;
  	$this->_map = null;
  	$this->_charCount = 0;
  	$this->_currentPos = 0;
  	$this->_datasSize = 0;
  }
  
  /**
   * @see Swift_CharacterStream::importByteStream()
   *
   * @param Swift_OutputByteStream $os
   */
  public function importByteStream(Swift_OutputByteStream $os)
  {
    $this->flushContents();
    $blocks=512;
    $os->setReadPointer(0);
    while(false!==($read = $os->read($blocks)))
      $this->write($read);
  }
  
  /**
   * @see Swift_CharacterStream::importString()
   *
   * @param string $string
   */
  public function importString($string)
  {
    $this->flushContents();
    $this->write($string);
  }
  
  /**
   * @see Swift_CharacterStream::read()
   *
   * @param int $length
   * @return string
   */
  public function read($length)
  {
  	if ($this->_currentPos>=$this->_charCount)
  	{
  	  return false;
  	}
  	$ret=false;
  	$length = ($this->_currentPos+$length > $this->_charCount)
  	  ? $this->_charCount - $this->_currentPos
  	  : $length;
  	  switch ($this->_mapType)
  	{
      case Swift_CharacterReader::MAP_TYPE_FIXED_LEN:
        $len = $length*$this->_map;
        $ret = substr($this->_datas,
            $this->_currentPos * $this->_map,
            $len);
        $this->_currentPos += $length;
        break;
      
      case Swift_CharacterReader::MAP_TYPE_INVALID:
        $end = $this->_currentPos + $length;
        $end = $end > $this->_charCount
          ?$this->_charCount
          :$end;
        $ret = '';
        for (; $this->_currentPos < $length; ++$this->_currentPos)
        {
          if (isset ($this->_map[$this->_currentPos]))
          {
            $ret .= '?';
          }
          else
          {
            $ret .= $this->_datas[$this->_currentPos];
          }
        }
        break;
      
      case Swift_CharacterReader::MAP_TYPE_POSITIONS:
        $end = $this->_currentPos + $length;
        $end = $end > $this->_charCount
          ?$this->_charCount
          :$end;
        $ret = '';
        $start = 0;
        if ($this->_currentPos>0)
        {
          $start = $this->_map['p'][$this->_currentPos-1];
        }
        $to = $start;
        for (; $this->_currentPos < $end; ++$this->_currentPos)
        {
          if (isset($this->_map['i'][$this->_currentPos])) {
          	$ret .= substr($this->_datas, $start, $to - $start).'?';
          	$start = $this->_map['p'][$this->_currentPos];
          } else {
          	$to = $this->_map['p'][$this->_currentPos];
          }
        }
        $ret .= substr($this->_datas, $start, $to - $start);
        break;
  	}
  	return $ret;
  }
  
  /**
   * @see Swift_CharacterStream::readBytes()
   *
   * @param int $length
   * @return int[]
   */
  public function readBytes($length)
  {
    $read=$this->read($length);
  	if ($read!==false)
  	{
      $ret = array_map('ord', str_split($read, 1));
      return $ret;
  	}
  	return false;
  }
  
  /**
   * @see Swift_CharacterStream::setPointer()
   *
   * @param int $charOffset
   */
  public function setPointer($charOffset)
  {
  	if ($this->_charCount<$charOffset){
  		$charOffset=$this->_charCount;
  	}
  	$this->_currentPos = $charOffset;
  }
  
  /**
   * @see Swift_CharacterStream::write()
   *
   * @param string $chars
   */
  public function write($chars)
  {
  	if (!isset($this->_charReader))
    {
      $this->_charReader = $this->_charReaderFactory->getReaderFor(
        $this->_charset);
      $this->_map = array();
      $this->_mapType = $this->_charReader->getMapType();
    }
  	$ignored='';
  	$this->_datas .= $chars;
    $this->_charCount += $this->_charReader->getCharPositions(substr($this->_datas, $this->_datasSize), $this->_datasSize, $this->_map, $ignored);
    if ($ignored!==false) {
      $this->_datasSize=strlen($this->_datas)-strlen($ignored);
    }
    else
    {
      $this->_datasSize=strlen($this->_datas);
    }
  }
}
 /* .tmp\flat\1\NullKeyCache.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\NullKeyCache.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/KeyCache.php';
//@require 'Swift/KeyCacheInputStream.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStrean.php';

/**
 * A null KeyCache that does not cache at all.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
class Swift_KeyCache_NullKeyCache implements Swift_KeyCache
{
  
  /**
   * Set a string into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param string $string
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function setString($nsKey, $itemKey, $string, $mode)
  {
  }
  
  /**
   * Set a ByteStream into the cache under $itemKey for the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_OutputByteStream $os
   * @param int $mode
   * @see MODE_WRITE, MODE_APPEND
   */
  public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os,
    $mode)
  {
  }
  
  /**
   * Provides a ByteStream which when written to, writes data to $itemKey.
   * NOTE: The stream will always write in append mode.
   * @param string $nsKey
   * @param string $itemKey
   * @return Swift_InputByteStream
   */
  public function getInputByteStream($nsKey, $itemKey,
    Swift_InputByteStream $writeThrough = null)
  {
  }
  
  /**
   * Get data back out of the cache as a string.
   * @param string $nsKey
   * @param string $itemKey
   * @return string
   */
  public function getString($nsKey, $itemKey)
  {
  }
  
  /**
   * Get data back out of the cache as a ByteStream.
   * @param string $nsKey
   * @param string $itemKey
   * @param Swift_InputByteStream $is to write the data to
   */
  public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is)
  {
  }
  
  /**
   * Check if the given $itemKey exists in the namespace $nsKey.
   * @param string $nsKey
   * @param string $itemKey
   * @return boolean
   */
  public function hasKey($nsKey, $itemKey)
  {
    return false;
  }
  
  /**
   * Clear data for $itemKey in the namespace $nsKey if it exists.
   * @param string $nsKey
   * @param string $itemKey
   */
  public function clearKey($nsKey, $itemKey)
  {
  }
  
  /**
   * Clear all data in the namespace $nsKey if it exists.
   * @param string $nsKey
   */
  public function clearAll($nsKey)
  {
  }
  
}
 /* .tmp\flat\1\ParameterizedHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ParameterizedHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Header.php';

/**
 * A MIME Header with parameters.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_ParameterizedHeader extends Swift_Mime_Header
{
  
  /**
   * Set the value of $parameter.
   * @param string $parameter
   * @param string $value
   */
  public function setParameter($parameter, $value);
  
  /**
   * Get the value of $parameter.
   * @return string
   */
  public function getParameter($parameter);
  
}
 /* .tmp\flat\1\ResponseListener.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\ResponseListener.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Events/ResponseEvent.php';

/**
 * Listens for responses from a remote SMTP server.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_ResponseListener extends Swift_Events_EventListener
{
  
  /**
   * Invoked immediately following a response coming back.
   * @param Swift_Events_ResponseEvent $evt
   */
  public function responseReceived(Swift_Events_ResponseEvent $evt);
  
}
 /* .tmp\flat\1\RfcComplianceException.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\RfcComplianceException.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/SwiftException.php';

/**
 * RFC Compliance Exception class.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_RfcComplianceException extends Swift_SwiftException
{
  
  /**
   * Create a new RfcComplianceException with $message.
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
 /* .tmp\flat\1\SendListener.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\SendListener.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Events/SendEvent.php';

/**
 * Listens for Messages being sent from within the Transport system.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_SendListener extends Swift_Events_EventListener
{
  
  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt);
  
  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt);
  
}
 /* .tmp\flat\1\SendmailTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\SendmailTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/EsmtpTransport.php';
//@require 'Swift/Transport/IoBuffer.php';
//@require 'Swift/Transport/Log.php';
//@require 'Swift/Events/EventDispatcher.php';

/**
 * SendmailTransport for sending mail through a sendmail/postfix (etc..) binary.
 * 
 * Supported modes are -bs and -t, with any additional flags desired.
 * It is advised to use -bs mode since error reporting with -t mode is not
 * possible.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_SendmailTransport
  extends Swift_Transport_AbstractSmtpTransport
{
  
  /**
   * Connection buffer parameters.
   * @var array
   * @access protected
   */
  private $_params = array(
    'timeout' => 30,
    'blocking' => 1,
    'command' => '/usr/sbin/sendmail -bs',
    'type' => Swift_Transport_IoBuffer::TYPE_PROCESS
    );
  
  /**
   * Create a new SendmailTransport with $buf for I/O.
   * @param Swift_Transport_IoBuffer $buf
   * @param Swift_Events_EventDispatcher $dispatcher
   */
  public function __construct(Swift_Transport_IoBuffer $buf,
    Swift_Events_EventDispatcher $dispatcher)
  {
    parent::__construct($buf, $dispatcher);
  }
  
  /**
   * Start the standalone SMTP session if running in -bs mode.
   */
  public function start()
  {
    if (false !== strpos($this->getCommand(), ' -bs'))
    {
      parent::start();
    }
  }
  
  /**
   * Set the command to invoke.
   * If using -t mode you are strongly advised to include -oi or -i in the
   * flags. For example: /usr/sbin/sendmail -oi -t
   * Swift will append a -f<sender> flag if one is not present.
   * The recommended mode is "-bs" since it is interactive and failure notifications
   * are hence possible.
   * @param string $command
   */
  public function setCommand($command)
  {
    $this->_params['command'] = $command;
    return $this;
  }
  
  /**
   * Get the sendmail command which will be invoked.
   * @return string
   */
  public function getCommand()
  {
    return $this->_params['command'];
  }
  
  /**
   * Send the given Message.
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * NOTE: If using 'sendmail -t' you will not be aware of any failures until
   * they bounce (i.e. send() will always return 100% success).
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $failedRecipients = (array) $failedRecipients;
    $command = $this->getCommand();
    $buffer = $this->getBuffer();
    
    if (false !== strpos($command, ' -t'))
    {
      if ($evt = $this->_eventDispatcher->createSendEvent($this, $message))
      {
        $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
        if ($evt->bubbleCancelled())
        {
          return 0;
        }
      }
      
      if (false === strpos($command, ' -f'))
      {
        $command .= ' -f' . $this->_getReversePath($message);
      }
      
      $buffer->initialize(array_merge($this->_params, array('command' => $command)));
      
      if (false === strpos($command, ' -i') && false === strpos($command, ' -oi'))
      {
        $buffer->setWriteTranslations(array("\r\n" => "\n", "\n." => "\n.."));
      }
      else
      {
        $buffer->setWriteTranslations(array("\r\n"=>"\n"));
      }
      
      $count = count((array) $message->getTo())
        + count((array) $message->getCc())
        + count((array) $message->getBcc())
        ;
      $message->toByteStream($buffer);
      $buffer->flushBuffers();
      $buffer->setWriteTranslations(array());
      $buffer->terminate();
      
      if ($evt)
      {
        $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
        $evt->setFailedRecipients($failedRecipients);
        $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
      }
      
      $message->generateId();
    }
    elseif (false !== strpos($command, ' -bs'))
    {
      $count = parent::send($message, $failedRecipients);
    }
    else
    {
      $this->_throwException(new Swift_TransportException(
        'Unsupported sendmail command flags [' . $command . ']. ' .
        'Must be one of "-bs" or "-t" but can include additional flags.'
        ));
    }
    
    return $count;
  }
  
  // -- Protected methods
  
  /** Get the params to initialize the buffer */
  protected function _getBufferParams()
  {
    return $this->_params;
  }
  
}
 /* .tmp\flat\1\SimpleCharacterReaderFactory.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\SimpleCharacterReaderFactory.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/CharacterReaderFactory.php';

/**
 * Standard factory for creating CharacterReaders.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_CharacterReaderFactory_SimpleCharacterReaderFactory
  implements Swift_CharacterReaderFactory
{

  /**
   * A map of charset patterns to their implementation classes.
   * @var array
   * @access private
   */
  private $_map = array();
  
  /**
   * Factories which have already been loaded.
   * @var Swift_CharacterReaderFactory[]
   * @access private
   */
  private $_loaded = array();
  
  /**
   * Creates a new CharacterReaderFactory.
   */
  public function __construct()
  {
    $prefix = 'Swift_CharacterReader_';
    
    $singleByte = array(
      'class' => $prefix . 'GenericFixedWidthReader',
      'constructor' => array(1)
      );
    
    $doubleByte = array(
      'class' => $prefix . 'GenericFixedWidthReader',
      'constructor' => array(2)
      );
      
    $fourBytes = array(
      'class' => $prefix . 'GenericFixedWidthReader',
      'constructor' => array(4)
      );
    
    //Utf-8
    $this->_map['utf-?8'] = array(
      'class' => $prefix . 'Utf8Reader',
      'constructor' => array()
      );
    
    //7-8 bit charsets
    $this->_map['(us-)?ascii'] = $singleByte;
    $this->_map['(iso|iec)-?8859-?[0-9]+'] = $singleByte;
    $this->_map['windows-?125[0-9]'] = $singleByte;
    $this->_map['cp-?[0-9]+'] = $singleByte;
    $this->_map['ansi'] = $singleByte;
    $this->_map['macintosh'] = $singleByte;
    $this->_map['koi-?7'] = $singleByte;
    $this->_map['koi-?8-?.+'] = $singleByte;
    $this->_map['mik'] = $singleByte;
    $this->_map['(cork|t1)'] = $singleByte;
    $this->_map['v?iscii'] = $singleByte;
    
    //16 bits
    $this->_map['(ucs-?2|utf-?16)'] = $doubleByte;
    
    //32 bits
    $this->_map['(ucs-?4|utf-?32)'] = $fourBytes;
    
    //Fallback
    $this->_map['.*'] = $singleByte;
  }
  
  /**
   * Returns a CharacterReader suitable for the charset applied.
   * @param string $charset
   * @return Swift_CharacterReader
   */
  public function getReaderFor($charset)
  {
    $charset = trim(strtolower($charset));
    foreach ($this->_map as $pattern => $spec)
    {
      $re = '/^' . $pattern . '$/D';
      if (preg_match($re, $charset))
      {
        if (!array_key_exists($pattern, $this->_loaded))
        {
          $reflector = new ReflectionClass($spec['class']);
          if ($reflector->getConstructor())
          {
            $reader = $reflector->newInstanceArgs($spec['constructor']);
          }
          else
          {
            $reader = $reflector->newInstance();
          }
          $this->_loaded[$pattern] = $reader;
        }
        return $this->_loaded[$pattern];
      }
    }
  }
  
}
 /* .tmp\flat\1\SimpleEventDispatcher.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\SimpleEventDispatcher.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventDispatcher.php';
//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Events/EventObject.php';
//@require 'Swift/Events/CommandEvent.php';
//@require 'Swift/Events/ResponseEvent.php';
//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Events/TransportChangeEvent.php';
//@require 'Swift/Events/TransportExceptionEvent.php';

/**
 * The EventDispatcher which handles the event dispatching layer.
 * 
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_SimpleEventDispatcher implements Swift_Events_EventDispatcher
{
  
  /** A map of event types to their associated listener types */
  private $_eventMap = array();
  
  /** Event listeners bound to this dispatcher */
  private $_listeners = array();
  
  /** Listeners queued to have an Event bubbled up the stack to them */
  private $_bubbleQueue = array();
  
  /**
   * Create a new EventDispatcher.
   */
  public function __construct()
  {
    $this->_eventMap = array(
      'Swift_Events_CommandEvent' => 'Swift_Events_CommandListener',
      'Swift_Events_ResponseEvent' => 'Swift_Events_ResponseListener',
      'Swift_Events_SendEvent' => 'Swift_Events_SendListener',
      'Swift_Events_TransportChangeEvent' => 'Swift_Events_TransportChangeListener',
      'Swift_Events_TransportExceptionEvent' => 'Swift_Events_TransportExceptionListener'
      );
  }
  
  /**
   * Create a new SendEvent for $source and $message.
   * 
   * @param Swift_Transport $source
   * @param Swift_Mime_Message
   * @return Swift_Events_SendEvent
   */
  public function createSendEvent(Swift_Transport $source,
    Swift_Mime_Message $message)
  {
    return new Swift_Events_SendEvent($source, $message);
  }
  
  /**
   * Create a new CommandEvent for $source and $command.
   * 
   * @param Swift_Transport $source
   * @param string $command That will be executed
   * @param array $successCodes That are needed
   * @return Swift_Events_CommandEvent
   */
  public function createCommandEvent(Swift_Transport $source,
    $command, $successCodes = array())
  {
    return new Swift_Events_CommandEvent($source, $command, $successCodes);
  }
  
  /**
   * Create a new ResponseEvent for $source and $response.
   * 
   * @param Swift_Transport $source
   * @param string $response
   * @param boolean $valid If the response is valid
   * @return Swift_Events_ResponseEvent
   */
  public function createResponseEvent(Swift_Transport $source,
    $response, $valid)
  {
    return new Swift_Events_ResponseEvent($source, $response, $valid);
  }
  
  /**
   * Create a new TransportChangeEvent for $source.
   * 
   * @param Swift_Transport $source
   * @return Swift_Events_TransportChangeEvent
   */
  public function createTransportChangeEvent(Swift_Transport $source)
  {
    return new Swift_Events_TransportChangeEvent($source);
  }
  
  /**
   * Create a new TransportExceptionEvent for $source.
   * 
   * @param Swift_Transport $source
   * @param Swift_TransportException $ex
   * @return Swift_Events_TransportExceptionEvent
   */
  public function createTransportExceptionEvent(Swift_Transport $source,
    Swift_TransportException $ex)
  {
    return new Swift_Events_TransportExceptionEvent($source, $ex);
  }
  
  /**
   * Bind an event listener to this dispatcher.
   * 
   * @param Swift_Events_EventListener $listener
   */
  public function bindEventListener(Swift_Events_EventListener $listener)
  {
    foreach ($this->_listeners as $l)
    {
      //Already loaded
      if ($l === $listener)
      {
        return;
      }
    }
    $this->_listeners[] = $listener;
  }
  
  /**
   * Dispatch the given Event to all suitable listeners.
   * 
   * @param Swift_Events_EventObject $evt
   * @param string $target method
   */
  public function dispatchEvent(Swift_Events_EventObject $evt, $target)
  {
    $this->_prepareBubbleQueue($evt);
    $this->_bubble($evt, $target);
  }
  
  // -- Private methods
  
  /** Queue listeners on a stack ready for $evt to be bubbled up it */
  private function _prepareBubbleQueue(Swift_Events_EventObject $evt)
  {
    $this->_bubbleQueue = array();
    $evtClass = get_class($evt);
    foreach ($this->_listeners as $listener)
    {
      if (array_key_exists($evtClass, $this->_eventMap)
        && ($listener instanceof $this->_eventMap[$evtClass]))
      {
        $this->_bubbleQueue[] = $listener;
      }
    }
  }
  
  /** Bubble $evt up the stack calling $target() on each listener */
  private function _bubble(Swift_Events_EventObject $evt, $target)
  {
    if (!$evt->bubbleCancelled() && $listener = array_shift($this->_bubbleQueue))
    {
      $listener->$target($evt);
      $this->_bubble($evt, $target);
    }
  }
  
}
 /* .tmp\flat\1\SimpleMailInvoker.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\SimpleMailInvoker.php */ ?>
<?php

/*
 Invokes the mail() function in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */
 
//@require 'Swift/Transport/MailInvoker.php';

/**
 * This is the implementation class for {@link Swift_Transport_MailInvoker}.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_SimpleMailInvoker implements Swift_Transport_MailInvoker
{
  
  /**
   * Send mail via the mail() function.
   * 
   * This method takes the same arguments as PHP mail().
   * 
   * @param string $to
   * @param string $subject
   * @param string $body
   * @param string $headers
   * @param string $extraParams
   * 
   * @return boolean
   */
  public function mail($to, $subject, $body, $headers = null, $extraParams = null)
  {
    if (!ini_get('safe_mode'))
    {
      return mail($to, $subject, $body, $headers, $extraParams);
    }
    else
    {
      return mail($to, $subject, $body, $headers);      
    }
  }
  
}
 /* .tmp\flat\1\StringReplacementFilter.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\StringReplacementFilter.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/StreamFilter.php';

/**
 * Processes bytes as they pass through a buffer and replaces sequences in it.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_StreamFilters_StringReplacementFilter implements Swift_StreamFilter
{
  
  /** The needle(s) to search for */
  private $_search;
  
  /** The replacement(s) to make */
  private $_replace;
  
  /**
   * Create a new StringReplacementFilter with $search and $replace.
   * @param string|array $search
   * @param string|array $replace
   */
  public function __construct($search, $replace)
  {
    $this->_search = $search;
    $this->_replace = $replace;
  }
  
  /**
   * Returns true if based on the buffer passed more bytes should be buffered.
   * @param string $buffer
   * @return boolean
   */
  public function shouldBuffer($buffer)
  {
    $endOfBuffer = substr($buffer, -1);
    foreach ((array) $this->_search as $needle)
    {
      if (false !== strpos($needle, $endOfBuffer))
      {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Perform the actual replacements on $buffer and return the result.
   * @param string $buffer
   * @return string
   */
  public function filter($buffer)
  {
    return str_replace($this->_search, $this->_replace, $buffer);
  }
  
}
 /* .tmp\flat\1\StringReplacementFilterFactory.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\StringReplacementFilterFactory.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/StreamFilters/StringReplacementFilter.php';
//@require 'Swift/StreamFilterFactory.php';

/**
 * Creates filters for replacing needles in a string buffer.
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_StreamFilters_StringReplacementFilterFactory
  implements Swift_ReplacementFilterFactory
{
  
  /** Lazy-loaded filters */
  private $_filters = array();
  
  /**
   * Create a new StreamFilter to replace $search with $replace in a string.
   * @param string $search
   * @param string $replace
   * @return Swift_StreamFilter
   */
  public function createFilter($search, $replace)
  {
    if (!isset($this->_filters[$search][$replace]))
    {
      if (!isset($this->_filters[$search]))
      {
        $this->_filters[$search] = array();
      }
      
      if (!isset($this->_filters[$search][$replace]))
      {
        $this->_filters[$search][$replace] = array();
      }
      
      $this->_filters[$search][$replace]
        = new Swift_StreamFilters_StringReplacementFilter($search, $replace);
    }
    
    return $this->_filters[$search][$replace];
  }
  
}
 /* .tmp\flat\1\TransportChangeListener.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\TransportChangeListener.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Events/TransportChangeEvent.php';

/**
 * Listens for changes within the Transport system.
 * 
 * @package Swift
 * @subpackage Events
 * 
 * @author Chris Corbyn
 */
interface Swift_Events_TransportChangeListener extends Swift_Events_EventListener
{
  
  /**
   * Invoked just before a Transport is started.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt);
  
  /**
   * Invoked immediately after the Transport is started.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function transportStarted(Swift_Events_TransportChangeEvent $evt);
  
  /**
   * Invoked just before a Transport is stopped.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt);
  
  /**
   * Invoked immediately after the Transport is stopped.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function transportStopped(Swift_Events_TransportChangeEvent $evt);
  
}
 /* .tmp\flat\1\TransportExceptionListener.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\TransportExceptionListener.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Events/TransportExceptionEvent.php';

/**
 * Listens for Exceptions thrown from within the Transport system.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_TransportExceptionListener
  extends Swift_Events_EventListener
{
  
  /**
   * Invoked as a TransportException is thrown in the Transport system.
   * @param Swift_Events_TransportExceptionEvent $evt
   */
  public function exceptionThrown(Swift_Events_TransportExceptionEvent $evt);
  
}
 /* .tmp\flat\1\UsAsciiReader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\UsAsciiReader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/CharacterReader.php';

/**
 * Analyzes US-ASCII characters.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_CharacterReader_UsAsciiReader
  implements Swift_CharacterReader
{
  /**
   * Returns the complete charactermap
   *
   * @param string $string
   * @param int $startOffset
   * @param string $ignoredChars
   */
  public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars)
  {
  	$strlen=strlen($string);
  	$ignoredChars='';
  	for( $i = 0; $i < $strlen; ++$i)
  	{
  	  if ($string[$i]>"\x07F")
  	  { // Invalid char
  	  	$currentMap[$i+$startOffset]=$string[$i];
  	  }
  	}
  	return $strlen;
  }
  
  /**
   * Returns mapType
   * @int mapType
   */
  public function getMapType()
  {
  	return self::MAP_TYPE_INVALID;
  }
 
  /**
   * Returns an integer which specifies how many more bytes to read.
   * A positive integer indicates the number of more bytes to fetch before invoking
   * this method again.
   * A value of zero means this is already a valid character.
   * A value of -1 means this cannot possibly be a valid character.
   * @param string $bytes
   * @return int
   */
  public function validateByteSequence($bytes, $size)
  {
    $byte = reset($bytes);
    if (1 == count($bytes) && $byte >= 0x00 && $byte <= 0x7F)
    {
      return 0;
    }
    else
    {
      return -1;
    }
  }

  /**
   * Returns the number of bytes which should be read to start each character.
   * @return int
   */
  public function getInitialByteSize()
  {
    return 1;
  }

}
 /* .tmp\flat\1\Utf8Reader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\Utf8Reader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/CharacterReader.php';

/**
 * Analyzes UTF-8 characters.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 * @author Xavier De Cock <xdecock@gmail.com>
 */
class Swift_CharacterReader_Utf8Reader
  implements Swift_CharacterReader
{

  /** Pre-computed for optimization */
  private static $length_map=array(
//N=0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x0N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x1N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x2N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x3N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x4N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x5N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x6N
    1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, //0x7N
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0x8N
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0x9N
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0xAN
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //0xBN
    2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, //0xCN
    2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, //0xDN
    3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3, //0xEN
    4,4,4,4,4,4,4,4,5,5,5,5,6,6,0,0  //0xFN
 );
  private static $s_length_map=array(
  "\x00"=>1, "\x01"=>1, "\x02"=>1, "\x03"=>1, "\x04"=>1, "\x05"=>1, "\x06"=>1, "\x07"=>1,
  "\x08"=>1, "\x09"=>1, "\x0a"=>1, "\x0b"=>1, "\x0c"=>1, "\x0d"=>1, "\x0e"=>1, "\x0f"=>1,
  "\x10"=>1, "\x11"=>1, "\x12"=>1, "\x13"=>1, "\x14"=>1, "\x15"=>1, "\x16"=>1, "\x17"=>1,
  "\x18"=>1, "\x19"=>1, "\x1a"=>1, "\x1b"=>1, "\x1c"=>1, "\x1d"=>1, "\x1e"=>1, "\x1f"=>1,
  "\x20"=>1, "\x21"=>1, "\x22"=>1, "\x23"=>1, "\x24"=>1, "\x25"=>1, "\x26"=>1, "\x27"=>1,
  "\x28"=>1, "\x29"=>1, "\x2a"=>1, "\x2b"=>1, "\x2c"=>1, "\x2d"=>1, "\x2e"=>1, "\x2f"=>1,
  "\x30"=>1, "\x31"=>1, "\x32"=>1, "\x33"=>1, "\x34"=>1, "\x35"=>1, "\x36"=>1, "\x37"=>1,
  "\x38"=>1, "\x39"=>1, "\x3a"=>1, "\x3b"=>1, "\x3c"=>1, "\x3d"=>1, "\x3e"=>1, "\x3f"=>1,
  "\x40"=>1, "\x41"=>1, "\x42"=>1, "\x43"=>1, "\x44"=>1, "\x45"=>1, "\x46"=>1, "\x47"=>1,
  "\x48"=>1, "\x49"=>1, "\x4a"=>1, "\x4b"=>1, "\x4c"=>1, "\x4d"=>1, "\x4e"=>1, "\x4f"=>1,
  "\x50"=>1, "\x51"=>1, "\x52"=>1, "\x53"=>1, "\x54"=>1, "\x55"=>1, "\x56"=>1, "\x57"=>1,
  "\x58"=>1, "\x59"=>1, "\x5a"=>1, "\x5b"=>1, "\x5c"=>1, "\x5d"=>1, "\x5e"=>1, "\x5f"=>1,
  "\x60"=>1, "\x61"=>1, "\x62"=>1, "\x63"=>1, "\x64"=>1, "\x65"=>1, "\x66"=>1, "\x67"=>1,
  "\x68"=>1, "\x69"=>1, "\x6a"=>1, "\x6b"=>1, "\x6c"=>1, "\x6d"=>1, "\x6e"=>1, "\x6f"=>1,
  "\x70"=>1, "\x71"=>1, "\x72"=>1, "\x73"=>1, "\x74"=>1, "\x75"=>1, "\x76"=>1, "\x77"=>1,
  "\x78"=>1, "\x79"=>1, "\x7a"=>1, "\x7b"=>1, "\x7c"=>1, "\x7d"=>1, "\x7e"=>1, "\x7f"=>1,
  "\x80"=>0, "\x81"=>0, "\x82"=>0, "\x83"=>0, "\x84"=>0, "\x85"=>0, "\x86"=>0, "\x87"=>0,
  "\x88"=>0, "\x89"=>0, "\x8a"=>0, "\x8b"=>0, "\x8c"=>0, "\x8d"=>0, "\x8e"=>0, "\x8f"=>0,
  "\x90"=>0, "\x91"=>0, "\x92"=>0, "\x93"=>0, "\x94"=>0, "\x95"=>0, "\x96"=>0, "\x97"=>0,
  "\x98"=>0, "\x99"=>0, "\x9a"=>0, "\x9b"=>0, "\x9c"=>0, "\x9d"=>0, "\x9e"=>0, "\x9f"=>0,
  "\xa0"=>0, "\xa1"=>0, "\xa2"=>0, "\xa3"=>0, "\xa4"=>0, "\xa5"=>0, "\xa6"=>0, "\xa7"=>0,
  "\xa8"=>0, "\xa9"=>0, "\xaa"=>0, "\xab"=>0, "\xac"=>0, "\xad"=>0, "\xae"=>0, "\xaf"=>0,
  "\xb0"=>0, "\xb1"=>0, "\xb2"=>0, "\xb3"=>0, "\xb4"=>0, "\xb5"=>0, "\xb6"=>0, "\xb7"=>0,
  "\xb8"=>0, "\xb9"=>0, "\xba"=>0, "\xbb"=>0, "\xbc"=>0, "\xbd"=>0, "\xbe"=>0, "\xbf"=>0,
  "\xc0"=>2, "\xc1"=>2, "\xc2"=>2, "\xc3"=>2, "\xc4"=>2, "\xc5"=>2, "\xc6"=>2, "\xc7"=>2,
  "\xc8"=>2, "\xc9"=>2, "\xca"=>2, "\xcb"=>2, "\xcc"=>2, "\xcd"=>2, "\xce"=>2, "\xcf"=>2,
  "\xd0"=>2, "\xd1"=>2, "\xd2"=>2, "\xd3"=>2, "\xd4"=>2, "\xd5"=>2, "\xd6"=>2, "\xd7"=>2,
  "\xd8"=>2, "\xd9"=>2, "\xda"=>2, "\xdb"=>2, "\xdc"=>2, "\xdd"=>2, "\xde"=>2, "\xdf"=>2,
  "\xe0"=>3, "\xe1"=>3, "\xe2"=>3, "\xe3"=>3, "\xe4"=>3, "\xe5"=>3, "\xe6"=>3, "\xe7"=>3,
  "\xe8"=>3, "\xe9"=>3, "\xea"=>3, "\xeb"=>3, "\xec"=>3, "\xed"=>3, "\xee"=>3, "\xef"=>3,
  "\xf0"=>4, "\xf1"=>4, "\xf2"=>4, "\xf3"=>4, "\xf4"=>4, "\xf5"=>4, "\xf6"=>4, "\xf7"=>4,
  "\xf8"=>5, "\xf9"=>5, "\xfa"=>5, "\xfb"=>5, "\xfc"=>6, "\xfd"=>6, "\xfe"=>0, "\xff"=>0,
 );

  /**
   * Returns the complete charactermap
   *
   * @param string $string
   * @param int $startOffset
   * @param array $currentMap
   * @param mixed $ignoredChars
   */
  public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars)
  {
  	if (!isset($currentMap['i']) || !isset($currentMap['p']))
  	{
  	  $currentMap['p'] = $currentMap['i'] = array();
   	}
  	$strlen=strlen($string);
  	$charPos=count($currentMap['p']);
  	$foundChars=0;
  	$invalid=false;
  	for ($i=0; $i<$strlen; ++$i)
  	{
  	  $char=$string[$i];
  	  $size=self::$s_length_map[$char];
  	  if ($size==0)
  	  {
  	    /* char is invalid, we must wait for a resync */
  	  	$invalid=true;
  	  	continue;
   	  }
   	  else
   	  {
   	  	if ($invalid==true)
   	  	{
   	  	  /* We mark the chars as invalid and start a new char */
   	  	  $currentMap['p'][$charPos+$foundChars]=$startOffset+$i;
   	      $currentMap['i'][$charPos+$foundChars]=true;
   	      ++$foundChars;
   	      $invalid=false;
   	  	}
   	  	if (($i+$size) > $strlen){
   	  		$ignoredChars=substr($string, $i);
   	  		break;
   	  	}
   	  	for ($j=1; $j<$size; ++$j)
   	  	{
          $char=$string[$i+$j];
          if ($char>"\x7F" && $char<"\xC0")
          {
            // Valid - continue parsing
          }
          else
          {
            /* char is invalid, we must wait for a resync */
            $invalid=true;
            continue 2;
          }
   	  	}
   	  	/* Ok we got a complete char here */
   	  	$lastChar=$currentMap['p'][$charPos+$foundChars]=$startOffset+$i+$size;
   	  	$i+=$j-1;
   	    ++$foundChars;
   	  }
  	}
  	return $foundChars;
  }
  
  /**
   * Returns mapType
   * @int mapType
   */
  public function getMapType()
  {
  	return self::MAP_TYPE_POSITIONS;
  }
 
  /**
   * Returns an integer which specifies how many more bytes to read.
   * A positive integer indicates the number of more bytes to fetch before invoking
   * this method again.
   * A value of zero means this is already a valid character.
   * A value of -1 means this cannot possibly be a valid character.
   * @param string $bytes
   * @return int
   */
  public function validateByteSequence($bytes, $size)
  {
    if ($size<1){
      return -1;
    }
    $needed = self::$length_map[$bytes[0]] - $size;
    return ($needed > -1)
      ? $needed
      : -1
      ;
  }

  /**
   * Returns the number of bytes which should be read to start each character.
   * @return int
   */
  public function getInitialByteSize()
  {
    return 1;
  }

}
 /* .tmp\flat\1\2\AntiFloodPlugin.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\AntiFloodPlugin.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Plugins/Sleeper.php';

/**
 * Reduces network flooding when sending large amounts of mail.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_AntiFloodPlugin
  implements Swift_Events_SendListener, Swift_Plugins_Sleeper
{
  
  /**
   * The number of emails to send before restarting Transport.
   * @var int
   * @access private
   */
  private $_threshold;
  
  /**
   * The number of seconds to sleep for during a restart.
   * @var int
   * @access private
   */
  private $_sleep;
  
  /**
   * The internal counter.
   * @var int
   * @access private
   */
  private $_counter = 0;
  
  /**
   * The Sleeper instance for sleeping.
   * @var Swift_Plugins_Sleeper
   * @access private
   */
  private $_sleeper;
  
  /**
   * Create a new AntiFloodPlugin with $threshold and $sleep time.
   * @param int $threshold
   * @param int $sleep time
   * @param Swift_Plugins_Sleeper $sleeper (not needed really)
   */
  public function __construct($threshold = 99, $sleep = 0,
    Swift_Plugins_Sleeper $sleeper = null)
  {
    $this->setThreshold($threshold);
    $this->setSleepTime($sleep);
    $this->_sleeper = $sleeper;
  }
  
  /**
   * Set the number of emails to send before restarting.
   * @param int $threshold
   */
  public function setThreshold($threshold)
  {
    $this->_threshold = $threshold;
  }
  
  /**
   * Get the number of emails to send before restarting.
   * @return int
   */
  public function getThreshold()
  {
    return $this->_threshold;
  }
  
  /**
   * Set the number of seconds to sleep for during a restart.
   * @param int $sleep time
   */
  public function setSleepTime($sleep)
  {
    $this->_sleep = $sleep;
  }
  
  /**
   * Get the number of seconds to sleep for during a restart.
   * @return int
   */
  public function getSleepTime()
  {
    return $this->_sleep;
  }
  
  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
  }
  
  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    ++$this->_counter;
    if ($this->_counter >= $this->_threshold)
    {
      $transport = $evt->getTransport();
      $transport->stop();
      if ($this->_sleep)
      {
        $this->sleep($this->_sleep);
      }
      $transport->start();
      $this->_counter = 0;
    }
  }
  
  /**
   * Sleep for $seconds.
   * @param int $seconds
   */
  public function sleep($seconds)
  {
    if (isset($this->_sleeper))
    {
      $this->_sleeper->sleep($seconds);
    }
    else
    {
      sleep($seconds);
    }
  }
  
}
 /* .tmp\flat\1\2\BandwidthMonitorPlugin.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\BandwidthMonitorPlugin.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Events/CommandListener.php';
//@require 'Swift/Events/CommandEvent.php';
//@require 'Swift/Events/ResponseListener.php';
//@require 'Swift/Events/ResponseEvent.php';
//@require 'Swift/InputByteStream.php';

/**
 * Reduces network flooding when sending large amounts of mail.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_BandwidthMonitorPlugin
  implements Swift_Events_SendListener, Swift_Events_CommandListener,
  Swift_Events_ResponseListener, Swift_InputByteStream
{
  
  /**
   * The outgoing traffic counter.
   * @var int
   * @access private
   */
  private $_out = 0;
  
  /**
   * The incoming traffic counter.
   * @var int
   * @access private
   */
  private $_in = 0;
  
  /** Bound byte streams */
  private $_mirrors = array();
  
  /**
   * Not used.
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
  }
  
  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = $evt->getMessage();
    $message->toByteStream($this);
  }
  
  /**
   * Invoked immediately following a command being sent.
   * @param Swift_Events_ResponseEvent $evt
   */
  public function commandSent(Swift_Events_CommandEvent $evt)
  {
    $command = $evt->getCommand();
    $this->_out += strlen($command);
  }
  
  /**
   * Invoked immediately following a response coming back.
   * @param Swift_Events_ResponseEvent $evt
   */
  public function responseReceived(Swift_Events_ResponseEvent $evt)
  {
    $response = $evt->getResponse();
    $this->_in += strlen($response);
  }
  
  /**
   * Called when a message is sent so that the outgoing counter can be increased.
   * @param string $bytes
   */
  public function write($bytes)
  {
    $this->_out += strlen($bytes);
    foreach ($this->_mirrors as $stream)
    {
      $stream->write($bytes);
    }
  }
  
  /**
   * Not used.
   */
  public function commit()
  {
  }
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   * 
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is)
  {
    $this->_mirrors[] = $is;
  }
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   * 
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is)
  {
    foreach ($this->_mirrors as $k => $stream)
    {
      if ($is === $stream)
      {
        unset($this->_mirrors[$k]);
      }
    }
  }
  
  /**
   * Not used.
   */
  public function flushBuffers()
  {
    foreach ($this->_mirrors as $stream)
    {
      $stream->flushBuffers();
    }
  }
  
  /**
   * Get the total number of bytes sent to the server.
   * @return int
   */
  public function getBytesOut()
  {
    return $this->_out;
  }
  
  /**
   * Get the total number of bytes received from the server.
   * @return int
   */
  public function getBytesIn()
  {
    return $this->_in;
  }
  
  /**
   * Reset the internal counters to zero.
   */
  public function reset()
  {
    $this->_out = 0;
    $this->_in = 0;
  }
  
}
 /* .tmp\flat\1\2\Base64Encoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\Base64Encoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Encoder.php';

/**
 * Handles Base 64 Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_Base64Encoder implements Swift_Encoder
{
  
  /**
   * Takes an unencoded string and produces a Base64 encoded string from it.
   * Base64 encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   * @param string $string to encode
   * @param int $firstLineOffset
   * @param int $maxLineLength, optional, 0 indicates the default of 76 bytes
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if (0 >= $maxLineLength || 76 < $maxLineLength)
    {
      $maxLineLength = 76;
    }
    
    $encodedString = base64_encode($string);
    $firstLine = '';
    
    if (0 != $firstLineOffset)
    {
      $firstLine = substr(
        $encodedString, 0, $maxLineLength - $firstLineOffset
        ) . "\r\n";
      $encodedString = substr(
        $encodedString, $maxLineLength - $firstLineOffset
        );
    }
    
    return $firstLine . trim(chunk_split($encodedString, $maxLineLength, "\r\n"));
  }
  
  /**
   * Does nothing.
   */
  public function charsetChanged($charset)
  {
  }
  
}
 /* .tmp\flat\1\2\CommandEvent.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\CommandEvent.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventObject.php';
//@require 'Swift/Transport.php';

/**
 * Generated when a command is sent over an SMTP connection.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_CommandEvent extends Swift_Events_EventObject
{
  
  /**
   * The command sent to the server.
   * @var string
   */
  private $_command;
  
  /**
   * An array of codes which a successful response will contain.
   * @var int[]
   */
  private $_successCodes = array();
  
  /**
   * Create a new CommandEvent for $source with $command.
   * @param Swift_Transport $source
   * @param string $command
   * @param array $successCodes
   */
  public function __construct(Swift_Transport $source,
    $command, $successCodes = array())
  {
    parent::__construct($source);
    $this->_command = $command;
    $this->_successCodes = $successCodes;
  }
  
  /**
   * Get the command which was sent to the server.
   * @return string
   */
  public function getCommand()
  {
    return $this->_command;
  }
  
  /**
   * Get the numeric response codes which indicate success for this command.
   * @return int[]
   */
  public function getSuccessCodes()
  {
    return $this->_successCodes;
  }
  
}
 /* .tmp\flat\1\2\ContentEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\ContentEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Encoder.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Interface for all Transfer Encoding schemes.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_ContentEncoder extends Swift_Encoder
{
  
  /**
   * Encode $in to $out.
   * @param Swift_OutputByteStream $os to read from
   * @param Swift_InputByteStream $is to write to
   * @param int $firstLineOffset
   * @param int $maxLineLength - 0 indicates the default length for this encoding
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0);
  
  /**
   * Get the MIME name of this content encoding scheme.
   * @return string
   */
  public function getName();
  
}
 /* .tmp\flat\1\2\DateHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\DateHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Headers/AbstractHeader.php';


/**
 * A Date MIME Header for Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_DateHeader extends Swift_Mime_Headers_AbstractHeader
{
  
  /**
   * The UNIX timestamp value of this Header.
   * @var int
   * @access private
   */
  private $_timestamp;
  
  /**
   * Creates a new DateHeader with $name and $timestamp.
   * Example:
   * <code>
   * <?php
   * $header = new Swift_Mime_Headers_DateHeader('Date', time());
   * ?>
   * </code>
   * @param string $name of Header
   */
  public function __construct($name)
  {
    $this->setFieldName($name);
  }
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_DATE;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a UNIX timestamp.
   * @param int $model
   */
  public function setFieldBodyModel($model)
  {
    $this->setTimestamp($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns a UNIX timestamp.
   * @return mixed
   */
  public function getFieldBodyModel()
  {
    return $this->getTimestamp();
  }
  
  /**
   * Get the UNIX timestamp of the Date in this Header.
   * @return int
   */
  public function getTimestamp()
  {
    return $this->_timestamp;
  }
  
  /**
   * Set the UNIX timestamp of the Date in this Header.
   * @param int $timestamp
   */
  public function setTimestamp($timestamp)
  {
    if (!is_null($timestamp))
    {
      $timestamp = (int) $timestamp;
    }
    $this->clearCachedValueIf($this->_timestamp != $timestamp);
    $this->_timestamp = $timestamp;
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      if (isset($this->_timestamp))
      {
        $this->setCachedValue(date('r', $this->_timestamp));
      }
    }
    return $this->getCachedValue();
  }
  
}
 /* .tmp\flat\1\2\DecoratorPlugin.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\DecoratorPlugin.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Plugins/Decorator/Replacements.php';

/**
 * Allows customization of Messages on-the-fly.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
class Swift_Plugins_DecoratorPlugin
  implements Swift_Events_SendListener, Swift_Plugins_Decorator_Replacements
{

  /** The replacement map */
  private $_replacements;
  
  /** The body as it was before replacements */
  private $_orginalBody;

  /** The original subject of the message, before replacements */
  private $_originalSubject;

  /** Bodies of children before they are replaced */
  private $_originalChildBodies = array();

  /** The Message that was last replaced */
  private $_lastMessage;

  /**
   * Create a new DecoratorPlugin with $replacements.
   * 
   * The $replacements can either be an associative array, or an implementation
   * of {@link Swift_Plugins_Decorator_Replacements}.
   * 
   * When using an array, it should be of the form:
   * <code>
   * $replacements = array(
   *  "address1@domain.tld" => array("{a}" => "b", "{c}" => "d"),
   *  "address2@domain.tld" => array("{a}" => "x", "{c}" => "y")
   * )
   * </code>
   * 
   * When using an instance of {@link Swift_Plugins_Decorator_Replacements},
   * the object should return just the array of replacements for the address
   * given to {@link Swift_Plugins_Decorator_Replacements::getReplacementsFor()}.
   * 
   * @param mixed $replacements
   */
  public function __construct($replacements)
  {
    if (!($replacements instanceof Swift_Plugins_Decorator_Replacements))
    {
      $this->_replacements = (array) $replacements;
    }
    else
    {
      $this->_replacements = $replacements;
    }
  }

  /**
   * Invoked immediately before the Message is sent.
   * 
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = $evt->getMessage();
    $this->_restoreMessage($message);
    $to = array_keys($message->getTo());
    $address = array_shift($to);
    if ($replacements = $this->getReplacementsFor($address))
    {
      $body = $message->getBody();
      $search = array_keys($replacements);
      $replace = array_values($replacements);
      $bodyReplaced = str_replace(
        $search, $replace, $body
        );
      if ($body != $bodyReplaced)
      {
        $this->_originalBody = $body;
        $message->setBody($bodyReplaced);
      }
      $subject = $message->getSubject();
      $subjectReplaced = str_replace(
        $search, $replace, $subject
        );
      if ($subject != $subjectReplaced)
      {
        $this->_originalSubject = $subject;
        $message->setSubject($subjectReplaced);
      }
      $children = (array) $message->getChildren();
      foreach ($children as $child)
      {
        list($type, ) = sscanf($child->getContentType(), '%[^/]/%s');
        if ('text' == $type)
        {
          $body = $child->getBody();
          $bodyReplaced = str_replace(
            $search, $replace, $body
            );
          if ($body != $bodyReplaced)
          {
            $child->setBody($bodyReplaced);
            $this->_originalChildBodies[$child->getId()] = $body;
          }
        }
      }
      $this->_lastMessage = $message;
    }
  }
  
  /**
   * Find a map of replacements for the address.
   * 
   * If this plugin was provided with a delegate instance of
   * {@link Swift_Plugins_Decorator_Replacements} then the call will be
   * delegated to it.  Otherwise, it will attempt to find the replacements
   * from the array provided in the constructor.
   * 
   * If no replacements can be found, an empty value (NULL) is returned.
   * 
   * @param string $address
   * 
   * @return array
   */
  public function getReplacementsFor($address)
  {
    if ($this->_replacements instanceof Swift_Plugins_Decorator_Replacements)
    {
      return $this->_replacements->getReplacementsFor($address);
    }
    else
    {
      return isset($this->_replacements[$address])
        ? $this->_replacements[$address]
        : null
        ;
    }
  }

  /**
   * Invoked immediately after the Message is sent.
   * 
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $this->_restoreMessage($evt->getMessage());
  }

  // -- Private methods

  /** Restore a changed message back to its original state */
  private function _restoreMessage(Swift_Mime_Message $message)
  {
    if ($this->_lastMessage === $message)
    {
      if (isset($this->_originalBody))
      {
        $message->setBody($this->_originalBody);
        $this->_originalBody = null;
      }
      if (isset($this->_originalSubject))
      {
        $message->setSubject($this->_originalSubject);
        $this->_originalSubject = null;
      }
      if (!empty($this->_originalChildBodies))
      {
        $children = (array) $message->getChildren();
        foreach ($children as $child)
        {
          $id = $child->getId();
          if (array_key_exists($id, $this->_originalChildBodies))
          {
            $child->setBody($this->_originalChildBodies[$id]);
          }
        }
        $this->_originalChildBodies = array();
      }
      $this->_lastMessage = null;
    }
  }

}
 /* .tmp\flat\1\2\FailoverTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\FailoverTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/LoadBalancedTransport.php';
//@require 'Swift/Mime/Message.php';

/**
 * Contains a list of redundant Transports so when one fails, the next is used.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_FailoverTransport
  extends Swift_Transport_LoadBalancedTransport
{
  
  /**
   * Registered transport curently used.
   * @var Swift_Transport
   * @access private
   */
  private $_currentTransport;
  
  /**
   * Creates a new FailoverTransport.
   */
  public function __construct()
  {
    parent::__construct();
  }
  
  /**
   * Send the given Message.
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $maxTransports = count($this->_transports);
    $sent = 0;
    
    for ($i = 0; $i < $maxTransports
      && $transport = $this->_getNextTransport(); ++$i)
    {
      try
      {
        if (!$transport->isStarted())
        {
          $transport->start();
        }
        
        return $transport->send($message, $failedRecipients);
      }
      catch (Swift_TransportException $e)
      {
        $this->_killCurrentTransport();
      }
    }
    
    if (count($this->_transports) == 0)
    {
      throw new Swift_TransportException(
        'All Transports in FailoverTransport failed, or no Transports available'
        );
    }
    
    return $sent;
  }
  
  // -- Protected methods
  
  protected function _getNextTransport()
  {
    if (!isset($this->_currentTransport))
    {
      $this->_currentTransport = parent::_getNextTransport();
    }
    return $this->_currentTransport;
  }
  
  protected function _killCurrentTransport()
  {
    $this->_currentTransport = null;
    parent::_killCurrentTransport();
  }
  
}
 /* .tmp\flat\1\2\FileByteStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\FileByteStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/ByteStream/AbstractFilterableInputStream.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/FileStream.php';
//@require 'Swift/IoException.php';

/**
 * Allows reading and writing of bytes to and from a file.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
class Swift_ByteStream_FileByteStream
  extends Swift_ByteStream_AbstractFilterableInputStream
  implements Swift_FileStream
{
  
  /** The internal pointer offset */
  private $_offset = 0;
  
  /** The path to the file */
  private $_path;
  
  /** The mode this file is opened in for writing */
  private $_mode;
  
  /** A lazy-loaded resource handle for reading the file */
  private $_reader;
  
  /** A lazy-loaded resource handle for writing the file */
  private $_writer;
  
  /** If magic_quotes_runtime is on, this will be true */
  private $_quotes = false;
  
  /**
   * Create a new FileByteStream for $path.
   * @param string $path
   * @param string $writable if true
   */
  public function __construct($path, $writable = false)
  {
    $this->_path = $path;
    $this->_mode = $writable ? 'w+b' : 'rb';
    $this->_quotes = get_magic_quotes_runtime();
  }
  
  /**
   * Get the complete path to the file.
   * @return string
   */
  public function getPath()
  {
    return $this->_path;
  }
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   * @throws Swift_IoException
   */
  public function read($length)
  {
    $fp = $this->_getReadHandle();
    if (!feof($fp))
    {
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(0);
      }
      $bytes = fread($fp, $length);
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(1);
      }
      $this->_offset = ftell($fp);
      return $bytes;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Move the internal read pointer to $byteOffset in the stream.
   * @param int $byteOffset
   * @return boolean
   */
  public function setReadPointer($byteOffset)
  {
    if (isset($this->_reader))
    {
      fseek($this->_reader, $byteOffset, SEEK_SET);
    }
    $this->_offset = $byteOffset;
  }
  
  // -- Private methods
  
  /** Just write the bytes to the file */
  protected function _commit($bytes)
  {
    fwrite($this->_getWriteHandle(), $bytes);
    $this->_resetReadHandle();
  }
  
  /** Not used */
  protected function _flush()
  {
  }
  
  /** Get the resource for reading */
  private function _getReadHandle()
  {
    if (!isset($this->_reader))
    {
      if (!$this->_reader = fopen($this->_path, 'rb'))
      {
        throw new Swift_IoException(
          'Unable to open file for reading [' . $this->_path . ']'
          );
      }
      fseek($this->_reader, $this->_offset, SEEK_SET);
    }
    return $this->_reader;
  }
  
  /** Get the resource for writing */
  private function _getWriteHandle()
  {
    if (!isset($this->_writer))
    {
      if (!$this->_writer = fopen($this->_path, $this->_mode))
      {
        throw new Swift_IoException(
          'Unable to open file for writing [' . $this->_path . ']'
          );
      }
    }
    return $this->_writer;
  }
  
  /** Force a reload of the resource for writing */
  private function _resetWriteHandle()
  {
    if (isset($this->_writer))
    {
      fclose($this->_writer);
      $this->_writer = null;
    }
  }
  
  /** Force a reload of the resource for reading */
  private function _resetReadHandle()
  {
    if (isset($this->_reader))
    {
      fclose($this->_reader);
      $this->_reader = null;
    }
  }
  
}
 /* .tmp\flat\1\2\HeaderEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\HeaderEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Encoder.php';

/**
 * Interface for all Header Encoding schemes.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_HeaderEncoder extends Swift_Encoder
{
  
  /**
   * Get the MIME name of this content encoding scheme.
   * @return string
   */
  public function getName();
  
}
 /* .tmp\flat\1\2\IdentificationHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\IdentificationHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Headers/AbstractHeader.php';
//@require 'Swift/RfcComplianceException.php';

/**
 * An ID MIME Header for something like Message-ID or Content-ID.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_IdentificationHeader
  extends Swift_Mime_Headers_AbstractHeader
{
  
  /**
   * The IDs used in the value of this Header.
   * This may hold multiple IDs or just a single ID.
   * @var string[]
   * @access private
   */
  private $_ids = array();
  
  /**
   * Creates a new IdentificationHeader with the given $name and $id.
   * @param string $name
   */
  public function __construct($name)
  {
    $this->setFieldName($name);
    $this->initializeGrammar();
  }
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_ID;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a string ID, or an array of IDs
   * @param mixed $model
   * @throws Swift_RfcComplianceException
   */
  public function setFieldBodyModel($model)
  {
    $this->setId($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns an array of IDs
   * @return array
   */
  public function getFieldBodyModel()
  {
    return $this->getIds();
  }
  
  /**
   * Set the ID used in the value of this header.
   * @param string $id
   * @throws Swift_RfcComplianceException
   */
  public function setId($id)
  {
    return $this->setIds(array($id));
  }
  
  /**
   * Get the ID used in the value of this Header.
   * If multiple IDs are set only the first is returned.
   * @return string
   */
  public function getId()
  {
    if (count($this->_ids) > 0)
    {
      return $this->_ids[0];
    }
  }
  
  /**
   * Set a collection of IDs to use in the value of this Header.
   * @param string[] $ids
   * @throws Swift_RfcComplianceException
   */
  public function setIds(array $ids)
  {
    $actualIds = array();
    
    foreach ($ids as $k => $id)
    {
      if (preg_match(
        '/^' . $this->getGrammar('id-left') . '@' .
        $this->getGrammar('id-right') . '$/D',
        $id
        ))
      {
        $actualIds[] = $id;
      }
      else
      {
        throw new Swift_RfcComplianceException(
          'Invalid ID given <' . $id . '>'
          );
      }
    }
    
    $this->clearCachedValueIf($this->_ids != $actualIds);
    $this->_ids = $actualIds;
  }
  
  /**
   * Get the list of IDs used in this Header.
   * @return string[]
   */
  public function getIds()
  {
    return $this->_ids;
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   * @throws Swift_RfcComplianceException
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      $angleAddrs = array();
    
      foreach ($this->_ids as $id)
      {
        $angleAddrs[] = '<' . $id . '>';
      }
    
      $this->setCachedValue(implode(' ', $angleAddrs));
    }
    return $this->getCachedValue();
  }
  
}
 /* .tmp\flat\1\2\LoadBalancedTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\LoadBalancedTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/LoadBalancedTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Redudantly and rotationally uses several Transport implementations when sending.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_LoadBalancedTransport extends Swift_Transport_LoadBalancedTransport
{
  
  /**
   * Creates a new LoadBalancedTransport with $transports.
   * @param array $transports
   */
  public function __construct($transports = array())
  {
    call_user_func_array(
      array($this, 'Swift_Transport_LoadBalancedTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.loadbalanced')
      );
    
    $this->setTransports($transports);
  }
  
  /**
   * Create a new LoadBalancedTransport instance.
   * @param string $transports
   * @return Swift_LoadBalancedTransport
   */
  public static function newInstance($transports = array())
  {
    return new self($transports);
  }
  
}
 /* .tmp\flat\1\2\LoggerPlugin.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\LoggerPlugin.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/CommandListener.php';
//@require 'Swift/Events/CommandEvent.php';
//@require 'Swift/Events/ResponseListener.php';
//@require 'Swift/Events/ResponseEvent.php';
//@require 'Swift/Events/TransportChangeListener.php';
//@require 'Swift/Events/TransportChangeEvent.php';
//@require 'Swift/Events/TransportExceptionEvent.php';
//@require 'Swift/Events/TransportExceptionListener.php';
//@require 'Swift/Events/TransportException.php';
//@require 'Swift/Plugins/Logger.php';

/**
 * Does real time logging of Transport level information.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
class Swift_Plugins_LoggerPlugin
  implements Swift_Events_CommandListener, Swift_Events_ResponseListener,
  Swift_Events_TransportChangeListener, Swift_Events_TransportExceptionListener,
  Swift_Plugins_Logger
{
  
  /** The logger which is delegated to */
  private $_logger;
  
  /**
   * Create a new LoggerPlugin using $logger.
   * 
   * @param Swift_Plugins_Logger $logger
   */
  public function __construct(Swift_Plugins_Logger $logger)
  {
    $this->_logger = $logger;
  }
  
  /**
   * Add a log entry.
   * 
   * @param string $entry
   */
  public function add($entry)
  {
    $this->_logger->add($entry);
  }
  
  /**
   * Clear the log contents.
   */
  public function clear()
  {
    $this->_logger->clear();
  }
  
  /**
   * Get this log as a string.
   * 
   * @return string
   */
  public function dump()
  {
    return $this->_logger->dump();
  }
  
  /**
   * Invoked immediately following a command being sent.
   * 
   * @param Swift_Events_ResponseEvent $evt
   */
  public function commandSent(Swift_Events_CommandEvent $evt)
  {
    $command = $evt->getCommand();
    $this->_logger->add(sprintf(">> %s", $command));
  }
  
  /**
   * Invoked immediately following a response coming back.
   * 
   * @param Swift_Events_ResponseEvent $evt
   */
  public function responseReceived(Swift_Events_ResponseEvent $evt)
  {
    $response = $evt->getResponse();
    $this->_logger->add(sprintf("<< %s", $response));
  }
  
  /**
   * Invoked just before a Transport is started.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ Starting %s", $transportName));
  }
  
  /**
   * Invoked immediately after the Transport is started.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function transportStarted(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ %s started", $transportName));
  }
  
  /**
   * Invoked just before a Transport is stopped.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ Stopping %s", $transportName));
  }
  
  /**
   * Invoked immediately after the Transport is stopped.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function transportStopped(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ %s stopped", $transportName));
  }
  
  /**
   * Invoked as a TransportException is thrown in the Transport system.
   * 
   * @param Swift_Events_TransportExceptionEvent $evt
   */
  public function exceptionThrown(Swift_Events_TransportExceptionEvent $evt)
  {
    $e = $evt->getException();
    $message = $e->getMessage();
    $this->_logger->add(sprintf("!! %s", $message));
    $message .= PHP_EOL;
    $message .= 'Log data:' . PHP_EOL;
    $message .= $this->_logger->dump();
    $evt->cancelBubble();
    throw new Swift_TransportException($message);
  }
  
}
 /* .tmp\flat\1\2\MailboxHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\MailboxHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Headers/AbstractHeader.php';
//@require 'Swift/Mime/HeaderEncoder.php';

/**
 * A Mailbox Address MIME Header for something like From or Sender.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_MailboxHeader extends Swift_Mime_Headers_AbstractHeader
{
  
  /**
   * The mailboxes used in this Header.
   * @var string[]
   * @access private
   */
  private $_mailboxes = array();
  
  /**
   * Creates a new MailboxHeader with $name.
   * @param string $name of Header
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function __construct($name, Swift_Mime_HeaderEncoder $encoder)
  {
    $this->setFieldName($name);
    $this->setEncoder($encoder);
    $this->initializeGrammar();
  }
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_MAILBOX;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a string, or an array of addresses.
   * @param mixed $model
   * @throws Swift_RfcComplianceException
   */
  public function setFieldBodyModel($model)
  {
    $this->setNameAddresses($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns an associative array like {@link getNameAddresses()}
   * @return array
   * @throws Swift_RfcComplianceException
   */
  public function getFieldBodyModel()
  {
    return $this->getNameAddresses();
  }
  
  /**
   * Set a list of mailboxes to be shown in this Header.
   * The mailboxes can be a simple array of addresses, or an array of
   * key=>value pairs where (email => personalName).
   * Example:
   * <code>
   * <?php
   * //Sets two mailboxes in the Header, one with a personal name
   * $header->setNameAddresses(array(
   *  'chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' //No associated personal name
   *  ));
   * ?>
   * </code>
   * @param string|string[] $mailboxes
   * @throws Swift_RfcComplianceException
   * @see __construct()
   * @see setAddresses()
   * @see setValue()
   */
  public function setNameAddresses($mailboxes)
  {
    $this->_mailboxes = $this->normalizeMailboxes((array) $mailboxes);
    $this->setCachedValue(null); //Clear any cached value
  }
  
  /**
   * Get the full mailbox list of this Header as an array of valid RFC 2822 strings.
   * Example:
   * <code>
   * <?php
   * $header = new Swift_Mime_Headers_MailboxHeader('From',
   *  array('chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' => 'Mark Corbyn')
   *  );
   * print_r($header->getNameAddressStrings());
   * // array (
   * // 0 => Chris Corbyn <chris@swiftmailer.org>,
   * // 1 => Mark Corbyn <mark@swiftmailer.org>
   * // )
   * ?>
   * </code>
   * @return string[]
   * @throws Swift_RfcComplianceException
   * @see getNameAddresses()
   * @see toString()
   */
  public function getNameAddressStrings()
  {
    return $this->_createNameAddressStrings($this->getNameAddresses());
  }
  
  /**
   * Get all mailboxes in this Header as key=>value pairs.
   * The key is the address and the value is the name (or null if none set).
   * Example:
   * <code>
   * <?php
   * $header = new Swift_Mime_Headers_MailboxHeader('From',
   *  array('chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' => 'Mark Corbyn')
   *  );
   * print_r($header->getNameAddresses());
   * // array (
   * // chris@swiftmailer.org => Chris Corbyn,
   * // mark@swiftmailer.org => Mark Corbyn
   * // )
   * ?>
   * </code>
   * @return string[]
   * @see getAddresses()
   * @see getNameAddressStrings()
   */
  public function getNameAddresses()
  {
    return $this->_mailboxes;
  }
  
  /**
   * Makes this Header represent a list of plain email addresses with no names.
   * Example:
   * <code>
   * <?php
   * //Sets three email addresses as the Header data
   * $header->setAddresses(
   *  array('one@domain.tld', 'two@domain.tld', 'three@domain.tld')
   *  );
   * ?>
   * </code>
   * @param string[] $addresses
   * @throws Swift_RfcComplianceException
   * @see setNameAddresses()
   * @see setValue()
   */
  public function setAddresses($addresses)
  {
    return $this->setNameAddresses(array_values((array) $addresses));
  }
  
  /**
   * Get all email addresses in this Header.
   * @return string[]
   * @see getNameAddresses()
   */
  public function getAddresses()
  {
    return array_keys($this->_mailboxes);
  }
  
  /**
   * Remove one or more addresses from this Header.
   * @param string|string[] $addresses
   */
  public function removeAddresses($addresses)
  {
    $this->setCachedValue(null);
    foreach ((array) $addresses as $address)
    {
      unset($this->_mailboxes[$address]);
    }
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @throws Swift_RfcComplianceException
   * @see toString()
   */
  public function getFieldBody()
  {
    //Compute the string value of the header only if needed
    if (is_null($this->getCachedValue()))
    {
      $this->setCachedValue($this->createMailboxListString($this->_mailboxes));
    }
    return $this->getCachedValue();
  }
  
  // -- Points of extension
  
  /**
   * Normalizes a user-input list of mailboxes into consistent key=>value pairs.
   * @param string[] $mailboxes
   * @return string[]
   * @access protected
   */
  protected function normalizeMailboxes(array $mailboxes)
  {
    $actualMailboxes = array();
    
    foreach ($mailboxes as $key => $value)
    {
      if (is_string($key)) //key is email addr
      {
        $address = $key;
        $name = $value;
      }
      else
      {
        $address = $value;
        $name = null;
      }
      $this->_assertValidAddress($address);
      $actualMailboxes[$address] = $name;
    }
    
    return $actualMailboxes;
  }
  
  /**
   * Produces a compliant, formatted display-name based on the string given.
   * @param string $displayName as displayed
   * @param boolean $shorten the first line to make remove for header name
   * @return string
   * @access protected
   */
  protected function createDisplayNameString($displayName, $shorten = false)
  {
    return $this->createPhrase($this, $displayName,
      $this->getCharset(), $this->getEncoder(), $shorten
      );
  }
  
  /**
   * Creates a string form of all the mailboxes in the passed array.
   * @param string[] $mailboxes
   * @return string
   * @throws Swift_RfcComplianceException
   * @access protected
   */
  protected function createMailboxListString(array $mailboxes)
  {
    return implode(', ', $this->_createNameAddressStrings($mailboxes));
  }
  
  // -- Private methods
  
  /**
   * Return an array of strings conforming the the name-addr spec of RFC 2822.
   * @param string[] $mailboxes
   * @return string[]
   * @access private
   */
  private function _createNameAddressStrings(array $mailboxes)
  {
    $strings = array();
    
    foreach ($mailboxes as $email => $name)
    {
      $mailboxStr = $email;
      if (!is_null($name))
      {
        $nameStr = $this->createDisplayNameString($name, empty($strings));
        $mailboxStr = $nameStr . ' <' . $mailboxStr . '>';
      }
      $strings[] = $mailboxStr;
    }
    
    return $strings;
  }
  
  /**
   * Throws an Exception if the address passed does not comply with RFC 2822.
   * @param string $address
   * @throws Exception If invalid.
   * @access protected
   */
  private function _assertValidAddress($address)
  {
    if (!preg_match('/^' . $this->getGrammar('addr-spec') . '$/D',
      $address))
    {
      throw new Swift_RfcComplianceException(
        'Address in mailbox given [' . $address .
        '] does not comply with RFC 2822, 3.6.2.'
        );
    }
  }
  
}
 /* .tmp\flat\1\2\MailTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\MailTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/MailTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Sends Messages using the mail() function.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_MailTransport extends Swift_Transport_MailTransport
{
  
  /**
   * Create a new MailTransport, optionally specifying $extraParams.
   * @param string $extraParams
   */
  public function __construct($extraParams = '-f%s')
  {
    call_user_func_array(
      array($this, 'Swift_Transport_MailTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.mail')
      );
    
    $this->setExtraParams($extraParams);
  }
  
  /**
   * Create a new MailTransport instance.
   * @param string $extraParams To be passed to mail()
   * @return Swift_MailTransport
   */
  public static function newInstance($extraParams = '-f%s')
  {
    return new self($extraParams);
  }
  
}
 /* .tmp\flat\1\2\Message.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\Message.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/MimeEntity.php';

/**
 * A Message (RFC 2822) object.
 * 
 * @package Swift
 * @subpackage Mime
 * 
 * @author Chris Corbyn
 */
interface Swift_Mime_Message extends Swift_Mime_MimeEntity
{
  
  /**
   * Generates a valid Message-ID and switches to it.
   * 
   * @return string
   */
  public function generateId();
  
  /**
   * Set the subject of the message.
   * 
   * @param string $subject
   */
  public function setSubject($subject);
  
  /**
   * Get the subject of the message.
   * 
   * @return string
   */
  public function getSubject();
  
  /**
   * Set the origination date of the message as a UNIX timestamp.
   * 
   * @param int $date
   */
  public function setDate($date);
  
  /**
   * Get the origination date of the message as a UNIX timestamp.
   * 
   * @return int
   */
  public function getDate();
  
  /**
   * Set the return-path (bounce-detect) address.
   * 
   * @param string $address
   */
  public function setReturnPath($address);
  
  /**
   * Get the return-path (bounce-detect) address.
   * 
   * @return string
   */
  public function getReturnPath();
  
  /**
   * Set the sender of this message.
   * 
   * If multiple addresses are present in the From field, this SHOULD be set.
   * 
   * According to RFC 2822 it is a requirement when there are multiple From
   * addresses, but Swift itself does not require it directly.
   * 
   * An associative array (with one element!) can be used to provide a display-
   * name: i.e. array('email@address' => 'Real Name').
   * 
   * If the second parameter is provided and the first is a string, then $name
   * is associated with the address.
   * 
   * @param mixed $address
   * @param string $name optional
   */
  public function setSender($address, $name = null);
  
  /**
   * Get the sender address for this message.
   * 
   * This has a higher significance than the From address.
   * 
   * @return string
   */
  public function getSender();
  
  /**
   * Set the From address of this message.
   * 
   * It is permissible for multiple From addresses to be set using an array.
   * 
   * If multiple From addresses are used, you SHOULD set the Sender address and
   * according to RFC 2822, MUST set the sender address.
   * 
   * An array can be used if display names are to be provided: i.e.
   * array('email@address.com' => 'Real Name').
   * 
   * If the second parameter is provided and the first is a string, then $name
   * is associated with the address.
   *
   * @param mixed $addresses
   * @param string $name optional
   */
  public function setFrom($addresses, $name = null);
  
  /**
   * Get the From address(es) of this message.
   * 
   * This method always returns an associative array where the keys are the
   * addresses.
   * 
   * @return string[]
   */
  public function getFrom();
  
  /**
   * Set the Reply-To address(es).
   * 
   * Any replies from the receiver will be sent to this address.
   * 
   * It is permissible for multiple reply-to addresses to be set using an array.
   * 
   * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
   * 
   * If the second parameter is provided and the first is a string, then $name
   * is associated with the address.
   * 
   * @param mixed $addresses
   * @param string $name optional
   */
  public function setReplyTo($addresses, $name = null);
  
  /**
   * Get the Reply-To addresses for this message.
   * 
   * This method always returns an associative array where the keys provide the
   * email addresses.
   * 
   * @return string[]
   */
  public function getReplyTo();
  
  /**
   * Set the To address(es).
   * 
   * Recipients set in this field will receive a copy of this message.
   * 
   * This method has the same synopsis as {@link setFrom()} and {@link setCc()}.
   * 
   * If the second parameter is provided and the first is a string, then $name
   * is associated with the address.
   * 
   * @param mixed $addresses
   * @param string $name optional
   */
  public function setTo($addresses, $name = null);
  
  /**
   * Get the To addresses for this message.
   * 
   * This method always returns an associative array, whereby the keys provide
   * the actual email addresses.
   * 
   * @return string[]
   */
  public function getTo();
  
  /**
   * Set the Cc address(es).
   * 
   * Recipients set in this field will receive a 'carbon-copy' of this message.
   * 
   * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
   * 
   * @param mixed $addresses
   * @param string $name optional
   */
  public function setCc($addresses, $name = null);
  
  /**
   * Get the Cc addresses for this message.
   * 
   * This method always returns an associative array, whereby the keys provide
   * the actual email addresses.
   * 
   * @return string[]
   */
  public function getCc();
  
  /**
   * Set the Bcc address(es).
   * 
   * Recipients set in this field will receive a 'blind-carbon-copy' of this
   * message.
   * 
   * In other words, they will get the message, but any other recipients of the
   * message will have no such knowledge of their receipt of it.
   * 
   * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
   * 
   * @param mixed $addresses
   * @param string $name optional
   */
  public function setBcc($addresses, $name = null);
  
  /**
   * Get the Bcc addresses for this message.
   * 
   * This method always returns an associative array, whereby the keys provide
   * the actual email addresses.
   * 
   * @return string[]
   */
  public function getBcc();
  
}
 /* .tmp\flat\1\2\PathHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\PathHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Headers/AbstractHeader.php';
//@require 'Swift/RfcComplianceException.php';

/**
 * A Path Header in Swift Mailer, such a Return-Path.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_PathHeader extends Swift_Mime_Headers_AbstractHeader
{
  
  /**
   * The address in this Header (if specified).
   * @var string
   * @access private
   */
  private $_address;
  
  /**
   * Creates a new PathHeader with the given $name.
   * @param string $name
   */
  public function __construct($name)
  {
    $this->setFieldName($name);
    $this->initializeGrammar();
  }
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_PATH;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a string for an address.
   * @param string $model
   * @throws Swift_RfcComplianceException
   */
  public function setFieldBodyModel($model)
  {
    $this->setAddress($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns a string email address.
   * @return mixed
   */
  public function getFieldBodyModel()
  {
    return $this->getAddress();
  }
  
  /**
   * Set the Address which should appear in this Header.
   * @param string $address
   * @throws Swift_RfcComplianceException
   */
  public function setAddress($address)
  {
    if (is_null($address))
    {
      $this->_address = null;
    }
    elseif ('' == $address
      || preg_match('/^' . $this->getGrammar('addr-spec') . '$/D', $address))
    {
      $this->_address = $address;
    }
    else
    {
      throw new Swift_RfcComplianceException(
        'Address set in PathHeader does not comply with addr-spec of RFC 2822.'
        );
    }
    $this->setCachedValue(null);
  }
  
  /**
   * Get the address which is used in this Header (if any).
   * Null is returned if no address is set.
   * @return string
   */
  public function getAddress()
  {
    return $this->_address;
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      if (isset($this->_address))
      {
        $this->setCachedValue('<' . $this->_address . '>');
      }
    }
    return $this->getCachedValue();
  }
  
}
 /* .tmp\flat\1\2\Pop3Exception.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\Pop3Exception.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/IoException.php';

/**
 * Pop3Exception thrown when an error occurs connecting to a POP3 host.
 * 
 * @package Swift
 * @subpackage Transport
 * 
 * @author Chris Corbyn
 */
class Swift_Plugins_Pop_Pop3Exception extends Swift_IoException
{
  
  /**
   * Create a new Pop3Exception with $message.
   * 
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
 /* .tmp\flat\1\2\PopBeforeSmtpPlugin.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\PopBeforeSmtpPlugin.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/TransportChangeListener.php';
//@require 'Swift/Events/TransportChangeEvent.php';

/**
 * Makes sure a connection to a POP3 host has been established prior to connecting to SMTP.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
class Swift_Plugins_PopBeforeSmtpPlugin
  implements Swift_Events_TransportChangeListener, Swift_Plugins_Pop_Pop3Connection
{
  
  /** A delegate connection to use (mostly a test hook) */
  private $_connection;
  
  /** Hostname of the POP3 server */
  private $_host;
  
  /** Port number to connect on */
  private $_port;
  
  /** Encryption type to use (if any) */
  private $_crypto;
  
  /** Username to use (if any) */
  private $_username;
  
  /** Password to use (if any) */
  private $_password;
  
  /** Established connection via TCP socket */
  private $_socket;
  
  /** Connect timeout in seconds */
  private $_timeout = 10;
  
  /** SMTP Transport to bind to */
  private $_transport;
  
  /**
   * Create a new PopBeforeSmtpPlugin for $host and $port.
   * 
   * @param string $host
   * @param int $port
   * @param string $cypto as "tls" or "ssl"
   */
  public function __construct($host, $port = 110, $crypto = null)
  {
    $this->_host = $host;
    $this->_port = $port;
    $this->_crypto = $crypto;
  }
  
  /**
   * Create a new PopBeforeSmtpPlugin for $host and $port.
   * 
   * @param string $host
   * @param int $port
   * @param string $cypto as "tls" or "ssl"
   * 
   * @return Swift_Plugins_PopBeforeSmtpPlugin
   */
  public static function newInstance($host, $port = 110, $crypto = null)
  {
    return new self($host, $port, $crypto);
  }
  
  /**
   * Set a Pop3Connection to delegate to instead of connecting directly.
   * 
   * @param Swift_Plugins_Pop_Pop3Connection $connection
   */
  public function setConnection(Swift_Plugins_Pop_Pop3Connection $connection)
  {
    $this->_connection = $connection;
    return $this;
  }
  
  /**
   * Bind this plugin to a specific SMTP transport instance.
   * 
   * @param Swift_Transport
   */
  public function bindSmtp(Swift_Transport $smtp)
  {
    $this->_transport = $smtp;
  }
  
  /**
   * Set the connection timeout in seconds (default 10).
   * 
   * @param int $timeout
   */
  public function setTimeout($timeout)
  {
    $this->_timeout = (int) $timeout;
    return $this;
  }
  
  /**
   * Set the username to use when connecting (if needed).
   * 
   * @param string $username
   */
  public function setUsername($username)
  {
    $this->_username = $username;
    return $this;
  }
  
  /**
   * Set the password to use when connecting (if needed).
   * 
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->_password = $password;
    return $this;
  }
  
  /**
   * Connect to the POP3 host and authenticate.
   * 
   * @throws Swift_Plugins_Pop_Pop3Exception if connection fails
   */
  public function connect()
  {
    if (isset($this->_connection))
    {
      $this->_connection->connect();
    }
    else
    {
      if (!isset($this->_socket))
      {
        if (!$socket = fsockopen(
          $this->_getHostString(), $this->_port, $errno, $errstr, $this->_timeout))
        {
          throw new Swift_Plugins_Pop_Pop3Exception(
            sprintf('Failed to connect to POP3 host [%s]: %s', $this->_host, $errstr)
          );
        }
        $this->_socket = $socket;
        
        if (false === $greeting = fgets($this->_socket))
        {
          throw new Swift_Plugins_Pop_Pop3Exception(
            sprintf('Failed to connect to POP3 host [%s]', trim($greeting))
          );
        }
        
        $this->_assertOk($greeting);
        
        if ($this->_username)
        {
          $this->_command(sprintf("USER %s\r\n", $this->_username));
          $this->_command(sprintf("PASS %s\r\n", $this->_password));
        }
      }
    }
  }
  
  /**
   * Disconnect from the POP3 host.
   */
  public function disconnect()
  {
    if (isset($this->_connection))
    {
      $this->_connection->disconnect();
    }
    else
    {
      $this->_command("QUIT\r\n");
      if (!fclose($this->_socket))
      {
        throw new Swift_Plugins_Pop_Pop3Exception(
          sprintf('POP3 host [%s] connection could not be stopped', $this->_host)
        );
      }
      $this->_socket = null;
    }
  }
  
  /**
   * Invoked just before a Transport is started.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt)
  {
    if (isset($this->_transport))
    {
      if ($this->_transport !== $evt->getTransport())
      {
        return;
      }
    }
    
    $this->connect();
    $this->disconnect();
  }
  
  /**
   * Not used.
   */
  public function transportStarted(Swift_Events_TransportChangeEvent $evt)
  {
  }
  
  /**
   * Not used.
   */
  public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt)
  {
  }
  
  /**
   * Not used.
   */
  public function transportStopped(Swift_Events_TransportChangeEvent $evt)
  {
  }
  
  // -- Private Methods
  
  private function _command($command)
  {
    if (!fwrite($this->_socket, $command))
    {
      throw new Swift_Plugins_Pop_Pop3Exception(
        sprintf('Failed to write command [%s] to POP3 host', trim($command))
      );
    }
    
    if (false === $response = fgets($this->_socket))
    {
      throw new Swift_Plugins_Pop_Pop3Exception(
        sprintf('Failed to read from POP3 host after command [%s]', trim($command))
      );
    }
    
    $this->_assertOk($response);
    
    return $response;
  }
  
  private function _assertOk($response)
  {
    if (substr($response, 0, 3) != '+OK')
    {
      throw new Swift_Plugins_Pop_Pop3Exception(
        sprintf('POP3 command failed [%s]', trim($response))
      );
    }
  }
  
  private function _getHostString()
  {
    $host = $this->_host;
    switch (strtolower($this->_crypto))
    {
      case 'ssl':
        $host = 'ssl://' . $host;
        break;
      
      case 'tls':
        $host = 'tls://' . $host;
        break;
    }
    return $host;
  }
  
}
 /* .tmp\flat\1\2\QpEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\QpEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Encoder.php';
//@require 'Swift/CharacterStream.php';

/**
 * Handles Quoted Printable (QP) Encoding in Swift Mailer.
 * Possibly the most accurate RFC 2045 QP implementation found in PHP.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_QpEncoder implements Swift_Encoder
{

  /**
   * The CharacterStream used for reading characters (as opposed to bytes).
   * @var Swift_CharacterStream
   * @access protected
   */
  protected $_charStream;

  /**
   * A filter used if input should be canonicalized.
   * @var Swift_StreamFilter
   * @access protected
   */
  protected $_filter;

  /**
   * Pre-computed QP for HUGE optmization.
   * @var string[]
   * @access protected
   */
  protected static $_qpMap = array(
    0   => '=00', 1   => '=01', 2   => '=02', 3   => '=03', 4   => '=04',
    5   => '=05', 6   => '=06', 7   => '=07', 8   => '=08', 9   => '=09',
    10  => '=0A', 11  => '=0B', 12  => '=0C', 13  => '=0D', 14  => '=0E',
    15  => '=0F', 16  => '=10', 17  => '=11', 18  => '=12', 19  => '=13',
    20  => '=14', 21  => '=15', 22  => '=16', 23  => '=17', 24  => '=18',
    25  => '=19', 26  => '=1A', 27  => '=1B', 28  => '=1C', 29  => '=1D',
    30  => '=1E', 31  => '=1F', 32  => '=20', 33  => '=21', 34  => '=22',
    35  => '=23', 36  => '=24', 37  => '=25', 38  => '=26', 39  => '=27',
    40  => '=28', 41  => '=29', 42  => '=2A', 43  => '=2B', 44  => '=2C',
    45  => '=2D', 46  => '=2E', 47  => '=2F', 48  => '=30', 49  => '=31',
    50  => '=32', 51  => '=33', 52  => '=34', 53  => '=35', 54  => '=36',
    55  => '=37', 56  => '=38', 57  => '=39', 58  => '=3A', 59  => '=3B',
    60  => '=3C', 61  => '=3D', 62  => '=3E', 63  => '=3F', 64  => '=40',
    65  => '=41', 66  => '=42', 67  => '=43', 68  => '=44', 69  => '=45',
    70  => '=46', 71  => '=47', 72  => '=48', 73  => '=49', 74  => '=4A',
    75  => '=4B', 76  => '=4C', 77  => '=4D', 78  => '=4E', 79  => '=4F',
    80  => '=50', 81  => '=51', 82  => '=52', 83  => '=53', 84  => '=54',
    85  => '=55', 86  => '=56', 87  => '=57', 88  => '=58', 89  => '=59',
    90  => '=5A', 91  => '=5B', 92  => '=5C', 93  => '=5D', 94  => '=5E',
    95  => '=5F', 96  => '=60', 97  => '=61', 98  => '=62', 99  => '=63',
    100 => '=64', 101 => '=65', 102 => '=66', 103 => '=67', 104 => '=68',
    105 => '=69', 106 => '=6A', 107 => '=6B', 108 => '=6C', 109 => '=6D',
    110 => '=6E', 111 => '=6F', 112 => '=70', 113 => '=71', 114 => '=72',
    115 => '=73', 116 => '=74', 117 => '=75', 118 => '=76', 119 => '=77',
    120 => '=78', 121 => '=79', 122 => '=7A', 123 => '=7B', 124 => '=7C',
    125 => '=7D', 126 => '=7E', 127 => '=7F', 128 => '=80', 129 => '=81',
    130 => '=82', 131 => '=83', 132 => '=84', 133 => '=85', 134 => '=86',
    135 => '=87', 136 => '=88', 137 => '=89', 138 => '=8A', 139 => '=8B',
    140 => '=8C', 141 => '=8D', 142 => '=8E', 143 => '=8F', 144 => '=90',
    145 => '=91', 146 => '=92', 147 => '=93', 148 => '=94', 149 => '=95',
    150 => '=96', 151 => '=97', 152 => '=98', 153 => '=99', 154 => '=9A',
    155 => '=9B', 156 => '=9C', 157 => '=9D', 158 => '=9E', 159 => '=9F',
    160 => '=A0', 161 => '=A1', 162 => '=A2', 163 => '=A3', 164 => '=A4',
    165 => '=A5', 166 => '=A6', 167 => '=A7', 168 => '=A8', 169 => '=A9',
    170 => '=AA', 171 => '=AB', 172 => '=AC', 173 => '=AD', 174 => '=AE',
    175 => '=AF', 176 => '=B0', 177 => '=B1', 178 => '=B2', 179 => '=B3',
    180 => '=B4', 181 => '=B5', 182 => '=B6', 183 => '=B7', 184 => '=B8',
    185 => '=B9', 186 => '=BA', 187 => '=BB', 188 => '=BC', 189 => '=BD',
    190 => '=BE', 191 => '=BF', 192 => '=C0', 193 => '=C1', 194 => '=C2',
    195 => '=C3', 196 => '=C4', 197 => '=C5', 198 => '=C6', 199 => '=C7',
    200 => '=C8', 201 => '=C9', 202 => '=CA', 203 => '=CB', 204 => '=CC',
    205 => '=CD', 206 => '=CE', 207 => '=CF', 208 => '=D0', 209 => '=D1',
    210 => '=D2', 211 => '=D3', 212 => '=D4', 213 => '=D5', 214 => '=D6',
    215 => '=D7', 216 => '=D8', 217 => '=D9', 218 => '=DA', 219 => '=DB',
    220 => '=DC', 221 => '=DD', 222 => '=DE', 223 => '=DF', 224 => '=E0',
    225 => '=E1', 226 => '=E2', 227 => '=E3', 228 => '=E4', 229 => '=E5',
    230 => '=E6', 231 => '=E7', 232 => '=E8', 233 => '=E9', 234 => '=EA',
    235 => '=EB', 236 => '=EC', 237 => '=ED', 238 => '=EE', 239 => '=EF',
    240 => '=F0', 241 => '=F1', 242 => '=F2', 243 => '=F3', 244 => '=F4',
    245 => '=F5', 246 => '=F6', 247 => '=F7', 248 => '=F8', 249 => '=F9',
    250 => '=FA', 251 => '=FB', 252 => '=FC', 253 => '=FD', 254 => '=FE',
    255 => '=FF'
    );

  /**
   * A map of non-encoded ascii characters.
   * @var string[]
   * @access protected
   */
  protected static $_safeMap = array();

  /**
   * Creates a new QpEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   * @param Swift_StreamFilter $filter if input should be canonicalized
   */
  public function __construct(Swift_CharacterStream $charStream,
    Swift_StreamFilter $filter = null)
  {
    $this->_charStream = $charStream;
    if (empty(self::$_safeMap))
    {
      foreach (array_merge(
        array(0x09, 0x20), range(0x21, 0x3C), range(0x3E, 0x7E)) as $byte)
      {
        self::$_safeMap[$byte] = chr($byte);
      }
    }
    $this->_filter = $filter;
  }

  /**
   * Takes an unencoded string and produces a QP encoded string from it.
   * QP encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   * @param string $string to encode
   * @param int $firstLineOffset, optional
   * @param int $maxLineLength, optional, 0 indicates the default of 76 chars
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if ($maxLineLength > 76 || $maxLineLength <= 0)
    {
      $maxLineLength = 76;
    }

    $thisLineLength = $maxLineLength - $firstLineOffset;

    $lines = array();
    $lNo = 0;
    $lines[$lNo] = '';
    $currentLine =& $lines[$lNo++];
    $size=$lineLen=0;

    $this->_charStream->flushContents();
    $this->_charStream->importString($string);

    //Fetching more than 4 chars at one is slower, as is fetching fewer bytes
    // Conveniently 4 chars is the UTF-8 safe number since UTF-8 has up to 6
    // bytes per char and (6 * 4 * 3 = 72 chars per line) * =NN is 3 bytes
    while (false !== $bytes = $this->_nextSequence())
    {
      //If we're filtering the input
      if (isset($this->_filter))
      {
        //If we can't filter because we need more bytes
        while ($this->_filter->shouldBuffer($bytes))
        {
          //Then collect bytes into the buffer
          if (false === $moreBytes = $this->_nextSequence(1))
          {
            break;
          }

          foreach ($moreBytes as $b)
          {
            $bytes[] = $b;
          }
        }
        //And filter them
        $bytes = $this->_filter->filter($bytes);
      }

      $enc = $this->_encodeByteSequence($bytes, $size);
      if ($currentLine && $lineLen+$size >= $thisLineLength)
      {
        $lines[$lNo] = '';
        $currentLine =& $lines[$lNo++];
        $thisLineLength = $maxLineLength;
        $lineLen=0;
      }
      $lineLen+=$size;
      $currentLine .= $enc;
    }

    return $this->_standardize(implode("=\r\n", $lines));
  }

  /**
   * Updates the charset used.
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->_charStream->setCharacterSet($charset);
  }

  // -- Protected methods

  /**
   * Encode the given byte array into a verbatim QP form.
   * @param int[] $bytes
   * @return string
   * @access protected
   */
  protected function _encodeByteSequence(array $bytes, &$size)
  {
    $ret = '';
    $size=0;
    foreach ($bytes as $b)
    {
      if (isset(self::$_safeMap[$b]))
      {
        $ret .= self::$_safeMap[$b];
        ++$size;
      }
      else
      {
        $ret .= self::$_qpMap[$b];
        $size+=3;
      }
    }
    return $ret;
  }

  /**
   * Get the next sequence of bytes to read from the char stream.
   * @param int $size number of bytes to read
   * @return int[]
   * @access protected
   */
  protected function _nextSequence($size = 4)
  {
    return $this->_charStream->readBytes($size);
  }

  /**
   * Make sure CRLF is correct and HT/SPACE are in valid places.
   * @param string $string
   * @return string
   * @access protected
   */
  protected function _standardize($string)
  {
    $string = str_replace(array("\t=0D=0A", " =0D=0A", "=0D=0A"),
      array("=09\r\n", "=20\r\n", "\r\n"), $string
      );
    switch ($end = ord(substr($string, -1)))
    {
      case 0x09:
      case 0x20:
        $string = substr_replace($string, self::$_qpMap[$end], -1);
    }
    return $string;
  }

}
 /* .tmp\flat\1\2\ReporterPlugin.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\ReporterPlugin.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Plugins/Reporter.php';

/**
 * Does real time reporting of pass/fail for each recipient.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_ReporterPlugin
  implements Swift_Events_SendListener
{
  
  /**
   * The reporter backend which takes notifications.
   * @var Swift_Plugin_Reporter
   * @access private
   */
  private $_reporter;
  
  /**
   * Create a new ReporterPlugin using $reporter.
   * @param Swift_Plugins_Reporter $reporter
   */
  public function __construct(Swift_Plugins_Reporter $reporter)
  {
    $this->_reporter = $reporter;
  }
  
  /**
   * Not used.
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
  }
  
  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = $evt->getMessage();
    $failures = array_flip($evt->getFailedRecipients());
    foreach ((array) $message->getTo() as $address => $null)
    {
      $this->_reporter->notify(
        $message, $address, (array_key_exists($address, $failures)
        ? Swift_Plugins_Reporter::RESULT_FAIL
        : Swift_Plugins_Reporter::RESULT_PASS)
        );
    }
    foreach ((array) $message->getCc() as $address => $null)
    {
      $this->_reporter->notify(
        $message, $address, (array_key_exists($address, $failures)
        ? Swift_Plugins_Reporter::RESULT_FAIL
        : Swift_Plugins_Reporter::RESULT_PASS)
        );
    }
    foreach ((array) $message->getBcc() as $address => $null)
    {
      $this->_reporter->notify(
        $message, $address, (array_key_exists($address, $failures)
        ? Swift_Plugins_Reporter::RESULT_FAIL
        : Swift_Plugins_Reporter::RESULT_PASS)
        );
    }
  }
  
}
 /* .tmp\flat\1\2\ResponseEvent.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\ResponseEvent.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventObject.php';

/**
 * Generated when a response is received on a SMTP connection.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_ResponseEvent extends Swift_Events_EventObject
{
  
  /**
   * The overall result.
   * @var boolean
   */
  private $_valid;
  
  /**
   * The response received from the server.
   * @var string
   */
  private $_response;
  
  /**
   * Create a new ResponseEvent for $source and $response.
   * @param Swift_Transport $source
   * @param string $response
   * @param boolean $valid
   */
  public function __construct(Swift_Transport $source, $response, $valid = false)
  {
    parent::__construct($source);
    $this->_response = $response;
    $this->_valid = $valid;
  }
  
  /**
   * Get the response which was received from the server.
   * @return string
   */
  public function getResponse()
  {
    return $this->_response;
  }
  
  /**
   * Get the success status of this Event.
   * @return boolean
   */
  public function isValid()
  {
    return $this->_valid;
  }
  
}
 /* .tmp\flat\1\2\Rfc2231Encoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\Rfc2231Encoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Encoder.php';
//@require 'Swift/CharacterStream.php';

/**
 * Handles RFC 2231 specified Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_Rfc2231Encoder implements Swift_Encoder
{
  
  /**
   * A character stream to use when reading a string as characters instead of bytes.
   * @var Swift_CharacterStream
   * @access private
   */
  private $_charStream;
  
  /**
   * Creates a new Rfc2231Encoder using the given character stream instance.
   * @param Swift_CharacterStream
   */
  public function __construct(Swift_CharacterStream $charStream)
  {
    $this->_charStream = $charStream;
  }
  
  /**
   * Takes an unencoded string and produces a string encoded according to
   * RFC 2231 from it.
   * @param string $string to encode
   * @param int $firstLineOffset
   * @param int $maxLineLength, optional, 0 indicates the default of 75 bytes
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    $lines = array(); $lineCount = 0;
    $lines[] = '';
    $currentLine =& $lines[$lineCount++];
    
    if (0 >= $maxLineLength)
    {
      $maxLineLength = 75;
    }
    
    $this->_charStream->flushContents();
    $this->_charStream->importString($string);
    
    $thisLineLength = $maxLineLength - $firstLineOffset;
    
    while (false !== $char = $this->_charStream->read(4))
    {
      $encodedChar = rawurlencode($char);
      if (0 != strlen($currentLine)
        && strlen($currentLine . $encodedChar) > $thisLineLength)
      {
        $lines[] = '';
        $currentLine =& $lines[$lineCount++];
        $thisLineLength = $maxLineLength;
      }
      $currentLine .= $encodedChar;
    }
    
    return implode("\r\n", $lines);
  }
  
  /**
   * Updates the charset used.
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->_charStream->setCharacterSet($charset);
  }
  
}
 /* .tmp\flat\1\2\SendEvent.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\SendEvent.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventObject.php';

/**
 * Generated when a message is being sent.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_SendEvent extends Swift_Events_EventObject
{
  
  /** Sending has yet to occur */
  const RESULT_PENDING = 0x0001;
  
  /** Sending was successful */
  const RESULT_SUCCESS = 0x0010;
  
  /** Sending worked, but there were some failures */
  const RESULT_TENTATIVE = 0x0100;
  
  /** Sending failed */
  const RESULT_FAILED = 0x1000;
  
  /**
   * The Message being sent.
   * @var Swift_Mime_Message
   */
  private $_message;
  
  /**
   * The Transport used in sending.
   * @var Swift_Transport
   */
  private $_transport;
  
  /**
   * Any recipients which failed after sending.
   * @var string[]
   */
  private $failedRecipients = array();
  
  /**
   * The overall result as a bitmask from the class constants.
   * @var int
   */
  private $result;
  
  /**
   * Create a new SendEvent for $source and $message.
   * @param Swift_Transport $source
   * @param Swift_Mime_Message $message
   */
  public function __construct(Swift_Transport $source,
    Swift_Mime_Message $message)
  {
    parent::__construct($source);
    $this->_message = $message;
    $this->_result = self::RESULT_PENDING;
  }
  
  /**
   * Get the Transport used to send the Message.
   * @return Swift_Transport
   */
  public function getTransport()
  {
    return $this->getSource();
  }
  
  /**
   * Get the Message being sent.
   * @return Swift_Mime_Message
   */
  public function getMessage()
  {
    return $this->_message;
  }
  
  /**
   * Set the array of addresses that failed in sending.
   * @param array $recipients
   */
  public function setFailedRecipients($recipients)
  {
    $this->_failedRecipients = $recipients;
  }
  
  /**
   * Get an recipient addresses which were not accepted for delivery.
   * @return string[]
   */
  public function getFailedRecipients()
  {
    return $this->_failedRecipients;
  }
  
  /**
   * Set the result of sending.
   * @return int
   */
  public function setResult($result)
  {
    $this->_result = $result;
  }
  
  /**
   * Get the result of this Event.
   * The return value is a bitmask from
   * {@link RESULT_PENDING, RESULT_SUCCESS, RESULT_TENTATIVE, RESULT_FAILED}
   * @return int
   */
  public function getResult()
  {
    return $this->_result;
  }
  
}
 /* .tmp\flat\1\2\SendmailTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\SendmailTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/SendmailTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * SendmailTransport for sending mail through a sendmail/postfix (etc..) binary.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_SendmailTransport extends Swift_Transport_SendmailTransport
{
  
  /**
   * Create a new SendmailTransport, optionally using $command for sending.
   * @param string $command
   */
  public function __construct($command = '/usr/sbin/sendmail -bs')
  {
    call_user_func_array(
      array($this, 'Swift_Transport_SendmailTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.sendmail')
      );
    
    $this->setCommand($command);
  }
  
  /**
   * Create a new SendmailTransport instance.
   * @param string $command
   * @return Swift_SendmailTransport
   */
  public static function newInstance($command = '/usr/sbin/sendmail -bs')
  {
    return new self($command);
  }
  
}
 /* .tmp\flat\1\2\SimpleHeaderFactory.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\SimpleHeaderFactory.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/HeaderFactory.php';
//@require 'Swift/Mime/HeaderEncoder.php';
//@require 'Swift/Encoder.php';
//@require 'Swift/Mime/Headers/MailboxHeader.php';
//@require 'Swift/Mime/Headers/DateHeader.php';
//@require 'Swift/Mime/Headers/UnstructuredHeader.php';
//@require 'Swift/Mime/Headers/ParameterizedHeader.php';
//@require 'Swift/Mime/Headers/IdentificationHeader.php';
//@require 'Swift/Mime/Headers/PathHeader.php';

/**
 * Creates MIME headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleHeaderFactory implements Swift_Mime_HeaderFactory
{

  /** The HeaderEncoder used by these headers */
  private $_encoder;
  
  /** The Encoder used by parameters */
  private $_paramEncoder;
  
  /** The charset of created Headers */
  private $_charset;
  
  /**
   * Creates a new SimpleHeaderFactory using $encoder and $paramEncoder.
   * @param Swift_Mime_HeaderEncoder $encoder
   * @param Swift_Encoder $paramEncoder
   * @param string $charset
   */
  public function __construct(Swift_Mime_HeaderEncoder $encoder,
    Swift_Encoder $paramEncoder, $charset = null)
  {
    $this->_encoder = $encoder;
    $this->_paramEncoder = $paramEncoder;
    $this->_charset = $charset;
  }
  
  /**
   * Create a new Mailbox Header with a list of $addresses.
   * @param string $name
   * @param array|string $addresses
   * @return Swift_Mime_Header
   */
  public function createMailboxHeader($name, $addresses = null)
  {
    $header = new Swift_Mime_Headers_MailboxHeader($name, $this->_encoder);
    if (isset($addresses))
    {
      $header->setFieldBodyModel($addresses);
    }
    $this->_setHeaderCharset($header);
    return $header;
  }
  
  /**
   * Create a new Date header using $timestamp (UNIX time).
   * @param string $name
   * @param int $timestamp
   * @return Swift_Mime_Header
   */
  public function createDateHeader($name, $timestamp = null)
  {
    $header = new Swift_Mime_Headers_DateHeader($name);
    if (isset($timestamp))
    {
      $header->setFieldBodyModel($timestamp);
    }
    $this->_setHeaderCharset($header);
    return $header;
  }
  
  /**
   * Create a new basic text header with $name and $value.
   * @param string $name
   * @param string $value
   * @return Swift_Mime_Header
   */
  public function createTextHeader($name, $value = null)
  {
    $header = new Swift_Mime_Headers_UnstructuredHeader($name, $this->_encoder);
    if (isset($value))
    {
      $header->setFieldBodyModel($value);
    }
    $this->_setHeaderCharset($header);
    return $header;
  }
  
  /**
   * Create a new ParameterizedHeader with $name, $value and $params.
   * @param string $name
   * @param string $value
   * @param array $params
   * @return Swift_Mime_ParameterizedHeader
   */
  public function createParameterizedHeader($name, $value = null,
    $params = array())
  {
    $header = new Swift_Mime_Headers_ParameterizedHeader($name,
      $this->_encoder, (strtolower($name) == 'content-disposition')
        ? $this->_paramEncoder
        : null
      );
    if (isset($value))
    {
      $header->setFieldBodyModel($value);
    }
    foreach ($params as $k => $v)
    {
      $header->setParameter($k, $v);
    }
    $this->_setHeaderCharset($header);
    return $header;
  }
  
  /**
   * Create a new ID header for Message-ID or Content-ID.
   * @param string $name
   * @param string|array $ids
   * @return Swift_Mime_Header
   */
  public function createIdHeader($name, $ids = null)
  {
    $header = new Swift_Mime_Headers_IdentificationHeader($name);
    if (isset($ids))
    {
      $header->setFieldBodyModel($ids);
    }
    $this->_setHeaderCharset($header);
    return $header;
  }
  
  /**
   * Create a new Path header with an address (path) in it.
   * @param string $name
   * @param string $path
   * @return Swift_Mime_Header
   */
  public function createPathHeader($name, $path = null)
  {
    $header = new Swift_Mime_Headers_PathHeader($name);
    if (isset($path))
    {
      $header->setFieldBodyModel($path);
    }
    $this->_setHeaderCharset($header);
    return $header;
  }
  
  /**
   * Notify this observer that the entity's charset has changed.
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->_charset = $charset;
    $this->_encoder->charsetChanged($charset);
    $this->_paramEncoder->charsetChanged($charset);
  }
  
  // -- Private methods
  
  /** Apply the charset to the Header */
  private function _setHeaderCharset(Swift_Mime_Header $header)
  {
    if (isset($this->_charset))
    {
      $header->setCharset($this->_charset);
    }
  }
  
}
 /* .tmp\flat\1\2\SimpleHeaderSet.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\SimpleHeaderSet.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/Mime/HeaderFactory.php';

/**
 * A collection of MIME headers.
 * 
 * @package Swift
 * @subpackage Mime
 * 
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleHeaderSet implements Swift_Mime_HeaderSet
{
  
  /** HeaderFactory */
  private $_factory;
  
  /** Collection of set Headers */
  private $_headers = array();
  
  /** Field ordering details */
  private $_order = array();
  
  /** List of fields which are required to be displayed */
  private $_required = array();
  
  /** The charset used by Headers */
  private $_charset;
  
  /**
   * Create a new SimpleHeaderSet with the given $factory.
   * 
   * @param Swift_Mime_HeaderFactory $factory
   * @param string $charset
   */
  public function __construct(Swift_Mime_HeaderFactory $factory,
    $charset = null)
  {
    $this->_factory = $factory;
    if (isset($charset))
    {
      $this->setCharset($charset);
    }
  }
  
  /**
   * Set the charset used by these headers.
   * 
   * @param string $charset
   */
  public function setCharset($charset)
  {
    $this->_charset = $charset;
    $this->_factory->charsetChanged($charset);
    $this->_notifyHeadersOfCharset($charset);
  }
  
  /**
   * Add a new Mailbox Header with a list of $addresses.
   * 
   * @param string $name
   * @param array|string $addresses
   */
  public function addMailboxHeader($name, $addresses = null)
  {
    $this->_storeHeader($name,
      $this->_factory->createMailboxHeader($name, $addresses));
  }
  
  /**
   * Add a new Date header using $timestamp (UNIX time).
   * 
   * @param string $name
   * @param int $timestamp
   */
  public function addDateHeader($name, $timestamp = null)
  {
    $this->_storeHeader($name,
      $this->_factory->createDateHeader($name, $timestamp));
  }
  
  /**
   * Add a new basic text header with $name and $value.
   * 
   * @param string $name
   * @param string $value
   */
  public function addTextHeader($name, $value = null)
  {
    $this->_storeHeader($name,
      $this->_factory->createTextHeader($name, $value));
  }
  
  /**
   * Add a new ParameterizedHeader with $name, $value and $params.
   * 
   * @param string $name
   * @param string $value
   * @param array $params
   */
  public function addParameterizedHeader($name, $value = null,
    $params = array())
  {
    $this->_storeHeader($name,
      $this->_factory->createParameterizedHeader($name, $value,
      $params));
  }
  
  /**
   * Add a new ID header for Message-ID or Content-ID.
   * 
   * @param string $name
   * @param string|array $ids
   */
  public function addIdHeader($name, $ids = null)
  {
    $this->_storeHeader($name, $this->_factory->createIdHeader($name, $ids));
  }
  
  /**
   * Add a new Path header with an address (path) in it.
   * 
   * @param string $name
   * @param string $path
   */
  public function addPathHeader($name, $path = null)
  {
    $this->_storeHeader($name, $this->_factory->createPathHeader($name, $path));
  }
  
  /**
   * Returns true if at least one header with the given $name exists.
   * 
   * If multiple headers match, the actual one may be specified by $index.
   * 
   * @param string $name
   * @param int $index
   * 
   * @return boolean
   */
  public function has($name, $index = 0)
  {
    $lowerName = strtolower($name);
    return array_key_exists($lowerName, $this->_headers)
      && array_key_exists($index, $this->_headers[$lowerName]);
  }
  
  /**
   * Set a header in the HeaderSet.
   * 
   * The header may be a previously fetched header via {@link get()} or it may
   * be one that has been created separately.
   * 
   * If $index is specified, the header will be inserted into the set at this
   * offset.
   * 
   * @param Swift_Mime_Header $header
   * @param int $index
   */
  public function set(Swift_Mime_Header $header, $index = 0)
  {
    $this->_storeHeader($header->getFieldName(), $header, $index);
  }
  
  /**
   * Get the header with the given $name.
   * 
   * If multiple headers match, the actual one may be specified by $index.
   * Returns NULL if none present.
   * 
   * @param string $name
   * @param int $index
   * 
   * @return Swift_Mime_Header
   */
  public function get($name, $index = 0)
  {
    if ($this->has($name, $index))
    {
      $lowerName = strtolower($name);
      return $this->_headers[$lowerName][$index];
    }
  }
  
  /**
   * Get all headers with the given $name.
   * 
   * @param string $name
   * 
   * @return array
   */
  public function getAll($name = null)
  {
    if (!isset($name))
    {
      $headers = array();
      foreach ($this->_headers as $collection)
      {
        $headers = array_merge($headers, $collection);
      }
      return $headers;
    }
    
    $lowerName = strtolower($name);
    if (!array_key_exists($lowerName, $this->_headers))
    {
      return array();
    }
    return $this->_headers[$lowerName];
  }
  
  /**
   * Remove the header with the given $name if it's set.
   * 
   * If multiple headers match, the actual one may be specified by $index.
   * 
   * @param string $name
   * @param int $index
   */
  public function remove($name, $index = 0)
  {
    $lowerName = strtolower($name);
    unset($this->_headers[$lowerName][$index]);
  }
  
  /**
   * Remove all headers with the given $name.
   * 
   * @param string $name
   */
  public function removeAll($name)
  {
    $lowerName = strtolower($name);
    unset($this->_headers[$lowerName]);
  }
  
  /**
   * Create a new instance of this HeaderSet.
   * 
   * @return Swift_Mime_HeaderSet
   */
  public function newInstance()
  {
    return new self($this->_factory);
  }
  
  /**
   * Define a list of Header names as an array in the correct order.
   * 
   * These Headers will be output in the given order where present.
   * 
   * @param array $sequence
   */
  public function defineOrdering(array $sequence)
  {
    $this->_order = array_flip(array_map('strtolower', $sequence));
  }
  
  /**
   * Set a list of header names which must always be displayed when set.
   * 
   * Usually headers without a field value won't be output unless set here.
   * 
   * @param array $names
   */
  public function setAlwaysDisplayed(array $names)
  {
    $this->_required = array_flip(array_map('strtolower', $names));
  }

  /**
   * Notify this observer that the entity's charset has changed.
   * 
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->setCharset($charset);
  }
  
  /**
   * Returns a string with a representation of all headers.
   * 
   * @return string
   */
  public function toString()
  {
    $string = '';
    $headers = $this->_headers;
    if ($this->_canSort())
    {
      uksort($headers, array($this, '_sortHeaders'));
    }
    foreach ($headers as $collection)
    {
      foreach ($collection as $header)
      {
        if ($this->_isDisplayed($header) || $header->getFieldBody() != '')
        {
          $string .= $header->toString();
        }
      }
    }
    return $string;
  }
  
  /**
   * Returns a string representation of this object.
   *
   * @return string
   *
   * @see toString()
   */
  public function __toString()
  {
    return $this->toString();
  }
  
  // -- Private methods
  
  /** Save a Header to the internal collection */
  private function _storeHeader($name, Swift_Mime_Header $header, $offset = null)
  {
    if (!isset($this->_headers[strtolower($name)]))
    {
      $this->_headers[strtolower($name)] = array();
    }
    if (!isset($offset))
    {
      $this->_headers[strtolower($name)][] = $header;
    }
    else
    {
      $this->_headers[strtolower($name)][$offset] = $header;
    }
  }
  
  /** Test if the headers can be sorted */
  private function _canSort()
  {
    return count($this->_order) > 0;
  }
  
  /** uksort() algorithm for Header ordering */
  private function _sortHeaders($a, $b)
  {
    $lowerA = strtolower($a);
    $lowerB = strtolower($b);
    $aPos = array_key_exists($lowerA, $this->_order)
      ? $this->_order[$lowerA]
      : -1;
    $bPos = array_key_exists($lowerB, $this->_order)
      ? $this->_order[$lowerB]
      : -1;
      
    if ($aPos == -1)
    {
      return 1;
    }
    elseif ($bPos == -1)
    {
      return -1;
    }
    
    return ($aPos < $bPos) ? -1 : 1;
  }
  
  /** Test if the given Header is always displayed */
  private function _isDisplayed(Swift_Mime_Header $header)
  {
    return array_key_exists(strtolower($header->getFieldName()), $this->_required);
  }
  
  /** Notify all Headers of the new charset */
  private function _notifyHeadersOfCharset($charset)
  {
    foreach ($this->_headers as $headerGroup)
    {
      foreach ($headerGroup as $header)
      {
        $header->setCharset($charset);
      }
    }
  }
  
}
 /* .tmp\flat\1\2\SimpleKeyCacheInputStream.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\SimpleKeyCacheInputStream.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/KeyCache.php';
//@require 'Swift/KeyCacheInputStream.php';

/**
 * Writes data to a KeyCache using a stream.
 * @package Swift
 * @subpackage KeyCache
 * @author Chris Corbyn
 */
class Swift_KeyCache_SimpleKeyCacheInputStream
  implements Swift_KeyCache_KeyCacheInputStream
{
  
  /** The KeyCache being written to */
  private $_keyCache;
  
  /** The nsKey of the KeyCache being written to */
  private $_nsKey;
  
  /** The itemKey of the KeyCache being written to */
  private $_itemKey;
  
  /** A stream to write through on each write() */
  private $_writeThrough = null;
  
  /**
   * Set the KeyCache to wrap.
   * @param Swift_KeyCache $keyCache
   */
  public function setKeyCache(Swift_KeyCache $keyCache)
  {
    $this->_keyCache = $keyCache;
  }
  
  /**
   * Specify a stream to write through for each write().
   * @param Swift_InputByteStream $is
   */
  public function setWriteThroughStream(Swift_InputByteStream $is)
  {
    $this->_writeThrough = $is;
  }
  
  /**
   * Writes $bytes to the end of the stream.
   * @param string $bytes
   * @param Swift_InputByteStream $is, optional
   */
  public function write($bytes, Swift_InputByteStream $is = null)
  {
    $this->_keyCache->setString(
      $this->_nsKey, $this->_itemKey, $bytes, Swift_KeyCache::MODE_APPEND
      );
    if (isset($is))
    {
      $is->write($bytes);
    }
    if (isset($this->_writeThrough))
    {
      $this->_writeThrough->write($bytes);
    }
  }
  
  /**
   * Not used.
   */
  public function commit()
  {
  }
  
  /**
   * Not used.
   */
  public function bind(Swift_InputByteStream $is)
  {
  }
  
  /**
   * Not used.
   */
  public function unbind(Swift_InputByteStream $is)
  {
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   */
  public function flushBuffers()
  {
    $this->_keyCache->clearKey($this->_nsKey, $this->_itemKey);
  }
  
  /**
   * Set the nsKey which will be written to.
   * @param string $nsKey
   */
  public function setNsKey($nsKey)
  {
    $this->_nsKey = $nsKey;
  }
  
  /**
   * Set the itemKey which will be written to.
   * @param string $itemKey
   */
  public function setItemKey($itemKey)
  {
    $this->_itemKey = $itemKey;
  }
  
  /**
   * Any implementation should be cloneable, allowing the clone to access a
   * separate $nsKey and $itemKey.
   */
  public function __clone()
  {
    $this->_writeThrough = null;
  }
  
}
 /* .tmp\flat\1\2\SimpleMimeEntity.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\SimpleMimeEntity.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/OutputByteStream.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/KeyCache.php';

/**
 * A MIME entity, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleMimeEntity implements Swift_Mime_MimeEntity
{
  
  /** A collection of Headers for this mime entity */
  private $_headers;
  
  /** The body as a string, or a stream */
  private $_body;
  
  /** The encoder that encodes the body into a streamable format */
  private $_encoder;
  
  /** A mime bounary, if any is used */
  private $_boundary;
  
  /** Mime types to be used based on the nesting level */
  private $_compositeRanges = array(
    'multipart/mixed' => array(self::LEVEL_TOP, self::LEVEL_MIXED),
    'multipart/alternative' => array(self::LEVEL_MIXED, self::LEVEL_ALTERNATIVE),
    'multipart/related' => array(self::LEVEL_ALTERNATIVE, self::LEVEL_RELATED)
    );
  
  /** A set of filter rules to define what level an entity should be nested at */
  private $_compoundLevelFilters = array();
    
  /** The nesting level of this entity */
  private $_nestingLevel = self::LEVEL_ALTERNATIVE;
  
  /** A KeyCache instance used during encoding and streaming */
  private $_cache;
  
  /** Direct descendants of this entity */
  private $_immediateChildren = array();
  
  /** All descendants of this entity */
  private $_children = array();
  
  /** The maximum line length of the body of this entity */
  private $_maxLineLength = 78;
  
  /** The order in which alternative mime types should appear */
  private $_alternativePartOrder = array(
    'text/plain' => 1,
    'text/html' => 2,
    'multipart/related' => 3
    );
  
  /** The CID of this entity */
  private $_id;
  
  /** The key used for accessing the cache */
  private $_cacheKey;
  
  protected $_userContentType;
  
  /**
   * Create a new SimpleMimeEntity with $headers, $encoder and $cache.
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache)
  {
    $this->_cacheKey = uniqid();
    $this->_cache = $cache;
    $this->_headers = $headers;
    $this->setEncoder($encoder);
    $this->_headers->defineOrdering(
      array('Content-Type', 'Content-Transfer-Encoding')
      );
    
    // This array specifies that, when the entire MIME document contains
    // $compoundLevel, then for each child within $level, if its Content-Type
    // is $contentType then it should be treated as if it's level is
    // $neededLevel instead.  I tried to write that unambiguously! :-\
    // Data Structure:
    // array (
    //   $compoundLevel => array(
    //     $level => array(
    //       $contentType => $neededLevel
    //     )
    //   )
    // )
    
    $this->_compoundLevelFilters = array(
      (self::LEVEL_ALTERNATIVE + self::LEVEL_RELATED) => array(
        self::LEVEL_ALTERNATIVE => array(
          'text/plain' => self::LEVEL_ALTERNATIVE,
          'text/html' => self::LEVEL_RELATED
          )
        )
      );

    $this->_id = $this->getRandomId();
  }
  
  /**
   * Generate a new Content-ID or Message-ID for this MIME entity.
   * @return string
   */
  public function generateId()
  {
    $this->setId($this->getRandomId());
    return $this->_id;
  }
  
  /**
   * Get the {@link Swift_Mime_HeaderSet} for this entity.
   * @return Swift_Mime_HeaderSet
   */
  public function getHeaders()
  {
    return $this->_headers;
  }
  
  /**
   * Get the nesting level of this entity.
   * @return int
   * @see LEVEL_TOP, LEVEL_MIXED, LEVEL_RELATED, LEVEL_ALTERNATIVE
   */
  public function getNestingLevel()
  {
    return $this->_nestingLevel;
  }
  
  /**
   * Get the Content-type of this entity.
   * @return string
   */
  public function getContentType()
  {
    return $this->_getHeaderFieldModel('Content-Type');
  }
  
  /**
   * Set the Content-type of this entity.
   * @param string $type
   */
  public function setContentType($type)
  {
    $this->_setContentTypeInHeaders($type);
    // Keep track of the value so that if the content-type changes automatically
    // due to added child entities, it can be restored if they are later removed
    $this->_userContentType = $type;
    return $this;
  }
  
  /**
   * Get the CID of this entity.
   * The CID will only be present in headers if a Content-ID header is present.
   * @return string
   */
  public function getId()
  {
    return $this->_headers->has($this->_getIdField())
      ? current((array) $this->_getHeaderFieldModel($this->_getIdField()))
      : $this->_id;
  }
  
  /**
   * Set the CID of this entity.
   * @param string $id
   */
  public function setId($id)
  {
    if (!$this->_setHeaderFieldModel($this->_getIdField(), $id))
    {
      $this->_headers->addIdHeader($this->_getIdField(), $id);
    }
    $this->_id = $id;
    return $this;
  }
  
  /**
   * Get the description of this entity.
   * This value comes from the Content-Description header if set.
   * @return string
   */
  public function getDescription()
  {
    return $this->_getHeaderFieldModel('Content-Description');
  }
  
  /**
   * Set the description of this entity.
   * This method sets a value in the Content-ID header.
   * @param string $description
   */
  public function setDescription($description)
  {
    if (!$this->_setHeaderFieldModel('Content-Description', $description))
    {
      $this->_headers->addTextHeader('Content-Description', $description);
    }
    return $this;
  }
  
  /**
   * Get the maximum line length of the body of this entity.
   * @return int
   */
  public function getMaxLineLength()
  {
    return $this->_maxLineLength;
  }
  
  /**
   * Set the maximum line length of lines in this body.
   * Though not enforced by the library, lines should not exceed 1000 chars.
   * @param int $length
   */
  public function setMaxLineLength($length)
  {
    $this->_maxLineLength = $length;
    return $this;
  }
  
  /**
   * Get all children added to this entity.
   * @return array of Swift_Mime_Entity
   */
  public function getChildren()
  {
    return $this->_children;
  }
  
  /**
   * Set all children of this entity.
   * @param array $children Swiift_Mime_Entity instances
   * @param int $compoundLevel For internal use only
   */
  public function setChildren(array $children, $compoundLevel = null)
  {
    //TODO: Try to refactor this logic
    
    $compoundLevel = isset($compoundLevel)
      ? $compoundLevel
      : $this->_getCompoundLevel($children)
      ;
    
    $immediateChildren = array();
    $grandchildren = array();
    $newContentType = $this->_userContentType;
    
    foreach ($children as $child)
    {
      $level = $this->_getNeededChildLevel($child, $compoundLevel);
      if (empty($immediateChildren)) //first iteration
      {
        $immediateChildren = array($child);
      }
      else
      {
        $nextLevel = $this->_getNeededChildLevel($immediateChildren[0], $compoundLevel);
        if ($nextLevel == $level)
        {
          $immediateChildren[] = $child;
        }
        elseif ($level < $nextLevel)
        {
          //Re-assign immediateChildren to grandchilden
          $grandchildren = array_merge($grandchildren, $immediateChildren);
          //Set new children
          $immediateChildren = array($child);
        }
        else
        {
          $grandchildren[] = $child;
        }
      }
    }
    
    if (!empty($immediateChildren))
    {
      $lowestLevel = $this->_getNeededChildLevel($immediateChildren[0], $compoundLevel);
      
      //Determine which composite media type is needed to accomodate the
      // immediate children
      foreach ($this->_compositeRanges as $mediaType => $range)
      {
        if ($lowestLevel > $range[0]
          && $lowestLevel <= $range[1])
        {
          $newContentType = $mediaType;
          break;
        }
      }
      
      //Put any grandchildren in a subpart
      if (!empty($grandchildren))
      {
        $subentity = $this->_createChild();
        $subentity->_setNestingLevel($lowestLevel);
        $subentity->setChildren($grandchildren, $compoundLevel);
        array_unshift($immediateChildren, $subentity);
      }
    }
    
    $this->_immediateChildren = $immediateChildren;
    $this->_children = $children;
    $this->_setContentTypeInHeaders($newContentType);
    $this->_fixHeaders();
    $this->_sortChildren();
    
    return $this;
  }
  
  /**
   * Get the body of this entity as a string.
   * @return string
   */
  public function getBody()
  {
    return ($this->_body instanceof Swift_OutputByteStream)
      ? $this->_readStream($this->_body)
      : $this->_body;
  }
  
  /**
   * Set the body of this entity, either as a string, or as an instance of
   * {@link Swift_OutputByteStream}.
   * @param mixed $body
   * @param string $contentType optional
   */
  public function setBody($body, $contentType = null)
  {
    if ($body !== $this->_body)
    {
      $this->_clearCache();
    }
    
    $this->_body = $body;
    if (isset($contentType))
    {
      $this->setContentType($contentType);
    }
    return $this;
  }
  
  /**
   * Get the encoder used for the body of this entity.
   * @return Swift_Mime_ContentEncoder
   */
  public function getEncoder()
  {
    return $this->_encoder;
  }
  
  /**
   * Set the encoder used for the body of this entity.
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function setEncoder(Swift_Mime_ContentEncoder $encoder)
  {
    if ($encoder !== $this->_encoder)
    {
      $this->_clearCache();
    }
    
    $this->_encoder = $encoder;
    $this->_setEncoding($encoder->getName());
    $this->_notifyEncoderChanged($encoder);
    return $this;
  }
  
  /**
   * Get the boundary used to separate children in this entity.
   * @return string
   */
  public function getBoundary()
  {
    if (!isset($this->_boundary))
    {
      $this->_boundary = '_=_swift_v4_' . time() . uniqid() . '_=_';
    }
    return $this->_boundary;
  }
  
  /**
   * Set the boundary used to separate children in this entity.
   * @param string $boundary
   * @throws Swift_RfcComplianceException
   */
  public function setBoundary($boundary)
  {
    $this->_assertValidBoundary($boundary);
    $this->_boundary = $boundary;
    return $this;
  }
  
  /**
   * Receive notification that the charset of this entity, or a parent entity
   * has changed.
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->_notifyCharsetChanged($charset);
  }
  
  /**
   * Receive notification that the encoder of this entity or a parent entity
   * has changed.
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function encoderChanged(Swift_Mime_ContentEncoder $encoder)
  {
    $this->_notifyEncoderChanged($encoder);
  }
  
  /**
   * Get this entire entity as a string.
   * @return string
   */
  public function toString()
  {
    $string = $this->_headers->toString();
    if (isset($this->_body) && empty($this->_immediateChildren))
    {
      if ($this->_cache->hasKey($this->_cacheKey, 'body'))
      {
        $body = $this->_cache->getString($this->_cacheKey, 'body');
      }
      else
      {
        $body = "\r\n" . $this->_encoder->encodeString($this->getBody(), 0,
          $this->getMaxLineLength()
          );
        $this->_cache->setString($this->_cacheKey, 'body', $body,
          Swift_KeyCache::MODE_WRITE
          );
      }
      $string .= $body;
    }
    
    if (!empty($this->_immediateChildren))
    {
      foreach ($this->_immediateChildren as $child)
      {
        $string .= "\r\n\r\n--" . $this->getBoundary() . "\r\n";
        $string .= $child->toString();
      }
      $string .= "\r\n\r\n--" . $this->getBoundary() . "--\r\n";
    }
    
    return $string;
  }
  
  /**
   * Returns a string representation of this object.
   *
   * @return string
   *
   * @see toString()
   */
  public function __toString()
  {
    return $this->toString();
  }
  
  /**
   * Write this entire entity to a {@link Swift_InputByteStream}.
   * @param Swift_InputByteStream
   */
  public function toByteStream(Swift_InputByteStream $is)
  {
    $is->write($this->_headers->toString());
    $is->commit();
    
    if (empty($this->_immediateChildren))
    {
      if (isset($this->_body))
      {
        if ($this->_cache->hasKey($this->_cacheKey, 'body'))
        {
          $this->_cache->exportToByteStream($this->_cacheKey, 'body', $is);
        }
        else
        {
          $cacheIs = $this->_cache->getInputByteStream($this->_cacheKey, 'body');
          if ($cacheIs)
          {
            $is->bind($cacheIs);
          }
          
          $is->write("\r\n");
          
          if ($this->_body instanceof Swift_OutputByteStream)
          {
            $this->_body->setReadPointer(0);
            
            $this->_encoder->encodeByteStream($this->_body, $is, 0,
              $this->getMaxLineLength()
              );
          }
          else
          {
            $is->write($this->_encoder->encodeString(
              $this->getBody(), 0, $this->getMaxLineLength()
              ));
          }
          
          if ($cacheIs)
          {
            $is->unbind($cacheIs);
          }
        }
      }
    }
    
    if (!empty($this->_immediateChildren))
    {
      foreach ($this->_immediateChildren as $child)
      {
        $is->write("\r\n\r\n--" . $this->getBoundary() . "\r\n");
        $child->toByteStream($is);
      }
      $is->write("\r\n\r\n--" . $this->getBoundary() . "--\r\n");
    }
  }
  
  // -- Protected methods
  
  /**
   * Get the name of the header that provides the ID of this entity */
  protected function _getIdField()
  {
    return 'Content-ID';
  }
  
  /**
   * Get the model data (usually an array or a string) for $field.
   */
  protected function _getHeaderFieldModel($field)
  {
    if ($this->_headers->has($field))
    {
      return $this->_headers->get($field)->getFieldBodyModel();
    }
  }
  
  /**
   * Set the model data for $field.
   */
  protected function _setHeaderFieldModel($field, $model)
  {
    if ($this->_headers->has($field))
    {
      $this->_headers->get($field)->setFieldBodyModel($model);
      return true;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Get the parameter value of $parameter on $field header.
   */
  protected function _getHeaderParameter($field, $parameter)
  {
    if ($this->_headers->has($field))
    {
      return $this->_headers->get($field)->getParameter($parameter);
    }
  }
  
  /**
   * Set the parameter value of $parameter on $field header.
   */
  protected function _setHeaderParameter($field, $parameter, $value)
  {
    if ($this->_headers->has($field))
    {
      $this->_headers->get($field)->setParameter($parameter, $value);
      return true;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Re-evaluate what content type and encoding should be used on this entity.
   */
  protected function _fixHeaders()
  {
    if (count($this->_immediateChildren))
    {
      $this->_setHeaderParameter('Content-Type', 'boundary',
        $this->getBoundary()
        );
      $this->_headers->remove('Content-Transfer-Encoding');
    }
    else
    {
      $this->_setHeaderParameter('Content-Type', 'boundary', null);
      $this->_setEncoding($this->_encoder->getName());
    }
  }
  
  /**
   * Get the KeyCache used in this entity.
   */
  protected function _getCache()
  {
    return $this->_cache;
  }
  
  /**
   * Empty the KeyCache for this entity.
   */
  protected function _clearCache()
  {
    $this->_cache->clearKey($this->_cacheKey, 'body');
  }
  
  /**
   * Returns a random Content-ID or Message-ID.
   * @return string
   */
  protected function getRandomId()
  {
    $idLeft = time() . '.' . uniqid();
    $idRight = !empty($_SERVER['SERVER_NAME'])
      ? $_SERVER['SERVER_NAME']
      : 'swift.generated';
    return $idLeft . '@' . $idRight;
  }
  
  // -- Private methods
  
  private function _readStream(Swift_OutputByteStream $os)
  {
    $string = '';
    while (false !== $bytes = $os->read(8192))
    {
      $string .= $bytes;
    }
    return $string;
  }
  
  private function _setEncoding($encoding)
  {
    if (!$this->_setHeaderFieldModel('Content-Transfer-Encoding', $encoding))
    {
      $this->_headers->addTextHeader('Content-Transfer-Encoding', $encoding);
    }
  }
  
  private function _assertValidBoundary($boundary)
  {
    if (!preg_match(
      '/^[a-z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-z0-9\'\(\)\+_\-,\.\/:=\?]$/Di',
      $boundary))
    {
      throw new Swift_RfcComplianceException('Mime boundary set is not RFC 2046 compliant.');
    }
  }
  
  private function _setContentTypeInHeaders($type)
  {
    if (!$this->_setHeaderFieldModel('Content-Type', $type))
    {
      $this->_headers->addParameterizedHeader('Content-Type', $type);
    }
  }
  
  private function _setNestingLevel($level)
  {
    $this->_nestingLevel = $level;
  }
  
  private function _getCompoundLevel($children)
  {
    $level = 0;
    foreach ($children as $child)
    {
      $level |= $child->getNestingLevel();
    }
    return $level;
  }
  
  private function _getNeededChildLevel($child, $compoundLevel)
  {
    $filter = array();
    foreach ($this->_compoundLevelFilters as $bitmask => $rules)
    {
      if (($compoundLevel & $bitmask) === $bitmask)
      {
        $filter = $rules + $filter;
      }
    }
    
    $realLevel = $child->getNestingLevel();
    $lowercaseType = strtolower($child->getContentType());
    
    if (isset($filter[$realLevel])
      && isset($filter[$realLevel][$lowercaseType]))
    {
      return $filter[$realLevel][$lowercaseType];
    }
    else
    {
      return $realLevel;
    }
  }
  
  private function _createChild()
  {
    return new self($this->_headers->newInstance(),
      $this->_encoder, $this->_cache);
  }
  
  private function _notifyEncoderChanged(Swift_Mime_ContentEncoder $encoder)
  {
    foreach ($this->_immediateChildren as $child)
    {
      $child->encoderChanged($encoder);
    }
  }
  
  private function _notifyCharsetChanged($charset)
  {
    $this->_encoder->charsetChanged($charset);
    $this->_headers->charsetChanged($charset);
    foreach ($this->_immediateChildren as $child)
    {
      $child->charsetChanged($charset);
    }
  }
  
  private function _sortChildren()
  {
    $shouldSort = false;
    foreach ($this->_immediateChildren as $child)
    {
      //NOTE: This include alternative parts moved into a related part
      if ($child->getNestingLevel() == self::LEVEL_ALTERNATIVE)
      {
        $shouldSort = true;
        break;
      }
    }
    
    //Sort in order of preference, if there is one
    if ($shouldSort)
    {
      usort($this->_immediateChildren, array($this, '_childSortAlgorithm'));
    }
  }
  
  private function _childSortAlgorithm($a, $b)
  {
    $typePrefs = array();
    $types = array(
      strtolower($a->getContentType()),
      strtolower($b->getContentType())
      );
    foreach ($types as $type)
    {
      $typePrefs[] = (array_key_exists($type, $this->_alternativePartOrder))
        ? $this->_alternativePartOrder[$type]
        : (max($this->_alternativePartOrder) + 1);
    }
    return ($typePrefs[0] >= $typePrefs[1]) ? 1 : -1;
  }
  
  // -- Destructor
  
  /**
   * Empties it's own contents from the cache.
   */
  public function __destruct()
  {
    $this->_cache->clearAll($this->_cacheKey);
  }
  
}
 /* .tmp\flat\1\2\SmtpTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\SmtpTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/EsmtpTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Sends Messages over SMTP with ESMTP support.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_SmtpTransport extends Swift_Transport_EsmtpTransport
{
  
  /**
   * Create a new SmtpTransport, optionally with $host, $port and $security.
   * @param string $host
   * @param int $port
   * @param int $security
   */
  public function __construct($host = 'localhost', $port = 25,
    $security = null)
  {
    call_user_func_array(
      array($this, 'Swift_Transport_EsmtpTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.smtp')
      );
    
    $this->setHost($host);
    $this->setPort($port);
    $this->setEncryption($security);
  }
  
  /**
   * Create a new SmtpTransport instance.
   * @param string $host
   * @param int $port
   * @param int $security
   * @return Swift_SmtpTransport
   */
  public static function newInstance($host = 'localhost', $port = 25,
    $security = null)
  {
    return new self($host, $port, $security);
  }
  
}
 /* .tmp\flat\1\2\StreamBuffer.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\StreamBuffer.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/ByteStream/AbstractFilterableInputStream.php';
//@require 'Swift/ReplacementFilterFactory.php';
//@require 'Swift/Transport/IoBuffer.php';
//@require 'Swift/TransportException.php';

/**
 * A generic IoBuffer implementation supporting remote sockets and local processes.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_StreamBuffer
  extends Swift_ByteStream_AbstractFilterableInputStream
  implements Swift_Transport_IoBuffer
{
  
  /** A primary socket */
  private $_stream;
  
  /** The input stream */
  private $_in;
  
  /** The output stream */
  private $_out;
  
  /** Buffer initialization parameters */
  private $_params = array();
  
  /** The ReplacementFilterFactory */
  private $_replacementFactory;
  
  /** Translations performed on data being streamed into the buffer */
  private $_translations = array();
  
  /**
   * Create a new StreamBuffer using $replacementFactory for transformations.
   * @param Swift_ReplacementFilterFactory $replacementFactory
   */
  public function __construct(
    Swift_ReplacementFilterFactory $replacementFactory)
  {
    $this->_replacementFactory = $replacementFactory;
  }
  
  /**
   * Perform any initialization needed, using the given $params.
   * Parameters will vary depending upon the type of IoBuffer used.
   * @param array $params
   */
  public function initialize(array $params)
  {
    $this->_params = $params;
    switch ($params['type'])
    {
      case self::TYPE_PROCESS:
        $this->_establishProcessConnection();
        break;
      case self::TYPE_SOCKET:
      default:
        $this->_establishSocketConnection();
        break;
    }
  }
  
  /**
   * Set an individual param on the buffer (e.g. switching to SSL).
   * @param string $param
   * @param mixed $value
   */
  public function setParam($param, $value)
  {
    if (isset($this->_stream))
    {
      switch ($param)
      {
        case 'protocol':
          if (!array_key_exists('protocol', $this->_params)
            || $value != $this->_params['protocol'])
          {
            if ('tls' == $value)
            {
              stream_socket_enable_crypto(
                $this->_stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT
                );
            }
          }
          break;
      }
    }
    $this->_params[$param] = $value;
  }
  
  /**
   * Perform any shutdown logic needed.
   */
  public function terminate()
  {
    if (isset($this->_stream))
    {
      switch ($this->_params['type'])
      {
        case self::TYPE_PROCESS:
          fclose($this->_in);
          fclose($this->_out);
          proc_close($this->_stream);
          break;
        case self::TYPE_SOCKET:
        default:
          fclose($this->_stream);
          break;
      }
    }
    $this->_stream = null;
    $this->_out = null;
    $this->_in = null;
  }
  
  /**
   * Set an array of string replacements which should be made on data written
   * to the buffer.  This could replace LF with CRLF for example.
   * @param string[] $replacements
   */
  public function setWriteTranslations(array $replacements)
  {
    foreach ($this->_translations as $search => $replace)
    {
      if (!isset($replacements[$search]))
      {
        $this->removeFilter($search);
        unset($this->_translations[$search]);
      }
    }
    
    foreach ($replacements as $search => $replace)
    {
      if (!isset($this->_translations[$search]))
      {
        $this->addFilter(
          $this->_replacementFactory->createFilter($search, $replace), $search
          );
        $this->_translations[$search] = true;
      }
    }
  }
  
  /**
   * Get a line of output (including any CRLF).
   * The $sequence number comes from any writes and may or may not be used
   * depending upon the implementation.
   * @param int $sequence of last write to scan from
   * @return string
   */
  public function readLine($sequence)
  {
    if (isset($this->_out) && !feof($this->_out))
    {
      $line = fgets($this->_out);
      return $line;
    }
  }
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   */
  public function read($length)
  {
    if (isset($this->_out) && !feof($this->_out))
    {
      $ret = fread($this->_out, $length);
      return $ret;
    }
  }
  
  /** Not implemented */
  public function setReadPointer($byteOffset)
  {
  }
  
  // -- Protected methods
  
  /** Flush the stream contents */
  protected function _flush()
  {
    if (isset($this->_in))
    {
      fflush($this->_in);
    }
  }
  
  /** Write this bytes to the stream */
  protected function _commit($bytes)
  {
    if (isset($this->_in)
      && fwrite($this->_in, $bytes))
    {
      return ++$this->_sequence;
    }
  }
  
  // -- Private methods
  
  /**
   * Establishes a connection to a remote server.
   * @access private
   */
  private function _establishSocketConnection()
  {
    $host = $this->_params['host'];
    if (!empty($this->_params['protocol']))
    {
      $host = $this->_params['protocol'] . '://' . $host;
    }
    $timeout = 15;
    if (!empty($this->_params['timeout']))
    {
      $timeout = $this->_params['timeout'];
    }
    if (!$this->_stream = fsockopen($host, $this->_params['port'], $errno, $errstr, $timeout))
    {
      throw new Swift_TransportException(
        'Connection could not be established with host ' . $this->_params['host'] .
        ' [' . $errstr . ' #' . $errno . ']'
        );
    }
    if (!empty($this->_params['blocking']))
    {
      stream_set_blocking($this->_stream, 1);
    }
    else
    {
      stream_set_blocking($this->_stream, 0);
    }
    $this->_in =& $this->_stream;
    $this->_out =& $this->_stream;
  }
  
  /**
   * Opens a process for input/output.
   * @access private
   */
  private function _establishProcessConnection()
  {
    $command = $this->_params['command'];
    $descriptorSpec = array(
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w'),
      2 => array('pipe', 'w')
      );
    $this->_stream = proc_open($command, $descriptorSpec, $pipes);
    stream_set_blocking($pipes[2], 0);
    if ($err = stream_get_contents($pipes[2]))
    {
      throw new Swift_TransportException(
        'Process could not be started [' . $err . ']'
        );
    }
    $this->_in =& $pipes[0];
    $this->_out =& $pipes[1];
  }
  
}
 /* .tmp\flat\1\2\TransportChangeEvent.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\TransportChangeEvent.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventObject.php';

/**
 * Generated when the state of a Transport is changed (i.e. stopped/started).
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_TransportChangeEvent extends Swift_Events_EventObject
{
  
  /**
   * Get the Transport.
   * @return Swift_Transport
   */
  public function getTransport()
  {
    return $this->getSource();
  }
  
}
 /* .tmp\flat\1\2\TransportException.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\TransportException.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/IoException.php';

/**
 * TransportException thrown when an error occurs in the Transport subsystem.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_TransportException extends Swift_IoException
{
  
  /**
   * Create a new TransportException with $message.
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
 /* .tmp\flat\1\2\TransportExceptionEvent.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\TransportExceptionEvent.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/EventObject.php';
//@require 'Swift/TransportException.php';

/**
 * Generated when a TransportException is thrown from the Transport system.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_TransportExceptionEvent extends Swift_Events_EventObject
{
  
  /**
   * The Exception thrown.
   * @var Swift_TransportException
   */
  private $_exception;
  
  /**
   * Create a new TransportExceptionEvent for $transport.
   * @param Swift_Transport $transport
   * @param Swift_TransportException $ex
   */
  public function __construct(Swift_Transport $transport,
    Swift_TransportException $ex)
  {
    parent::__construct($transport);
    $this->_exception = $ex;
  }
  
  /**
   * Get the TransportException thrown.
   * @return Swift_TransportException
   */
  public function getException()
  {
    return $this->_exception;
  }
  
}
 /* .tmp\flat\1\2\UnstructuredHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\UnstructuredHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Headers/AbstractHeader.php';
//@require 'Swift/Mime/HeaderEncoder.php';

/**
 * A Simple MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_UnstructuredHeader
  extends Swift_Mime_Headers_AbstractHeader
{
  
  /**
   * The value of this Header.
   * @var string
   * @access private
   */
  private $_value;
  
  /**
   * Creates a new SimpleHeader with $name.
   * @param string $name
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function __construct($name, Swift_Mime_HeaderEncoder $encoder)
  {
    $this->setFieldName($name);
    $this->setEncoder($encoder);
  }
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_TEXT;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a string for the field value.
   * @param string $model
   */
  public function setFieldBodyModel($model)
  {
    $this->setValue($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns a string.
   * @return string
   */
  public function getFieldBodyModel()
  {
    return $this->getValue();
  }
  
  /**
   * Get the (unencoded) value of this header.
   * @return string
   */
  public function getValue()
  {
    return $this->_value;
  }
  
  /**
   * Set the (unencoded) value of this header.
   * @param string $value
   */
  public function setValue($value)
  {
    $this->clearCachedValueIf($this->_value != $value);
    $this->_value = $value;
  }
  
  /**
   * Get the value of this header prepared for rendering.
   * @return string
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      $this->setCachedValue(
        str_replace('\\', '\\\\', $this->encodeWords(
          $this, $this->_value, -1, $this->getCharset(), $this->getEncoder()
          ))
        );
    }
    return $this->getCachedValue();
  }
  
}
 /* .tmp\flat\1\2\3\Attachment.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\Attachment.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/SimpleMimeEntity.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/FileStream.php';
//@require 'Swift/KeyCache.php';

/**
 * An attachment, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Attachment extends Swift_Mime_SimpleMimeEntity
{
  
  /** Recognized MIME types */
  private $_mimeTypes = array();
  
  /**
   * Create a new Attachment with $headers, $encoder and $cache.
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param array $mimeTypes optional
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache,
    $mimeTypes = array())
  {
    parent::__construct($headers, $encoder, $cache);
    $this->setDisposition('attachment');
    $this->setContentType('application/octet-stream');
    $this->_mimeTypes = $mimeTypes;
  }
  
  /**
   * Get the nesting level used for this attachment.
   * Always returns {@link LEVEL_MIXED}.
   * @return int
   */
  public function getNestingLevel()
  {
    return self::LEVEL_MIXED;
  }
  
  /**
   * Get the Content-Disposition of this attachment.
   * By default attachments have a disposition of "attachment".
   * @return string
   */
  public function getDisposition()
  {
    return $this->_getHeaderFieldModel('Content-Disposition');
  }
  
  /**
   * Set the Content-Disposition of this attachment.
   * @param string $disposition
   */
  public function setDisposition($disposition)
  {
    if (!$this->_setHeaderFieldModel('Content-Disposition', $disposition))
    {
      $this->getHeaders()->addParameterizedHeader(
        'Content-Disposition', $disposition
        );
    }
    return $this;
  }
  
  /**
   * Get the filename of this attachment when downloaded.
   * @return string
   */
  public function getFilename()
  {
    return $this->_getHeaderParameter('Content-Disposition', 'filename');
  }
  
  /**
   * Set the filename of this attachment.
   * @param string $filename
   */
  public function setFilename($filename)
  {
    $this->_setHeaderParameter('Content-Disposition', 'filename', $filename);
    $this->_setHeaderParameter('Content-Type', 'name', $filename);
    return $this;
  }
  
  /**
   * Get the file size of this attachment.
   * @return int
   */
  public function getSize()
  {
    return $this->_getHeaderParameter('Content-Disposition', 'size');
  }
  
  /**
   * Set the file size of this attachment.
   * @param int $size
   */
  public function setSize($size)
  {
    $this->_setHeaderParameter('Content-Disposition', 'size', $size);
    return $this;
  }
  
  /**
   * Set the file that this attachment is for.
   * @param Swift_FileStream $file
   * @param string $contentType optional
   */
  public function setFile(Swift_FileStream $file, $contentType = null)
  {
    $this->setFilename(basename($file->getPath()));
    $this->setBody($file, $contentType);
    if (!isset($contentType))
    {
      $extension = strtolower(substr(
        $file->getPath(), strrpos($file->getPath(), '.') + 1
        ));
      
      if (array_key_exists($extension, $this->_mimeTypes))
      {
        $this->setContentType($this->_mimeTypes[$extension]);
      }
    }
    return $this;
  }
  
}
 /* .tmp\flat\1\2\3\Base64ContentEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\Base64ContentEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Encoder/Base64Encoder.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Handles Base 64 Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_Base64ContentEncoder
  extends Swift_Encoder_Base64Encoder
  implements Swift_Mime_ContentEncoder
{
  
  /**
   * Encode stream $in to stream $out.
   * @param Swift_OutputByteStream $in
   * @param Swift_InputByteStream $out
   * @param int $firstLineOffset
   * @param int $maxLineLength, optional, 0 indicates the default of 76 bytes
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if (0 >= $maxLineLength || 76 < $maxLineLength)
    {
      $maxLineLength = 76;
    }
    
    $remainder = 0;
    
    while (false !== $bytes = $os->read(8190))
    {
      $encoded = base64_encode($bytes);
      $encodedTransformed = '';
      $thisMaxLineLength = $maxLineLength - $remainder - $firstLineOffset;
      
      while ($thisMaxLineLength < strlen($encoded))
      {
        $encodedTransformed .= substr($encoded, 0, $thisMaxLineLength) . "\r\n";
        $firstLineOffset = 0;
        $encoded = substr($encoded, $thisMaxLineLength);
        $thisMaxLineLength = $maxLineLength;
        $remainder = 0;
      }
      
      if (0 < $remainingLength = strlen($encoded))
      {
        $remainder += $remainingLength;
        $encodedTransformed .= $encoded;
        $encoded = null;
      }
      
      $is->write($encodedTransformed);
    }
  }
  
  /**
   * Get the name of this encoding scheme.
   * Returns the string 'base64'.
   * @return string
   */
  public function getName()
  {
    return 'base64';
  }
  
}
 /* .tmp\flat\1\2\3\Base64HeaderEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\Base64HeaderEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//require_once dirname(__FILE__) . '/../HeaderEncoder.php';
//require_once dirname(__FILE__) . '/../../Encoder/Base64Encoder.php';


/**
 * Handles Base64 (B) Header Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderEncoder_Base64HeaderEncoder
  extends Swift_Encoder_Base64Encoder
  implements Swift_Mime_HeaderEncoder
{
  
  /**
   * Get the name of this encoding scheme.
   * Returns the string 'B'.
   * @return string
   */
  public function getName()
  {
    return 'B';
  }
  
}
 /* .tmp\flat\1\2\3\FailoverTransport.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\FailoverTransport.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Transport/FailoverTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Contains a list of redundant Transports so when one fails, the next is used.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_FailoverTransport extends Swift_Transport_FailoverTransport
{
  
  /**
   * Creates a new FailoverTransport with $transports.
   * @param array $transports
   */
  public function __construct($transports = array())
  {
    call_user_func_array(
      array($this, 'Swift_Transport_FailoverTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.failover')
      );
    
    $this->setTransports($transports);
  }
  
  /**
   * Create a new FailoverTransport instance.
   * @param string $transports
   * @return Swift_FailoverTransport
   */
  public static function newInstance($transports = array())
  {
    return new self($transports);
  }
  
}
 /* .tmp\flat\1\2\3\MimePart.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\MimePart.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/SimpleMimeEntity.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/KeyCache.php';

/**
 * A MIME part, in a multipart message.
 * 
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_MimePart extends Swift_Mime_SimpleMimeEntity
{
  
  /** The format parameter last specified by the user */
  protected $_userFormat;
  
  /** The charset last specified by the user */
  protected $_userCharset;
  
  /** The delsp parameter last specified by the user */
  protected $_userDelSp;
  
  /** The nesting level of this MimePart */
  private $_nestingLevel = self::LEVEL_ALTERNATIVE;
  
  /**
   * Create a new MimePart with $headers, $encoder and $cache.
   * 
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param string $charset
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, $charset = null)
  {
    parent::__construct($headers, $encoder, $cache);
    $this->setContentType('text/plain');
    if (!is_null($charset))
    {
      $this->setCharset($charset);
    }
  }
  
  /**
   * Set the body of this entity, either as a string, or as an instance of
   * {@link Swift_OutputByteStream}.
   * 
   * @param mixed $body
   * @param string $contentType optional
   * @param string $charset optional
   */
  public function setBody($body, $contentType = null, $charset = null)
  {
    parent::setBody($body, $contentType);
    if (isset($charset))
    {
      $this->setCharset($charset);
    }
    return $this;
  }
  
  /**
   * Get the character set of this entity.
   * 
   * @return string
   */
  public function getCharset()
  {
    return $this->_getHeaderParameter('Content-Type', 'charset');
  }
  
  /**
   * Set the character set of this entity.
   * 
   * @param string $charset
   */
  public function setCharset($charset)
  {
    $this->_setHeaderParameter('Content-Type', 'charset', $charset);
    if ($charset !== $this->_userCharset)
    {
      $this->_clearCache();
    }
    $this->_userCharset = $charset;
    parent::charsetChanged($charset);
    return $this;
  }
  
  /**
   * Get the format of this entity (i.e. flowed or fixed).
   * 
   * @return string
   */
  public function getFormat()
  {
    return $this->_getHeaderParameter('Content-Type', 'format');
  }
  
  /**
   * Set the format of this entity (flowed or fixed).
   * 
   * @param string $format
   */
  public function setFormat($format)
  {
    $this->_setHeaderParameter('Content-Type', 'format', $format);
    $this->_userFormat = $format;
    return $this;
  }
  
  /**
   * Test if delsp is being used for this entity.
   * 
   * @return boolean
   */
  public function getDelSp()
  {
    return ($this->_getHeaderParameter('Content-Type', 'delsp') == 'yes')
      ? true
      : false;
  }
  
  /**
   * Turn delsp on or off for this entity.
   * 
   * @param boolean $delsp
   */
  public function setDelSp($delsp = true)
  {
    $this->_setHeaderParameter('Content-Type', 'delsp', $delsp ? 'yes' : null);
    $this->_userDelSp = $delsp;
    return $this;
  }
  
  /**
   * Get the nesting level of this entity.
   * 
   * @return int
   * @see LEVEL_TOP, LEVEL_ALTERNATIVE, LEVEL_MIXED, LEVEL_RELATED
   */
  public function getNestingLevel()
  {
    return $this->_nestingLevel;
  }
  
  /**
   * Receive notification that the charset has changed on this document, or a
   * parent document.
   * 
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->setCharset($charset);
  }
  
  // -- Protected methods
  
  /** Fix the content-type and encoding of this entity */
  protected function _fixHeaders()
  {
    parent::_fixHeaders();
    if (count($this->getChildren()))
    {
      $this->_setHeaderParameter('Content-Type', 'charset', null);
      $this->_setHeaderParameter('Content-Type', 'format', null);
      $this->_setHeaderParameter('Content-Type', 'delsp', null);
    }
    else
    {
      $this->setCharset($this->_userCharset);
      $this->setFormat($this->_userFormat);
      $this->setDelSp($this->_userDelSp);
    }
  }
  
  /** Set the nesting level of this entity */
  protected function _setNestingLevel($level)
  {
    $this->_nestingLevel = $level;
  }
  
}
 /* .tmp\flat\1\2\3\ParameterizedHeader.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\ParameterizedHeader.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Headers/UnstructuredHeader.php';
//@require 'Swift/Mime/HeaderEncoder.php';
//@require 'Swift/Mime/ParameterizedHeader.php';
//@require 'Swift/Encoder.php';

/**
 * An abstract base MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_ParameterizedHeader
  extends Swift_Mime_Headers_UnstructuredHeader
  implements Swift_Mime_ParameterizedHeader
{
  
  /**
   * The Encoder used to encode the parameters.
   * @var Swift_Encoder
   * @access private
   */
  private $_paramEncoder;
  
  /**
   * The parameters as an associative array.
   * @var string[]
   * @access private
   */
  private $_params = array();
  
  /**
   * RFC 2231's definition of a token.
   * @var string
   * @access private
   */
  private $_tokenRe;
  
  /**
   * Creates a new ParameterizedHeader with $name.
   * @param string $name
   * @param Swift_Mime_HeaderEncoder $encoder
   * @param Swift_Encoder $paramEncoder, optional
   */ 
  public function __construct($name, Swift_Mime_HeaderEncoder $encoder,
    Swift_Encoder $paramEncoder = null)
  {
    $this->setFieldName($name);
    $this->setEncoder($encoder);
    $this->_paramEncoder = $paramEncoder;
    $this->initializeGrammar();
    $this->_tokenRe = '(?:[\x21\x23-\x27\x2A\x2B\x2D\x2E\x30-\x39\x41-\x5A\x5E-\x7E]+)';
  }
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_PARAMETERIZED;
  }
  
  /**
   * Set the character set used in this Header.
   * @param string $charset
   */
  public function setCharset($charset)
  {
    parent::setCharset($charset);
    if (isset($this->_paramEncoder))
    {
      $this->_paramEncoder->charsetChanged($charset);
    }
  }
  
  /**
   * Set the value of $parameter.
   * @param string $parameter
   * @param string $value
   */
  public function setParameter($parameter, $value)
  {
    $this->setParameters(array_merge($this->getParameters(), array($parameter => $value)));
  }
  
  /**
   * Get the value of $parameter.
   * @return string
   */
  public function getParameter($parameter)
  {
    $params = $this->getParameters();
    return array_key_exists($parameter, $params)
      ? $params[$parameter]
      : null;
  }
  
  /**
   * Set an associative array of parameter names mapped to values.
   * @param string[]
   */
  public function setParameters(array $parameters)
  {
    $this->clearCachedValueIf($this->_params != $parameters);
    $this->_params = $parameters;
  }
  
  /**
   * Returns an associative array of parameter names mapped to values.
   * @return string[]
   */
  public function getParameters()
  {
    return $this->_params;
  }
  
  /**
   * Get the value of this header prepared for rendering.
   * @return string
   */
  public function getFieldBody() //TODO: Check caching here
  {
    $body = parent::getFieldBody();
    foreach ($this->_params as $name => $value)
    {
      if (!is_null($value))
      {
        //Add the parameter
        $body .= '; ' . $this->_createParameter($name, $value);
      }
    }
    return $body;
  }
  
  // -- Protected methods
  
  /**
   * Generate a list of all tokens in the final header.
   * This doesn't need to be overridden in theory, but it is for implementation
   * reasons to prevent potential breakage of attributes.
   * @return string[]
   * @access protected
   */
  protected function toTokens($string = null)
  {
    $tokens = parent::toTokens(parent::getFieldBody());
    
    //Try creating any parameters
    foreach ($this->_params as $name => $value)
    {
      if (!is_null($value))
      {
        //Add the semi-colon separator
        $tokens[count($tokens)-1] .= ';';
        $tokens = array_merge($tokens, $this->generateTokenLines(
          ' ' . $this->_createParameter($name, $value)
          ));
      }
    }
    
    return $tokens;
  }
  
  // -- Private methods
  
  /**
   * Render a RFC 2047 compliant header parameter from the $name and $value.
   * @param string $name
   * @param string $value
   * @return string
   * @access private
   */
  private function _createParameter($name, $value)
  {
    $origValue = $value;
    
    $encoded = false;
    //Allow room for parameter name, indices, "=" and DQUOTEs
    $maxValueLength = $this->getMaxLineLength() - strlen($name . '=*N"";') - 1;
    $firstLineOffset = 0;
    
    //If it's not already a valid parameter value...
    if (!preg_match('/^' . $this->_tokenRe . '$/D', $value))
    {
      //TODO: text, or something else??
      //... and it's not ascii
      if (!preg_match('/^' . $this->getGrammar('text') . '*$/D', $value))
      {
        $encoded = true;
        //Allow space for the indices, charset and language
        $maxValueLength = $this->getMaxLineLength() - strlen($name . '*N*="";') - 1;
        $firstLineOffset = strlen(
          $this->getCharset() . "'" . $this->getLanguage() . "'"
          );
      }
    }
    
    //Encode if we need to
    if ($encoded || strlen($value) > $maxValueLength)
    {
      if (isset($this->_paramEncoder))
      {
        $value = $this->_paramEncoder->encodeString(
          $origValue, $firstLineOffset, $maxValueLength
          );
      }
      else //We have to go against RFC 2183/2231 in some areas for interoperability
      {
        $value = $this->getTokenAsEncodedWord($origValue);
        $encoded = false;
      }
    }
    
    $valueLines = isset($this->_paramEncoder) ? explode("\r\n", $value) : array($value);
    
    //Need to add indices
    if (count($valueLines) > 1)
    {
      $paramLines = array();
      foreach ($valueLines as $i => $line)
      {
        $paramLines[] = $name . '*' . $i .
          $this->_getEndOfParameterValue($line, $encoded, $i == 0);
      }
      return implode(";\r\n ", $paramLines);
    }
    else
    {
      return $name . $this->_getEndOfParameterValue(
        $valueLines[0], $encoded, true
        );
    }
  }
  
  /**
   * Returns the parameter value from the "=" and beyond.
   * @param string $value to append
   * @param boolean $encoded
   * @param boolean $firstLine
   * @return string
   * @access private
   */
  private function _getEndOfParameterValue($value, $encoded = false, $firstLine = false)
  {
    if (!preg_match('/^' . $this->_tokenRe . '$/D', $value))
    {
      $value = '"' . $value . '"';
    }
    $prepend = '=';
    if ($encoded)
    {
      $prepend = '*=';
      if ($firstLine)
      {
        $prepend = '*=' . $this->getCharset() . "'" . $this->getLanguage() .
          "'";
      }
    }
    return $prepend . $value;
  }
  
}
 /* .tmp\flat\1\2\3\PlainContentEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\PlainContentEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Handles binary/7/8-bit Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_PlainContentEncoder
  implements Swift_Mime_ContentEncoder
{
  
  /**
   * The name of this encoding scheme (probably 7bit or 8bit).
   * @var string
   * @access private
   */
  private $_name;
  
  /**
   * True if canonical transformations should be done.
   * @var boolean
   * @access private
   */
  private $_canonical;
  
  /**
   * Creates a new PlainContentEncoder with $name (probably 7bit or 8bit).
   * @param string $name
   * @param boolean $canonical If canonicalization transformation should be done.
   */
  public function __construct($name, $canonical = false)
  {
    $this->_name = $name;
    $this->_canonical = $canonical;
  }
  
  /**
   * Encode a given string to produce an encoded string.
   * @param string $string
   * @param int $firstLineOffset, ignored
   * @param int $maxLineLength - 0 means no wrapping will occur
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if ($this->_canonical)
    {
      $string = $this->_canonicalize($string);
    }
    return $this->_safeWordWrap($string, $maxLineLength, "\r\n");
  }
  
  /**
   * Encode stream $in to stream $out.
   * @param Swift_OutputByteStream $in
   * @param Swift_InputByteStream $out
   * @param int $firstLineOffset, ignored
   * @param int $maxLineLength, optional, 0 means no wrapping will occur
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    $leftOver = '';
    while (false !== $bytes = $os->read(8192))
    {
      $toencode = $leftOver . $bytes;
      if ($this->_canonical)
      {
        $toencode = $this->_canonicalize($toencode);
      }
      $wrapped = $this->_safeWordWrap($toencode, $maxLineLength, "\r\n");
      $lastLinePos = strrpos($wrapped, "\r\n");
      $leftOver = substr($wrapped, $lastLinePos);
      $wrapped = substr($wrapped, 0, $lastLinePos);
      
      $is->write($wrapped);
    }
    if (strlen($leftOver))
    {
      $is->write($leftOver);
    }
  }
  
  /**
   * Get the name of this encoding scheme.
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }
  
  /**
   * Not used.
   */
  public function charsetChanged($charset)
  {
  }
  
  // -- Private methods
  
  /**
   * A safer (but weaker) wordwrap for unicode.
   * @param string $string
   * @param int $length
   * @param string $le
   * @return string
   * @access private
   */
  private function _safeWordwrap($string, $length = 75, $le = "\r\n")
  {
    if (0 >= $length)
    {
      return $string;
    }
    
    $originalLines = explode($le, $string);
    
    $lines = array();
    $lineCount = 0;
    
    foreach ($originalLines as $originalLine)
    {
      $lines[] = '';
      $currentLine =& $lines[$lineCount++];
      
      //$chunks = preg_split('/(?<=[\ \t,\.!\?\-&\+\/])/', $originalLine);
      $chunks = preg_split('/(?<=\s)/', $originalLine);
      
      foreach ($chunks as $chunk)
      {
        if (0 != strlen($currentLine)
          && strlen($currentLine . $chunk) > $length)
        {
          $lines[] = '';
          $currentLine =& $lines[$lineCount++];
        }
        $currentLine .= $chunk;
      }
    }
    
    return implode("\r\n", $lines);
  }
  
  /**
   * Canonicalize string input (fix CRLF).
   * @param string $string
   * @return string
   * @access private
   */
  private function _canonicalize($string)
  {
    return str_replace(
      array("\r\n", "\r", "\n"),
      array("\n", "\n", "\r\n"),
      $string
      );
  }
  
}
 /* .tmp\flat\1\2\3\QpContentEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\QpContentEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Encoder/QpEncoder.php';
//@require 'Swift/InputByteStrean.php';
//@require 'Swift/OutputByteStream.php';
//@require 'Swift/CharacterStream.php';

/**
 * Handles Quoted Printable (QP) Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_QpContentEncoder extends Swift_Encoder_QpEncoder
  implements Swift_Mime_ContentEncoder
{

  /**
   * Creates a new QpContentEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   * @param Swift_StreamFilter $filter if canonicalization should occur
   */
  public function __construct(Swift_CharacterStream $charStream,
    Swift_StreamFilter $filter = null)
  {
    parent::__construct($charStream, $filter);
  }

  /**
   * Encode stream $in to stream $out.
   * QP encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   * @param Swift_OutputByteStream $os output stream
   * @param Swift_InputByteStream $is input stream
   * @param int $firstLineOffset
   * @param int $maxLineLength
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if ($maxLineLength > 76 || $maxLineLength <= 0)
    {
      $maxLineLength = 76;
    }

    $thisLineLength = $maxLineLength - $firstLineOffset;

    $this->_charStream->flushContents();
    $this->_charStream->importByteStream($os);
    
    $currentLine = '';
    $prepend = '';
    $size=$lineLen=0;

    while (false !== $bytes = $this->_nextSequence())
    {
      //If we're filtering the input
      if (isset($this->_filter))
      {
        //If we can't filter because we need more bytes
        while ($this->_filter->shouldBuffer($bytes))
        {
          //Then collect bytes into the buffer
          if (false === $moreBytes = $this->_nextSequence(1))
          {
            break;
          }

          foreach ($moreBytes as $b)
          {
            $bytes[] = $b;
          }
        }
        //And filter them
        $bytes = $this->_filter->filter($bytes);
      }

      $enc = $this->_encodeByteSequence($bytes, $size);
      if ($currentLine && $lineLen+$size >= $thisLineLength)
      {
        $is->write($prepend . $this->_standardize($currentLine));
        $currentLine = '';
        $prepend = "=\r\n";
        $thisLineLength = $maxLineLength;
        $lineLen=0;
      }
      $lineLen+=$size;
      $currentLine .= $enc;
    }
    if (strlen($currentLine))
    {
      $is->write($prepend . $this->_standardize($currentLine));
    }
  }

  /**
   * Get the name of this encoding scheme.
   * Returns the string 'quoted-printable'.
   * @return string
   */
  public function getName()
  {
    return 'quoted-printable';
  }

}
 /* .tmp\flat\1\2\3\QpHeaderEncoder.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\QpHeaderEncoder.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//require_once dirname(__FILE__) . '/../HeaderEncoder.php';
//require_once dirname(__FILE__) . '/../../Encoder/QpEncoder.php';
//require_once dirname(__FILE__) . '/../../CharacterStream.php';

/**
 * Handles Quoted Printable (Q) Header Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderEncoder_QpHeaderEncoder extends Swift_Encoder_QpEncoder
  implements Swift_Mime_HeaderEncoder
{

  private static $_headerSafeMap = array();

  /**
   * Creates a new QpHeaderEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   */
  public function __construct(Swift_CharacterStream $charStream)
  {
    parent::__construct($charStream);
    if (empty(self::$_headerSafeMap))
    {
      foreach (array_merge(
        range(0x61, 0x7A), range(0x41, 0x5A),
        range(0x30, 0x39), array(0x20, 0x21, 0x2A, 0x2B, 0x2D, 0x2F)
        ) as $byte)
      {
        self::$_headerSafeMap[$byte] = chr($byte);
      }
    }
  }

  /**
   * Get the name of this encoding scheme.
   * Returns the string 'Q'.
   * @return string
   */
  public function getName()
  {
    return 'Q';
  }

  /**
   * Takes an unencoded string and produces a Q encoded string from it.
   * @param string $string to encode
   * @param int $firstLineOffset, optional
   * @param int $maxLineLength, optional, 0 indicates the default of 76 chars
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    return str_replace(array(' ', '=20', "=\r\n"), array('_', '_', "\r\n"),
      parent::encodeString($string, $firstLineOffset, $maxLineLength)
      );
  }

  // -- Overridden points of extension

  /**
   * Encode the given byte array into a verbatim QP form.
   * @param int[] $bytes
   * @return string
   * @access protected
   */
  protected function _encodeByteSequence(array $bytes, &$size)
  {
    $ret = '';
    $size=0;
    foreach ($bytes as $b)
    {
      if (isset(self::$_headerSafeMap[$b]))
      {
        $ret .= self::$_headerSafeMap[$b];
        ++$size;
      }
      else
      {
        $ret .= self::$_qpMap[$b];
        $size+=3;
      }
    }
    return $ret;
  }

}
 /* .tmp\flat\1\2\3\ThrottlerPlugin.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\ThrottlerPlugin.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Events/SendEvent.php';
//@require 'Swift/Plugins/BandwidthMonitorPlugin.php';
//@require 'Swift/Plugins/Sleeper.php';
//@require 'Swift/Plugins/Timer.php';

/**
 * Throttles the rate at which emails are sent.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_ThrottlerPlugin
  extends Swift_Plugins_BandwidthMonitorPlugin
  implements Swift_Plugins_Sleeper, Swift_Plugins_Timer
{
  
  /** Flag for throttling in bytes per minute */
  const BYTES_PER_MINUTE = 0x01;
  
  /** Flag for throttling in emails per minute */
  const MESSAGES_PER_MINUTE = 0x10;
  
  /**
   * The Sleeper instance for sleeping.
   * @var Swift_Plugins_Sleeper
   * @access private
   */
  private $_sleeper;
  
  /**
   * The Timer instance which provides the timestamp.
   * @var Swift_Plugins_Timer
   * @access private
   */
  private $_timer;
  
  /**
   * The time at which the first email was sent.
   * @var int
   * @access private
   */
  private $_start;
  
  /**
   * The rate at which messages should be sent.
   * @var int
   * @access private
   */
  private $_rate;
  
  /**
   * The mode for throttling.
   * This is {@link BYTES_PER_MINUTE} or {@link MESSAGES_PER_MINUTE}
   * @var int
   * @access private
   */
  private $_mode;
  
  /**
   * An internal counter of the number of messages sent.
   * @var int
   * @access private
   */
  private $_messages = 0;
  
  /**
   * Create a new ThrottlerPlugin.
   * @param int $rate
   * @param int $mode, defaults to {@link BYTES_PER_MINUTE}
   * @param Swift_Plugins_Sleeper $sleeper (only needed in testing)
   * @param Swift_Plugins_Timer $timer (only needed in testing)
   */
  public function __construct($rate, $mode = self::BYTES_PER_MINUTE,
    Swift_Plugins_Sleeper $sleeper = null, Swift_Plugins_Timer $timer = null)
  {
    $this->_rate = $rate;
    $this->_mode = $mode;
    $this->_sleeper = $sleeper;
    $this->_timer = $timer;
  }
  
  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $time = $this->getTimestamp();
    if (!isset($this->_start))
    {
      $this->_start = $time;
    }
    $duration = $time - $this->_start;
    
    if (self::BYTES_PER_MINUTE == $this->_mode)
    {
      $sleep = $this->_throttleBytesPerMinute($duration);
    }
    else
    {
      $sleep = $this->_throttleMessagesPerMinute($duration);
    }
    
    if ($sleep > 0)
    {
      $this->sleep($sleep);
    }
  }
  
  /**
   * Invoked when a Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    parent::sendPerformed($evt);
    ++$this->_messages;
  }
  
  /**
   * Sleep for $seconds.
   * @param int $seconds
   */
  public function sleep($seconds)
  {
    if (isset($this->_sleeper))
    {
      $this->_sleeper->sleep($seconds);
    }
    else
    {
      sleep($seconds);
    }
  }
  
  /**
   * Get the current UNIX timestamp
   * @return int
   */
  public function getTimestamp()
  {
    if (isset($this->_timer))
    {
      return $this->_timer->getTimestamp();
    }
    else
    {
      return time();
    }
  }
  
  // -- Private methods
  
  /**
   * Get a number of seconds to sleep for.
   * @param int $timePassed
   * @return int
   * @access private
   */
  private function _throttleBytesPerMinute($timePassed)
  {
    $expectedDuration = $this->getBytesOut() / ($this->_rate / 60);
    return (int) ceil($expectedDuration - $timePassed);
  }
  
  /**
   * Get a number of seconds to sleep for.
   * @param int $timePassed
   * @return int
   * @access private
   */
  private function _throttleMessagesPerMinute($timePassed)
  {
    $expectedDuration = $this->_messages / ($this->_rate / 60);
    return (int) ceil($expectedDuration - $timePassed);
  }
  
}
 /* .tmp\flat\1\2\3\4\Attachment.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\4\Attachment.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Attachment.php';
//@require 'Swift/ByteStream/FileByteStream.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Attachment class for attaching files to a {@link Swift_Mime_Message}.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Attachment extends Swift_Mime_Attachment
{
  
  /**
   * Create a new Attachment.
   * Details may be optionally provided to the constructor.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   */
  public function __construct($data = null, $filename = null,
    $contentType = null)
  {
    call_user_func_array(
      array($this, 'Swift_Mime_Attachment::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('mime.attachment')
      );
    
    $this->setBody($data);
    $this->setFilename($filename);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  /**
   * Create a new Attachment.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   * @return Swift_Mime_Attachment
   */
  public static function newInstance($data = null, $filename = null,
    $contentType = null)
  {
    return new self($data, $filename, $contentType);
  }
  
  /**
   * Create a new Attachment from a filesystem path.
   * @param string $path
   * @param string $contentType optional
   * @return Swift_Mime_Attachment
   */
  public static function fromPath($path, $contentType = null)
  {
    return self::newInstance()->setFile(
      new Swift_ByteStream_FileByteStream($path),
      $contentType
      );
  }
  
}
 /* .tmp\flat\1\2\3\4\EmbeddedFile.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\4\EmbeddedFile.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Attachment.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/KeyCache.php';
//@require

/**
 * An embedded file, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_EmbeddedFile extends Swift_Mime_Attachment
{
  
  /**
   * Creates a new Attachment with $headers and $encoder.
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param array $mimeTypes optional
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache,
    $mimeTypes = array())
  {
    parent::__construct($headers, $encoder, $cache, $mimeTypes);
    $this->setDisposition('inline');
    $this->setId($this->getId());
  }
  
  /**
   * Get the nesting level of this EmbeddedFile.
   * Returns {@link LEVEL_RELATED}.
   * @return int
   */
  public function getNestingLevel()
  {
    return self::LEVEL_RELATED;
  }
  
}
 /* .tmp\flat\1\2\3\4\MimePart.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\4\MimePart.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/MimePart.php';
//@require 'Swift/DependencyContainer.php';

/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_MimePart extends Swift_Mime_MimePart
{
  
  /**
   * Create a new MimePart.
   * Details may be optionally passed into the constructor.
   * @param string $body
   * @param string $contentType
   * @param string $charset
   */
  public function __construct($body = null, $contentType = null,
    $charset = null)
  {
    call_user_func_array(
      array($this, 'Swift_Mime_MimePart::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('mime.part')
      );
    
    if (!isset($charset))
    {
      $charset = Swift_DependencyContainer::getInstance()
        ->lookup('properties.charset');
    }
    $this->setBody($body);
    $this->setCharset($charset);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  /**
   * Create a new MimePart.
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @return Swift_Mime_MimePart
   */
  public static function newInstance($body = null, $contentType = null,
    $charset = null)
  {
    return new self($body, $contentType, $charset);
  }
  
}
 /* .tmp\flat\1\2\3\4\SimpleMessage.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\4\SimpleMessage.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Message.php';
//@require 'Swift/Mime/MimePart.php';
//@require 'Swift/Mime/MimeEntity.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/Mime/ContentEncoder.php';

/**
 * The default email message class.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleMessage extends Swift_Mime_MimePart
  implements Swift_Mime_Message
{
  
  /**
   * Create a new SimpleMessage with $headers, $encoder and $cache.
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param string $charset
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, $charset = null)
  {
    parent::__construct($headers, $encoder, $cache, $charset);
    $this->getHeaders()->defineOrdering(array(
      'Return-Path',
      'Sender',
      'Message-ID',
      'Date',
      'Subject',
      'From',
      'Reply-To',
      'To',
      'Cc',
      'Bcc',
      'MIME-Version',
      'Content-Type',
      'Content-Transfer-Encoding'
      ));
    $this->getHeaders()->setAlwaysDisplayed(
      array('Date', 'Message-ID', 'From')
      );
    $this->getHeaders()->addTextHeader('MIME-Version', '1.0');
    $this->setDate(time());
    $this->setId($this->getId());
    $this->getHeaders()->addMailboxHeader('From');
  }
  
  /**
   * Always returns {@link LEVEL_TOP} for a message instance.
   * @return int
   */
  public function getNestingLevel()
  {
    return self::LEVEL_TOP;
  }
  
  /**
   * Set the subject of this message.
   * @param string $subject
   */
  public function setSubject($subject)
  {
    if (!$this->_setHeaderFieldModel('Subject', $subject))
    {
      $this->getHeaders()->addTextHeader('Subject', $subject);
    }
    return $this;
  }
  
  /**
   * Get the subject of this message.
   * @return string
   */
  public function getSubject()
  {
    return $this->_getHeaderFieldModel('Subject');
  }
  
  /**
   * Set the date at which this message was created.
   * @param int $date
   */
  public function setDate($date)
  {
    if (!$this->_setHeaderFieldModel('Date', $date))
    {
      $this->getHeaders()->addDateHeader('Date', $date);
    }
    return $this;
  }
  
  /**
   * Get the date at which this message was created.
   * @return int
   */
  public function getDate()
  {
    return $this->_getHeaderFieldModel('Date');
  }
  
  /**
   * Set the return-path (the bounce address) of this message.
   * @param string $address
   */
  public function setReturnPath($address)
  {
    if (!$this->_setHeaderFieldModel('Return-Path', $address))
    {
      $this->getHeaders()->addPathHeader('Return-Path', $address);
    }
    return $this;
  }
  
  /**
   * Get the return-path (bounce address) of this message.
   * @return string
   */
  public function getReturnPath()
  {
    return $this->_getHeaderFieldModel('Return-Path');
  }
  
  /**
   * Set the sender of this message.
   * This does not override the From field, but it has a higher significance.
   * @param string $sender
   * @param string $name optional
   */
  public function setSender($address, $name = null)
  {
    if (!is_array($address) && isset($name))
    {
      $address = array($address => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Sender', (array) $address))
    {
      $this->getHeaders()->addMailboxHeader('Sender', (array) $address);
    }
    return $this;
  }
  
  /**
   * Get the sender of this message.
   * @return string
   */
  public function getSender()
  {
    return $this->_getHeaderFieldModel('Sender');
  }
  
  /**
   * Add a From: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addFrom($address, $name = null)
  {
    $current = $this->getFrom();
    $current[$address] = $name;
    return $this->setFrom($current);
  }
  
  /**
   * Set the from address of this message.
   * 
   * You may pass an array of addresses if this message is from multiple people.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   * 
   * @param string $addresses
   * @param string $name optional
   */
  public function setFrom($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('From', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('From', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the from address of this message.
   * 
   * @return string
   */
  public function getFrom()
  {
    return $this->_getHeaderFieldModel('From');
  }
  
  /**
   * Add a Reply-To: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addReplyTo($address, $name = null)
  {
    $current = $this->getReplyTo();
    $current[$address] = $name;
    return $this->setReplyTo($current);
  }
  
  /**
   * Set the reply-to address of this message.
   * 
   * You may pass an array of addresses if replies will go to multiple people.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   *
   * @param string $addresses
   * @param string $name optional
   */
  public function setReplyTo($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Reply-To', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Reply-To', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the reply-to address of this message.
   * 
   * @return string
   */
  public function getReplyTo()
  {
    return $this->_getHeaderFieldModel('Reply-To');
  }
  
  /**
   * Add a To: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addTo($address, $name = null)
  {
    $current = $this->getTo();
    $current[$address] = $name;
    return $this->setTo($current);
  }
  
  /**
   * Set the to addresses of this message.
   * 
   * If multiple recipients will receive the message and array should be used.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   * 
   * @param array $addresses
   * @param string $name optional
   */
  public function setTo($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('To', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('To', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the To addresses of this message.
   * 
   * @return array
   */
  public function getTo()
  {
    return $this->_getHeaderFieldModel('To');
  }
  
  /**
   * Add a Cc: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addCc($address, $name = null)
  {
    $current = $this->getCc();
    $current[$address] = $name;
    return $this->setCc($current);
  }
  
  /**
   * Set the Cc addresses of this message.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   *
   * @param array $addresses
   * @param string $name optional
   */
  public function setCc($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Cc', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Cc', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the Cc address of this message.
   * 
   * @return array
   */
  public function getCc()
  {
    return $this->_getHeaderFieldModel('Cc');
  }
  
  /**
   * Add a Bcc: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addBcc($address, $name = null)
  {
    $current = $this->getBcc();
    $current[$address] = $name;
    return $this->setBcc($current);
  }
  
  /**
   * Set the Bcc addresses of this message.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   * 
   * @param array $addresses
   * @param string $name optional
   */
  public function setBcc($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Bcc', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Bcc', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the Bcc addresses of this message.
   * 
   * @return array
   */
  public function getBcc()
  {
    return $this->_getHeaderFieldModel('Bcc');
  }
  
  /**
   * Set the priority of this message.
   * The value is an integer where 1 is the highest priority and 5 is the lowest.
   * @param int $priority
   */
  public function setPriority($priority)
  {
    $priorityMap = array(
      1 => 'Highest',
      2 => 'High',
      3 => 'Normal',
      4 => 'Low',
      5 => 'Lowest'
      );
    $pMapKeys = array_keys($priorityMap);
    if ($priority > max($pMapKeys))
    {
      $priority = max($pMapKeys);
    }
    elseif ($priority < min($pMapKeys))
    {
      $priority = min($pMapKeys);
    }
    if (!$this->_setHeaderFieldModel('X-Priority',
      sprintf('%d (%s)', $priority, $priorityMap[$priority])))
    {
      $this->getHeaders()->addTextHeader('X-Priority',
        sprintf('%d (%s)', $priority, $priorityMap[$priority]));
    }
    return $this;
  }
  
  /**
   * Get the priority of this message.
   * The returned value is an integer where 1 is the highest priority and 5
   * is the lowest.
   * @return int
   */
  public function getPriority()
  {
    list($priority) = sscanf($this->_getHeaderFieldModel('X-Priority'),
      '%[1-5]'
      );
    return isset($priority) ? $priority : 3;
  }
  
  /**
   * Ask for a delivery receipt from the recipient to be sent to $addresses
   * @param array $addresses
   */
  public function setReadReceiptTo($addresses)
  {
    if (!$this->_setHeaderFieldModel('Disposition-Notification-To', $addresses))
    {
      $this->getHeaders()
        ->addMailboxHeader('Disposition-Notification-To', $addresses);
    }
    return $this;
  }
  
  /**
   * Get the addresses to which a read-receipt will be sent.
   * @return string
   */
  public function getReadReceiptTo()
  {
    return $this->_getHeaderFieldModel('Disposition-Notification-To');
  }
  
  /**
   * Attach a {@link Swift_Mime_MimeEntity} such as an Attachment or MimePart.
   * @param Swift_Mime_MimeEntity $entity
   */
  public function attach(Swift_Mime_MimeEntity $entity)
  {
    $this->setChildren(array_merge($this->getChildren(), array($entity)));
    return $this;
  }
  
  /**
   * Remove an already attached entity.
   * @param Swift_Mime_MimeEntity $entity
   */
  public function detach(Swift_Mime_MimeEntity $entity)
  {
    $newChildren = array();
    foreach ($this->getChildren() as $child)
    {
      if ($entity !== $child)
      {
        $newChildren[] = $child;
      }
    }
    $this->setChildren($newChildren);
    return $this;
  }
  
  /**
   * Attach a {@link Swift_Mime_MimeEntity} and return it's CID source.
   * This method should be used when embedding images or other data in a message.
   * @param Swift_Mime_MimeEntity $entity
   * @return string
   */
  public function embed(Swift_Mime_MimeEntity $entity)
  {
    $this->attach($entity);
    return 'cid:' . $entity->getId();
  }
  
  /**
   * Get this message as a complete string.
   * @return string
   */
  public function toString()
  {
    if (count($children = $this->getChildren()) > 0 && $this->getBody() != '')
    {
      $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
      $string = parent::toString();
      $this->setChildren($children);
    }
    else
    {
      $string = parent::toString();
    }
    return $string;
  }
  
  /**
   * Returns a string representation of this object.
   *
   * @return string
   *
   * @see toString()
   */
  public function __toString()
  {
    return $this->toString();
  }
  
  /**
   * Write this message to a {@link Swift_InputByteStream}.
   * @param Swift_InputByteStream $is
   */
  public function toByteStream(Swift_InputByteStream $is)
  {
    if (count($children = $this->getChildren()) > 0 && $this->getBody() != '')
    {
      $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
      parent::toByteStream($is);
      $this->setChildren($children);
    }
    else
    {
      parent::toByteStream($is);
    }
  }
  
  // -- Protected methods
  
  /** @see Swift_Mime_SimpleMimeEntity::_getIdField() */
  protected function _getIdField()
  {
    return 'Message-ID';
  }
  
  // -- Private methods
  
  /** Turn the body of this message into a child of itself if needed */
  private function _becomeMimePart()
  {
    $part = new parent($this->getHeaders()->newInstance(), $this->getEncoder(),
      $this->_getCache(), $this->_userCharset
      );
    $part->setContentType($this->_userContentType);
    $part->setBody($this->getBody());
    $part->setFormat($this->_userFormat);
    $part->setDelSp($this->_userDelSp);
    $part->_setNestingLevel($this->_getTopNestingLevel());
    return $part;
  }
  
  /** Get the highest nesting level nested inside this message */
  private function _getTopNestingLevel()
  {
    $highestLevel = $this->getNestingLevel();
    foreach ($this->getChildren() as $child)
    {
      $childLevel = $child->getNestingLevel();
      if ($highestLevel < $childLevel)
      {
        $highestLevel = $childLevel;
      }
    }
    return $highestLevel;
  }
  
}
 /* .tmp\flat\1\2\3\4\5\EmbeddedFile.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\4\5\EmbeddedFile.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/Attachment.php';
//@require 'Swift/DependencyContainer.php';
//@require 'Swift/ByteStream/FileByteStream.php';

/**
 * An embedded file, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_EmbeddedFile extends Swift_Mime_EmbeddedFile
{
  
  /**
   * Create a new EmbeddedFile.
   * Details may be optionally provided to the constructor.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   */
  public function __construct($data = null, $filename = null,
    $contentType = null)
  {
    call_user_func_array(
      array($this, 'Swift_Mime_EmbeddedFile::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('mime.embeddedfile')
      );
    
    $this->setBody($data);
    $this->setFilename($filename);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  /**
   * Create a new EmbeddedFile.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   * @return Swift_Mime_EmbeddedFile
   */
  public static function newInstance($data = null, $filename = null,
    $contentType = null)
  {
    return new self($data, $filename, $contentType);
  }
  
  /**
   * Create a new EmbeddedFile from a filesystem path.
   * @param string $path
   * @return Swift_Mime_EmbeddedFile
   */
  public static function fromPath($path)
  {
    return self::newInstance()->setFile(
      new Swift_ByteStream_FileByteStream($path)
      );
  }
  
}
 /* .tmp\flat\1\2\3\4\5\Message.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\4\5\Message.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Mime/SimpleMessage.php';
//@require 'Swift/MimePart.php';
//@require 'Swift/DependencyContainer.php';

/**
 * The Message class for building emails.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Message extends Swift_Mime_SimpleMessage
{
  
  /**
   * Create a new Message.
   * Details may be optionally passed into the constructor.
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   */
  public function __construct($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    call_user_func_array(
      array($this, 'Swift_Mime_SimpleMessage::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('mime.message')
      );
    
    if (!isset($charset))
    {
      $charset = Swift_DependencyContainer::getInstance()
        ->lookup('properties.charset');
    }
    $this->setSubject($subject);
    $this->setBody($body);
    $this->setCharset($charset);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  /**
   * Create a new Message.
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @return Swift_Mime_Message
   */
  public static function newInstance($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    return new self($subject, $body, $contentType, $charset);
  }
  
  /**
   * Add a MimePart to this Message.
   * @param string|Swift_OutputByteStream $body
   * @param string $contentType
   * @param string $charset
   */
  public function addPart($body, $contentType = null, $charset = null)
  {
    return $this->attach(Swift_MimePart::newInstance(
      $body, $contentType, $charset
      ));
  }
  
}
 /* .tmp\flat\1\2\3\4\5\6\Image.php */ ?>
<?php /* zenmagick/lib/core/external/swift-4.0.6.packed.php.prep\\1\2\3\4\5\6\Image.php */ ?>
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//@require 'Swift/Image.php';
//@require 'Swift/ByteStream/FileByteStream.php';

/**
 * An image, embedded in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Image extends Swift_EmbeddedFile
{
  
  /**
   * Create a new EmbeddedFile.
   * Details may be optionally provided to the constructor.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   */
  public function __construct($data = null, $filename = null,
    $contentType = null)
  {
    parent::__construct($data, $filename, $contentType);
  }
  
  /**
   * Create a new Image.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   * @return Swift_Mime_EmbeddedFile
   */
  public static function newInstance($data = null, $filename = null,
    $contentType = null)
  {
    return new self($data, $filename, $contentType);
  }
  
  /**
   * Create a new Image from a filesystem path.
   * @param string $path
   * @return Swift_Mime_EmbeddedFile
   */
  public static function fromPath($path)
  {
    $image = self::newInstance()->setFile(
      new Swift_ByteStream_FileByteStream($path)
      );
    return $image;
  }
  
}
?>