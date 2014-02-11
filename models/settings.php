<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.model' );
    
    JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' ); 
    
    class GeoxityhotelmanagerModelSettings extends JModel {
        
        const PROFILE_TABLE     = '#__geoxity_hotel_profile'; 
        const HOTEL_TABLE       = '#__hg_hotelitems'; 
        const FACILITIES_TABLE  = '#__hg_facilities'; 
        
        var $_total             = NULL; 
        var $_pagination        = NULL; 
        
        public function __construct() {
            
            parent::__construct(); 
        }
        
        /**
         * Returns an array of facility objects fetched from hotel guide
         */
        public function getFacilities() {
            $database = &JFactory::getDBO(); 
            
            $database->setQuery( 'SELECT * FROM '.self::FACILITIES_TABLE ); 
            
            $database->query(); 
            
            $facilities = NULL; 
            $facilities = $database->loadObjectList(); 
            
            return $facilities; 
        }
        
        /**
         * Returns na array of hotels
         */
        public function getHotels( $userID ) {
            $database = &JFactory::getDBO(); 

            $query  = ' SELECT 
                            profile.hotel_id, 
                            hotel.title 
                        FROM 
                            '.self::PROFILE_TABLE.' AS profile, 
                            '.self::HOTEL_TABLE.' as hotel 
                        WHERE 
                            profile.hotel_id=hotel.id 
                        AND 
                            ( 
                            profile.manager_id='.$userID.' 
                            OR profile.admin='.$userID.' 
                            OR profile.staff_1='.$userID.' 
                            OR profile.staff_2='.$userID.' 
                            OR profile.staff_3='.$userID.' )'; 
            
            $database->setQuery( $query ); 
            
            $database->query(); 
            
            $hotels = NULL; 
            $hotels = $database->loadObjectList(); 
            
            return $hotels; 
        }
        
        /**
         * Returns an object representing a HotelGuide setting data
         */
        public function getHotelGuideSettings( $hotelID ) {
            $hotelGuideSettings = NULL; 
            
            $hotelGuideSettings = &$this->getTable( 'Hotelguideprofile' ); 
            
            $hotelGuideSettings->load( $hotelID ) ; 

            return $hotelGuideSettings; 
        }
        
        /**
         * Returns the name hotel profile settings in name value pairs
         */
        public function getProfileSettings( $hotelID ) {
            
            $database = &JFactory::getDBO(); 
            
            $database->setQuery( 'SELECT * FROM '.self::PROFILE_TABLE.' WHERE hotel_id='.$hotelID.' LIMIT 1' ); 
            
            $database->query(); 
            
            $profileSettings = NULL; 
            
            $profileSettings = &$this->getTable( 'Profilesettings' ); 
            
            $profileSettings->load( $database->loadObject()->id ) ; 
            
            return $profileSettings; 
        }
        
        /**
         * Executes the process of updateing a hotel guide profile. 
         * The data to be saved here is saved to the com_hotelguide table
         */
        public function updateHotelGuideProfile( $hotelGuideProfile ) {
            
            $hotelGuideSettings = NULL; 
            $hotelGuideSettings = &$this->getTable( 'Hotelguideprofile' ); 
            
            return $hotelGuideSettings->save( $hotelGuideProfile ); 
        }
        
        /**
         * Executes the process of updating a profile setting for a hotel. 
         * If the current hotel does not have a record, it will automatically
         * create one
         * 
         * @params      Object      $hotelProfile
         */
        public function updateProfileSetting( $hotelProfile ) {
            
            $profileSettings = NULL; 
            $profileSettings = &$this->getTable( 'Profilesettings' ); 
            
            return $profileSettings->save( $hotelProfile ); 
        }
        
        public function updateCollaboratorCredentials ( $id, $username, $password ) {
            $database = &JFactory::getDBO(); 
            
            $query      = 'UPDATE jos_users SET `username`="'.$username.'" , `password`=md5("'.$password.'") WHERE `id`="'.$id.'"'; 
            
            $database->setQuery( $query ); 
            
            $database->query(); 
        }
    }
?>
