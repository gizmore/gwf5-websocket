<?php
final class GWS_Logout extends GWS_Command
{
    public function execute(GWS_Message $msg)
    {
        $session = GWF_Session::instance();
        method('Login', 'Logout')->execute();
        GWF_User::$CURRENT = GWF_User::ghost();
        $user = GWF_User::current();
        $msg->replyBinary($msg->cmd(), $this->userToBinary($user));
    }
}

GWS_Commands::register(0x0104, new GWS_Logout());
