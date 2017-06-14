<?php
/**
 * Get cookie and user JSON for external apps.
 * @author gizmore
 * @since 4.0
 * @version 5.0
 */
final class Websocket_GetSecret extends GWF_Method
{
	public function execute()
	{
		header("Access-Control-Allow-Origin: ".$_SERVER['SERVER_NAME']);
		header("Access-Control-Allow-Credentials: true");
		$json = array(
			'user' => Module_GWF::instance()->gwfUserJS(),
			'secret' => Module_Websocket::instance()->secret(),
			'count' => Common::getRequestInt('count', 0),
		);
		return GWF_Response::make($json);
	}
}
