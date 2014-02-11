<?php
    defined( '_JEXEC' ) or die( 'Restricted Acces' ); 
    
    class TableHotelguideprofile extends JTable {
        
        var $id                                 = NULL; 
        var $user_id                            = NULL; 
        var $title                              = NULL; 
        var $alias                              = NULL; 
        var $intro                              = NULL; 
        var $facilities                         = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__hg_hotelitems', 'id', $database );
        }
    }
?>
