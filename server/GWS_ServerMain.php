<?php
include 'module/Websocket/gwf4-ratchet/autoload.php';

# Load config
include 'protected/config.php'; # <-- You might need to adjust this path.

# Init GDO and GWF core
include 'inc/GWF5.php';

$gwf5 = new GWF5();
GWF_Log::init(null, 0x28);
$db = new GDODB(GWF_DB_HOST, GWF_DB_USER, GWF_DB_PASS, GWF_DB_NAME);
GDOCache::init();

# Init some config like
$_SERVER['REQUEST_URI'] = 'ws.php';
$_GET['ajax'] = '1';
$_GET['fmt'] = 'json';
$_GET['mo'] = 'Websocket';
$_GET['me'] = 'Run';

# Load
$gwf5->loadModulesCache();

# Create WS
$gws = Module_Websocket::instance();

include 'GWS_Global.php';
include 'GWS_Commands.php';
include $gws->cfgWebsocketProcessorPath();
include 'GWS_Server.php';

$processor = $gws->processorClass();

$server = new GWS_Server();
$server->initGWSServer(new $processor(), $gws);
$server->mainloop($gws->cfgTimer());
