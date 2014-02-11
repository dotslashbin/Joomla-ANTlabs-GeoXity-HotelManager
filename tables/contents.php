<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' );
    
    class TableContents extends JTable {
        
        var $id         = NULL; 
        var $title      = NULL; 
        var $fulltext   = NULL; 
        var $created    = NULL;
        var $created_by = NULL; 
        var $catid      = NULL;    
        var $state      = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__content', 'id', $database );
        }
    }
?>
