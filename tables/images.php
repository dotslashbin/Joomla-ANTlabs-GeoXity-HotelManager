<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' );
    
    class TableImages extends JTable {
        
        var $id             = NULL; 
        var $filename       = NULL; 
        var $description    = NULL; 
        var $hotel          = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__hg_galleryitems', 'id', $database );
        }
    }
?>
