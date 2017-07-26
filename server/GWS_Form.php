<?php
/**
 * Fill a GWF_Form with a GWS_Message.
 * 
 * @author gizmore
 * @since 5.0
 * 
 * @see GDOType;
 * @see GWF_Form
 * @see GWS_Message
 */
final class GWS_Form
{
	public static function bindMethod(GWF_MethodForm $method, GWS_Message $msg)
	{
		return self::bind($method->getForm(), $msg);
	}
	
	public static function bind(GWF_Form $form, GWS_Message $msg)
	{
		foreach ($form->getFields() as $gdoType)
		{
			if ($gdoType instanceof GDO_String)
			{
				$gdoType->setGDOValue($msg->readString());
			}
			elseif ($gdoType instanceof GDO_Decimal)
			{
				$gdoType->setGDOValue($msg->readFloat());
			}
			elseif ($gdoType instanceof GDO_Bool)
			{
			    $gdoType->setGDOValue($msg->read8() > 0);
			}
		    elseif ($gdoType instanceof GDO_Int)
			{
				$gdoType->setGDOValue($msg->readN($gdoType->bytes, $gdoType->signed()));
			}
			elseif ($gdoType instanceof GDO_Object)
			{
			    $gdoType->value($msg->read32u());
			}
		}
		return $form;
	}
}
