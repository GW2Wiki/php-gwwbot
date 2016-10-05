<?php
/**
 *
 * @filesource   Config.php
 * @created      03.10.2016
 * @package      GW2Wiki\GWWBot
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBot;

/**
 * Class Config
 */
class Config{

	public $host;
	public $port;
	public $user;
	public $pass;
	public $nick;
	public $channels;
	public $adminpass;
	public $owner;
	public $googleapi;

	public $prefix = '!';
	public $logfile = 'smileybot.log'; // no whitespace please.

}
