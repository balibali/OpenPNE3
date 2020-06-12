<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opWebRequest class manages web requests.
 *
 * @package    OpenPNE
 * @subpackage request
 * @author     Kousuke Ebihara <ebihara@php.net>
 */
class opWebRequest extends sfWebRequest
{
 /**
  * @see sfWebRequest
  */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array(), $options = array())
  {
    parent::initialize($dispatcher, $parameters, $attributes, $options);

    $this->parameterHolder = new opParameterHolder();
    $this->attributeHolder = new opParameterHolder();

    $this->parameterHolder->add($parameters);
    $this->attributeHolder->add($attributes);

    // POST parameters
    $this->getParameters = get_magic_quotes_gpc() ? sfToolkit::stripslashesDeep($_GET) : $_GET;
    $this->parameterHolder->add($this->getParameters);

    // POST parameters
    $this->postParameters = get_magic_quotes_gpc() ? sfToolkit::stripslashesDeep($_POST) : $_POST;
    $this->parameterHolder->add($this->postParameters);

    if ($this->isMethod(sfWebRequest::POST))
    {
      $this->parameterHolder->remove('sf_method');
    }

    // additional parameters
    $this->requestParameters = $this->parseRequestParameters();
    $this->parameterHolder->add($this->requestParameters);

    $this->fixParameters();
  }

 /**
  * @see sfWebRequest
  */
  public function checkCSRFProtection()
  {
    try
    {
      parent::checkCSRFProtection();
    }
    catch (sfValidatorErrorSchema $e)
    {
      // retry checking for using sfForm (just for BC)
      $form = new sfForm();
      $form->bind($form->isCSRFProtected() ? array($form->getCSRFFieldName() => $this->getParameter($form->getCSRFFieldName())) : array());

      if (!$form->isValid())
      {
        throw $form->getErrorSchema();
      }
    }
  }

  public function getHost()
  {
    $result = parent::getHost();
    if (!$result)
    {
      $result = parse_url(sfConfig::get('op_base_url'), PHP_URL_HOST);
    }

    return $result;
  }

  public function getCurrentQueryString()
  {
    return http_build_query($this->getGetParameters());
  }

  public function getParameter($name, $default = null, $isStripNullbyte = true)
  {
    return $this->parameterHolder->get($name, $default, $isStripNullbyte);
  }

  public function getGetParameters($isStripNullbyte = true)
  {
    if ($isStripNullbyte)
    {
      return opToolkit::stripNullByteDeep(parent::getGetParameters());
    }
    else
    {
      return parent::getGetParameters();
    }
  }

  public function getPostParameters($isStripNullbyte = true)
  {
    if ($isStripNullbyte)
    {
      return opToolkit::stripNullByteDeep(parent::getPostParameters());
    }
    else
    {
      return parent::getPostParameters();
    }
  }

  public function getGetParameter($name, $default = null, $isStripNullbyte = true)
  {
    if ($isStripNullbyte)
    {
      return opToolkit::stripNullByteDeep(parent::getGetParameter($name, $default));
    }
    else
    {
      return parent::getGetParameter($name, $default);
    }
  }

  public function getPostParameter($name, $default = null, $isStripNullbyte = true)
  {
    if ($isStripNullbyte)
    {
      return opToolkit::stripNullByteDeep(parent::getPostParameter($name, $default));
    }
    else
    {
      return parent::getPostParameters($name, $default);
    }
  }

  public function getUrlParameter($name, $default = null, $isStripNullbyte = true)
  {
    if ($isStripNullbyte)
    {
      return opToolkit::stripNullByteDeep(parent::getUrlParameter($name, $default));
    }
    else
    {
      return parent::getUrlParameter($name, $default);
    }
  }

  public function getCurrentUri($isAbsolute = false)
  {
    $uri = $this->getUri();
    if (!$isAbsolute)
    {
      $uri = str_replace($this->getUriPrefix(), '', $uri);
    }

    return $uri;
  }

  public function isSmartphone($checkCookie = true)
  {
    if ($checkCookie && '1' === $this->getCookie('disable_smt', false))
    {
      return false;
    }

    $userAgent = $this->getHttpHeader('User-Agent');

    if (!$userAgent)
    {
      return false;
    }

    return preg_match('/iPhone/', $userAgent) || preg_match('/iPad/', $userAgent) || preg_match('/Android/', $userAgent);
  }
}
