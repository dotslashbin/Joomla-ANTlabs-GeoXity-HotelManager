<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' );
    
    class TableProfilesettings extends JTable {
        
        var $id                                 = NULL; 
        var $hotel_id                           = NULL; 
        var $account_plan                       = NULL; 
        var $hotel_account_status               = NULL; 
        var $gateway                            = NULL; 
        var $theme                              = NULL; 
        var $logo                               = NULL; 
        var $greeting                           = NULL; 
        var $news_limit                         = NULL;
        var $broadcast_source                   = NULL; 
        var $news_section_title                 = NULL; 
        var $broadcast_screen_name              = NULL; 
        var $broadcast_keywords                 = NULL; 
        var $facilities_content                 = NULL; 
        var $twitter_access_token               = NULL; 
        var $twitter_access_token_secret        = NULL; 
        var $twitter_oauth_consumer_key         = NULL; 
        var $twitter_oauth_consumer_secret      = NULL; 
        var $twitter                            = NULL; 
        var $facebook                           = NULL; 
        var $linkedin                           = NULL;
        var $weibo_nickname                     = NULL; 
        var $weibo_username                     = NULL; 
        var $weibo_password                     = NULL;
        var $weibo_key                          = NULL;
        var $weibo_secret                       = NULL; 
        var $social_media_enabled               = NULL; 
        var $weather_enabled                    = NULL; 
        var $weather_location                   = NULL; 
        var $custom_page_title                  = NULL; 
        var $custom_page_content                = NULL; 
        var $custom_page_published              = NULL; 
        var $hotel_info_published               = NULL; 
        var $facilities_content_published       = NULL; 
        var $accounts_domain                    = NULL; 
        var $accounts_quota                     = NULL; 
        var $accounts_used                      = NULL;     
        var $accounts_template                  = NULL; 
        var $super_admin                        = NULL; 
        var $admin                              = NULL; 
        var $staff_1                            = NULL; 
        var $staff_2                            = NULL; 
        var $staff_3                            = NULL;
        var $support_chat_enabled               = NULL; 
        var $support_staff_id                   = NULL; 
        var $manager_id                         = NULL; 
        var $last_update                        = NULL; 
        var $updated_by                         = NULL; 
        
        public function __construct( &$database ) {
            
            parent::__construct( '#__geoxity_hotel_profile', 'id', $database );
        }
    }
?>