<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    require_once ( JPATH_COMPONENT.DS.'controller.php' );
    
    $controller = new GeoxityhotelmanagerController(); 
    
    $test = JRequest::getCmd( 'task' ); 
    
    // Perform the Request task
    $controller->execute( JRequest::getCmd( 'task' ) );

    // Redirect if set by the controller
    $controller->redirect();
?>
