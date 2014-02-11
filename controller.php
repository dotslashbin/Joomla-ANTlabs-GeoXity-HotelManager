<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport('joomla.application.component.controller');
    
    include_once( JPATH_BASE.DS.'helpers'.DS.'geoxityhelpers.php' ); 
    
    class GeoxityhotelmanagerController extends JController {
        
        const   AUTHENTICATION_ERROR_MESSAGE    = 'authentication error'; 
        const   SUCCESS_MESSAGE                 = 'success'; 
        const   ERROR_MESSAGE                   = 'error'; 
        const   MODEL_PREFIX                    = 'Goexityhotelmanager'; 
        
        const   HOTEL_SECTION_NAME              = 'Hotel Content'; 
        const   CUSTOMIZABLE_PAGE               = 'customizable-page'; 
        const   HOTEL_INFO                      = 'hotel-info'; 
        const   FACILITIES_CONTENT              = 'facilities-content'; 
        
        const   PORTAL_IP_ADDRESS               = '192.168.125.18'; 
        
        // API
        const   API_PATH                        = 'https://192.168.124.21/api/?'; 
//        const   API_PATH                        = 'https://10.30.1.48/api/?'; 
        const   API_PASSWORD                    = 'admin'; 
        const   MAXIMUM_VERSION_COUNT           = 3; 
        const   DEFAULT_PLAN                    = 'Unlimited';
        
        public function __construct($config = array()) {
            
            parent::__construct($config);
        }
        
        /**
         * Adding multiple accounts 
         */
        public function addGatewayAccounts() {

            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID                        = &JRequest::getVar( 'hotel_id' ); 
            $accountCreationType            = &JRequest::getVar( 'account_creation_type' ); 
            $model                          = &$this->getModel( 'settings' ); 
            $templateIndex                  = &JRequest::getVar( 'template_index', 0 ); 
            
            $hotelProfile                   = $model->getProfileSettings( $hotelID );
            
            $templates                      = json_decode( $hotelProfile->accounts_template ); 
            $template                       = $templates[$templateIndex]; 
            
            $output                         = Array(); 
            $code                           = &JRequest::getVar( 'custom_code' ); 
            
            $code                           = cleanup_input( $code ); 
            $user                           = @getUser(); 
            
            $addedUserAcconts               = array(); 
            
            $currentDate                    = date("Y-m-d");// current date
            $date                           = strtotime(date("Y-m-d", strtotime($currentDate)) . " +30 days");
            $after31Days                    = date('Y-m-d', $date); 
                
            $duration                       = (  ( int ) $template->duration_in_minutes * 60 ); 
            
            $returnObject                   = new stdClass(); 
            
            if( !empty( $code ) && $code != '' ) {
                
                $query                      = self::API_PATH.'op=user_add&api_password='.self::API_PASSWORD.'&usergroup='.str_replace( ' ', '%20', $template->plan ).'&userid='.$code.'@'.trim( $hotelProfile->hotel_id ).'&password=NOPASSWD&concurrency=1&creator='.$user->id.'&radiusattrs=Simultaneous-Use=1|ANTlabs-Expire-After='.$duration.'|Session-Timeout='.$duration.'&expiry='.$after31Days;
                
                $addedUserAcconts[]         = '<div>'.$code.'</div>'; 

                $results                    = file_get_contents( $query );  
                
                $output[] = $results; 
                
                // FETCHING RECENTLY ADDED ACCOUNT
                $recentlyCreatedAccount                             = $this->getHSIAaccount( $code, $hotelProfile->hotel_id ); 
                $recentlyCreatedAccount['stripped_access_code']     = removeDomainPortionFromAccount( $hotelProfile->hotel_id, $recentlyCreatedAccount['userid'] ); 
                
                $recentlyCreatedAccount['qos']                      = $this->getPlanDescription( str_replace( ' ', '%20', $template->plan ) ); 
                
                $createdOnDate                                      = date( 'M j Y g:i a', strtotime( $recentlyCreatedAccount['created_on'] ) ); 
                $recentlyCreatedAccount['created_on']               = $createdOnDate; 
                $recentlyCreatedAccount['first_login']              = 'N/A'; 
                
                $expiryText                                         = ( !empty( $recentlyCreatedAccount[ 'expiry' ] ) )? date( 'M j Y', strtotime( $recentlyCreatedAccount['expiry'] ) ):'N/A'; 
                
                $recentlyCreatedAccount['expiry_text']              = $expiryText; 
                
                $duration                                           = (int) $recentlyCreatedAccount['ANTlabs-Expire-After']; 
                
                $duration                                           = ( $duration / 60 ); 
            
                $days                                               = floor( $duration / 1440 ); 
                $hours                                              = floor( ( $duration - $days * 1440 ) / 60 );  
                
//                exit( 'CUSTOM CODE days: ' .var_dump( $duration )); // TODO: remove
                
                $updatedAccountsUsed                                = (int) ( $hotelProfile->accounts_used + $this->getQuotaCalculation( $duration ) ); 
                
                $hotelProfile->accounts_used                        = $updatedAccountsUsed; 
                
                if( !empty( $days ) && !empty( $hours ) ) {
                    $readableHumanFormat = $days.' d'.$hours.' h'; 
                } else if( !empty( $days ) && empty( $hours ) ) {
                    $readableHumanFormat = $days.' d'; 
                } else if( empty( $days ) && !empty( $hours ) ) {
                    $readableHumanFormat = $hours.' h'; 
                }

                $recentlyCreatedAccount['human_readable_duration'] = $readableHumanFormat; 

                $user = JFactory::getUser( $recentlyCreatedAccount['creator'] ); 

                $recentlyCreatedAccount['created_by_whom'] = $user->name; 
                
                $returnObject->data                                 = $recentlyCreatedAccount; 
                
            } else {
                
                $numberOfAccounts           = ( $numberOfAccountsValue > 1 )? $numberOfAccountsValue:1; 
                
                $numberOfCharacters         = $template->number_of_characters; 
                $prefix                     = $template->prefix; 
                $suffix                     = $template->suffix; 
                
                $recentlyCreatedAccounts            = array(); 

                /**
                 * The loop was removed because the implementation was transferred
                 * to the javascript, so that it will be possible to display 
                 * progress bars or status
                 */
//                for( $iterator = 0; $iterator < (int) $numberOfAccounts; $iterator++ ) {
                    $code = $this->getRandomCode( (int) $numberOfCharacters, 'alphanum' ); 
                    
                    $generatedCode                                      = trim( $prefix ).$code.trim( $suffix ); 
                    $query                                              = self::API_PATH.'op=user_add&api_password='.self::API_PASSWORD.'&usergroup='.str_replace( ' ', '%20', $template->plan ).'&userid='.$generatedCode.'@'.trim( $hotelProfile->hotel_id ).'&password=NOPASSWD&concurrency=1&creator='.$user->id.'&radiusattrs=Simultaneous-Use=2|ANTlabs-Expire-After='.$duration.'|Session-Timeout='.$duration.'&expiry='.$after31Days;              
                    
                    $results                                            = file_get_contents( $query );  
                    
                    $addedUserAcconts[]                                 = '<div>'.$generatedCode.'</div>'; 
                
                    $output[]                                           = $results; 
                    
                    // FETCHING RECENTLY ADDED ACCOUNT
                    $recentlyCreatedAccount                             = $this->getHSIAaccount( $generatedCode, $hotelProfile->hotel_id ); 

                    $recentlyCreatedAccount['stripped_access_code']     = removeDomainPortionFromAccount( $hotelProfile->hotel_id, $recentlyCreatedAccount['userid'] ); 
                    
                    $recentlyCreatedAccount['qos']                      = $this->getPlanDescription( str_replace( ' ', '%20', $template->plan ) ); 
                    
                    $createdOnDate                                      = date( 'M j Y g:i a', strtotime( $recentlyCreatedAccount['created_on'] ) ); 
                    $recentlyCreatedAccount['created_on']               = $createdOnDate; 
                    $recentlyCreatedAccount['first_login']              = 'N/A'; 

                    $expiryText                                         = ( !empty( $recentlyCreatedAccount[ 'expiry' ] ) )? date( 'M j Y', strtotime( $recentlyCreatedAccount['expiry'] ) ):'N/A'; 

                    $recentlyCreatedAccount['expiry_text']              = $expiryText; 

                    $duration                                           = (int) $recentlyCreatedAccount['ANTlabs-Expire-After']; 
                    
                    $duration                                           = ( $duration / 60 ); 

                    $days                                               = floor( $duration / 1440 ); 
                    $hours                                              = floor( ( $duration - $days * 1440 ) / 60 );  
                    
                    $updatedAccountsUsed                                = (int) ( $hotelProfile->accounts_used + $this->getQuotaCalculation( $duration ) ); 
                
                    $hotelProfile->accounts_used                        = $updatedAccountsUsed;     
                    
                    $readableHumanFormat = ''; 

                    if( !empty( $days ) && !empty( $hours ) ) {
                        $readableHumanFormat = $days.' d'.$hours.' h'; 
                    } else if( !empty( $days ) && empty( $hours ) ) {
                        $readableHumanFormat = $days.' d'; 
                    } else if( empty( $days ) && !empty( $hours ) ) {
                        $readableHumanFormat = $hours.' h'; 
                    }

                    $recentlyCreatedAccount['human_readable_duration'] = $readableHumanFormat; 

                    $user = JFactory::getUser( $recentlyCreatedAccount['creator'] ); 

                    $recentlyCreatedAccount['created_by_whom'] = $user->name; 
                    
                    $recentlyCreatedAccounts[] = $recentlyCreatedAccount; 
                    
//                }
                
                $returnObject->data             = $recentlyCreatedAccounts; 
            }
            
            $model                              = $this->getModel( 'settings' ); 
            
            $result                             = $model->updateProfileSetting( $hotelProfile ); 
            
            $returnObject->added_user_accounts  = $addedUserAcconts;    
            $returnObject->used_quota_value     = $hotelProfile->accounts_used; 
            
            $successMessage                     = 'Successfully created access codes.'; 
            $successMessage                     .= implode( '', $addedUserAcconts ); 
            
            $this->executeReturn( 1, $returnObject, $successMessage, 'accounts-creation-generation' ); 
            
            exit(); 
        }
        
        public function addSlideshowImage() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID            = &JRequest::getVar( 'hotel_id' ); 
            $filename           = &JRequest::getVar( 'filename' ); 
            
            $slideshowModel     = &$this->getModel( 'slideshow' ); 
            
            $lastInsertID       = $slideshowModel->addImage( $hotelID, $filename ); 
            
            $image              = new stdClass(); 
            $image->id          = $lastInsertID; 
            $image->filename    = $filename; 
            $image->hotel_id    = $hotelID; 
            
            $result             = ( $lastInsertID > 0 )? 1:0; 
            
            $tansferResult = $this->updateMedia( $hotelID, 'gallery' ); 
            
            $this->executeReturn( $result, $image, 'Successfully inserted Contents' ); 
            
            exit(); 
        }
        
        public function changePassword() {
            
            global $mainframe, $option; 
            
            if( $this->isAuthenticatedUser() ) {
                $user               = @getUser(); 

                jimport( 'joomla.application.component.model ');

                JLoader::import( 'user', 'components'.DS.'com_user'.DS.'models' ); 
                $model              = JModel::getInstance( 'user', 'UserModel' ); 
                
                $post               = &JRequest::get( 'post' ); 
                $post['password']   = &JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
                $post['password2']  = &JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);
                $post['id']         = $user->id; 
                
                unset( $post['task'] ); 
                unset( $post['option'] ); 
                unset( $post['submit'] ); 
                
                $this->validatePasswords( $post['password'], $post['password2'] ); 
                
                $message                = ''; 
                $messageType            = ''; 
                
                if ($model->store($post)) {
			$message	= JText::_( 'Your settings have been saved.' );
                        $messageType    = 'notice'; 
		} else {
                    $message            = 'there was a problem saving'; 
                    $messageType        = 'error'; 
		}
            } 
            
            $mainframe->redirect( 'index.php?option='.$option, $message, $messageType ); 
        }
        
        public function changeUserEmail() {
            
            global $mainframe, $option; 
            
            if( $this->isAuthenticatedUser() ) {
                $user               = @getUser(); 
                
                $email              = &JRequest::getVar( 'new_email' );
                
                if( filter_var ( $email, FILTER_VALIDATE_EMAIL ) ) {
                    jimport( 'joomla.application.component.model ');

                    JLoader::import( 'user', 'components'.DS.'com_user'.DS.'models' ); 
                    $model              = JModel::getInstance( 'user', 'UserModel' ); 
                    
                    $post               = array(); 
                    
                    $post['id']         = $user->id; 
                    $post['email']      = trim( $email ); 
                    
                    if ( $model->store( $post ) ) {
                        $message	= JText::_( 'Your email address has been updated.' );
                        $messageType    = 'notice'; 
                    } else {
                        $message	= JText::_( 'There was a problem updating your email address. ' );
                        $messageType    = 'error'; 
                    }
                    
                } else {
                    $message            = 'You did not enter a valid email address'; 
                    $messageType        = 'error'; 
                }
            }
            
            $mainframe->redirect( 'index.php?option='.$option, $message, $messageType ); 
        }
        
        /**
         * Checks for authenticated for ajax calls. This will return an json
         * object containing the error if user is not authenticated
         */
        private function checkAuthenticationForAjaxCall() {
            
            $returnObject = NULL; 
            
            if ( $this->isAuthenticatedUser() == TRUE ) {
                
                return TRUE; 
                
            } else {
                $returnObject = $this->getReturnData( NULL, self::AUTHENTICATION_ERROR_MESSAGE , 'You need to login to use this feature' ); 
            
                $returnObject->type = 'error'; 

                echo json_encode( $returnObject );
                
                exit(); 
            }
            
            exit(); 
        }
        
        /**
         * Checks if there is current hotel id given. 
         * If there is no hotel Id, the page is redirected back to the index. 
         * 
         * TODO: implement this better. The checking of this could be somewhere
         * to be running sitewide 
         */
        private function checkHotel() {
            global $mainframe; 
            
            $hotelID = @getHotelId(); 
            
            if( $hotelID > 0 ) {
                return TRUE; 
            } else {
                $mainframe->redirect( JURI::base(), 'Hotel ID is missing', 'error' );
            }
        }
        
        /**
         * Does the tasks of creating the logo folder based on the given HOTEL ID. 
         * If the folder already exists, it will just ignore it.
         */
        public function createFolders() {
            
            $hotelID        = &JRequest::getVar( 'hotel_id' ); 
            
            /**
             * Creating logo folder
             */
            @mkdir( JPATH_BASE.DS.'images'.DS.'logos'.DS.$hotelID, 0777 ); 
            
            /*
             * Creating slideshow folders
             */
            @mkdir( JPATH_BASE.DS.'images'.DS.'hotelguide'.DS.'gallery'.DS.'hotel_'.$hotelID, 0777 ); 
            @mkdir( JPATH_BASE.DS.'images'.DS.'hotelguide'.DS.'gallery'.DS.'hotel_'.$hotelID.DS.'album', 0777 ); 
            
            /*
             * Creating HSIA accounts imports folder 
             */
            @mkdir( JPATH_BASE.DS.'imports'.DS.'hotel_'.$hotelID, 0777 ); 
            exit(); 
        }
        
        /**
         * Executes the tasks of adding a keyword for the broadcast messages 
         */
        public function addBroadcastKeyword() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID            = &JRequest::getVar( 'hotel_id' ); 
            $keyword            = &JRequest::getVar( 'keyword' ); 
            
            $profile            = getHotelProfile( $hotelID ); 
            
            $currentKeywords    = @json_decode( $profile->broadcast_keywords ); 
            
            $keywordsToSave = array();
            
            if( !empty ( $currentKeywords ) ) {
                
                $keywordsToSave = json_decode( $profile->broadcast_keywords ); 
            } 
            
            if( !in_array( trim( $keyword ), $keywordsToSave ) ) { // CHECK if already in array
                $keywordsToSave[]   = trim( $keyword ); 
            }
            
            $newSetOfKeywords   = json_encode( $keywordsToSave ); 
            
            $profile->broadcast_keywords = $newSetOfKeywords; 
            
            $model              = &$this->getModel( 'settings' ); 
            
            $result             = $model->updateProfileSetting( $profile ); 
            
            echo $newSetOfKeywords; 
            
            // TODO: return $newSetOfKeywords
            
            
            exit(); 
        }
        
        public function deleteBroadcastKeyword() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID            = &JRequest::getVar( 'hotel_id' ); 
            $keyword            = &JRequest::getVar( 'keyword' ); 
            
            $keyword            = trim( $keyword ); 
            
            $profile            = getHotelProfile( $hotelID ); 
            
            $currentKeywords    = @json_decode( $profile->broadcast_keywords ); 
            $filteredKeywords   = array();
            
            foreach( $currentKeywords as $currentKeyword ) {
                
                if( strtolower(trim($currentKeyword)) != strtolower(trim( $keyword )) ) {
                    $filteredKeywords[] = $currentKeyword; 
                }
                    
            }
            
            $newSetOfKeywords   = json_encode( $filteredKeywords ); 
            
            $profile->broadcast_keywords = $newSetOfKeywords; 
            
            $model              = &$this->getModel( 'settings' ); 
            
            $result             = $model->updateProfileSetting( $profile ); 
            
            echo $newSetOfKeywords; 
            
            exit(); 
        }
        
        /**
         * Executes the process of deleting a broadcast tweet message
         */
        public function deleteBroadcastMessage() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $messageID          = &JRequest::getVar( 'tweet_id' ); 
            
            $hotelID            = &JRequest::getVar( 'hotel_id' ); 
            
            $settingsModel      = &$this->getModel( 'settings' ); 
            
            $hotelProfile       = $settingsModel->getProfileSettings( $hotelID ); 
            
            if( strtolower(trim($hotelProfile->broadcast_source)) === 'twitter' ) {
                
                include_once( JPATH_BASE.DS.'ANTlabsUtils'.DS.'twitteroauth'.DS.'twitteroauth.php' ); 
            
                $TwitterOAuth = new TwitterOAuth( 
                            $hotelProfile->twitter_oauth_consumer_key, 
                            $hotelProfile->twitter_oauth_consumer_secret, 
                            $hotelProfile->twitter_access_token, 
                            $hotelProfile->twitter_access_token_secret
                        ); 

                $test = $TwitterOAuth->delete( 'http://api.twitter.com/1/statuses/destroy/'.$messageID.'.json' );  
                
            } else if( strtolower(trim($hotelProfile->broadcast_source)) === 'weibo' ) {
                
                include_once( JPATH_BASE.DS.'helpers'.DS.'weibo.class.php' ); 
            
                $weibo = new weibo( $hotelProfile->weibo_key, $hotelProfile->webo_secret ); 
                
                $weibo->setUser( $hotelProfile->weibo_username, $hotelProfile->weibo_password ); 
                
                $result = $weibo->destroy( $messageID ); 
            }
            
            exit(); 
        }
        
        public function deleteChatLogs() {
            global $option, $mainframe; 
            
            $hotelID            = JRequest::getVar( 'hotel_id' ); 
            
            $user               = JFactory::getUser(); 
            $clientID           = JRequest::getVar( 'client_user_id' ); 
            
            $client             = JFactory::getUser(); 
            
            deleteChatMessages( array( $clientID, $user->id ), $hotelID ); 
            
            $mainframe->redirect( 'index.php?option='.$option ); 
        }
        
        /**
         * Exeutes the process of deleting a news content
         */
        public function deleteContents() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $newsIDs        = &JRequest::getVar( 'news_ids' ); 

            $IDs = array(); 
            $IDs = explode( ',', $newsIDs ); 
            
            $contentsModel              =&$this->getModel( 'contents' ); 
            
            $deleteResult               = $contentsModel->deleteContents( $IDs ); 
            
            $result                     = ( $deleteResult )? 1:0; 
            
            $hotelID                        = &JRequest::getVar( 'hotel_id' ); 
            
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
            
            $returnObject                   = new stdClass(); 
            $returnObject->type             = 'news'; 
            $returnObject->page_count       = $numberOfNewsPages; 
            
            $this->executeReturn( $result, $returnObject, 'Successfully deleted Contents' ); 
            
            exit(); 
        }
        
        /**
         * Executes the tasks of deleting geoxity content, called from ajax
         */
        public function deleteGeoxityContent() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $contentID          = &JRequest::getVar( 'content_id' ); 
            
            $model              = $this->getModel( 'geoxitycontents' ); 
            
            $result             = $model->deleteContent( $contentID ); 
            
            $this->executeReturn( $result, $contentID, $message );
            
            exit(); 
        }
        
        public function deleteImage() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $imageID                    = &JRequest::getVar( 'image_id' ); 
            
            $slideshowModel             = $this->getModel( 'slideshow' ); 
            
            $slideshowModel->deleteImage( $imageID ); 
            
            $image = new stdClass(); 
            $image->id = $imageID; 
           
            $this->executeReturn( 1, $image, 'Successfully deleted Image' ); 
            
            exit(); 
        }
        
        /**
         * Runs the default display 
         */
        public function display() {
            global $mainframe; 
            
            $document = &JFactory::getDocument(); 
            
            $viewName = JRequest::getVar( 'view', 'settings' ); 
            
            $viewType = $document->getType(); 
            
            $view = &$this->getView( $viewName, $viewType ); 

            // INITIALIZING MODELS: start // 
            // Settings
            $settingsModel      = &$this->getModel( 'settings' ); 
            $view->setModel( $settingsModel, TRUE ); 
            
            // Ratings
            $ratingsModel       = &$this->getModel( 'ratings' ); 
            $view->setModel( $ratingsModel ); 
            
            // Contents
            $contentsModel      = &$this->getModel( 'contents' ); 
            $view->setModel( $contentsModel ); 
            
            // Slideshow
            $slideshowModel     = &$this->getModel( 'slideshow' ); 
            $view->setModel( $slideshowModel ); 
            
            $geoxityContentsModel = &$this->getModel( 'geoxitycontents' ); 
            $view->setModel( $geoxityContentsModel ); 
            // INITIALIZING MODELS: end // 
            
            $mainframe->setTemplate( 'geoxity_hotel_manager' );      
            
            $view->setLayout( 'default' ); 
            $view->display(); 
        }
        
        /**
         * Executes the process of creating a downloadable csv file for accouns
         */
        public function downloadAccountsCsv() {
            
            $hotelID        = &JRequest::getVar( 'hotel_id' ); 
         
            $model          = $this->getModel( 'settings' ); 
            $profile        = $model->getProfileSettings( $hotelID ); 
            
            $userAccounts   = getUserAccounts( $profile ); 
            
            $dataToExport   = array(); 
            $headers        = array();
            
            $headers[]      = 'Access Code'; 
            $headers[]      = 'Created On'; 
            $headers[]      = 'Expires On'; 
            $headers[]      = 'Duration'; 
            $headers[]      = 'Created By'; 
            
            $dataToExport[]   = implode( ',', $headers ); 
            
            foreach( $userAccounts as $account ) {
                $fieldsToSave = array(); 
                
                $fieldsToSave[] = $account['stripped_access_code']; 
                $fieldsToSave[] = $account['created_on']; 
                $fieldsToSave[] = $account['expiry_text']; 
                $fieldsToSave[] = $account['human_readable_duration']; 
                $fieldsToSave[] = $account['created_by_whom']; 
                
                $dataToExport[] = implode( ',', $fieldsToSave ); 
            }
            
            $dataToWriteToFile = implode( PHP_EOL , $dataToExport ); 
            
            header('Content-Type: application/csv');
            header('Content-Disposition: csv; filename="accounts.csv"');
            header('Content-Length: ' . strlen($dataToWriteToFile));
            echo $dataToWriteToFile; 
            
            exit(); 
            
        }
        
        public function downloadChatLogs() {
            
            $hotelID            = JRequest::getVar( 'hotel_id' ); 
            
            $user               = JFactory::getUser(); 
            
            
            $clientID           = JRequest::getVar( 'client_user_id' ); 
            $client             = NULL; 
            if ( !empty( $clientID ) ) {
                $client         = JFactory::getUser( $clientID ); 
            }
            
            $chatLogs           = getChatLogs( array( '"'.$clientID.'"', '"'.$user->id.'"' ),  $hotelID ); 
            
            $clientName         = ''; 
            
            $inputtedEmail      = NULL; 
            foreach( $chatLogs as $log ) {
                if( !empty( $log->inputted_email ) ) {
                    $inputtedEmail = $log->inputted_email; 
                    
                    break; 
                }
            }
            
            $clientEmail        = ( !empty( $client->email) )? $client->email:$inputtedEmail; 
            
            if ( startsWith( $clientID, 'guest_') ) {
                /*
                 * This means that the client id is not an actual joomla user
                 * id, but a generated one, therefore the messages were sent
                 * from an offline user
                 */
                $clientName     = 'N/A'; 
            } else {
                /*
                 * This means that the client ID is an actual joomla user ID, & 
                 * the messages were sent from a logged in user.
                 */
            }
            
            $dataToWriteToFile  =  'CLIENT NAME: '.$clientName.PHP_EOL.'EMAIL: '.$clientEmail.PHP_EOL.PHP_EOL.'CHAT LOGS as of '.date( 'm-d-y' ).PHP_EOL; 
            
            foreach( $chatLogs as $log ) {
               
                if ( startsWith( $log->from, 'guest_' ) ) {
                    /*
                     * This means that the "from" attribute of the message does
                     * not contain a valid joomla user id, but a generated one 
                     * instead
                     */
                    $messageSender = $log->from; 
                    $dataToWriteToFile .= '['.$log->sent.'] '.$messageSender.' : '.$log->message.PHP_EOL;
                } else {

                    /*
                     * This means that the "from" attribute of the message contains
                     * a valid joomla user id
                     */
                    
                    $messageSender = JFactory::getUser( $log->from ); 
                    $dataToWriteToFile .= '['.$log->sent.'] '.$messageSender->name.' : '.$log->message.PHP_EOL; 
                }
            }
            
            header('Content-Type: txt/plain');
            header('Content-Disposition: attachment; filename="chat-logs.txt"');
            header('Content-Length: ' . strlen($dataToWriteToFile));
            
            echo $dataToWriteToFile; 
            
            exit(); 
        }
        
        /**
         * Executes the return process using the data to return, and the message
         */
        private function executeReturn( $result, $dataToReturn, $message, $section = NULL ) {
            $returnObject = NULL; 
            
            $dataToReturn->form_section = $section; 
            
            $message .= '<p style="font-size: 8px; margin-top: 20px;">Click here to hide this message</p>'; 
            
            if( $result == 1 ) {
                $returnObject = $this->getReturnData( $dataToReturn, self::SUCCESS_MESSAGE, $message ); 
                
            } else {
                $returnObject = $this->getReturnData( NULL, self::ERROR_MESSAGE, $message ); 
            }
            
            echo json_encode( $returnObject ); 
            
            exit(); 
        }
        
        private function updateMedia( $hotelID, $type = 'gallery' ) {           
            
            $command = ''; 
            
            if ( $type === 'gallery' ) {
                @shell_exec( 'sudo /home/joomla/html/slideshowsync '.trim( $hotelID ) ); 
            }
            
            if ( $type === 'logo' ) {
                @shell_exec( 'sudo /home/joomla/html/logosync '.trim( $hotelID ) ); 
            }
            
            return TRUE; 
        }
        
        public function updateMessageCenter() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID                        = &JRequest::getVar( 'hotel_id' ); 
            
            $ratingsModel                   = $this->getModel( 'ratings' ); 
            
            $totalRatings                   = $ratingsModel->getNewRatingsCount( $hotelID ); 
            
            $returnObject                   = new stdClass(); 
            
            $returnObject->totalRatings     = $totalRatings; 
            
            $this->executeReturn( 1, $returnObject, '' ); 
            
            exit(); 
        }
        
        public function updateNewsDisplay() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID                    = &JRequest::getVar( 'hotel_id' ); 
            $page                       = &JRequest::getVar( 'page' ); 
            
            $contentsModel              = $this->getModel( 'contents' ); 
            
            $contents                   = $contentsModel->getContents( $hotelID, self::HOTEL_SECTION_NAME, $page ); 
            
            $result                     = ( count( $contents ) > 0 )? 1:0; 
            
            $returnObject               = new stdClass(); 
            $returnObject->type         = 'news-list'; 
            $returnObject->content      = $contents; 
            
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
            
            $returnObject->page_count = $numberOfNewsPages; 
           
            $this->executeReturn( $result, $returnObject, '' ); 
            
            exit(); 
        }
        
        public function updateNewsSectionTitle() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $newsSectionTitle                       = &JRequest::getVar( 'news_section_title' ); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            
            $user                                   = @getUser(); 
            
            $model                                  = $this->getModel( 'settings' ); 
            
            $hotelProfile->id                       = $id;
            $hotelProfile->updated_by               = $user->id; 
            $hotelProfile->news_section_title       = $newsSectionTitle; 
            
            $result                                 = $model->updateProfileSetting( $hotelProfile ); 
            
            $this->executeReturn( $result, $hotelProfile, 'Section title saved.', 'news-section-title' ); 
            
            exit(); 
        }
        
        /*
         * Executes the updating of rating
         */
        public function updateRatingDisplay() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID                    = &JRequest::getVar( 'hotel_id' ); 
            $page                       = &JRequest::getVar( 'page' ); 
            $sortValue                  = &JRequest::getVar( 'sort_value' ); 
            $isRead                     = &JRequest::getVar( 'is_read' ); 
            $isUnread                   = &JRequest::getVar( 'is_unread' ); 
            $isPageUpdate               = &JRequest::getVar( 'is_page_update' ); 
            
            $wasRead                    = NULL; 
            
            if( empty( $isRead ) && empty( $isUnread ) ) {
                $wasRead = NULL; 
            } else if ( !empty( $isRead ) && empty( $isUnread ) ) {
                $wasRead = '1'; 
            } else if ( empty( $isRead ) && !empty( $isUnread ) ) {
                $wasRead = '0'; 
            }
            
            $dateRange                  = &JRequest::getVar( 'date_range' ); 
            
            $rateRangeFrom              = &JRequest::getVar( 'rate_range_from' ); 
            $rateRangeTo                = &JRequest::getVar( 'rating_rage_to' ); 
            
            $ratingsModel               = &$this->getModel( 'ratings' ); 
            $ratings                    = $ratingsModel->getData( $hotelID, $page, $sortValue, $wasRead, $dateRange, $rateRangeFrom, $rateRangeTo ); 
            
            $numberOfRatingPages            = 0; 
            $numberOfRatingsToDisplay       = 0; 
            
            if( !empty( $ratings ) ) {
                $numberOfRatingsToDisplay   = count($ratings); 
                
                $totalNumberOfRatings       = $ratingsModel->getTotalCount( $hotelID ); 
                
                $numberOfRatingPages        = ceil( $totalNumberOfRatings / $numberOfRatingsToDisplay ); 
            }
            
            $result                     = ( count($ratings) > 0 )? 1:0; 
            
            $returnObject               = new stdClass(); 
            $returnObject->type         = 'ratings-list'; 
            $returnObject->content      = $ratings; 
            
            $returnObject->page_count   = $numberOfRatingPages; 
            $returnObject->is_page_update = ( !empty( $isPageUpdate ) )?1:0; 
            
            $this->executeReturn( $result, $returnObject, '', NULL );
            
            exit(); 
        }
        
        private function getDurationInMinutes( $days, $hours, $minutes = 1 ) {
            
            $durationInMinutes          = $minutes; 
            
            if( !empty( $days ) ) {
                $durationInMinutes      += ( $days * 24 * 60 ); 
            }
            
            if( !empty( $hours ) ) {
                $durationInMinutes      += ( $hours * 60 ); 
            }
            
            return $durationInMinutes; 
            
        }
        
        private function getHSIAaccount( $userID, $hotelID ) {
            
            $query              = self::API_PATH.'op=user_get&api_password=admin&userid='.$userID.'@'.$hotelID; 
            
            $results            = file( $query ); 
            
            $accountDetails     = getConvertedNameValuePairs( $results, TRUE ); 
            $heads              = getConvertedNameValuePairs( $results, FALSE ); 
            
            $account            = array(); 
            foreach( $accountDetails as $detail  ) {
                list( $key, $value ) =  explode( ' = ', $detail[0] ); 
                
                $account[ trim( $key ) ] = trim( $value ); 
            }
            
            return $account; 
        }
        
        public function getContent() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $contentID      = &JRequest::getVar( 'content_id' ); 
            
            $model          = &$this->getModel( 'geoxitycontents' ); 

            $result        = $model->getContent( $contentID ); 
            
            
            $content            = new stdClass(); 
            $content->id        = $result->id; 
            $content->content   = $result->content; 
            $content->title     = $result->title;
            $content->section   = $result->section; 

            echo json_encode( $content ); 
            
            exit(); 
        }
        
        public function getContents() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $profileID      = &JRequest::getVar( 'profile_id' ); 
            $section        = &JRequest::getvar( 'section' ); 
            
            $model          = &$this->getModel( 'geoxitycontents' ); 
            
            $contents       = $model->getContents( $profileID, $section ); 
            
            foreach( $contents as $content ) {
                $content->date_added = date( 'd M Y H:i A', strtotime( $content->date_added ) );
            }
            
            echo json_encode( $contents ); 
            
            exit(); 
        }
        
        public function getChatMessageLogs() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $messageLogs = getChatMessageLogs(); 
            
            echo json_encode( $messageLogs ); 
            
            exit(); 
        }   
        
        public function getCurrentSupportPersonel() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID                = &JRequest::getVar( 'hotel_id' );
            
            $profile                = getHotelProfile( $hotelID ); 
            
            $currentLoggedInUser    = &JFactory::getUser(); 
            
            if ( $currentLoggedInUser->id != $profile->support_staff_id ) {
                $supportPersonnel   = &JFactory::getUser( $profile->support_staff_id ); 
                
                echo $supportPersonnel->name; 
            }
               
            /**
             *message-center-hotel-id-container 
             */
            
            exit(); 
        }
        
        private function generateAccounts( $numberOfAccounts, $duration, $numberOfCharacters, $prefix, $suffix, $user, $hotelProfile ) {
            
            $addedUserAcconts = array(); 
            
            for( $iterator = 0; $iterator < (int) $numberOfAccounts; $iterator++ ) {
                $code = $this->getRandomCode( (int) $numberOfCharacters, 'alphanum' ); 

                $generatedCode = trim( $prefix ).$code.trim( $suffix ); 
                $query = self::API_PATH.'op=user_add&api_password='.self::API_PASSWORD.'&usergroup='.self::DEFAULT_PLAN.'&userid='.$generatedCode.'@'.trim( $hotelProfile->hotel_id ).'&password=NOPASSWD&concurrency=1&duration='.$duration.'&firstname='.$user->id;              

                $results = file_get_contents( $query );  

                $addedUserAcconts[] = '<div>'.$generatedCode.'</div>'; 
            }
            
            return $addedUserAcconts; 
        }
        
        public function getNews() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $ID                         = &JRequest::getVar( 'news_id' ); 
            
            $contentsModel              = &$this->getModel( 'contents' ); 
            
            $resultObject               = $contentsModel->getContent( trim( $ID ) ); 
            
            $content                    = NULL; 
            if( !empty( $resultObject ) ) {
                
                $content = new stdClass(); 
                
                $content->id            = $resultObject->id; 
                $content->created       = $resultObject->created; 
                $content->created_by    = $resultObject->created_by; 
                $content->fulltext      = $resultObject->fulltext; 
                $content->title         = $resultObject->title; 
             
                echo json_encode( $content ); 
            }
            
            exit(); 
        }
        
        private function getPlanDescription( $planName ) {
            $userGroups = getUserAcountGroups(); 
            
            foreach( $userGroups as $key => $value ) {
                if ( $key === $planName ) {
                    return $value; 
                    break; 
                }
            }
        }
        
        /**
         * Returns a standard object containing the processed content. being PROCESSED
         * means that it undergoes saving to the database, or setting one to primary
         * or saving as draft
         * 
         * @param type $returnObject
         * @param type $contentID
         * @param type $content 
         */
        private function getProcessedContent( $profileID, $returnObject, $content, $saveOption, $section, $contentID = NULL ) {
            
            assert( !empty( $returnObject ) ); 
            assert( !empty( $content ) ); 
            
            $geoxitycontentsmodel                   = $this->getModel( 'geoxitycontents' ); 
            
            /**
             * If $contentID is NOT EMPTY, this means that the value contains
             * a content id, therefore it should be calling on update on the 
             * content of the list. Otherwise, it should be calling to add a 
             * new record to the database 
             */
            if( !empty( $contentID ) ) { 
                
                $content->id = $contentID; 
                
                if( !empty( $saveOption ) && $saveOption == 'save-publish' ) { // SAVE AND PUBLISH
                    $geoxitycontentsmodel->saveContent( $content ); 
                    $geoxitycontentsmodel->setCurrent( $contentID ); 
                    $contentToReturn = $geoxitycontentsmodel->getContent( $contentID ); 
                    
                    /**
                     * Removing the draft when save and publish
                     */
                    $draft = $geoxitycontentsmodel->getDraft( $content->profile_id, $content->section ); 
                    
                    if( !empty( $draft ) ) {
                        $geoxitycontentsmodel->deleteContent( $draft->id ); 
                    }
                    
                } else { // SAVE ONLY
                    
                    $geoxitycontentsmodel->saveContent( $content ); 
                    $contentToReturn = $geoxitycontentsmodel->getContent( $contentID ); 
                }
                
//                $returnObject->new_record_inserted = FALSE; 
                
            } else {
                /* If there is no customPageID, this means that the content to be
                 * saved is to be a new record, however, there will be a test if there 
                 * is a record that is currently on draft. If so, it shall replace
                 * it, otherwise, it will just insert the new record as draft 
                 */
                
                if( !empty( $saveOption ) && $saveOption == 'save-publish' ) {

                    $database                   = &JFactory::getDBO(); 
                    $geoxitycontentsmodel->saveContent( $content ); 
                    $lastInsertID               = $database->insertid(); 
                    $geoxitycontentsmodel->setCurrent( $lastInsertID ); 
//                    $returnObject->new_record_inserted = TRUE; 
                    $contentToReturn = $geoxitycontentsmodel->getContent( $lastInsertID ); 
                    
                    
                    /**
                     * Removing the draft when save and publish
                     */
                    $draft = $geoxitycontentsmodel->getDraft( $content->profile_id, $content->section ); 
                    
                    if( !empty( $draft ) ) {
                        $geoxitycontentsmodel->deleteContent( $draft->id ); 
                    }
                    
                } else {
                    
                    $draft = $geoxitycontentsmodel->getDraft( $profileID, $section ); 
                    if( !empty( $draft ) ) {
                        $content->id = $draft->id; 
                        $geoxitycontentsmodel->saveContent( $content ); 
                        $contentToReturn = $geoxitycontentsmodel->getContent( $content->id ); 
//                        $returnObject->new_record_inserted = FALSE; 

                    } else {
                        $database                   = &JFactory::getDBO(); 
                        $geoxitycontentsmodel->saveContent( $content ); 
                        $lastInsertID               = $database->insertid(); 
                        $contentToReturn = $geoxitycontentsmodel->getContent( $lastInsertID ); 
//                        $returnObject->new_record_inserted = TRUE; 
                    }
                }
            }
            
            $content                                = new stdClass(); 
            $content->id                            = $contentToReturn->id; 
            $content->profile_id                    = $contentToReturn->profile_id; 
            $content->section                       = $contentToReturn->section; 
            $content->title                         = @$contentToReturn->title; 
            $content->content                       = $contentToReturn->content; 
            $content->updated_by                    = $contentToReturn->updated_by; 
            $content->date_added                    = date( 'd M Y H:i A', strtotime( $contentToReturn->date_added ) ); 
            $content->is_current                    = $contentToReturn->is_current; 
            
            $returnObject->content                  = $content; 
            
            return $returnObject; 
        }
        
        private function getQuotaCalculation( $duration ) {
            $minutes    = 60; 
            $hours      = 24; 
            
            $quotaCalculation = (int) ceil( ( $duration / $minutes ) / $hours ); 
            
            return $quotaCalculation; 
        }
        
        private function getRandomCode( $length = 8, $seeds = 'alphanum' ) {

            // Possible seeds
            $seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
            $seedings['numeric'] = '0123456789';
            $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
            $seedings['hexidec'] = '0123456789abcdef';

            // Choose seed
            if (isset($seedings[$seeds]))
            {
                $seeds = $seedings[$seeds];
            }

            // Seed generator
            list($usec, $sec) = explode(' ', microtime());
            $seed = (float) $sec + ((float) $usec * 100000);
            mt_srand($seed);

            // Generate
            $str = '';
            $seeds_count = strlen($seeds);

            for ($i = 0; $length > $i; $i++)
            {
                $str .= $seeds{mt_rand(0, $seeds_count - 1)};
            }

            return $str;
        }
        
        /**
         * Returns an object representing a return data
         */
        private function getReturnData( $data, $messageType, $message ) {
            
            $returnData                 = new stdClass(); 
            
            $returnData->data           = $data; 
            $returnData->messageType    = $messageType; 
            $returnData->message        = $message; 
            
            return $returnData; 
        }
        
        public function getUpdatedQuotaUsage() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID                    = &JRequest::getVar( 'hotel_id' ); 
            
            $profile                    = getHotelProfile( $hotelID ); 
            
            echo trim( $profile->accounts_used ); 
            
            exit(); 
        }
        
        public function importUserAccounts() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID        = &JRequest::getVar( 'hotel_id' ); 
            
            $settingsModel  = &$this->getModel( 'settings' ); 
            $hotelProfile   = $settingsModel->getProfileSettings( $hotelID ); 
            
            
            $filePath       = &JRequest::getVar( 'file_path' ); 

            $fileContents   = file_get_contents(  JPATH_BASE.DS.$filePath ); 
            
            $userAccounts   = explode( ',' , $fileContents ); 
            
            foreach( $userAccounts as $code ) {
                $query = self::API_PATH.'op=user_add&api_password='.self::API_PASSWORD.'&usergroup=Unlimited&uid='.$code.'@'.trim( $hotelProfile->hotel_id ).'&password=NOPASSWD';
                
                $results = file_get_contents( $query );  
                
                // TODO: implement return
                
            }
                
            exit(); 
        }
        
        public function initializeFolders() {
            
            echo "folders initialized"; 
            exit(); 
        }
        
        /**
         * Checks for user authentication, and redirects user to the login 
         * page if the user has not registered
         */
        private function isAuthenticatedUser() {
            global $mainframe, $option; 
            
            $user = NULL; 
            $user = @getUser(); 
            
            if ( !empty( $user ) ) {
                return TRUE; 
            }
            
            $mainframe->redirect( 'index.php?option='.$option ); 
            
            return FALSE; 
        }
        
        public function previewNews() {
            
            $viewName = 'showcontent'; 
            
            $document = &JFactory::getDocument(); 
            
            $viewType = $document->getType(); 
            
            $view = &$this->getView( $viewName, $viewType ); 
            
            $contentsModel      = &$this->getModel( 'contents' ); 
            
            $result =  $view->setModel( $contentsModel, TRUE ); 
            
            $view->setLayout( 'default' ); 
            $view->display(); 
        }
        
        /**
         * Updates the greeting message for the greeting module
         */
        public function updateGreeting() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            $greeting                               = &JRequest::getVar( 'greeting' ); 
            
            $user                                   = @getUser(); 

            $hotelProfile                           = new stdClass(); 
            $hotelProfile->id                       = $id; 
            $hotelProfile->greeting                 = trim( $greeting ); 
            $hotelProfile->updated_by               = $user->id; 
            
            $model                                  = $this->getModel( 'settings' ); 

            $result                                 = $model->updateProfileSetting( $hotelProfile ); 
            
            $this->executeReturn( $result, $hotelProfile, 'Site greeting has been updated', 'settings-greeting' ); 
            
            exit(); 
        }
        
        /**
         * Sends the broadcast message to the twitter account configured for it
         */
        public function sendBroadcastMessage() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $message                                = &JRequest::getVar( 'broadcastMessage' ); 
            $keywords                               = &JRequest::getVar( 'keywords' ); 
            
            $model                                  = $this->getModel( 'settings' ); 
            $profile                                = $model->getProfileSettings( $hotelID ); 
            
            if( !empty( $keywords ) ) {
                
                $keywordsContainer = explode( ',', $keywords ); 
                
                foreach($keywordsContainer as $keyword) {
                    if( $profile->broadcast_source == 'twitter' ) {
                        
                        $message .= '#'.trim( $keyword ); 
                    
                    } else if ( $profile->broadcast_source == 'weibo' ) {
                        $message .= '#'.trim( $keyword ).'# '; 
                    }
                }
            }
            
            if( strtolower( trim( $profile->broadcast_source ) ) === 'twitter' ) {
                
                $this->sendTwitterBroadcast( $profile, $message ); 
                
            } else if ( strtolower( trim( $profile->broadcast_source ) ) === 'weibo' ) {
                
                $this->sendWeiboBroadcast( $profile, $message ); 
                
            }
            
            exit(); 
        }
        
        /**
         * Does the tasks of sending / posting a twitter status update for 
         * broadcast
         * 
         * 
         * @param           Object      $profile        Profile object
         * @param           String      $message        Message to be sent
         */
        private function sendTwitterBroadcast( $profile, $message ) {
            assert( !empty( $profile ) ); 
            
            include_once( JPATH_BASE.DS.'ANTlabsUtils'.DS.'twitteroauth'.DS.'twitteroauth.php' ); 
            
            $TwitterOAuth = new TwitterOAuth( 
                        $profile->twitter_oauth_consumer_key, 
                        $profile->twitter_oauth_consumer_secret, 
                        $profile->twitter_access_token, 
                        $profile->twitter_access_token_secret
                    ); 
            
             
            $response = $TwitterOAuth->post( 'https://api.twitter.com/1/statuses/update.json', array( 'status' => $message ) ) ; 
            
            if( !empty( $response->id_str ) ) {
                
                $tweet                  = new stdClass(); 
                $tweet->id              = $response->id_str; 
                $tweet->message         = $message; 
                $tweet->created_at      = date( 'd-m-Y H:i a' ); 
                
                $returnObject           = new stdClass(); 
                
                $returnObject->type     = 'tweet'; 
                $returnObject->content  = $tweet; 
                
                $this->executeReturn( 1, $returnObject, 'Broadcast message submitted', 'broadcast-page-container' );
            } else {
                $this->executeReturn( 0, NULL, 'There was a problem submitting your broadcast message, try again.', 'broadcast-page-container' );
            }
            
            exit(); 
        }
        
        /**
         * Does the task of posting a weibo status update for broadcast
         * @param       Object      $profile 
         */
        private function sendWeiboBroadcast( $profile, $message ) {
            
            include_once( JPATH_BASE.DS.'helpers'.DS.'weibo.class.php' ); 
            
            $weibo = new weibo( $profile->weibo_key, $profile->webo_secret ); 
            
            $weibo->setUser( $profile->weibo_username, $profile->weibo_password ); 

            $result = $weibo->update( $message ); 
            
//            if ( !empty( $result->id ) ) {
                
                $feed                       = new stdClass(); 
                
                $feed->id                   = $result->id; 
                $feed->message              = $result->text; 
                $feed->created_at           = $result->created_at; 
                
                $returnObject               = new stdClass(); 
                
                $returnObject->type         = 'tweet'; 
                $returnObject->content      = $feed; 
                
                $this->executeReturn( 1, $returnObject, 'Broadcast message submitted', 'broadcast-page-container' );
                
//            } else {
//                
//                $this->executeReturn( 0, NULL, 'Broadcast message submitted', 'broadcast-page-container' );
//                
//            }
            
            exit(); 
            
        }
        
        public function setToPrimary() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $contentID                  = &JRequest::getVar( 'content_id' ); 
            $profileID                  = &JRequest::getVar( 'profile_id' ); 
            $model                      = &$this->getModel( 'geoxitycontents' ); 
            
            $content                    = $model->getContent( $contentID ); 
            
            $model->setCurrent( $contentID ); 
            
