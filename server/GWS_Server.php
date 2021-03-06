<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

include 'GWS_Message.php';

final class GWS_Server implements MessageComponentInterface
{
    /**
     * @var GWS_Commands
     */
    private $handler;
    private $allowGuests;
    
    private $gws;
	private $server;
	private $ipc;
	
	public function __construct()
	{
	    if ($this->ipc)
	    {
	        msg_remove_queue($this->ipc);
	    }
	}
	
	public function mainloop($timerInterval=0)
	{
		GWF_Log::logMessage("GWS_Server::mainloop()");
		if ($timerInterval > 0)
		{
			$this->server->loop->addPeriodicTimer($timerInterval/1000.0, [$this->handler, 'timer']);
		}
		if (GWF_IPC)
		{
		    $this->ipc = msg_get_queue(1);
		    $this->server->loop->addPeriodicTimer(0.250, [$this, 'ipcTimer']);
		}
		$this->server->run();
	}
	
	public function ipcTimer()
	{
	    $message = null; $messageType = 0;
	    msg_receive($this->ipc, 1, $messageType, 1000000, $message, true, MSG_IPC_NOWAIT);
	    if ($message)
	    {
	        GWS_Commands::webHook($message);
	    }
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
		    GDO_IP::$CURRENT = $from->getRemoteAddress();
			GWF_User::$CURRENT = $from->user();
			GWF_Session::reloadID($from->user()->tempGet('sess_id'));
			try
			{
				$this->handler->executeMessage($message);
			}
			catch (Exception $e)
			{
			    GWF_Log::logException($e);
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
		printf("%s >> BIN\n", $from->user() ? $from->user()->displayNameLabel() : '???');
		GDO_IP::$CURRENT = $from->getRemoteAddress();
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
			    GWF_User::$CURRENT = $from->user();
// 			    GWF_Session::reloadID($from->user()->tempGet('sess_id'));
			    $this->handler->executeMessage($message);
			}
			catch (Exception $e) {
				GWF_Log::logWebsocket(GWF_Debug::backtraceException($e, false));
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
			$conn = $message->conn();
			$user->tempSet('sess_id', GWF_Session::instance()->getID());
			GWS_Global::addUser($user, $conn);
			
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
		include_once $path;
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
