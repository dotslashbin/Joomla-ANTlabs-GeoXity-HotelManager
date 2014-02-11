<?php
    
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.model' );
    
    JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' ); 
    
    class GeoxityhotelmanagerModelFacilities extends JModel {

        const FACILITIES_TABLE = '#__hg_facilities'; 
        
        public function __construct() {
            
            parent::__construct(); 
        }
        
        public function getFacilities( $IDs = array() ) {
            
            $query = ''; 
            if( !empty( $IDs ) ) {
                $query = 'SELECT * FROM '.self::FACILITIES_TABLE.' WHERE id in ('. implode( ',' , $IDs ) .')'; 
            } else {
                $query = 'SELECT * FROM '.self::FACILITIES_TABLE; 
            }
            
            $database = &JFactory::getDBO(); 
            
            $database->setQuery( $query ); 
            
            $database->query(); 
            
            $facilities = $database->loadObjectList(); 
            
            return $facilities; 
        }
    }
?>
