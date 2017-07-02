<?php
final class GWS_Register extends GWS_CommandForm
{
	public function getMethod()
	{
		return method('Register', 'Form');
	}
	
	public function replySuccess(GWS_Message $msg, GWF_Form $form, GWF_Response $response)
	{
		GWF_User::$CURRENT = $user = GWF_Session::instance()->getUser();
		GWF_Session::reset();
		$msg->replyBinary($msg->cmd(), $this->userToBinary($user));
	}
}

GWS_Commands::register(0x0102, new GWS_Register());
