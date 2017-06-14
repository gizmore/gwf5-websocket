<?php
/**
 * Example of a GWS_Commands implementation.
 * 
 * @author gizmore
 * @since 5.0
 * @see GWS_Command
 */
final class GWS_NoCommands extends GWS_Commands
{
	public function init() {}
	public function timer() {}
	public function connect(GWF_User $user) {}
	public function disconnect(GWF_User $user) {}
}
