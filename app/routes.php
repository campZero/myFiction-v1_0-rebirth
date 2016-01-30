<?php
/* --------------------
	Common routes
-------------------- */

$fw->route( 'GET /', 'Controller\Page->getMain' );

$fw->route(
  [ 'GET /?/redirect/@b/@c', 'GET /redirect/@a/@b', ],
  'Controller\Redirect->filter' );

$fw->route(
  [ 'GET /story', 'GET /story/@action', 'GET /story/@action/@id', ],
  'Controller\Story->index' );

$fw->route(
  [ 'GET /story/search', 'GET /story/search/*', ],
  'Controller\Story->search' );

$fw->route(
  [ 'GET /authors', 'GET /authors/*', ],
  'Controller\Authors->index' );

$fw->route( 'GET /shoutbox/@action/@sub', 'Controller\Blocks->shoutbox' );

// Ajax routes
$fw->route( 'GET /blocks/calendar/* [ajax]', 'Controller\Blocks->calendar' );

if (\Controller\Auth::isLoggedIn())
{
	/* --------------------
		Member routes
	-------------------- */
	$fw->route('GET|POST /login', function($fw) { $fw->reroute('/', false); } );

	$fw->route(
		[ 'GET /logout', 'GET /logout/*' ],
		'Controller\Auth->logout' );

	$fw->route( 'GET|POST /panel', 'Controller\Panel->main' );

	$fw->route(
		[ 'GET|POST /userCP', 'GET|POST /userCP/*' ],
		'Controller\UserCP->index' );
	
	$fw->route(
		[ 'GET|POST /userCP/messaging', 'GET|POST /userCP/messaging/*' ],
		'Controller\UserCP->messaging' );
	
	// Ajax routes
	$fw->route( 'GET /userCP/@module/* [ajax]', 'Controller\UserCP->ajax' );

	if ( $_SESSION['groups'] & 64 )
	{
		/* --------------------
			Mod/Admin routes
		-------------------- */
		$fw->route(
			[ 'GET|POST /adminCP', 'GET|POST /adminCP/*' ],
			'Controller\AdminCP->index' );

		
	}

	if ( $_SESSION['groups'] & 128 )
	{
		/* --------------------
			Admin only routes
		-------------------- */
		$fw->route(
			[ 'GET /adminCP/settings', 'GET|POST /adminCP/settings/@module' ],
			'Controller\AdminCP->settings' );

			$fw->route(
			[ 'POST /adminCP/settings/@module' ], //, 'POST /adminCP/settings/save/*' ],
			'Controller\AdminCP->settingsSave' );

		
	}
}
else
{
	/* --------------------
		Guest routes
	-------------------- */

	$fw->route( 'GET|POST /forgotpw', 'Controller\Auth->forgotpw' );

	$fw->route( 'GET|POST /register', 'Controller\Auth->register' );

	$fw->route(
		[ 'GET|POST /logout', 'GET|POST /logout' ],
		function($fw) { $fw->reroute('/', false); } );

	$fw->route(
		[
			'GET|POST /userCP',
			'GET|POST /userCP/*',
			'GET|POST /adminCP',
			'GET|POST /adminCP/*',
			'GET|POST /login'
		],
		'Controller\Auth->login' );
}