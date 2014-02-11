<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.view' ); 
    
    include_once( JPATH_BASE.DS.'helpers'.DS.'geoxityhelpers.php' ); 
    
    include_once( JPATH_BASE.DS.'ANTlabsUtils'.DS.'geoxityuser.php' ); 
    
    class GeoxityhotelmanagerViewHotelinfo extends JView {
        
        const HOTEL_SECTION_NAME            = 'Hotel Content'; 
        
        const PAGE_TITLE                    = 'Hotel Info'; 
        
        public function display( $template = null ) {
            $hotelID            = &JRequest::getVar( 'hotel_id' ); 
            
            $settingsModel      = &$this->getModel( 'settings' ); 
            
            $hotelGuideProfile  = $settingsModel->getHotelGuideSettings( $hotelID ); 
            
            $title              = self::PAGE_TITLE; 
            
            $this->assignRef( 'title', $title ); 
            
            $this->assignRef( 'content', $hotelGuideProfile->intro ); 
            
            $document       = &JFactory::getDocument(); 
            
            $joomla         = &JFactory::getApplication(); 
        
            $siteName       = $joomla->getCfg( 'sitename' ); 
            
            $document->setTitle( $siteName.' - Hotel Info' );
            
            parent::display(); 
        }
    }
?>
