<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.view' ); 
    
    include_once( JPATH_BASE.DS.'helpers'.DS.'geoxityhelpers.php' ); 
    
    include_once( JPATH_BASE.DS.'ANTlabsUtils'.DS.'geoxityuser.php' ); 
    
    class GeoxityhotelmanagerViewFacilities extends JView {
        
        const HOTEL_SECTION_NAME            = 'Hotel Content'; 
        
        public function display( $template = null ) {
            $hotelID            = &JRequest::getVar( 'hotel_id' ); 
            
            $settingsModel      = &$this->getModel( 'settings' ); 
            $facilitiesModel    = &$this->getModel( 'facilities' ); 
            
            $title              = 'Facilities'; 
            
            $hotelProfile       = $settingsModel->getProfileSettings( $hotelID ); 
            $hotelGuideProfile  = $settingsModel->getHotelGuideSettings( $hotelID ); 
            
            $facilityIDs        = explode( ',', $hotelGuideProfile->facilities ); 
            
            $facilities         = $facilitiesModel->getFacilities( $facilityIDs ); 
            
            $facilitiesContent  = $hotelProfile->facilities_content; 
            
            $this->assignRef( 'title' , $title ); 
            $this->assignRef( 'facilities', $facilities ); 
            $this->assignRef( 'facilitiesContent', $facilitiesContent ); 
            
            $document       = &JFactory::getDocument(); 
            
            $joomla         = &JFactory::getApplication(); 
        
            $siteName       = $joomla->getCfg( 'sitename' ); 
            
            $document->setTitle( $siteName.' - Facilities' );
            
            parent::display(); 
        }
    }
?>
