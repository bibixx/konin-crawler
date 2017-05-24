<?php
	error_reporting(0);

	function getClasses( $schoolValue, $schoolName ) {
		global $session;

		$page = $session->getPage();
		$form = $page->find( "css", "form#j_idt46" );
		$data = array();
		$citySelect = $form->findById( "j_idt46:citySelect" );
		$citySelect->selectOption( "0948667" );
		$schoolSelect = $form->findById( "j_idt46:schoolSelect" );
		$schoolSelect->selectOption( $schoolValue );

		$form->findButton( "j_idt46:j_idt58" )->click();

		$newPage = $session->getPage();
		$classes = $newPage->findById( "j_idt46:results" )->find( "css", "table" )->findAll( "css", "tr" );
		array_shift( $classes );

		$classArray = array( "id" => $schoolValue, "schoolName" => $schoolName );
		foreach ( $classes as $key => $value ) {
			$tds = $value->findAll( "css", "td" );
			$classArray[] = array(
				"name" => str_replace( $schoolName . " - ", "", $tds[ 0 ]->getText() ),
				"freePlaces" => $tds[ 1 ]->getText(),
				"willingNumber" => $tds[ 2 ]->getText(),
				"firstChoice" => $tds[ 3 ]->getText(),
			);
		}

		return $classArray;
	}

	require_once 'vendor/autoload.php';
	$driver = new \Behat\Mink\Driver\GoutteDriver();

	$session = new \Behat\Mink\Session($driver);

	// start the session
	$session->start();
	$session->visit( "https://konin.edu.com.pl/kandydat/app/statistics.html" );
	$page = $session->getPage();
	$form = $page->find( "css", "form#j_idt46" );
	$data = array();
	$citySelect = $form->findById( "j_idt46:citySelect" );
	$citySelect->selectOption( "0948667" );
	$schoolSelect = $form->findById( "j_idt46:schoolSelect" );

	$allSchools = $schoolSelect->findAll( "css", "option" );
	array_shift( $allSchools );

	foreach ($argv as $arg) {
    $e = explode( "=", $arg );
    if( count( $e ) == 2 ) {
      $_GET[ $e[ 0 ] ] = $e[ 1 ];
		} else {
			$_GET[ $e[ 0 ] ] = 0;
		}
	}

	foreach ( $allSchools as $key => $value ) {
		if ( isset( $_GET[ "name" ] ) || isset( $_GET[ "id" ] ) ) {
			if ( ( isset( $_GET[ "id" ] ) && $_GET[ "id" ] === $value->getAttribute( "value" ) ) || ( isset( $_GET[ "name" ] ) && $_GET[ "name" ] === $value->getText() ) ) {
				$data[] = getClasses( $value->getAttribute( "value" ), $value->getText() );
			}
		} else {
			$data[] = getClasses( $value->getAttribute( "value" ), $value->getText() );
		}
	}

	header( 'Content-Type: application/json' );
	echo json_encode( $data );
?>
