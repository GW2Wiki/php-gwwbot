<?php
/**
 *
 * @filesource   bootstrap.php
 * @created      02.10.2016
 * @package      GW2Wiki\GWWBot
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBot;

date_default_timezone_set('UTC');
ini_set('zlib.output_compression', 0);

/**
 * Checks whether the script is running in CLI mode.
 */
if(!function_exists('is_cli')){
	function is_cli(){
		return !isset($_SERVER['SERVER_SOFTWARE']) && (PHP_SAPI === 'cli' || (is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0));
	}
}
