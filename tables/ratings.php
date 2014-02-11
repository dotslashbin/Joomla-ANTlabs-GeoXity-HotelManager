<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' );
    
    class TableRatings extends JTable {
        
        var $id             = NULL; 
        var $hotel_id       = NULL; 
        var $rated_by       = NULL; 
        var $rating         = NULL; 
        var $comment        = NULL; 
        var $was_read       = NULL; 
        var $date_added     = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__geoxity_hotel_ratings', 'id', $database );
        }
    }
?>
