<?php
/**
 * Class ClientAbstract
 *
 * @filesource   ClientAbstract.php
 * @created      02.10.2016
 * @package      GW2Wiki\GWWBot\Client
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBot\Client;

use GW2Wiki\GWWBot\Config;
use GW2Wiki\GWWBot\Language\LanguageInterface;
use GW2Wiki\GWWBot\Response;

/**
 *
 */
abstract class ClientAbstract implements ClientInterface{

	/**
	 * @var \GW2Wiki\GWWBot\Config
	 */
	protected $config;

	/**
	 * @var \GW2Wiki\GWWBot\Language\LanguageInterface
	 */
	protected $lang;

	/**
	 * @var resource
	 */
	protected $socket;

	/**
	 * @var \stdClass
	 */
	protected $data;

	/**
	 * @var array
	 */
	protected $command;

	/**
	 * @var float microtime
	 */
	protected $starttime;

	/**
	 * @var bool
	 */
	protected $auth_sent = false;

	/**
	 * @var bool
	 */
	protected $logstart = false;

	/**
	 * @var bool
	 */
	protected $away = false;

	/**
	 * ClientAbstract constructor.
	 *
	 * @param \GW2Wiki\GWWBot\Config                     $config
	 * @param \GW2Wiki\GWWBot\Language\LanguageInterface $lang
	 */
	public function __construct(Config $config, LanguageInterface $lang){
		$this->starttime        = microtime(true);
		$this->config           = $config;
		$this->lang             = $lang;
		$this->config->channels = explode(',', $config->channels);
	}

	/**
	 *
	 */
	public function connect(){
		$this->socket = @fsockopen($this->config->host, $this->config->port, $errno, $errstr, 5);

		if(!$this->socket){
			$this->log(sprintf($this->lang->connectionError, $errno, $errstr));
		}

		stream_set_timeout($this->socket, 300);
		stream_set_blocking($this->socket, 0);

		$this->log('<> '.sprintf($this->lang->connected, $this->config->host, $this->config->port));

		$this->send('USER '.$this->config->user.' 0 * :...');
		$this->send('NICK '.$this->config->nick);
	}

	/**
	 *
	 */
	public function run(){

		if(!$this->socket){
			throw new ClientException($this->lang->connectionError);
		}

		while(true){

			if(!$this->socket){
				$this->log($this->lang->connectionError);

				exit; // @TODO: reconnect
			}

			$this->autorun();

			while($in = fgets($this->socket)){
				flush();

				if(!empty($in)){
					$this->receive($in);
				}

				unset($in);
			}

			sleep(1);
		}
	}

	/**
	 * @return string
	 */
	protected function uptime(){
		$uptime = microtime(true) - $this->starttime;
		$hours  = floor($uptime / 3600);
		$uptime %= 3600;

		return $hours.':'.str_pad(floor($uptime / 60), 2, '0', STR_PAD_LEFT).':'.str_pad($uptime % 60, 2, '0', STR_PAD_LEFT);
	}

	/**
	 * @param string $line
	 */
	protected function log(string $line){
		$time    = '['.date('c', time()).']'.sprintf('[%10s] ', $this->uptime());
		$logfile = fopen(date('Y-m-d').'-'.$this->config->logfile, 'a');

		if(!$this->logstart){
			$this->logstart = true;

			fwrite($logfile, $time.$this->lang->logstart.PHP_EOL);
		}

		$line = $time.$line.PHP_EOL;

		fwrite($logfile, $line);
		fclose($logfile);

		echo $line;
	}

	/**
	 * @param string $cmd
	 */
	protected function send(string $cmd){
		$this->log('-> '.$cmd);
		fputs($this->socket, $cmd.PHP_EOL);
	}

	/**
	 * @param string $response
	 */
	protected function receive(string $response){
		$this->data = new Response;

		$r = explode(' ', $response, 2);

		$this->data->_raw     = $response;
		$this->data->_command = trim($r[0]);
		$this->data->_body    = trim($r[1]);

		if($this->data->_command === 'PING'){
			$this->log('<- '.trim($response));
			$this->pong();
		}

		$re = '/^:(?P<nick>[^!]*)[!]'
		      .'(?P<ident>[^\s]*)[@]'
		      .'(?P<host>[^\s]*)[\s]'
		      .'(?P<action>[^\s]*)'
		      .'(:?[\s](?P<channel>[^\s]*)(:?[\s](?P<param1>[^\s:]*))?(:?[\s](?P<param2>[^\s:]*))?)?'
		      .'(:?\s[:](?P<message>.*))?'
		      .'$/';

		if(preg_match($re, trim($response), $matches) > 0){
			$this->data->_matches = $matches;

			foreach($matches as $property => $value){
				$value = trim($value);
				if(property_exists($this->data, $property) && !empty($value)){
					$this->data->{$property} = $value;
				}
			}

			// verbose/debug mode
#			$this->log('<- ['.$this->data->action.': <'.$this->data->nick.'> '.$this->data->channel.' : '.$this->data->message.']');

			if(in_array($this->data->action, ['PRIVMSG', 'JOIN', 'KICK', 'MODE', 'QUIT', 'NOTICE', 'PART', 'NICK',], true)){
				$this->log('<- '.call_user_func([$this, strtolower($this->data->action)]));
			}
			else{
				var_dump($this->data);
				$this->log('<- [FIXME] '.trim($response));
			}

		}

	}

	/**
	 *
	 */
	protected function autorun(){
		// @TODO: implement autorun method if needed
	}

