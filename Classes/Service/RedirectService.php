<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012 Oliver Hader <oliver.hader@typo3.org>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class Tx_IrreWorkspaces_Service_RedirectService implements t3lib_Singleton {
	const NAME = 'Tx_IrreWorkspaces';
	const LIFETIME = 600;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @return Tx_IrreWorkspaces_Service_RedirectService
	 */
	static public function getInstance() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_RedirectService');
	}

	/**
	 * @param boolean $setCookie
	 */
	public function fetch($setCookie = TRUE) {
		$arguments = $this->getArguments();

		if (!empty($arguments['url']) && !empty($arguments['hmac'])) {
			if ($this->setUrl($arguments['url'], $arguments['hmac']) && $setCookie) {
				$this->setCookie(
					$this->getValueForCookie($this->getUrl())
				);
			}
		} elseif ($this->getCookie(self::NAME)) {
			list($hmac, $url) = t3lib_div::trimExplode('::', $this->getCookie(self::NAME), TRUE, 2);
			$this->setUrl($url, $hmac);
		}
	}

	public function handle(array $parameters, t3lib_userAuth $parent) {
		$this->fetch();

		if (!$parent instanceof t3lib_beUserAuth) {
			return FALSE;
		}

		if (is_array($parent->user) && !empty($parent->user['uid'])) {
			if ($this->getUrl()) {
				$this->setCookie('', TRUE);
				t3lib_utility_Http::redirect($this->getUrl());
			}
		}
	}

	public function setUrl($url, $hmac) {
		$result = FALSE;

		if (t3lib_div::hmac($url) === $hmac) {
			$this->url = $url;
			$result = TRUE;
		}

		return $result;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getValueForCookie($url) {
		return t3lib_div::hmac($url) . '::' . $url;
	}

	public function getValueForUrl($url) {
		$arguments = array(
			'url' => $url,
			'hmac' => t3lib_div::hmac($url),
		);

		return t3lib_div::implodeArrayForUrl(self::NAME, $arguments);
	}

	/**
	 * @param string $cookieValue
	 * @param boolean $revoke
	 */
	public function setCookie($cookieValue, $revoke = FALSE) {
		$settings = $GLOBALS['TYPO3_CONF_VARS']['SYS'];

			// Get the domain to be used for the cookie (if any):
		$cookieDomain = $this->getCookieDomain();
			// If no cookie domain is set, use the base path:
		$cookiePath = ($cookieDomain ? '/' : t3lib_div::getIndpEnv('TYPO3_SITE_PATH'));
			// If the cookie lifetime is set, use it:
		$cookieExpire = ($revoke ? $GLOBALS['EXEC_TIME'] - self::LIFETIME : $GLOBALS['EXEC_TIME'] + self::LIFETIME);
			// Use the secure option when the current request is served by a secure connection:
		$cookieSecure = (bool) $settings['cookieSecure'] && t3lib_div::getIndpEnv('TYPO3_SSL');
			// Deliver cookies only via HTTP and prevent possible XSS by JavaScript:
		$cookieHttpOnly = (bool) $settings['cookieHttpOnly'];

		setcookie(
			self::NAME,
			$cookieValue,
			$cookieExpire,
			$cookiePath,
			$cookieDomain,
			$cookieSecure,
			$cookieHttpOnly
		);
	}

	/**
	 * Gets the domain to be used on setting cookies.
	 * The information is taken from the value in $TYPO3_CONF_VARS[SYS][cookieDomain].
	 *
	 * @return string The domain to be used on setting cookies
	 */
	protected function getCookieDomain() {
		$result = '';
		$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
			// If a specific cookie domain is defined for a given TYPO3_MODE,
			// use that domain
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['BE']['cookieDomain'])) {
			$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['BE']['cookieDomain'];
		}

		if ($cookieDomain) {
			if ($cookieDomain{0} == '/') {
				$match = array();
				$matchCnt = @preg_match($cookieDomain, t3lib_div::getIndpEnv('TYPO3_HOST_ONLY'), $match);
				if ($matchCnt === FALSE) {
					t3lib_div::sysLog('The regular expression for the cookie domain (' . $cookieDomain . ') contains errors. The session is not shared across sub-domains.', 'Core', 3);
				} elseif ($matchCnt) {
					$result = $match[0];
				}
			} else {
				$result = $cookieDomain;
			}
		}

		return $result;
	}

	/**
	 * Gets the value of a specified cookie.
	 *
	 * Uses HTTP_COOKIE, if available, to avoid a IE8 bug where multiple
	 * cookies with the same name might be returned if the user accessed
	 * the site without "www." first and switched to "www." later:
	 *   Cookie: fe_typo_user=AAA; fe_typo_user=BBB
	 * In this case PHP will set _COOKIE as the first cookie, when we
	 * would need the last one (which is what this function then returns).
	 *
	 * @param string $cookieName
	 * @return string|NULL
	 */
	protected function getCookie($cookieName) {
		$cookieValue = NULL;

		if (isset($_SERVER['HTTP_COOKIE'])) {
			$cookies = t3lib_div::trimExplode(';', $_SERVER['HTTP_COOKIE']);
			foreach ($cookies as $cookie) {
				list ($name, $value) = t3lib_div::trimExplode('=', $cookie);
				if (trim($name) === $cookieName) {
						// Use the last one
					$cookieValue = urldecode($value);
				}
			}
		} else {
				// Fallback if there is no HTTP_COOKIE, use original method:
			$cookieValue = isset($_COOKIE[$cookieName]) ? stripslashes($_COOKIE[$cookieName]) : '';
		}

		return $cookieValue;
	}

	/**
	 * @return array
	 */
	protected function getArguments() {
		return (array) t3lib_div::_GP(self::NAME);
	}
}

?>