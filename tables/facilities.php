<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' );
    
    class TableFacilities extends JTable {
        
        var $id                                 = NULL; 
        var $name                               = NULL; 
        var $description                        = NULL; 
        var $published                          = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__hg_facilities', 'id', $database );
        }
    }
?>