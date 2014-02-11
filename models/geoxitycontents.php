<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.model' );
    
    JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' ); 
    
    class GeoxityhotelmanagerModelGeoxitycontents extends JModel {
        
        const CONTENTS_TABLE = '#__geoxity_contents'; 
        
        public function __construct( $config = array() ) {
            parent::__construct($config);
        }
        
        /**
         * Executes the tasks of deleting a content
         * 
         * @param       int         $ID
         */
        public function deleteContent( $ID ) {
            
            assert( !empty( $ID ) ); 
            
            $content = &$this->getTable( 'geoxitycontents' ); 
            
            return $content->delete( $ID ); 
        }
        
        /**
         * Returns an object representing a content record
         * 
         * @param       int      $ID
         */
        public function getContent( $ID ) {
            
            $content = $this->getTable( 'geoxitycontents' ); 
            
            $content->load( $ID ) ; 
            
            return $content;
        }
        
        /**
         * Returns an array of contents in name value pairs, given the section 
         * and profiile id
         * 
         * @param           int         $profileID
         * @param           String      $section
         */
        public function getContents( $profileID, $section ) {

            assert( !empty( $profileID ) ); 
            assert( !empty( $section ) ); 
            
            $database       = &JFactory::getDBO(); 
            
            $query          = 'SELECT * FROM '.self::CONTENTS_TABLE.' WHERE section="'.$section.'" AND profile_id='.$profileID.' ORDER BY date_added DESC'; 

            $database->setQuery( $query ); 
            $database->query(); 
            
            $contents       = NULL; 
            $contents       = $database->loadObjectList(); 
            
            return $contents; 
        }
        
        /**
         * Returns an object representing the draft record given the profile
         * id and the section. If nothing is returned, it means that there is 
         * no draft as of the moment
         * 
         * @param           int         $profileID
         * @param           String      $section
         */
        public function getDraft( $profileID, $section ) {
            assert( !empty( $profileID ) ); 
            assert( !empty( $section ) ); 
            
            $database           = &JFactory::getDBO(); 
            
            $query = 'SELECT * FROM '.self::CONTENTS_TABLE.' WHERE profile_id='.trim( $profileID ).' AND section="'.trim( $section ).'" AND is_current=0'; 
            
            $database->setQuery( $query ); 
            
            $database->query(); 
            
            return $database->loadObject(); 
        }
        
        /**
         * Returns an object representing the content that is currently published
         * given the profile id and seciton
         */
        public function getCurrentContent( $profileID, $section )  {
            assert( !empty( $profileID ) ); 
            assert( !empty( $section ) ); 
            
            $database           = &JFactory::getDBO(); 
            
            $query              = 'SELECT * FROM '.self::CONTENTS_TABLE.' WHERE profile_id='.$profileID.' AND section="'.$section.'" AND is_current=1'; 
            
            $database->setQuery( $query ); 
            
            $database->query(); 
            
            $currentContent = NULL; 
            $currentContent = $database->loadObject(); 
            
            return $currentContent; 
        }
        
        /**
         * Returns the number of contents that matches the profile id and the 
         * section given
         *
         * @param       int         $profileID
         * @param       String      $section
         */
        public function getContentCount( $profileID, $section ) {
            assert( !empty( $profileID ) ); 
            assert( !empty( $section ) ); 
            
            $database           = &JFactory::getDBO(); 
            
            $query              = 'SELECT id FROM '.self::CONTENTS_TABLE.' WHERE profile_id='.$profileID.' AND section="'.$section.'"'; 
            
            $database->setQuery( $query ); 
            
            $database->query(); 
            
            $results = $database->loadObjectList(); 
            
            return count( $results ); 
        }
        
        /**
         * Returns an object representing the previously published content
         * given the profile ID and section ID
         */
        public function getPreviousContent( $profileID, $section ) {
            assert( !empty( $profileID ) ); 
            assert( !empty( $section ) ); 
            
            $database           = &JFactory::getDBO(); 
            
            $query = 'SELECT * FROM '.self::CONTENTS_TABLE.' WHERE profile_id='.trim( $profileID ).' AND section="'.trim( $section ).'" AND is_current=2'; 
            
            $database->setQuery( $query ); 
            
            $database->query(); 
            
            return $database->loadObject(); 
        }
        
        public function saveContent( $content ) {
            
            $contentsTable      = NULL; 
            $contentsTable      = &$this->getTable( 'geoxitycontents' ); 
            
            return $contentsTable->save( $content ); 
        }
        
        /**
         * Executes the task of setting a content as the currently published
         * content
         * 
         * @param       int          $ID 
         */
        public function setCurrent( $ID ) {
            
            $content            = $this->getContent( $ID ); 
            
            $database           = &JFactory::getDBO(); 
            
            if( $content->is_current == 1 ) {
                return NULL; 
            } else {
                
                $query = 'DELETE FROM '.self::CONTENTS_TABLE.' WHERE id !='.$ID.' AND section="'.$content->section.'" AND profile_id="'.$content->profile_id.'" AND is_current="2"';  
                $database->setQuery( $query ); 
                $database->query(); 
                
                $query = 'UPDATE '.self::CONTENTS_TABLE.' SET is_current="2" WHERE is_current="1" AND section="'.$content->section.'" AND profile_id="'.$content->profile_id.'"'; 
                $database->setQuery( $query ); 
                $database->query(); 
                
                // Setting the content to be published as "1" ( meaning currently published )
                $database->setQuery( 'UPDATE '.self::CONTENTS_TABLE.' SET is_current="1" WHERE id="'.$content->id.'"' ); 
                $database->query(); 
            }
            
            
        }
    }
?>
