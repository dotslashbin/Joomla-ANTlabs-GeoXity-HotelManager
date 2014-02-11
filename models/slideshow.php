<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.model' );
    
    JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' ); 
    
    class GeoxityhotelmanagerModelSlideshow extends JModel {
        
        const GALLERY_TABLE             = '#__hg_galleryitems'; 
        
        const CATEGORIES_TABLE          = '#__hg_cats_item_relations'; 
        
        var $_total                     = NULL; 
        var $_pagination                = NULL; 
        
        public function __construct() {
            parent::__construct();
            
        }
        
        /**
         * Executes the process of adding an image to the slideshow. 
         * 
         * NOTE: this is done instead of using save because of the additional 
         * requirement of fetching the category id for the image entry to be 
         * compatible with hotelGuide
         * 
         * @param       String      $hotelID
         * @param       String      $filename
         * 
         * @returns     Integer     last inserted ID
         */
        public function addImage( $hotelID, $filename ) {
            assert( !empty( $hotelID ) ); 
            assert( !empty( $filename ) ); 
            
            $database           = &JFactory::getDBO(); 
            
            // fetching category id
            
            $database->setQuery( 'SELECT catid FROM '.self::CATEGORIES_TABLE.' WHERE itemid='.$hotelID ); 
            $database->query(); 
            $categoryID = $database->loadResult(); 

            // Inserting to the databse
            $database->setQuery( 'INSERT INTO '.self::GALLERY_TABLE.'( catid, filename, hotel ) VALUES( "'.$categoryID.'", "'.$filename.'", "'.$hotelID.'" )' ); 
            
            $database->query(); 
            
            return $database->insertid(); 
        }
        
        public function deleteImage( $imageID ) {
            assert( !empty( $imageID ) ); 
            
            $image = NULL; 
            $image = &$this->getTable( 'Images' ); 
            
            return $image->delete( $imageID ); 
        }
        
        public function getImages( $hotelID ) {
            assert( !empty( $hotelID ) ); 
            
            $database = &JFactory::getDBO(); 
            
            $database->setQuery( 'SELECT id, filename, description FROM '.self::GALLERY_TABLE.' WHERE hotel='.$hotelID ); 
            
            $database->query(); 
            
            $images = array(); 
            $images = $database->loadObjectList(); 
            
            return $images; 
        }
        
        public function updateImage( $imageInput ) {
            $image = NULL; 
            $image = &$this->getTable( 'Images' ); 
            
            return $image->save( $imageInput ); 
        }
    }
?>
