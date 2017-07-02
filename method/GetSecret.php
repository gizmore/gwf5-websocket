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
		$json = array(
			'user' => Module_GWF::instance()->gwfUserJSON(),
			'secret' => Module_Websocket::instance()->secret(),
			'count' => Common::getRequestInt('count', 0),
		);
		die(json_encode($json));
	}
}
