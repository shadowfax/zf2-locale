<?php
/**
 * Copyright (c) 2012 Soflomo http://soflomo.com.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     SlmLocale
 * @subpackage  Strategy
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2012 Soflomo http://soflomo.com.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://ensemble.github.com
 */

namespace SlmLocale\Strategy;

use SlmLocale\LocaleEvent;
use Zend\Http\Header\Cookie;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request as HttpRequest;

class CookieStrategy extends AbstractStrategy
{
    const DEFAULT_COOKIE_NAME = 'slm_locale';

    protected $cookieName = self::DEFAULT_COOKIE_NAME;

    protected $cookieExpires = null;

    protected $cookiePath = null;

    protected $cookieDomain = null;

    protected $cookieSecure = null;

    protected $cookieHttponly = null;

    protected $cookieMaxAge = null;

    protected $cookieVersion = null;

    public function setOptions(array $options = array())
    {
    	if (isset($options['cookie'])) {
    		$cookieConfig = $options['cookie'];

    		if (isset($cookieConfig['name'])) {
    			$this->cookieName = $cookieConfig['name'];
    		}
    		if (isset($cookieConfig['expires'])) {
    			$this->cookiePath = $cookieConfig['expires'];
    		}
    		if (isset($cookieConfig['path'])) {
    			$this->cookiePath = $cookieConfig['path'];
    		}
    		if (isset($cookieConfig['domain'])) {
    			$this->cookieDomain = $cookieConfig['domain'];
    		}
    		if (isset($cookieConfig['secure'])) {
    			$this->cookieSecure = $cookieConfig['secure'];
    		}
    		if (isset($cookieConfig['httponly'])) {
    			$this->cookieHttponly = $cookieConfig['httponly'];
    		}
    		if (isset($cookieConfig['maxAge'])) {
    			$this->cookieMaxAge = $cookieConfig['maxAge'];
    		}
    		if (isset($cookieConfig['version'])) {
    			$this->cookieVersion = $cookieConfig['version'];
    		}
    	}
    }

    public function detect(LocaleEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof HttpRequest) {
            return;
        }
        if (!$event->hasSupported()) {
            return;
        }

        $cookie = $request->getCookie();
        if (!$cookie || !$cookie->offsetExists($this->cookieName)) {
            return;
        }

        $locale    = $cookie->offsetGet($this->cookieName);
        $supported = $event->getSupported();

        if (in_array($locale, $supported)) {
            return $locale;
        }
    }

    public function found(LocaleEvent $event)
    {
        $locale   = $event->getLocale();
        $request  = $event->getRequest();
        
        if (!$request instanceof HttpRequest) {
            return;
        }
        
        $cookie   = $request->getCookie();

        // Omit Set-Cookie header when cookie is present
        if ($cookie instanceof Cookie
            && $cookie->offsetExists($this->cookieName)
            && $locale === $cookie->offsetGet($this->cookieName)
        ) {
            return;
        }

        $response = $event->getResponse();
        $cookies  = $response->getCookie();

        $setCookie = new SetCookie(
        	$this->cookieName, $locale,
        	$this->cookieExpires, $this->cookiePath,
        	$this->cookieDomain, $this->cookieSecure,
        	$this->cookieHttponly, $this->cookieMaxAge,
        	$this->cookieVersion
        );
        $response->getHeaders()->addHeader($setCookie);
    }
}