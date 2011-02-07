<?php
/* GENERATED CODE - DO NOT EDIT! */
class ZMSwiftInit {

public static function init() {
// cache_deps.php


Swift_DependencyContainer::getInstance()
  
  -> register('cache')
  -> asAliasOf('cache.array')
  
  -> register('tempdir')
  -> asValue('/tmp')
  
  -> register('cache.null')
  -> asSharedInstanceOf('Swift_KeyCache_NullKeyCache')
  
  -> register('cache.array')
  -> asSharedInstanceOf('Swift_KeyCache_ArrayKeyCache')
  -> withDependencies(array('cache.inputstream'))
  
  -> register('cache.disk')
  -> asSharedInstanceOf('Swift_KeyCache_DiskKeyCache')
  -> withDependencies(array('cache.inputstream', 'tempdir'))
  
  -> register('cache.inputstream')
  -> asNewInstanceOf('Swift_KeyCache_SimpleKeyCacheInputStream')
  
  ;
// mime_deps.php


$swift_mime_types = ZMRuntime::yamlParse(file_get_contents(ZMFileUtils::mkPath(array(zenmagick\base\Runtime::getInstallationPath(), "lib", "core", "external", "mime_types.yaml"))));

Swift_DependencyContainer::getInstance()
    
  -> register('properties.charset')
  -> asValue('utf-8')
  
  -> register('mime.message')
  -> asNewInstanceOf('Swift_Mime_SimpleMessage')
  -> withDependencies(array(
    'mime.headerset',
    'mime.qpcontentencoder',
    'cache',
    'properties.charset'
  ))
  
  -> register('mime.part')
  -> asNewInstanceOf('Swift_Mime_MimePart')
  -> withDependencies(array(
    'mime.headerset',
    'mime.qpcontentencoder',
    'cache',
    'properties.charset'
  ))
  
  -> register('mime.attachment')
  -> asNewInstanceOf('Swift_Mime_Attachment')
  -> withDependencies(array(
    'mime.headerset',
    'mime.base64contentencoder',
    'cache'
  ))
  -> addConstructorValue($swift_mime_types)
  
  -> register('mime.embeddedfile')
  -> asNewInstanceOf('Swift_Mime_EmbeddedFile')
  -> withDependencies(array(
    'mime.headerset',
    'mime.base64contentencoder',
    'cache'
  ))
  -> addConstructorValue($swift_mime_types)
  
  -> register('mime.headerfactory')
  -> asNewInstanceOf('Swift_Mime_SimpleHeaderFactory')
  -> withDependencies(array(
      'mime.qpheaderencoder',
      'mime.rfc2231encoder',
      'properties.charset'
    ))
  
  -> register('mime.headerset')
  -> asNewInstanceOf('Swift_Mime_SimpleHeaderSet')
  -> withDependencies(array('mime.headerfactory', 'properties.charset'))
  
  -> register('mime.qpheaderencoder')
  -> asNewInstanceOf('Swift_Mime_HeaderEncoder_QpHeaderEncoder')
  -> withDependencies(array('mime.charstream'))
  
  -> register('mime.charstream')
  -> asNewInstanceOf('Swift_CharacterStream_NgCharacterStream')
  -> withDependencies(array('mime.characterreaderfactory', 'properties.charset'))
  
  -> register('mime.bytecanonicalizer')
  -> asSharedInstanceOf('Swift_StreamFilters_ByteArrayReplacementFilter')
  -> addConstructorValue(array(array(0x0D, 0x0A), array(0x0D), array(0x0A)))
  -> addConstructorValue(array(array(0x0A), array(0x0A), array(0x0D, 0x0A)))
  
  -> register('mime.characterreaderfactory')
  -> asSharedInstanceOf('Swift_CharacterReaderFactory_SimpleCharacterReaderFactory')
  
  -> register('mime.qpcontentencoder')
  -> asNewInstanceOf('Swift_Mime_ContentEncoder_QpContentEncoder')
  -> withDependencies(array('mime.charstream', 'mime.bytecanonicalizer'))
  
  -> register('mime.7bitcontentencoder')
  -> asNewInstanceOf('Swift_Mime_ContentEncoder_PlainContentEncoder')
  -> addConstructorValue('7bit')
  -> addConstructorValue(true)
  
  -> register('mime.8bitcontentencoder')
  -> asNewInstanceOf('Swift_Mime_ContentEncoder_PlainContentEncoder')
  -> addConstructorValue('8bit')
  -> addConstructorValue(true)
  
  -> register('mime.base64contentencoder')
  -> asSharedInstanceOf('Swift_Mime_ContentEncoder_Base64ContentEncoder')
  
  -> register('mime.rfc2231encoder')
  -> asNewInstanceOf('Swift_Encoder_Rfc2231Encoder')
  -> withDependencies(array('mime.charstream'))
  
  ;
  
// transport_deps.php


Swift_DependencyContainer::getInstance()
  
  -> register('transport.smtp')
  -> asNewInstanceOf('Swift_Transport_EsmtpTransport')
  -> withDependencies(array(
    'transport.buffer',
    array('transport.authhandler'),
    'transport.eventdispatcher'
  ))
  
  -> register('transport.sendmail')
  -> asNewInstanceOf('Swift_Transport_SendmailTransport')
  -> withDependencies(array(
    'transport.buffer',
    'transport.eventdispatcher'
  ))
  
  -> register('transport.mail')
  -> asNewInstanceOf('Swift_Transport_MailTransport')
  -> withDependencies(array('transport.mailinvoker', 'transport.eventdispatcher'))
  
  -> register('transport.loadbalanced')
  -> asNewInstanceOf('Swift_Transport_LoadBalancedTransport')
  
  -> register('transport.failover')
  -> asNewInstanceOf('Swift_Transport_FailoverTransport')
  
  -> register('transport.mailinvoker')
  -> asSharedInstanceOf('Swift_Transport_SimpleMailInvoker')
  
  -> register('transport.buffer')
  -> asNewInstanceOf('Swift_Transport_StreamBuffer')
  -> withDependencies(array('transport.replacementfactory'))
  
  -> register('transport.authhandler')
  -> asNewInstanceOf('Swift_Transport_Esmtp_AuthHandler')
  -> withDependencies(array(
    array(
      'transport.crammd5auth',
      'transport.loginauth',
      'transport.plainauth'
    )
  ))
  
  -> register('transport.crammd5auth')
  -> asNewInstanceOf('Swift_Transport_Esmtp_Auth_CramMd5Authenticator')
  
  -> register('transport.loginauth')
  -> asNewInstanceOf('Swift_Transport_Esmtp_Auth_LoginAuthenticator')
  
  -> register('transport.plainauth')
  -> asNewInstanceOf('Swift_Transport_Esmtp_Auth_PlainAuthenticator')
  
  -> register('transport.eventdispatcher')
  -> asNewInstanceOf('Swift_Events_SimpleEventDispatcher')
  
  -> register('transport.replacementfactory')
  -> asSharedInstanceOf('Swift_StreamFilters_StringReplacementFilterFactory')
  
  ;
}}
