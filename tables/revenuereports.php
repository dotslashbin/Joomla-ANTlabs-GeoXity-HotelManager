<?php 
    defined( '_JEXEC' ) or die( 'Restricted Acces' ); 
    
    class TableRevenuereports extends JTable {
        
        var $id                 = NULL; 
        var $hotel_id           = NULL; 
        var $account_user_id    = NULL; 
        var $date_created       = NULL;
        var $plan               = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__geoxity_reports_revenue', 'id', $database );
        }
    }
?>