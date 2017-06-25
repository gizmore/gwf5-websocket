<?php $navbar instanceof GWF_Navbar; ?>
<?php
if ($navbar->isLeft())
{
	$navbar->addField(GDO_Template::make()->module(Module_Websocket::instance())->template('ws-connect-bar.php'));
}
