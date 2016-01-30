<?php
namespace View;

class UserCP extends Base
{

	public static function showMenu($menu="")
	{
		\Base::instance()->set('panel_menu', $menu);
		return \Template::instance()->render('usercp/menu.html');
	}

	public static function msgInOutbox($data, $select="inbox")
	{
		if ( $select == "outbox" )
		{
			$select = "Outbox";
			$person_is = "Recipient";
			$date_means = "Sent";
		}
		else
		{
			$select = "Inbox";
			$person_is = "Sender";
			$date_means = "Received";
		}
		$fw = \Base::instance();

		$fw->set('messages', $data);
		$fw->set('WHICH', $select);
		$fw->set('PERSON_IS', $person_is);
		$fw->set('DATE_MEANS', $date_means);

		return \Template::instance()->render('usercp/messaging.inout.html');
	}

	public static function msgRead($data)
	{
		return \Template::instance()->render
														('usercp/messaging.read.html','text/html', 
															[
																"message"	=> $data,
																"forward"		=> ($data['sender_id']==$_SESSION['userID']),
																"BASE"		=> \Base::instance()->get('BASE')
															]
														);
	}

	public static function msgWrite($data)
	{
		\Base::instance()->set('write_data', $data);
		return \Template::instance()->render('usercp/messaging.write.html');
	}

}