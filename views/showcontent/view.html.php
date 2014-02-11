<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.view' ); 
    
    include_once( JPATH_BASE.DS.'helpers'.DS.'geoxityhelpers.php' ); 
    
    include_once( JPATH_BASE.DS.'ANTlabsUtils'.DS.'geoxityuser.php' ); 
    
    class GeoxityhotelmanagerViewShowContent extends JView {
        
        public function display( $template = null ) {
            $hotelID            = &JRequest::getVar( 'hotel_id' ); 
            
            $this->checkIfPreview( $hotelID ); 
            
            $contentID          = &JRequest::getVar( 'content_id' ); 
            
            $contentsModel      = &$this->getModel(); 
            
            $content            = $contentsModel->getContent( $contentID ); 
            
            $this->assignRef( 'title', $content->title ); 
            $this->assignRef( 'content', $content->fulltext ); 
            
            $document       = &JFactory::getDocument(); 
            
            $joomla         = &JFactory::getApplication(); 
        
            $siteName       = $joomla->getCfg( 'sitename' ); 
            
            $document->setTitle( $siteName.' - '.$content->title );
            
            parent::display(); 
        }
        
        /**
         * This method executes the process of checking if the page to be loaded
         * is for preview. The intention is for this to be available only on 
         * preivew, if not, redirect to home page.
         * 
         * @param       Numeric String      $hotelID
         */
        private function checkIfPreview( $hotelID ) {
            global $mainframe; 
            
            $isPreview          = &JRequest::getVar( 'preview' ); 
            
            if( $isPreview != 1 ) {
                $mainframe->redirect( 'index.php?hotel_id='.$hotelID ); 
            } 
        }
    }
?>
