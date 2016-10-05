<?php
/**
 *
 * @filesource   ircbot.php
 * @created      02.10.2016
 * @package      GW2Wiki\GWWBotCLI
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBotCLI;

require_once __DIR__.'/../vendor/autoload.php';

if(!\GW2Wiki\GWWBot\is_cli()){
	throw new ClientException('CLI mode only');
}

use Dotenv\Dotenv;
use GW2Wiki\GWWBot\Client\ClientException;
use GW2Wiki\GWWBot\Config;
use GW2Wiki\GWWBot\Client\GWWBot;
use GW2Wiki\GWWBot\Language\GWWEnglish;

(new Dotenv(__DIR__.'/../config', '.env'))->load();

$c = new Config;

$c->host      = getenv('IRC_HOST');
$c->port      = getenv('IRC_PORT');
$c->user      = getenv('IRC_USER');
$c->pass      = getenv('IRC_PASS');
$c->nick      = getenv('IRC_NICK');
$c->channels  = getenv('IRC_CHAN');
$c->adminpass = getenv('IRC_ADMINPASS');
$c->owner     = getenv('IRC_OWNER');
$c->googleapi = getenv('GOOGLE_API');

$bot = new GWWBot($c, new GWWEnglish);
$bot->connect();
$bot->run();
