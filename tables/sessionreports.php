<?php
    defined( '_JEXEC' ) or die( 'Restricted Acces' ); 
    
    class TableSessionreports extends JTable {
        
        var $id                 = NULL; 
        var $hotel_id           = NULL; 
        var $generated_on       = NULL; 
        var $current_count      = NULL;
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__geoxity_reports_session', 'id', $database );
        }
    }
?>