	/**
	 *
	 */
	protected function pong(){
		$this->send('PONG '.$this->data->_body);

		if(!$this->auth_sent){

			if(!empty($this->config->user) && !empty($this->config->pass)){
				$this->send('AUTHSERV AUTH '.$this->config->user.' '.$this->config->pass);
			}

			foreach($this->config->channels as $channel){
				$channel = trim($channel);

				$this->send('JOIN '.$channel);
				$this->send('MODE '.$channel);
			}

			$this->auth_sent = true;
		}

	}

	/**
	 * @param     $text
	 * @param int $char
	 *
	 * @return string
	 */
	protected function wrap($text, $char = 1){
		return chr($char).$text.chr($char);
	}

	/**
	 * @param $text
	 *
	 * @return string
	 */
	protected function me($text){
		return $this->wrap('ACTION '.$text);
	}

	/**
	 * @param $channel
	 * @param $text
	 */
	protected function say($channel, $text){
		$this->send('PRIVMSG '.$channel.' :'.strtr($text, [
			'[b]' => "\x02", //bold
			'[c]' => "\x03", //color, followed by number 0-15 (foreground[,background])
#			'[i]' => "\x09", //italic
			'[n]' => "\x0f", //normal/reset
			'[u]' => "\x1f", //underline
#			'[s]' => "\x13", //strike
			'[r]' => "\x16", //reverse
		]));
	}

	/**
	 * @return string|void
	 */
	protected function privmsg(){

		if(substr($this->data->message, 0, 1) === chr(1)){
			return $this->ctcp();

		}
		else if(substr($this->data->message, 0, strlen($this->config->prefix)) === $this->config->prefix){
			return $this->prefixed();
		}
		else{
			return $this->chat();
		}
	}

	/**
	 * @return string|void
	 */
	protected abstract function chat();

	/**
	 *
	 */
	protected function ctcp(){
		$ctcp = trim($this->data->message, chr(1));

		switch(true){
			case substr($ctcp, 0, 4) === 'PING':
				$notice = 'PING '.substr($ctcp, 5);
				break;
			case $ctcp === 'VERSION':
				$notice = 'VERSION '.sprintf($this->lang->version, PHP_VERSION, PHP_OS);
				break;
			case $ctcp === 'TIME':
				$notice = 'TIME '.date('D M d H:i:s Y');
				break;
			case $ctcp === 'CLIENTINFO':
				$notice = 'VERSION PING CLIENTINFO TIME ';
				break;
			default:
				$notice = false;
				break;
		}

		if($notice){
			$this->send('NOTICE '.$this->data->nick.' : '.$this->wrap($notice));
		}

	}

	/**
	 *
	 */
	protected function prefixed(){
		$this->command = explode(' ', $this->data->message, 3);

		$this->command[0] = substr($this->command[0], strlen($this->config->prefix));

		if($this->data->channel === $this->config->nick){
			return isset($this->command[1]) && trim($this->command[1]) === $this->config->adminpass
				? $this->prefix_dm_password()
				: $this->prefix_dm_nopassword();
		}
		else{
			return $this->prefix_channel();
		}

	}

	/**
	 * @return string
	 */
	protected abstract function prefix_dm_password();

	/**
	 * @return string
	 */
	protected abstract function prefix_dm_nopassword();

	/**
	 * @return string
	 */
	protected abstract function prefix_channel();


	/**
	 * @TODO silenced...
	 *
	 * @return string
	 */
	protected function join(){

		if($this->data->nick === $this->config->owner){
#			$this->say($this->data->channel, $this->me(sprintf($this->lang->helloOwner, $this->config->owner, $this->data->channel)));

			// uncomment this if you're brave
#			$this->send('MODE '. $this->data->channel.' +o '. $this->data->nick);
		}
		else if($this->data->nick === $this->config->nick){
#			$this->send('NOTICE '.$this->data->channel.' : '.$this->lang->helloNotice);
#			$this->say($this->data->channel, $this->lang->helloFromSelf);
		}
		else{
			if($this->away){
#				$this->say($this->data->channel, $this->me(sprintf($this->lang->helloOwnerAway, $this->data->nick, $this->data->channel, $this->config->owner)));
			}
			else{
#				$this->say($this->data->channel, $this->me(sprintf($this->lang->helloUser, $this->data->channel, $this->data->nick)));
			}
		}

		return sprintf($this->lang->msg_join, $this->data->nick, $this->data->channel);
	}

	/**
	 * @return string
	 */
	protected function kick(){

		if($this->data->param1 === $this->config->nick){
			$this->send('JOIN '.$this->data->channel);
			$this->send('MODE '.$this->data->channel);
#			$this->say($this->data->channel, $this->me($this->lang->kickRejoin));
		}
		else if($this->data->param1 === $this->config->owner){
			$this->say($this->data->channel, sprintf($this->lang->kickOwner, strtoupper($this->config->owner)));
		}
		else{
			$this->say($this->data->channel, $this->me(sprintf($this->lang->kickTaunt)));
		}

		return sprintf($this->lang->msg_kick, $this->data->param1, $this->data->channel, $this->data->nick);
	}

	/**
	 * @return string
	 */
	protected function mode(){
		return $this->data->nick.' SET MODE '.$this->data->message.($this->data->param1 ? ' ('.$this->data->param1.' -> '.$this->data->param2.')' : '');
	}

	/**
	 * @return string
	 */
	protected function quit(){
		return sprintf($this->lang->msg_quit, $this->data->nick, $this->data->message);
	}

	/**
	 * @return string
	 */
	protected function notice(){
		return sprintf($this->lang->msg_notice, $this->data->nick, $this->data->message);
	}

	/**
	 * @return string
	 */
	protected function part(){
		return sprintf($this->lang->msg_part, $this->data->nick, $this->data->channel);
	}

	/**
	 * @return string
	 */
	public function nick(){
		return sprintf($this->lang->msg_nick, $this->data->nick, str_replace(':', '', $this->data->channel));
	}

}
