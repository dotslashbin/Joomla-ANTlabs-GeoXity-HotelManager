<?php

    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.model' );
    
    JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' ); 
    
    class GeoxityhotelmanagerModelContents extends JModel {
        
        const CATEGORIES_TABLE      = '#__categories'; 
        const SECTIONS_TABLE        = '#__sections'; 
        const CONTENTS_TABLE        = '#__content'; 
        const CATEGORY_PREFIX       = 'hotel'; 
        const ROW_LIMIT             = 10; 
        
        
        public function __construct() {
            
            parent::__construct(); 
        }
        
        /**
         * Executes the process of deleting contents given an array of IDs 
         * 
         * @param       Array       $contentIDs 
         */
        public function deleteContents( $contentIDs ) {
            assert( !empty( $contentIDs ) && is_array( $contentIDs ) ); 
            
            $content = NULL; 
            $content = &$this->getTable( 'Contents' ); 

            if( !empty( $contentIDs ) && is_array( $contentIDs ) ) {
                foreach( $contentIDs as $ID ) {
                    $content->delete( $ID ); 
                }
                
                return TRUE; 
            } 
            
            return FALSE; 
        }
        
        /**
         * Returns an object that represents a category record in 
         * name-value pairs
         * 
         * @param       Integer     $hotelID
         */
        public function getCategory( $hotelID ) {
            assert( !empty( $hotelID ) ); 
            
            $database = &JFactory::getDBO(); 
            
            $database->setQuery( 'SELECT * FROM '.self::CATEGORIES_TABLE.' WHERE alias="'.self::CATEGORY_PREFIX.trim( $hotelID ).'"'); 
            
            $database->query(); 
            
            $category = NULL; 
            $category = $database->loadObject(); 
            
            return $category; 
        }
        
        public function getContent( $ID ) {
            assert( !empty( $ID ) ); 
            
            $content = NULL; 
            $content = &$this->getTable( 'Contents' ); 
            
            $content->load( $ID ); 
            
            return $content; 
        }
        
        public function getContents( $hotelID, $sectionTitle = '', $page = 1) {
            assert( !empty( $hotelID ) ); 
            
            $query = 'SELECT * FROM '.self::CATEGORIES_TABLE.' WHERE alias="'.self::CATEGORY_PREFIX.$hotelID.'"'; 
            
            if( !empty( $sectionTitle ) ) { // fetch for section object if section title is given
                $section = NULL; 
                $section = $this->getSection( $sectionTitle ); 
                
                $query .= ' AND section='.$section->id; 
            } 
            
            
            $database = &JFactory::getDBO(); 
            
            $database->setQuery( $query ); // fetching category
            
            $database->query(); 
            
            $category = $database->loadObject(); 
            
            // building query for contents
            $contents = array(); 
            if ( !empty( $category ) ) {
                $query = 'SELECT * FROM '.self::CONTENTS_TABLE.' WHERE catid='.$category->id; 
                
                $offset = ( $page - 1 ) * self::ROW_LIMIT; 
                
                $database->setQuery( $query , $offset, self::ROW_LIMIT); 
                
                $database->query(); 
                
                $contents = $database->loadObjectList(); 
                
            } else {
                exit(' There is a problem fetching the categories for content. Please setup joomla section and contents for hotel news' ); 
            }
            
            return $contents; 
        }
        
        private function getSection( $title ) {
            assert( !empty( $title ) ); 
            
            $database = &JFactory::getDBO(); 
            
            
            $database->setQuery( 'SELECT * FROM '.self::SECTIONS_TABLE.' WHERE title="'.trim( $title ).'"' ); 
            
            $database->query(); 
            
            $section = NULL; 
            $section = $database->loadObject(); 
            
            return $section; 
        }
        
        public function getTotalCount( $categoryID ) {
            assert( !empty( $categoryID ) ); 
            
            $database = &JFactory::getDBO(); 
            $database->setQuery( 'SELECT * FROM '.self::CONTENTS_TABLE.' WHERE catid='.$categoryID ); 
            
            $database->query(); 
            
            return $database->getNumRows(); 
        }
        
        /**
         * exectues the process of saving content to the database
         * 
         * @param       Object      $content        Content object
         */
        public function saveContent( $contentInput ) {
            assert( !empty( $contentInput ) ); 
            
            $content = NULL; 
            $content = &$this->getTable( 'Contents' ); 
            
            return $content->save( $contentInput ); 
        }
    }
?>
