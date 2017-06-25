<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

include 'GWS_Message.php';

final class GWS_Server implements MessageComponentInterface
{
	private $gws;
	private $server;
	
	/**
	 * @var GWS_Commands
	 */
	private $handler;
	private $allowGuests;
// 	private $consoleLog;
	
	public function mainloop($timerInterval=0)
	{
		GWF_Log::logMessage("GWS_Server::mainloop()");
		if ($timerInterval > 0)
		{
			$this->server->loop->addPeriodicTimer($timerInterval, array($this->handler, 'timer'));
		}
		$this->server->run();
	}
	
	###############
	### Ratchet ###
	###############
	public function onOpen(ConnectionInterface $conn)
	{
		GWF_Log::logCron(sprintf("GWS_Server::onOpen()"));
	}

	public function onMessage(ConnectionInterface $from, $data)
	{
		printf("%s >> %s\n", $from->user() ? $from->user()->displayName() : '???', $data);
		$message = new GWS_Message($data, $from);
		$message->readTextCmd();
		if ($from->user())
		{
			try {
				$this->handler->executeMessage($message);
			}
			catch (Exception $e) {
				$message->replyErrorMessage($message->cmd(), $e->getMessage());
			}
		}
		else
		{
			$message->replyError(0x0002);
		}
	}
	
	public function onBinaryMessage(ConnectionInterface $from, $data)
	{
		printf("%s >> BIN\n", $from->user() ? $from->user()->displayName() : '???');
		echo GWS_Message::hexdump($data);
		$message = new GWS_Message($data, $from);
		$message->readCmd();
		if (!$from->user())
		{
			$this->onAuthBinary($message);
		}
		else
		{
			try {
				$this->handler->executeMessage($message);
			}
			catch (Exception $e) {
				$message->replyErrorMessage($message->cmd(), $e->getMessage());
			}
		}
	}
	
	public function onAuthBinary(GWS_Message $message)
	{
		if (!$message->cmd() === 0x0001)
		{
			$message->replyError(0x0001);
		}
		elseif (!$cookie = $message->readString())
		{
			$message->replyError(0x0002);
		}
		elseif (!GWF_Session::reload($cookie))
		{
			$message->replyError(0x0003);
		}
		elseif (!($user = GWF_User::current()))
		{
			$message->replyError(0x0004);
		}
		else
		{
			$message->conn()->setUser($user);
			$user->tempSet('ws', $message->conn());
			GWS_Global::addUser($user);
			GWF_Session::commit();
			$message->replyText('AUTH', json_encode($user->getVars(['user_name', 'user_guest_name', 'user_id', 'user_credits'])));
			$this->handler->connect($user);
		}
	}
	
	public function onClose(ConnectionInterface $conn)
	{
		GWF_Log::logCron(sprintf("GWS_Server::onClose()"));
		if ($user = $conn->user())
		{
			$conn->setUser(false);
			GWS_Global::removeUser($user);
			$this->handler->disconnect($user);
		}
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		GWF_Log::logCron(sprintf("GWS_Server::onError()"));
	}
	
	############
	### Init ###
	############
	public function initGWSServer($handler, Module_Websocket $gws)
	{
		$this->handler = $handler;
		$this->gws = $gws;
		$port = $gws->cfgPort();
		GWF_Log::logCron("GWS_Server::initGWSServer() Port $port");
		$this->allowGuests = $gws->cfgAllowGuests();
// 		$this->consoleLog = GWS_Global::$LOGGING = $gws->cfgConsoleLogging();
		$this->server = IoServer::factory(new HttpServer(new WsServer($this)), $port, $this->socketOptions());
		$this->handler->init();
		$_REQUEST['fmt'] = 'ws';
		$this->registerCommands();
		return true;
	}
	
	private function registerCommands()
	{
		foreach (GWF5::instance()->getActiveModules() as $module)
		{
			GWF_Filewalker::traverse($module->filePath('websocket'), [$this, 'registerModuleCommands']);
		}
	}
	
	public function registerModuleCommands(string $entry, string $path)
	{
		include $path;
	}
	
	private function socketOptions()
	{
// 		$pemCert = trim($this->gws->cfgWebsocketCert());
// 		if (empty($pemCert))
		{
			return array();
		}
// 		else
// 		{
// 			return array(
// 				'ssl' => array(
// 					'local_cert' => $pemCert,
// 				),
// 			);
// 		}
	}
}
