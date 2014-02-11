<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.view' ); 
    
    include_once( JPATH_BASE.DS.'helpers'.DS.'geoxityhelpers.php' ); 
    
    include_once( JPATH_BASE.DS.'ANTlabsUtils'.DS.'geoxityuser.php' ); 
    
    class GeoxityhotelmanagerViewCustompage extends JView {
        
        const HOTEL_SECTION_NAME            = 'Hotel Content'; 
        
        public function display( $template = null ) {
            $hotelID        = &JRequest::getVar( 'hotel_id' ); 
            
            $contentID      = &JRequest::getVar( 'content_id' ); 
            
            $settingsModel  = $this->getModel( 'settings' ); 
            
            $contentModel   = $this->getModel( 'geoxitycontents' ); 
            
            $hotelProfile   = $settingsModel->getProfileSettings( $hotelID ); 
            
            $content        = $contentModel->getContent( $contentID ); 
            
            $this->assignRef( 'title', $hotelProfile->custom_page_title ); 
            $this->assignRef( 'content', $content ); 
            
            parent::display(); 
        }
    }
?>
