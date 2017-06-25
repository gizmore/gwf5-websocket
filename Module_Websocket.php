<?php
/**
 * Websocket server module.
 * 
 * @author gizmore
 * 
 * @since 4.1
 * @version 5.0
 */
final class Module_Websocket extends GWF_Module
{
	##############
	### Module ###
	##############
	public $module_priority = 45;
	public function onLoadLanguage() { return $this->loadLanguage('lang/websocket'); }

	##############
	### Config ###
	##############
	public function getConfig()
	{
		return array(
			GDO_Checkbox::make('ws_guests')->initial('1'),
			GDO_Int::make('ws_port')->bytes(2)->unsigned()->initial('61221'),
			GDO_Duration::make('ws_timer')->initial('0'),
			GDO_Path::make('ws_processor')->initial($this->defaultProcessorPath()),
			GDO_Url::make('ws_url')->initial('ws://'.GWF_Url::host().':61221'),
		);
	}
	public function cfgUrl() { return $this->getConfigValue('ws_url'); }
	public function cfgPort() { return $this->getConfigValue('ws_port'); }
	public function cfgTimer() { return $this->getConfigValue('ws_timer'); }
	public function cfgWebsocketProcessorPath() { return $this->getConfigValue('ws_processor'); }
	public function cfgAllowGuests() { return $this->getConfigValue('ws_guests'); }

	public function defaultProcessorPath() { return sprintf('%smodule/Websocket/server/GWS_NoCommands.php', GWF_PATH); }
	public function processorClass() { return GWF_String::substrTo(basename($this->cfgWebsocketProcessorPath()), '.'); }

	##########
	### JS ###
	##########
	public function onIncludeScripts()
	{
		$this->addJavascript('js/gwf-websocket-srvc.js');
		$this->addJavascript('js/gwf-ws-navbar-ctrl.js');
		$this->addJavascript('js/gws-message.js');
		GWF_Javascript::addJavascriptInline($this->configJS());
	}
	
	private function configJS()
	{
		return sprintf('window.GWF_CONFIG.ws_url = "%s"; window.GWF_CONFIG.ws_secret = "%s";', $this->cfgUrl(), $this->secret());
	}
	
	public function secret()
	{
		$sess = GWF_Session::instance();
		return $sess ? $sess->cookieContent() : 'resend';
	}
	
	##############
	### Navbar ###
	##############
	public function onRenderFor(GWF_Navbar $navbar)
	{
		$this->templatePHP('sidebars.php', ['navbar' => $navbar]);
	}
}
