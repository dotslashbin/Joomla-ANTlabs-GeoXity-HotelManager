<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' );
    
    class TableGeoxitycontents extends JTable {
        
        var $id                         = NULL; 
        var $profile_id                 = NULL; 
        var $jooomla_content_id         = NULL; 
        var $section                    = NULL; 
        var $title                      = NULL; 
        var $content                    = NULL; 
        var $is_current                 = NULL; 
        var $date_added                 = NULL; 
        var $updated_by                 = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__geoxity_contents', 'id', $database );
        }
    }
?>
