<?php
include "GWS_Form.php";
/**
 * Call GWF_MethodForm via websockets.
 * @author gizmore
 * @since 5.0
 * @version 5.0
 */
abstract class GWS_CommandForm extends GWS_Command
{
	/**
	 * @return GWF_MethodForm
	 */
	public abstract function getMethod();
	
	public function postExecute(GWS_Message $msg, GWF_Form $form, GWF_Response $response)
	{
		if ($response->isError())
		{
			$msg->replyErrorMessage($msg->cmd(), json_encode($response->getHTML()));
		}
		else
		{
			$this->replySuccess($msg, $form, $response);
		}
	}
	
	public function replySuccess(GWS_Message $msg, GWF_Form $form, GWF_Response $response)
	{
		$msg->replyBinary($msg->cmd());
	}
	
	public function execute(GWS_Message $msg)
	{
		$_POST = []; $_REQUEST = []; $_FILES = [];
		$method = $this->getMethod();
		$form = GWS_Form::bindMethod($method, $msg);
		$this->selectSubmit($form);
		$this->removeCSRF($form);
		$response = $method->renderForm();
		$this->postExecute($msg, $form, $response);
	}
	
	/**
	 * @param GWF_Form $form
	 * @return GDO_Submit[]
	 */
	public function getSubmits(GWF_Form $form)
	{
		$submits = [];
		foreach ($form->getFields() as $field)
		{
			if ($field instanceof GDO_Submit)
			{
				$submits[] = $field;
			}
		}
		return $submits;
	}
	
	public function removeCSRF(GWF_Form $form)
	{
		$form->removeField('xsrf');
	}
	
	public function selectSubmit(GWF_Form $form)
	{
		$this->selectSubmitNum($form, 0);
	}
	
	public function selectSubmitNum(GWF_Form $form, int $num)
	{	
		$submits = $this->getSubmits($form);
		if ($submit = @$submits[$num])
		{
			$name = $submit->name;
			$_REQUEST[$name] = $_POST[$name] = $name;
		}
	}
}
