<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.view' ); 
    
    include_once( JPATH_BASE.DS.'helpers'.DS.'geoxityhelpers.php' ); 
    
    include_once( JPATH_BASE.DS.'ANTlabsUtils'.DS.'geoxityuser.php' ); 
    
    class GeoxityhotelmanagerViewSettings extends JView {
        
        const   HOTEL_SECTION_NAME          = 'Hotel Content'; 
        const   MAX_IMAGE_COUNT             = 5; 
        
        // GEOXITY CONTENTS LABELS
        const   CUSTOMIZABLE_PAGE           = 'customizable-page'; 
        const   HOTEL_INFO                  = 'hotel-info'; 
        const   FACILITIES_CONTENT          = 'facilities-content'; 
        const   MAXIMUM_VERSION_COUNT       = 3; 
        
        public function display( $template = null ) {
            
            global $mainframe, $option; 
            
            $user                   = &JFactory::getUser(); 
            
            $hotelID                = NULL; 
            $settingsModel          = &$this->getModel( 'settings' ); 
            $hotels                 = $settingsModel->getHotels( $user->id ); 
            
            $session                = &JFactory::getSession(); 
            
            $this->assignRef( 'hotelChoices', $hotels ); 
            
            if ( !empty( $user->id ) || $user->id != 0 ) {
                $this->initializeHotelConfiguration( $hotels, $user ); 
            }
            
            $hotelID = @$session->get( 'managed_hotel_id' ); 
            
            if ( !empty( $hotelID ) ) {
                
                $this->assignRef( 'currentUser', $user ); 
                
                $defaultModel      = &$this->getModel(); 
            
                $document   = NULL; 
                $document   = &JFactory::getDocument(); 
                $document->addStyleSheet( '/templates/geoxity/template-resources/common/styles/hotel-manager.css' ); 

                $profile = $defaultModel->getProfileSettings( $hotelID ); 
                
                // DEBUG MODE: start ##########################################
                /**
                 * deleteUserAccounts will call to delete the HSIA user accounts
                 * creted for a particular profile. To use this, uncomment the 
                 * 2 lines below. This will not be loading the whole page, but 
                 * will terminate as soon as the accounts will be deleted
                 */
//                deteteUserAccounts( $profile, 1500 ); 
//                exit(); 
                // DEBUG MODE: end ##########################################
                
                // TRU AUTH USER ACCOUNTS: start //
                if( $profile->gateway == 'hsia' ) {
                    
                    $profile->hsia_accounts                 = getUserAccounts( $profile ); 
                    
                    $totalNumberOfAccounts                  = getUserAccountsCount( $profile );
                    
                    $profile->hsia_total_records_count      = $totalNumberOfAccounts; 
                    
                    $profile->number_of_accounts_matched    = $totalNumberOfAccounts; 
                    
                    $profile->hsia_pages                    = getHSIAAccountsListPages( $totalNumberOfAccounts ); 
                    
                    $accountGroups                          = getUserAcountGroups(); 
                    
                    $this->assignRef( 'accountGroups', $accountGroups ); 
                }

                // TRU AUTH USER ACCOUNTS: start //
                $this->assignRef( 'profile', $profile ); 
                $this->assignRef( 'hotelGuideProfile', $defaultModel->getHotelGuideSettings( $hotelID ) ); 
                
                /*
                 * Broadcast 
                 */
                
                $broadcastMessages = $this->getBroadcastMessages( $profile ); 
                
                $this->assignRef( 'broadcastMessages', $broadcastMessages ); 

                /*
                 * Fetching facilities
                 */
                $this->assignRef( 'facilities', @$this->get( 'Facilities' ) ); 

                // RATINGS: start // 
                $ratingsModel                   = $this->getModel( 'ratings' ); 
                $ratings                        = @$ratingsModel->getData( $hotelID ); 
                $totalNumberOfRatings           = $ratingsModel->getTotalCount( $hotelID ); 
                
                $numberOfRatingPages            = 0; 
                $numberOfRatingsToDisplay       = 0; 
                if( !empty( $ratings ) ) {
                    $numberOfRatingsToDisplay   = count($ratings); 

                    $numberOfRatingPages        = ceil( $totalNumberOfRatings / $numberOfRatingsToDisplay ); 
                }

                $unreadCount                    = $ratingsModel->getNewRatingsCount( $hotelID ); 

                $this->assignRef( 'ratings', $ratings ); 
                $this->assignRef( 'totalUnread', $unreadCount ); 
                $this->assignRef( 'totalRatings', $totalNumberOfRatings ); 
                $this->assignRef( 'totalRatingPages', $numberOfRatingPages ); 
                $this->assignRef( 'averageRating', $this->getAverageRating( $ratings ) ); 
                // RATINGS: end // 

                // NEWS: start // 
                $contentsModel                  = $this->getModel( 'contents' ); 
                $news                           = $contentsModel->getContents( $hotelID, self::HOTEL_SECTION_NAME ); 
                $contentCategory                = $contentsModel->getCategory( $hotelID ); 
                $totalNews                      = $contentsModel->getTotalCount( $contentCategory->id ); 

                $numberOfNewsToDisplay          = 0; 
                $totalNewsPages                 = 0; 
                if( !empty( $news ) ) {
                    $numberOfNewsToDisplay      = count( $news ); 

                    $numberOfNewsPages          = ceil( $totalNews / $numberOfNewsToDisplay ); 
                }

                $this->assignRef( 'news', $news ); 
                $this->assignRef( 'totalNumberOfNews', $totalNews ); 
                $this->assignRef( 'totalNumberOfNewsPages', $numberOfNewsPages ); 
                // NEWS: end // 

                // SLIDESHOW: start // 
                $albumDirectory = JPATH_BASE.'/images/hotelguide/gallery/hotel_'.$hotelID.'/album/';

                $slideshowModel = $this->getModel( 'slideshow' ); 
                $slideshowImages = $slideshowModel->getImages( $hotelID ); 

                $this->assignRef( 'albumDirectory', $albumDirectory ); 
                $this->assignRef( 'slideshowImages', $slideshowImages ); 
                // SLIDESHOW: end //
                
                $geoxityContentsModel       = $this->getModel( 'geoxitycontents' ); 
                // CUSTOMIZABLE PAGE VERSIONS: start // 
                $customizablePageVersions   = $geoxityContentsModel->getContents( $profile->id, self::CUSTOMIZABLE_PAGE ); 
                
                $currentCustomPage          = $this->getCurrent( $customizablePageVersions ); 
                
                $this->assignRef( 'customizablePageVersions', $customizablePageVersions ); 
                $this->assignRef( 'customizablePageCurrent' , $currentCustomPage ); 
                // CUSTOMIZABLE PAGE VERSIONS: end // 
                
                // HOTEL INFO VERSIONS: start //
                $hotelInfoVersions          = $geoxityContentsModel->getContents( $profile->id, self::HOTEL_INFO ); 
                
                $currentHotelInfo           = $this->getCurrent( $hotelInfoVersions ); 
                $this->assignRef( 'hotelInfoVersions', $hotelInfoVersions ); 
                $this->assignRef( 'hotelInfoCurrent', $currentHotelInfo ); 
                // HOTEL INFO VERSIONS: end //
                
                // FACILITIES CONTENT VERSIONS: start // 
                $facilitiesContentVersions  = $geoxityContentsModel->getContents( $profile->id, self::FACILITIES_CONTENT ); 
                
                $currentFacilitiesPageContent = $this->getCurrent( $facilitiesContentVersions ); 
                
                $this->assignRef( 'facilitiesContentVersions', $facilitiesContentVersions ); 
                $this->assignRef( 'facilitiesContentCurrent', $currentFacilitiesPageContent ); 
                // FACILITIES CONTENT VERSIONS: end // 
                
                /*
                 * Non data variables for the view
                 */
                $maxImageCount = self::MAX_IMAGE_COUNT; 
                $imagePath = 'templates/geoxity/template-resources/common/images/'; 

                $this->assignRef( 'maxImageCount', $maxImageCount ); 
                
                $maxVersionCount = self::MAXIMUM_VERSION_COUNT; 
                $this->assignRef( 'maxVersionCount', $maxImageCount ); 
                $this->assignRef( 'imagePath', $imagePath ); 
                $this->assignRef( 'countries', $this->getCountries() ); 
                
                $this->assignRef( 'hotelIDInSession', $session->get( 'managed_hotel_id' ) ); 
                
                $messages = array(); 
                $messages = $mainframe->getMessageQueue(); 
                
                $this->assignRef( 'messages', $messages ); 
            } 
            
            
            
            /**
             * Chat support messages 
             */
            $chatMessages = getChatMessageLogs(); 
            $this->assign( 'chatMessages', $chatMessages ); 
            
            $this->assign( 'buttonsPath', 'templates/geoxity/template-resources/common/images/buttons/' ); 
                
            $this->assignRef( 'option', $option ); 
            parent::display( $template ); 
        }
        
        private function getAverageRating( $ratings ) {
            
            if( !empty( $ratings ) ) {
                $sumOfRatings = 0; 
            
                foreach ( $ratings as $rating ) {
                    $sumOfRatings += $rating->rating; 
                }

                $averageRating =  $sumOfRatings / count( $ratings ); 

                return ceil( $averageRating ); 
            } else {
                return NULL; 
            }
        }

        /**
         * Returns an array of broadcast messages
         * @param type $profile
         * @return \stdClass 
         */
        private function getBroadcastMessages( $profile ) {
            
            $broadcastMessages = array(); 
            
            if( strtolower( trim( $profile->broadcast_source ) ) === 'twitter' ) {
                
                $tweets = getTwitterFeeds( 'broadcast', NULL, $twitteruserName ) ;
            
                foreach ( $tweets as $tweet ) {

                    $broadcastMessage               = new stdClass();

                    $broadcastMessage->id           = $tweet->tweet_id; 
                    $broadcastMessage->message      = $tweet->tweet; 
                    $broadcastMessage->created_at   = $tweet->created_at; 

                    $broadcastMessages[] = $broadcastMessage; 
                }
                
            } else if ( strtolower( trim( $profile->broadcast_source ) ) === 'weibo' ) {
                
                include_once( JPATH_BASE.DS.'helpers'.DS.'weibo.class.php' ); 
                
                $weibo                              = new weibo( $profile->weibo_key, $profile->secret );
                
                $feeds                              = $weibo->user_timeline( $profile->weibo_nickname ); 
                
                foreach( $feeds as $feed ) {
                    
                    $broadcastMessage               = new stdClass(); 
                    
                    $broadcastMessage->id           = $feed->mid; 
                    $broadcastMessage->message      = $feed->text; 
                    $broadcastMessage->created_at   = $feed->created_at; 
                    
                    $broadcastMessages[] = $broadcastMessage; 
                    
                }
                
            } 
            
            return $broadcastMessages;
        }
        
        private function getCountries() {
            $countries = array(
                  "GB" => "United Kingdom",
                  "US" => "United States",
                  "AF" => "Afghanistan",
                  "AL" => "Albania",
                  "DZ" => "Algeria",
                  "AS" => "American Samoa",
                  "AD" => "Andorra",
                  "AO" => "Angola",
                  "AI" => "Anguilla",
                  "AQ" => "Antarctica",
                  "AG" => "Antigua And Barbuda",
                  "AR" => "Argentina",
                  "AM" => "Armenia",
                  "AW" => "Aruba",
                  "AU" => "Australia",
                  "AT" => "Austria",
                  "AZ" => "Azerbaijan",
                  "BS" => "Bahamas",
                  "BH" => "Bahrain",
                  "BD" => "Bangladesh",
                  "BB" => "Barbados",
                  "BY" => "Belarus",
                  "BE" => "Belgium",
                  "BZ" => "Belize",
                  "BJ" => "Benin",
                  "BM" => "Bermuda",
                  "BT" => "Bhutan",
                  "BO" => "Bolivia",
                  "BA" => "Bosnia And Herzegowina",
                  "BW" => "Botswana",
                  "BV" => "Bouvet Island",
                  "BR" => "Brazil",
                  "IO" => "British Indian Ocean Territory",
                  "BN" => "Brunei Darussalam",
                  "BG" => "Bulgaria",
                  "BF" => "Burkina Faso",
                  "BI" => "Burundi",
                  "KH" => "Cambodia",
                  "CM" => "Cameroon",
                  "CA" => "Canada",
                  "CV" => "Cape Verde",
                  "KY" => "Cayman Islands",
                  "CF" => "Central African Republic",
                  "TD" => "Chad",
                  "CL" => "Chile",
                  "CN" => "China",
                  "CX" => "Christmas Island",
                  "CC" => "Cocos (Keeling) Islands",
                  "CO" => "Colombia",
                  "KM" => "Comoros",
                  "CG" => "Congo",
                  "CD" => "Congo, The Democratic Republic Of The",
                  "CK" => "Cook Islands",
                  "CR" => "Costa Rica",
                  "CI" => "Cote D'Ivoire",
                  "HR" => "Croatia (Local Name: Hrvatska)",
                  "CU" => "Cuba",
                  "CY" => "Cyprus",
                  "CZ" => "Czech Republic",
                  "DK" => "Denmark",
                  "DJ" => "Djibouti",
                  "DM" => "Dominica",
                  "DO" => "Dominican Republic",
                  "TP" => "East Timor",
                  "EC" => "Ecuador",
                  "EG" => "Egypt",
                  "SV" => "El Salvador",
                  "GQ" => "Equatorial Guinea",
                  "ER" => "Eritrea",
                  "EE" => "Estonia",
                  "ET" => "Ethiopia",
                  "FK" => "Falkland Islands (Malvinas)",
                  "FO" => "Faroe Islands",
                  "FJ" => "Fiji",
                  "FI" => "Finland",
                  "FR" => "France",
                  "FX" => "France, Metropolitan",
                  "GF" => "French Guiana",
                  "PF" => "French Polynesia",
                  "TF" => "French Southern Territories",
                  "GA" => "Gabon",
                  "GM" => "Gambia",
                  "GE" => "Georgia",
                  "DE" => "Germany",
                  "GH" => "Ghana",
                  "GI" => "Gibraltar",
                  "GR" => "Greece",
                  "GL" => "Greenland",
                  "GD" => "Grenada",
                  "GP" => "Guadeloupe",
                  "GU" => "Guam",
                  "GT" => "Guatemala",
                  "GN" => "Guinea",
                  "GW" => "Guinea-Bissau",
                  "GY" => "Guyana",
                  "HT" => "Haiti",
                  "HM" => "Heard And Mc Donald Islands",
                  "VA" => "Holy See (Vatican City State)",
                  "HN" => "Honduras",
                  "HK" => "Hong Kong",
                  "HU" => "Hungary",
                  "IS" => "Iceland",
                  "IN" => "India",
                  "ID" => "Indonesia",
                  "IR" => "Iran (Islamic Republic Of)",
                  "IQ" => "Iraq",
                  "IE" => "Ireland",
                  "IL" => "Israel",
                  "IT" => "Italy",
                  "JM" => "Jamaica",
                  "JP" => "Japan",
                  "JO" => "Jordan",
                  "KZ" => "Kazakhstan",
                  "KE" => "Kenya",
                  "KI" => "Kiribati",
                  "KP" => "Korea, Democratic People's Republic Of",
                  "KR" => "Korea, Republic Of",
                  "KW" => "Kuwait",
                  "KG" => "Kyrgyzstan",
                  "LA" => "Lao People's Democratic Republic",
                  "LV" => "Latvia",
                  "LB" => "Lebanon",
                  "LS" => "Lesotho",
                  "LR" => "Liberia",
                  "LY" => "Libyan Arab Jamahiriya",
                  "LI" => "Liechtenstein",
                  "LT" => "Lithuania",
                  "LU" => "Luxembourg",
                  "MO" => "Macau",
                  "MK" => "Macedonia, Former Yugoslav Republic Of",
                  "MG" => "Madagascar",
                  "MW" => "Malawi",
                  "MY" => "Malaysia",
                  "MV" => "Maldives",
                  "ML" => "Mali",
                  "MT" => "Malta",
                  "MH" => "Marshall Islands",
                  "MQ" => "Martinique",
                  "MR" => "Mauritania",
                  "MU" => "Mauritius",
                  "YT" => "Mayotte",
                  "MX" => "Mexico",
                  "FM" => "Micronesia, Federated States Of",
                  "MD" => "Moldova, Republic Of",
                  "MC" => "Monaco",
                  "MN" => "Mongolia",
                  "MS" => "Montserrat",
                  "MA" => "Morocco",
                  "MZ" => "Mozambique",
                  "MM" => "Myanmar",
                  "NA" => "Namibia",
                  "NR" => "Nauru",
                  "NP" => "Nepal",
                  "NL" => "Netherlands",
                  "AN" => "Netherlands Antilles",
                  "NC" => "New Caledonia",
                  "NZ" => "New Zealand",
                  "NI" => "Nicaragua",
                  "NE" => "Niger",
                  "NG" => "Nigeria",
                  "NU" => "Niue",
                  "NF" => "Norfolk Island",
                  "MP" => "Northern Mariana Islands",
                  "NO" => "Norway",
                  "OM" => "Oman",
                  "PK" => "Pakistan",
                  "PW" => "Palau",
                  "PA" => "Panama",
                  "PG" => "Papua New Guinea",
                  "PY" => "Paraguay",
                  "PE" => "Peru",
                  "PH" => "Philippines",
                  "PN" => "Pitcairn",
                  "PL" => "Poland",
                  "PT" => "Portugal",
                  "PR" => "Puerto Rico",
                  "QA" => "Qatar",
                  "RE" => "Reunion",
                  "RO" => "Romania",
                  "RU" => "Russian Federation",
                  "RW" => "Rwanda",
                  "KN" => "Saint Kitts And Nevis",
                  "LC" => "Saint Lucia",
                  "VC" => "Saint Vincent And The Grenadines",
                  "WS" => "Samoa",
                  "SM" => "San Marino",
                  "ST" => "Sao Tome And Principe",
                  "SA" => "Saudi Arabia",
                  "SN" => "Senegal",
                  "SC" => "Seychelles",
                  "SL" => "Sierra Leone",
                  "SG" => "Singapore",
                  "SK" => "Slovakia (Slovak Republic)",
                  "SI" => "Slovenia",
                  "SB" => "Solomon Islands",
                  "SO" => "Somalia",
                  "ZA" => "South Africa",
                  "GS" => "South Georgia, South Sandwich Islands",
                  "ES" => "Spain",
                  "LK" => "Sri Lanka",
                  "SH" => "St. Helena",
                  "PM" => "St. Pierre And Miquelon",
                  "SD" => "Sudan",
                  "SR" => "Suriname",
                  "SJ" => "Svalbard And Jan Mayen Islands",
                  "SZ" => "Swaziland",
                  "SE" => "Sweden",
                  "CH" => "Switzerland",
                  "SY" => "Syrian Arab Republic",
                  "TW" => "Taiwan",
                  "TJ" => "Tajikistan",
                  "TZ" => "Tanzania, United Republic Of",
                  "TH" => "Thailand",
                  "TG" => "Togo",
                  "TK" => "Tokelau",
                  "TO" => "Tonga",
                  "TT" => "Trinidad And Tobago",
                  "TN" => "Tunisia",
                  "TR" => "Turkey",
                  "TM" => "Turkmenistan",
                  "TC" => "Turks And Caicos Islands",
                  "TV" => "Tuvalu",
                  "UG" => "Uganda",
                  "UA" => "Ukraine",
                  "AE" => "United Arab Emirates",
                  "UM" => "United States Minor Outlying Islands",
                  "UY" => "Uruguay",
                  "UZ" => "Uzbekistan",
                  "VU" => "Vanuatu",
                  "VE" => "Venezuela",
                  "VN" => "Viet Nam",
                  "VG" => "Virgin Islands (British)",
                  "VI" => "Virgin Islands (U.S.)",
                  "WF" => "Wallis And Futuna Islands",
                  "EH" => "Western Sahara",
                  "YE" => "Yemen",
                  "YU" => "Yugoslavia",
                  "ZM" => "Zambia",
                  "ZW" => "Zimbabwe"
                );
            
            return $countries; 
        }
        
        /**
         * Returns an object in name value pairs that represent the current
         * content
         */
        private function getCurrent( $contents ) {
            
            foreach( $contents as $content ) {
                if( intval( $content->is_current ) == 1  ) {
                    
                    return $content; 
                    break; 
                }
            }
        }
        
        /**
         * This method will will initialize the hotel to be configured. Here
         * are the steps for initialization...
         * 
         * 1. check from form post to check if there is something passed. If so, 
         * it means that the user has chosen from the list of available hotels, 
         * thus a hotel ID can be generated from that, and then set that hotel
         * ID in session
         * 
         * 2. If step 1 fails, it will take the first hotel form the array of 
         * hotels associated with the user, and then set that one in session
         */
        private function initializeHotelConfiguration( $hotels, $user ) {
            
            global $mainframe, $option; 
            
            $hotelIDContainer       = &Jrequest::getVar( 'hotel_choice' ); 
            $session                = &JFactory::getSession(); 
            
            if( !empty( $hotelIDContainer ) ) {
                
                $session->set( 'managed_hotel_id', $hotelIDContainer ); 
            } else {
                
                $hotelIDLoadedInSession = @$session->get( 'managed_hotel_id' ); 
                
                if( empty( $hotelIDLoadedInSession ) && count( $hotels ) > 0 ) {
                    
                    $session->set( 'managed_hotel_id', $hotels[0]->hotel_id ); 
                    
                    return TRUE; 
                }
            }
            
        }
    }
?>