//            $contents                   = $model->getContents( $profileID, $content->section ); 
            
            $returnObject               = new stdClass(); 
//            $returnObject->contents     = $contents; 
            
            $returnObject->profile_id   = $content->profile_id; 
            $returnObject->section      = $content->section; 
            
            $section                    = NULL; 

            if( $content->section == self::CUSTOMIZABLE_PAGE ) {
                $section = 'customizable-page-page-container'; 
            }
            
            if ( $content->section == self::HOTEL_INFO ) {
                $section = 'settings-hotel-info'; 
            }
            
            if ( $content->section == self::FACILITIES_CONTENT ) {
                $section = 'settings-facilities-content'; 
            }
            
            $this->executeReturn( 1, $returnObject, 'Content updated', $section );
            
            exit(); 
        }
        
        /*
         * Executes the process of submitting news as joomla content
         */
        public function submitNews() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $news                                   = &JRequest::getVar( 'news', '', '', '', JREQUEST_ALLOWHTML ); 
            $title                                  = &JRequest::getVar( 'title' ); 
            $user                                   = @getUser(); 
            $contentID                              = &JRequest::getVar( 'content_id' );
            
            $contentModel                           = &$this->getModel( 'Contents' ); 
            
            $category                               = $contentModel->getCategory( $hotelID ); 
            
            $content                                = new stdClass(); 
            $content->id                            = $contentID; 
            $content->title                         = trim( $title ); 
            $content->fulltext                      = trim( $news ); 
            $content->created                       = date( 'Y-m-d H:i:s' ); 
            $content->created_by                    = $user->id; 
            $content->catid                         = $category->id; 
            $content->state                         = 1; 
            
            $result                                 = $contentModel->saveContent( $content ); 
            
            if( empty( $content->id ) || $content->id == 0 ) {
                
                $database = JFactory::getDBO(); 
                
                $content->id = $database->insertid(); 
                
                $content->insertion_type = 'add'; 
            } else {
                $content->insertion_type = 'update'; 
            }
            
            $returnObject                           = new stdClass(); 
            $returnObject->type                     = 'news'; 
            $returnObject->content                  = $content; 
            
            $this->executeReturn( $result, $returnObject, 'News Saved successfully', NULL ); 
            
            exit(); 
        }
        
        public function togglePublish() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $id             = &JRequest::getVar( 'id' ); 
            $content        = &JRequest::getVar( 'content' ); 
            $hotelID        = &JRequest::getVar( 'hotel_id' ); 
            
            $settingsModel  = &$this->getModel( 'settings' ); 
            
            $hotelProfile   = $settingsModel->getProfileSettings( $hotelID ); 
            
            $returnObject               = new stdClass(); 
            
            switch( $content ) {
                case 'customizable page':
                    
                        if( $hotelProfile->custom_page_published == 1 ) {
                            $hotelProfile->custom_page_published = 0; 
                        } else {
                            $hotelProfile->custom_page_published = 1; 
                        }

                        @$settingsModel->updateProfileSetting( $hotelProfile ); 
                        
                        $returnObject->type         = 'customizable page'; 
                        $returnObject->published    = $hotelProfile->custom_page_published; 
                    
                    break; 
                
                case 'hotel info': 
                    
                        if( $hotelProfile->hotel_info_published == 1 ) {
                            $hotelProfile->hotel_info_published = 0; 
                        } else {
                            $hotelProfile->hotel_info_published = 1; 
                        }

                        @$settingsModel->updateProfileSetting( $hotelProfile ); 
                        
                        $returnObject->type         = 'hotel info'; 
                        $returnObject->published    = $hotelProfile->hotel_info_published; 
                        
                    break; 
                    
                case 'facilities content':
                    
                        if( $hotelProfile->facilities_content_published == 1 ) {
                            $hotelProfile->facilities_content_published = 0; 
                        } else {
                            $hotelProfile->facilities_content_published = 1; 
                        }

                        @$settingsModel->updateProfileSetting( $hotelProfile ); 
                        
                        $returnObject->type         = 'facilities content'; 
                        $returnObject->published    = $hotelProfile->facilities_content_published; 
                        
                    break; 
                    
                case 'news':
                    
                        list( $prefix, $newsID ) = explode( '_' , $id ); 
                    
                        $contentModel      = &$this->getModel( 'contents' ); 

                        $newsItem          = $contentModel->getContent( $newsID ); 
                        
                        if ( $newsItem->state == 1 ) {
                            $newsItem->state = 0; 
                        } else {
                            $newsItem->state = 1; 
                        }
                        
                        $contentModel->saveContent( $newsItem ); 
                        
                        $returnObject->type         = 'news'; 
                        $returnObject->published    = $newsItem->state; 
                        $returnObject->newsItemID   = $newsItem->id;  
                    break; 
                    
                default:
                    break; 
            }
            
            echo json_encode( $returnObject ); 

            exit(); 
        }
        
        public function toggleRating() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $ratingID                       = &JRequest::getVar( 'rating_id' ); 
            
            $hotelID                        = &JRequest::getvar( 'hotel_id' ); 
            
            $ratingsModel                   = &$this->getModel( 'ratings' ); 
            
            $rating                         = $ratingsModel->getRating( $ratingID ); 
            
            $currentReadValue               = $rating->was_read; 
            
            $rating->was_read               = ( (int) $currentReadValue == 1 )? 0:1; 
            
            $ratingsModel->saveRating( $rating ); 
            
            $returnObject                   = new stdClass(); 
            
            $returnObject->totalNewRatings  = $ratingsModel->getNewRatingsCount( $hotelID ); 
            
            $this->executeReturn( 1 , $returnObject, '' ); 
            
            exit(); 
        }
        
        public function togglefSupportChatReceiver() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $enable     = &JRequest::getVar( 'enable' ); 
            
            $hotelID    = &JRequest::getVar( 'hotel_id' ); 
            
            $profile    = getHotelProfile( $hotelID ); 
            
            $model      = $this->getModel( 'settings' ); 
            
            $user       = &JFactory::getUser(); 
            
            $IDtoReturn = 0; 
            
            if( $enable === '1' ) {
                
                $profile->support_staff_id = $user->id;  
                
                $IDtoReturn = $user->id; 
                
            } else {
                
                if ( $profile->support_staff_id == $user->id ) {
                    
                    $profile->support_staff_id = 0; 
                    
                }
            }
            
            $result     = $model->updateProfileSetting( $profile ); 
            
            echo $IDtoReturn; 
            
            exit(); 
        }
        
        public function updateAccountsTemplate() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $user                                   = @getUser(); 
            $id                                     = &JRequest::getVar( 'id' ); 
            
            $plan                                   = &JRequest::getVar( 'plan' ); 
            
            $characters                             = &JRequest::getVar( 'characters' );
            $durationInDays                         = &JRequest::getVar( 'duration_days' ); 
            $durationInHours                        = &JRequest::getVar( 'duration_hours' ); 
            $prefix                                 = &JRequest::getVar( 'prefix' );
            $suffix                                 = &JRequest::getVar( 'suffix' );
            
            $max                                    = count( explode( '|', $characters ) );  
            
            $templates                              = array(); 
            
            for( $iterator = 0; $iterator < $max; $iterator++ ) {
                $template                           = new stdClass(); 
                
                $planContainer                      = explode( '|', $plan ); 
                $charactersContainer                = explode( '|', $characters ); 
                $daysContainer                      = explode( '|', $durationInDays ); 
                $hoursContainer                     = explode( '|', $durationInHours ); 
                $prefixContainer                    = explode( '|', $prefix ); 
                $suffixContianer                    = explode( '|', $suffix ); 
                
                $template->days_value               = $daysContainer[$iterator]; 
                $template->hours_value              = $hoursContainer[$iterator]; 
                
                $duration                           = $this->getDurationInMinutes( $daysContainer[$iterator], $hoursContainer[$iterator], NULL ); 
                
                $template->duration_in_minutes      = ( !empty( $duration ) || $duration != 0 )? $duration:1; 
                $template->number_of_characters     = $charactersContainer[$iterator]; 
                
                $template->prefix                   = $prefixContainer[$iterator]; 
                $template->suffix                   = $suffixContianer[$iterator]; 
                
                $template->plan                     = $planContainer[$iterator]; 
                
                $templates[]                        = $template; 
            }
            
            $hotelProfile->id                       = $id; 
            $hotelProfile->updated_by               = $user->id; 
            $hotelProfile->accounts_template        = json_encode( $templates ); 
            
            $model                                  = $this->getModel( 'settings' ); 
            
            $result                                 = $model->updateProfileSetting( $hotelProfile ); 
            
            /*
             * $result                     = $slideshowModel->updateImage( $image ); 
            
            $this->executeReturn( $result, $image, 'Successfully updated image caption', 'settings-slideshow' ); 
             * 
             */
            
            $this->executeReturn( $result, $hotelProfile, 'Successfully updated template', 'accounts-template' ); 
            exit(); 
        }
        
        public function updateCaption() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $imageID                    = &JRequest::getVar( 'image_id' ); 
            $caption                    = &JRequest::getVar( 'caption' ); 
            
            $image                      = new stdClass(); 
            
            $image->id                  = $imageID; 
            $image->description         = $caption; 
            
            $slideshowModel             = $this->getModel( 'slideshow' ); 
            
            $result                     = $slideshowModel->updateImage( $image ); 
            
            $this->executeReturn( $result, $image, 'Successfully updated image caption', 'settings-slideshow' ); 
            
            exit(); 
        }
        
        /**
         * Calls to update the customizable page
         */
        public function updateCustomPage() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            
            $contentID                              = &JRequest::getVar( 'content_id' ); 
            $customPageTitle                        = &JRequest::getVar( 'page_title' ); 
            $customPageContent                      = &JRequest::getVar( 'page_content', '', '', 'STRING', JREQUEST_ALLOWHTML ); 
            $contentID                              = &JRequest::getVar( 'content_id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $saveOption                             = &JRequest::getVar( 'save_option' ); 
            
            $user                                   = @getUser(); 
            
            $content                                = new stdClass(); 
            
            $content->profile_id                    = $id; 
            $content->section                       = self::CUSTOMIZABLE_PAGE; 
            $content->title                         = $customPageTitle; 
            $content->content                       = $customPageContent; 
            $content->updated_by                    = $user->id; 
            $content->hotel_id                      = $hotelID; 
            $content->date_added                    = date( 'Y-m-d H:i' ); 
            
            $returnObject                           = new stdClass(); 
            
            if( empty( $contentID ) ) {
                $content->date_added                = date( 'Y-m-d H:i:s' ); 
            }
            
            $returnObject->type                     = 'customizable page'; 
            
            $returnObject                           = $this->getProcessedContent( $id, $returnObject, $content, $saveOption, self::CUSTOMIZABLE_PAGE, $contentID ); 
            
            $this->executeReturn( 1, $returnObject, 'A new version of your content has been added', 'pages-custom-page' ); 

            exit(); 
        }
        
        /**
         * Calls to execute the updating of hotel facilites
         */
        public function updateFacilities() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $facilities                             = &JRequest::getVar( 'facilities' ); 
            
            $user                                   = @getUser(); 
            
            $hotelProfile                           = new stdClass(); 
            $hotelProfile->id                       = $hotelID;
            $hotelProfile->updated_by               = $user->id; 
            $hotelProfile->facilities               = trim( $facilities ); 
            
            $model                                  = $this->getModel( 'settings' ); 

            $result                                 = $model->updateHotelGuideProfile( $hotelProfile ); 
            
            $this->executeReturn( $result, $hotelProfile, 'Hotel facilites updated', 'settings-facilities' ); 
            
            exit(); 
        }
        
        public function updateFacilitiesContent() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $facilitiesContent                      = &JRequest::getVar( 'facilities_content', '', '', '', JREQUEST_ALLOWHTML  ); 
            $contentID                              = &JRequest::getVar( 'content_id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $id                                     = &JRequest::getVar( 'id' ); 
            $saveOption                             = &JRequest::getVar( 'save_option' ); 
            
            $user                                   = @getUser(); 
            
            $content                                = new stdClass(); 
            $content->profile_id                    = $id; 
            $content->content                       = $facilitiesContent; 
            $content->section                       = self::FACILITIES_CONTENT; 
            $content->updated_by                    = $user->id; 
            $content->hotel_id                      = $hotelID; 

            $returnObject                           = new stdClass(); 
            
            if( empty( $contentID ) ) {
                $content->date_added                = date( 'Y-m-d H:i:s' ); 
            }
            
            $returnObject->type                     = 'facilities content'; 
            
            $returnObject                           = $this->getProcessedContent( $id, $returnObject, $content, $saveOption, self::FACILITIES_CONTENT ,$contentID );
            
            $this->executeReturn( 1, $returnObject, 'Facilities content page saved.', 'settings-facilities-content' ); 
            
            exit(); 
        }
        
        public function updateHotelAlias() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $hotelAlias                             = &JRequest::getVar( 'alias' ); 
            
            $user                                   = @getUser(); 

            $hotelProfile                           = new stdClass(); 
            $hotelProfile->id                       = $hotelID; 
            $hotelProfile->alias                    = $hotelAlias; 
            
            $model                                  = @$this->getModel( 'settings' ); 

            $result                                 = @$model->updateHotelGuideProfile( $hotelProfile ); 

            $this->executeReturn( $result, $hotelProfile, 'Site info has been updated' ); 
            
            exit(); 
        }
        
        public function updateHotelLogo() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $fileName                               = &JRequest::getVar( 'filename' ); 
            
            $user                                   = @getUser(); 
            
            $hotelProfile->id                       = $id; 
            $hotelProfile->updated_by               = $user->id; 
            $hotelProfile->logo                     = $fileName; 
            $hotelProfile->hotel_id                 = $hotelID; 
            
            $this->updateMedia( $hotelID, 'logo' ); 
            
            $model                                  = $this->getModel( 'settings' ); 
            $result                                 = $model->updateProfileSetting( $hotelProfile ); 
            
            $this->executeReturn( $result, $hotelProfile, 'Successfully updated hotel logo', 'settings-hotel-logo'); 
            
            exit(); 
        }
        
        public function updateHSIAAccountsList() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $page                                               = &JRequest::getVar( 'page' ); 
            $id                                                 = &JRequest::getvar( 'id' ); 
            $hotelID                                            = &JRequest::getVar( 'hotel_id' ); 
            $userID                                             = &JRequest::getVar( 'user_id' ); 
            $created_on                                         = &JRequest::getVar( 'created_on' ); 
            $user_group                                         = &JRequest::getVar( 'user_group' ); 
            $expires_on                                         = &JRequest::getVar( 'expires_on' ); 
            $first_login                                        = &JRequest::getVar( 'first_login' ); 
            $order_by                                           = &JRequest::getVar( 'order_by' ); 
            $sort_order                                         = &JRequest::getVar( 'sort_order' ); 
            
            $profile                                            = getHotelProfile( trim( $hotelID ) ); 
            
            $pageToView                                         = ( !empty( $page ) )? $page:1; 
            
            if( $pageToView == 1 ) {
                $pageToView = 0; 
            }
            
            $searchParameters                                   = array(); 
            $searchParameters['userID']                         = $userID; 
            $searchParameters['order_by']                       = trim( $order_by ); 
            $searchParameters['sort_order']                     = trim( $sort_order ); 

            // CREATED ON
            list( $createdOnStart, $createdOnEnd )              = explode( '-', $created_on ); 
            
            $dateString                                         = ''; 
            
            if ( !empty( $createdOnStart ) ) {
                
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $createdOnStart ) ) ) ;
                $searchParameters['createdOnStart']             = str_replace( ' ', '%20', $dateString ); 
                
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $createdOnStart.' 23:59' ) ) ) ;
                $searchParameters['createdOnEnd']               = str_replace( ' ', '%20', $dateString ); 
            }
            
            if ( !empty( $user_group ) ) {
                $searchParameters['userGroup']                  = trim( $user_group ); 
            }   
            
            if ( !empty( $createdOnStart ) && !empty( $createdOnEnd ) ) {
                
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $createdOnEnd.' 23:59' ) ) );
                $searchParameters['createdOnEnd']               = str_replace( ' ', '%20', $dateString ); 
            }
            
            // EXPIRES ON
            list( $expiresOnStart, $expiresOnEnd )              = explode( '-', $expires_on );       
            if ( !empty( $expiresOnStart ) ) {
                
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $expiresOnStart ) ) );
                $searchParameters['expiresOnStart']             = str_replace( ' ', '%20', $dateString );  
                
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $expiresOnStart.' 23:59' ) ) ); 
                $searchParameters['expiresOnEnd']               = str_replace( ' ', '%20', $dateString ); 
            }
            
            if( !empty( $expiresOnStart ) && !empty( $expiresOnEnd ) ) {
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $expiresOnEnd.' 23:59' ) ) ); 
                $searchParameters['expiresOnEnd']               = str_replace( ' ', '%20', $dateString ); 
            }
            
            // FIRST LOGIN
            list( $firstLoginStart, $firstLoginEnd )            = explode( '-', $first_login ); 
            if ( !empty( $firstLoginStart ) ) {
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $firstLoginStart ) ) ); 
                $searchParameters['firstLoginStart']            = str_replace( ' ', '%20', $dateString ); 
                
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $firstLoginStart.' 23:59' ) ) ); 
                $searchParameters['firstLoginEnd']              = str_replace( ' ', '%20', $dateString );
            }
            
            if ( !empty( $firstLoginStart )  && !empty( $firstLoginEnd ) ) {
                
                $dateString                                     = date( 'Y-m-d H:i:s', strtotime( trim( $firstLoginEnd.' 23:59' ) ) ); 
                
                $searchParameters['firstLoginEnd']              = str_replace( ' ', '%20', $dateString ); 
            }
            
            $returnObject                                       = new stdClass(); 
            
            $userAccounts                                       = getUserAccounts( $profile, 50, $pageToView, $searchParameters ); 
            
            $numberOfAcountsReturned                            = getUserAccountsCount( $profile, $searchParameters); 
            
            $returnObject->number_of_accounts_matched           = $numberOfAcountsReturned; 
            $returnObject->user_accounts = $userAccounts; 
            $returnObject->page_count = getHSIAAccountsListPages( $numberOfAcountsReturned ); 
            
            echo json_encode( $returnObject ); 
            
            exit(); 
        }
        
        /**
         * Updates hotel info on hotelguide database
         */
        public function updateHotelInfo() {
            
            $this->checkAuthenticationForAjaxCall(); 
            
            $id                                     = &JRequest::getVar( 'id' ); 
            $hotelID                                = &JRequest::getVar( 'hotel_id' ); 
            $hotelInfo                              = &JRequest::getVar( 'hotel_info', '', '', '', JREQUEST_ALLOWHTML ); 
            $hotelAlias                             = &JRequest::getVar( 'hotelAlias' ); 
            $contentID                              = &JRequest::getVar( 'content_id' ); 
            $saveOption                             = &JRequest::getVar( 'save_option' ); 
            
            $user                                   = @getUser(); 

            $hotelProfile                           = new stdClass(); 
            $hotelProfile->id                       = $hotelID; 
            $hotelProfile->intro                    = $hotelInfo; 
            
            $model                                  = @$this->getModel( 'settings' ); 
            $result                                 = @$model->updateHotelGuideProfile( $hotelProfile ); 
            
            $content                                = new stdClass(); 
            
            $content->profile_id                    = $id; 
            $content->hotel_id                      = $hotelID; 
            $content->section                       = self::HOTEL_INFO;
            $content->content                       = $hotelInfo; 
            $content->updated_by                    = $user->id; 
            
            $returnObject                           = new stdClass(); 
            
            if( empty( $contentID ) ) {
                $content->date_added                = date( 'Y-m-d H:i:s' ); 
            }
            
            
            $returnObject->type                     = 'hotel info'; 
            
            $returnObject                           = $this->getProcessedContent( $id, $returnObject, $content, $saveOption, self::HOTEL_SECTION_NAME ,$contentID ); 

            $this->executeReturn( 1, $returnObject, 'Site info has been updated', 'settings-hotel-info' ); 
            
            exit(); 
        }
        
        /**
         * Executes the processs of updating a hotel's social media settings
         */
        public function updateSocialMediaSettings() {
            
            $this->checkAuthenticationForAjaxCall(); 

            $facebook                               = &JRequest::getVar( 'facebook' ); 
            $twitter                                = &JRequest::getVar( 'twitter' ); 
            $linkedin                               = &JRequest::getVar( 'linkedin' ); 
            $enabled                                = &JRequest::getVar( 'social_media_enabled' ); 
            $id                                     = &JRequest::getVar( 'id' ); 
            
            $user                                   = @getUser(); 
            
            $hotelProfile                           = new stdClass(); 

            $hotelProfile->id                       = $id; 
            $hotelProfile->twitter                  = $twitter;
            $hotelProfile->facebook                 = $facebook; 
            $hotelProfile->linkedin                 = $linkedin; 
            $hotelProfile->social_media_enabled     = $enabled; 
            $hotelProfile->updated_by               = $user->id; 

            $model                                  = $this->getModel( 'settings' ); 

            $result                                 = $model->updateProfileSetting( $hotelProfile ); 
            
            $this->executeReturn( $result, $hotelProfile, 'Social media settings updated', 'settings-social-media' ); 

            exit(); 
        }
        
        public function updateStaffCredentials() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $hotelID                        = &JRequest::getVar( 'hotel_id' ); 
            $collaboratorType               = &JRequest::getVar( 'collaborator_type' ); 
            $username                       = &JRequest::getVar( 'username' ); 
            $password                       = &JRequest::getVar( 'password' );
            
            $model                          = &$this->getModel( 'settings' ); 
            
            $hotelProfile                   = $model->getProfileSettings( $hotelID );
            
            $userID                         = NULL; 
            
            switch( $collaboratorType )  {
                case 'admin':
                        $userID = $hotelProfile->admin; 
                    break; 
                case 'staff-1':
                        $userID = $hotelProfile->staff_1;     
                    break; 
                case 'staff-2':
                        $userID = $hotelProfile->staff_2;     
                    break; 
                case 'staff-3':
                        $userID = $hotelProfile->staff_3;     
                    break; 
            }
            
            $userToUpdate                  = & JFactory::getUser( $userID ); 
            
            $result = $model->updateCollaboratorCredentials( $userToUpdate->id, $username, $password ); 
            
            echo $result; 
            
            
            exit(); 
        }
        
        /**
         * Executes the process of updating the hotel's preferred weather location
         */
        public function updateWeather() {
            $this->checkAuthenticationForAjaxCall(); 
            
            $weather                                = &JRequest::getVar( 'weather' ); 
            $weather_enabled                        = &JRequest::getVar( 'weather_enabled' ); 
            $id                                     = &JRequest::getVar( 'id' ); 
            
            $user                                   = @getUser(); 
            
            $hotelProfile                           = new stdClass(); 

            $hotelProfile->id                       = $id; 
            $hotelProfile->weather_enabled          = $weather_enabled; 
            $hotelProfile->weather_location         = $weather;
            $hotelProfile->updated_by               = $user->id; 
            
            $model                                  = $this->getModel( 'settings' ); 

            $result                                 = $model->updateProfileSetting( $hotelProfile ); 
            
            $this->executeReturn( $result, $hotelProfile, 'Weather location updated', 'settings-weather' ); 

            exit(); 
        }
        
        private function validatePasswords( $password1, $password2 ) {
            
            global $mainframe, $option; 
            
            if( empty( $password1 ) || empty( $password2 ) ) {
                $mainframe->redirect( 'index.php?option='.$option, 'You must put values to both password and cofirmation fields', 'error'); 
            }
            
            if( $password1 != $password2 ) {
                $mainframe->redirect( 'index.php?option='.$option, 'Passwords do not match', 'error' ); 
            }
            
            return TRUE; 
        }
        
        public function viewCustomPage() {
            $viewName = 'custompage'; 
            
            $document = &JFactory::getDocument(); 
            
            $viewType = $document->getType(); 
            
            $view = &$this->getView( $viewName, $viewType ); 
            
            $settingsModel      = &$this->getModel( 'settings' ); 
            $view->setModel( $settingsModel, TRUE ); 
            
            $view->setLayout( 'default' ); 
            $view->display( ); 
        }
        
        public function viewFacilities() {
            $viewName = 'facilities'; 
            
            $document = &JFactory::getDocument(); 
            
            $viewType = $document->getType(); 
            
            $view = &$this->getView( $viewName, $viewType ); 
            
            $settingsModel      = &$this->getModel( 'settings' ); 
            $facilitiesModel    = &$this->getModel( 'facilities' ); 
            
            $view->setModel( $settingsModel, TRUE ); 
            $view->setModel( $facilitiesModel, TRUE ); 
            
            $view->setLayout( 'default' ); 
            $view->display(); 
        }
        
        public function viewHotelInfo() {
            $viewName = 'hotelinfo'; 
            
            $document = &JFactory::getDocument(); 
            
            $viewType = $document->getType(); 
            
            $view = &$this->getView( $viewName, $viewType ); 
            
            $settingsModel      = &$this->getModel( 'settings' ); 
            $view->setModel( $settingsModel, TRUE ); 
            
            $view->setLayout( 'default' ); 
            $view->display(); 
        }
    }
?>
