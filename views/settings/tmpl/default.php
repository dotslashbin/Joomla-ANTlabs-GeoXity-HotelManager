<?php 
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    include_once( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'versions-common-buttons.php' ); 
    
    /**
     * NOTES: 
     * 
     * When running on live, change the index of the $this->messages variable to "1", and make sure that
     * the "action" value of the forms for changing the email and passwords is set to none or blank
     */
    $currentMessage             = ''; 
    $currentMessageType         = ''; 
    if( !empty( $this->messages ) ) {
        
        $currentMessage         = $this->messages[1]['message']; // TODO: on live, change index to 1
        $currentMessageType     = $this->messages[1]['type'];    // TODO: on live change index to 1
    }
  
    $loggedInUser               = &JFactory::getUser(); 
?>
<div id="dialog-overlay"></div>
<div id="dialog-box">
	<div class="dialog-content">
	</div>
</div>

<div id="hotel-choice-container">
    <form method="post" action="" id="hotel-change-form" name="hotel-change-form" >
        <input type="hidden" name="support-person-name-container" id="support-person-name-container" value="<?php echo $loggedInUser->name; ?>" />
        <input type="hidden" name="logged-in-user-id-container" id="logged-in-user-id-container" value="<?php echo $loggedInUser->id; ?>"/>
        <input type="hidden" name="chat-support-current-hotel-id-container" id="chat-support-current-hotel-id-container" value="<?php echo $this->profile->hotel_id; ?>" />
        <label for="hotel_choice">Choose which hotel to manage:</label>
        <select name="hotel_choice" id="hotel_choice">
            <option value="">Select here</option>
            <?php foreach( $this->hotelChoices as $hotelChoice ): ?>
                <?php
                    $isSelectedChoice = FALSE; 
                    
                    if ( ($this->hotelIDInSession == $hotelChoice->hotel_id) || (count( $this->hotelChoices ) == 1) ) {
                        $isSelectedChoice = TRUE;    
                    }
                ?>
            <option value="<?php echo $hotelChoice->hotel_id; ?>" <?php if( $isSelectedChoice ): ?>SELECTED<?php endif; ?>><?php echo $hotelChoice->title; ?></option>
            <?php endforeach; ?>
        </select>
        &nbsp; 
        <input type="hidden" value="<?php echo $this->option; ?>" />
        <input type="submit" value="" class="form-buttons go-button" />
    </form>
</div>
<form method="post" action="" name="hotel-manager-menu-links-form" id="hotel-manager-menu-links-form">
    <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="hotel-manager-menu-option" />
    <input type="hidden" name="task" value="updateHotelInfo" />
    <?php 
        $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
    ?>
    <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="hotel-manager-menu-hotel_id" />
    <?php
        $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
    ?>
    <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
</form>
<?php if( !empty( $this->profile ) ) { ?>
<div id="hotel-manager">
    <div id="menu">
        <ul>
            <li class="menuItem" id="message-center-menu-link">
                <a href="" title="Message Center">
                    <img src="<?php echo $this->imagePath; ?>icons/hotel-manager-message-center.png" alt="Message Center" border="0"/>
                </a>
            </li>
            <?php if( $this->profile->gateway == 'hsia' ): ?>
            <li class="menuItem" id="accounts-creation-menu-link">
                <a href="" title="HSIA Accounts">
                    <img src="<?php echo $this->imagePath; ?>icons/hotel-manager-accounts-creation.png" alt="Message Center" border="0"/>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="menuItem" id="news-menu-link">
                <a href="" title="News">
                    <img src="<?php echo $this->imagePath; ?>icons/hotel-manager-news.png" alt="News" border="0"/> 
                </a>
            </li>
            <li class="menuItem" id="broadcast-menu-link">
                <a href="" title="Broadcast Messages">
                    <img src="<?php echo $this->imagePath; ?>icons/hotel-manager-broadcast.png" alt="Broadcast" border="0"/> 
                </a>
            </li>
            <li class="menuItem" id="ratings-menu-link">
                <a href="" title="Guest Ratings">
                    <img src="<?php echo $this->imagePath; ?>icons/hotel-manager-ratings.png" alt="Guest Ratings" border="0"/> 
                </a>
            </li>
            <li class="menuItem" id="custom-pge-menu-link">
                <a href="" title="Pages">
                    <img src="<?php echo $this->imagePath; ?>icons/hotel-manager-customizable-page.png" alt="Customizable Page" border="0"/> 
                </a>
            </li>
            
            <li class="menuItem" id="settings-menu-link">
                <a href="" title="Settings">
                    <img src="<?php echo $this->imagePath; ?>icons/hotel-manager-settings.png" alt="Settings" border="0"/>
                </a>
            </li>
        </ul>
        </form>
    </div>
    <div id="sections">
        <!-- MESSAGE CENTER SECTION: start --> 
        <div class="section" id="message-center-menu-link-section">
            <div class="section-container">
                <div class="page-container" id="hotel-manager-front">
                    <div class="panels"  style="border: none;">
                        <div class="panel-section">
                            <div class="page-header">Message Center</div>
                            <div>
                                <p>
                                    <?php echo date( 'l F d, Y'); ?>
                                </p>
                                <p>
                                    You have the following updates:
                                </p>
                            </div>
                            <div class="message-center-notifications-container">
                                <?php if( !empty( $this->totalUnread ) ) { ?>
                                    <p>
                                        Guest Ratings: <a id="message-center-total-ratings-display"><?php echo $this->totalUnread; ?></a>
                                    </p>
                                    <?php } else { ?>
                                    <p>
                                        Guest Ratings: <span id="message-center-total-ratings-display">0</span>
                                    </p>
                                <?php } ?>
                            </div>
                            
                            <?php if( $this->profile->support_chat_enabled === '1' ): ?>
                            <div class="message-center-notifications-container">
                                <?php
                                    $value = ( $this->profile->support_chat_enabled === '1' )? '1':'0'; 
                                ?>
                                <input type="hidden" name="is-chat-enabled" id="is-chat-enabled" value="<?php echo $value; ?>" />
                                <input type="hidden" name="message-center-options-container" id="message-center-options-container" value="<?php echo $this->option; ?>" /> 
                                <input type="hidden" name="message-center-hotel-id-container" id="message-center-hotel-id-container" value="<?php echo $this->profile->hotel_id; ?>" /> 
                                <input type="hidden" name="current-support-personnel" id="current-support-personnel" value="<?php echo $this->profile->support_staff_id; ?>" /> 
                                <p>
                                    <?php
                                        $holderName = ''; 
                                        if( $loggedInUser->id == $this->profile->support_staff_id ) {
                                            $holderName = 'YOU'; 
                                        } else if( ( !empty( $this->profile->support_staff_id ) ) && ( $loggedInUser->id != $this->profile->support_staff_id ) ) {
                                            $activeSupportUser = &JFactory::getUser( $this->profile->support_staff_id ) ; 
                                            
                                            $holderName = $activeSupportUser->name; 
                                        } else {
                                            $holderName = 'N/A'; 
                                        }
                                    ?>
                                    Active: <span id="current-support-holder-container"><?php echo $holderName; ?></span>
                                </p>
                                <p>
                                    <input type="checkbox" name="toggle-chat-receiver"  id="toggle-chat-receiver" <?php if( $loggedInUser->id == $this->profile->support_staff_id ): ?>checked="checked"<?php endif; ?>/>
                                    <label for="toggle-chat-receiver">I would like to receive support chat</lable>
                                </p>
                                <?php if( count( $this->chatMessages ) > 0 ): ?>
                                <p>
                                    Messages:
                                </p>
                                <ol id="chat-support-message-log-container">
                                    <?php foreach( $this->chatMessages as $message ): ?>
                                    <li>
                                        <?php echo $message->name.' ('.$message->Messages.')' ?>
                                        <div>
                                            <a class="chat-links delete-chat-logs-link" href="index.php?option=com_geoxityhotelmanager&task=deleteChatLogs&hotel_id=<?php echo $this->profile->hotel_id; ?>&client_user_id=<?php echo $message->User; ?>" >Delete</a>&nbsp;|&nbsp;
                                            <a class="chat-links download-chat-logs-link" href="index.php?option=com_geoxityhotelmanager&task=downloadChatLogs&hotel_id=<?php echo $this->profile->hotel_id; ?>&client_user_id=<?php echo $message->User; ?>" >Download</a>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ol>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if( $this->profile->gateway == 'hsia' ): ?>
                    <div class="panels">
                        <div class="panel-section">
                            <div class="page-header">geoxity HSIA Account Summary</div>
                            <!--
                            <div>
                                Plan Name: <span class="hsia-accounts-value">Name of Plan</span>
                            </div>
                            <div>
                                Account valid from: <span>mm-dd-yy to mm-dd-yy</span>
                            </div>
                            -->
                            <div style="margin-top: 20px;">
                                Monthly Quota: <span><?php echo $this->profile->accounts_quota; ?> accounts</span>
                            </div>
                            <?php
                                $additionalStyle = ''; 
                                
                                $used = (int) $this->profile->accounts_used; 
                                $max  = (int) $this->profile->accounts_quota; 
                                
                                if( $used >= $max ) {
                                    $additionalStyle = 'style=" color: #992D1D;"'; 
                                }
                            ?>
                            <div id="message-center-accounts-quota-container">
                                Accounts Created: <span id="message-center-accounts-count-container" <?php echo $additionalStyle; ?>><?php echo $this->profile->accounts_used; ?></span> / <span id="message-center-accounts-max-container"><?php echo $this->profile->accounts_quota; ?></span> accounts
                            </div>
                        </div>
                        <div class="panel-section">
                            <div class="page-header">Accounts Creation</div>
                            <p>
                                This is where you can generate accounts
                            </p>
                            <img src="<?php echo $this->buttonsPath; ?>gray-Create-Accounts.png" id="generate-accounts-shortcut" alt="Generate Accounts"/>
                        </div>
                        <!-- NOTE: this has been commented temporarily since the 
                        features for this has not yet been implemented 
                 
                        <div class="panel-section">
                            <div class="page-header">View</div>
                            <div>
                                <a href="">Accounts History</a>
                            </div>
                            <div>
                                <a href="">Device Logs</a>
                            </div>
                            <div>
                                <a href="">Session Logs</a>
                            </div>
                        </div>
                        -->
                    </div>
                    <?php endif; ?>
                    
                    <div class="panels">
                        <div class="panel-section">
                            <div class="page-header">Profile</div>
                            <div>
                                Current Email Address: <?php echo $this->currentUser->email; ?>
                            </div>
                            <div>
                                <a href="#" class="change-email-password-shortcut">Change Email Address</a>
                            </div>
                            <div>
                                <a href="#" class="change-email-password-shortcut">Change Password</a>
                            </div>
                        </div>
                        <?php 
                            $loggedInUser   = &JFactory::getUser(); 
                            
                            $privilege      = getUserAccountPrivilege( $this->profile, $loggedInUser->id ); 
                        ?>
                        <?php if( $privilege != 'staff' ) { ?>
                        <div class="panel-section">
                            <div class="page-header">Staff</div>
                            <input type="hidden" name="collaborators_hotel-id" id="collaborators_hotel-id" value="<?php echo $this->profile->hotel_id; ?>" />
                            <div id="staff-accounts-container">
                                
                                <?php if( $privilege == 'manager'): ?>
                                    <div class="staff-credentials-container">
                                        <?php 
                                            $admin = &JFactory::getUser( $this->profile->admin ); 
                                        ?>
                                        <span>Admin:</span><?php echo $admin->username; ?>&nbsp;<a href="" class="toggle-edit-staff-credential" id="toggle-edit-staff-credential_0">edit</a>
                                        <div class="staff-credential-container">
                                            <div class="username-credential">
                                                <label for="staff_admin_username" >Username:</label>
                                                <input type="text" name="admin_username" id="admin_username" value="<?php echo $admin->username; ?>"/>
                                            </div>
                                            <div class="password-credential">
                                                <label for="staff_admin_password" >Password:</label>
                                                <input type="text" name="admin_username" id="admin_password" />
                                            </div>
                                            <div>
                                                <input type="button" class="form-buttons save-button staff-credential-update-button" id="admin_update" />
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if( $privilege == 'manager' ||  $privilege == 'admin' ): ?>
                                <div class="staff-credentials-container">
                                    <?php 
                                        $staff_1 = &JFactory::getUser( $this->profile->staff_1 ); 
                                    ?>
                                    <span>Staff 1:</span><?php echo $staff_1->username; ?>&nbsp;<a href="" class="toggle-edit-staff-credential" id="toggle-edit-staff-credential_1">edit</a> 
                                    <div class="staff-credential-container">
                                        <div class="username-credential">
                                            <label for="staff_staff_1_username" >Username:</label>
                                            <input type="text" name="staff-1_username" id="staff-1_username" value="<?php echo $staff_1->username; ?>" />
                                        </div>
                                        <div class="password-credential">
                                            <label for="staff_staff_1_password" >Password:</label>
                                            <input type="text" name="staff_1_username" id="staff-1_password" />
                                        </div>
                                        <div>
                                            <input type="button" class="form-buttons save-button staff-credential-update-button" id="staff-1_update" />
                                        </div>
                                    </div>
                                </div>
                                <div class="staff-credentials-container">
                                    <?php 
                                        $staff_2 = &JFactory::getUser( $this->profile->staff_2 ); 
                                    ?>
                                    <span>Staff 2:</span><?php echo $staff_2->username; ?>&nbsp;<a href="" class="toggle-edit-staff-credential" id="toggle-edit-staff-credential_2">edit</a>
                                    <div class="staff-credential-container">
                                        <div class="username-credential">
                                            <label for="staff_staff_2_username" >Username:</label>
                                            <input type="text" name="staff_2_username" id="staff-2_username" value="<?php echo $staff_2->username; ?>" />
                                        </div>
                                        <div clas="password-credential">
                                            <label for="staff_staff_2_password" >Password:</label>
                                            <input type="text" name="staff_2_username" id="staff-2_password" />
                                        </div>
                                        <div>
                                            <input type="button" class="form-buttons save-button staff-credential-update-button" id="staff-2_update" />
                                        </div>
                                    </div>
                                </div>
                                <div class="staff-credentials-container">
                                    <?php 
                                        $staff_3 = &JFactory::getUser( $this->profile->staff_3 ); 
                                    ?>
                                    <span>Staff 3:</span><?php echo $staff_3->username; ?>&nbsp;<a href="" class="toggle-edit-staff-credential" id="toggle-edit-staff-credential_3" >edit</a>
                                    <div class="staff-credential-container">
                                        <div class="username-credential">
                                            <label for="staff_staff_3_username" >Username:</label>
                                            <input type="text" name="staff_3_username" id="staff-3_username" value="<?php echo $staff_3->username; ?>" />
                                        </div>
                                        <div class="password-credential">
                                            <label for="staff_staff_3_password" >Password:</label>
                                            <input type="text" name="staff_3_username" id="staff-3_password" />
                                        </div>
                                        <div>
                                            <input type="button" class="form-buttons save-button staff-credential-update-button" id="staff-3_update" />
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                        <?php } else { ?>
                        <div class="panel-section">
                            <div class="page-header">View Current Portal</div>
                                <img id="message-center-page-illustration" src="<?php echo $this->imagePath; ?>illustrations/Message-Center.jpg " atl="Page Illustration"/>
                            <input type="button" style="margin-top: 10px;" onclick="window.open( 'http://portal.geoxity.com/?hotel_id=<?php echo $this->profile->hotel_id; ?>' );" class="form-buttons preview-button" />
                        </div>
                        <?php } ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <!-- MESSAGE CENTER SECTION: end --> 
        
        <?php if( $this->profile->gateway == 'hsia' ): ?>
        <!-- ACCOUNTS CREATION SECTION: start -->
        <div class="section" id="accounts-creation-link-section">
            <div class="section-container">
                <div class="page-container">
                    <div class="forms-container" id="accounts-creation-container">
                        <div class="page-header">Accounts Creation</div>
                        
                        <div class="menu-section-head blue" id="create-accounts-container" style="width: 910px;">
                            Create Account(s)
                        </div>
                        
                        <div class="menu-section-content" id="accounts-creation-generation">
                            <?php 
                                include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'accounts-quota-section.php' ); 
                            ?>
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="accounts-creation" id="accounts-creation" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container">
                                    <div class="field-container">
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="accounts-option-container" />
                                        <input type="hidden" name="task" value="addGatewayAccounts" />
                                        <input type="hidden" name="template_index" value="0" id="template-index-container"/>
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="accounts-hotel-id-container"/>
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                        <input type="hidden" name="accounts_quota_container" id="accounts_quota_container" value="<?php echo $this->profile->accounts_quota; ?>" /> 
                                        <input type="hidden" id="total-hsia-accounts-container" name="total-hsia-accounts-container" value="<?php echo $this->profile->hsia_total_records_count; ?>" />
                                        <input type="hidden" id="profile-status-container" name="profile-status-container" value="<?php echo $this->profile->hotel_account_status; ?>" />
                                    </div>
                                    
                                    <div id="print-accounts-container" class="field-container">
                                        <input type="hidden" name="added-accounts-container" id="added-accounts-container" value="" />
                                        <a href="#" id="print-added-accounts-button">Print</a>
                                    </div>
                                    
                                    <div>
                                        <p class="grayed-out form-instruction">Create an account with a custom access code or generate multiple accounts.</p>
                                    </div>
                                    <div class="field-container">
                                        <label for="account-creation-type-series">Generate:</label>
                                        <input type="text" name="number_of_accounts" id="number_of_accounts" value="1"/>
                                        number of accounts.
                                        <input type="hidden" name="accounts_secret_container" id="accounts_secret_container" value="0" >
                                    </div>
                                    <div class="field-container">
                                        <label for="account-creation-type-code">Access Code:</label>
                                        <input type="text" name="custom_code" id="custom_code" />
                                        <span class="grayed-out" id="custom-code-message-container">(optional)</span>
                                    </div>
                                    <div class="field-container">
                                        
                                        <div class="accounts-generation-button">
                                            <?php if( strtolower( $this->profile->account_plan ) === 'premium' ): ?>
                                            <div class="template-button-label">
                                                Template 1
                                            </div>
                                            <?php endif; ?>
                                            <input type="button" class="hotel-manager-save-button form-buttons generate-button accounts-creation-submit-button" id="accounts-creation-submit-button_0"/>
                                        </div>
                                       
                                        <?php if( strtolower($this->profile->account_plan) === 'premium' ): ?>
                                        <div class="accounts-generation-button">
                                            <div class="template-button-label">
                                                Template 2
                                            </div>
                                            <input type="button" class="hotel-manager-save-button form-buttons generate-button accounts-creation-submit-button" id="accounts-creation-submit-button_1"/>
                                        </div>
                                        <?php endif; ?>
                                        <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="accounts-creation-loader" class="ajax-loader"/>
                                        <div class="clear"></div>
                                    </div>
                                    
                                    <div class="field-container" id="account-creation-progress" style="display: none;" >
                                        Created: <span id="created-accounts-dispaly"></span> / <span id="total-accounts-to-create-display"></span>
                                    </div>
                                    
                                    <div class="field-container">
                                        <div id="account-creation-progress-bar"></div>
                                    </div>
                                    
                                </div>
                            </form>
                            <div>
                                <p class="grayed-out form-instruction">
                                    Click to import a CSV file.
                                </p>
                            </div>
                            <div class="field-container">
                                <input type="file" name="hsia_accounts_import_field" id="hsia_accounts_import_field" />
                                <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="hotel-accounts-import-loader" class="ajax-loader"/>
                            </div>
                        </div>
                        
                        <!-- ACCOUNT TEMPLATES: start -->
                        <div class="menu-section-head gray" style="width: 910px;">
                            Template
                        </div>
                        
                        <div class="menu-section-content" id="accounts-template">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="accounts-template" id="accounts-template" class="hotel-manager-form">
                                <div id="accounts-template-forms-container">
                                    <div class="hotel-manager-form-fields-container">
                                        <h3>Template 1</h3>
                                        <div class="field-container">
                                            <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                            <input type="hidden" name="task" value="updateAccountsTemplate" />
                                            <?php 
                                                $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                            ?>
                                            <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                            <?php
                                                $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                            ?>
                                            <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                        </div>
                                        
                                        <?php
                                            $accountsTemplate = json_decode( $this->profile->accounts_template ); 
                                        ?>
                                        
                                        <div class="field-container">
                                            <label for="account_plan1">QoS:</label>
                                            <?php 
                                                $value = ( !empty( $accountsTemplate[0]->plan ) )? $accountsTemplate[0]->plan:NULL;
                                            ?>
                                            <select id="account_plan1" name="account_plan1">
                                                <?php foreach( $this->accountGroups as $key => $group ): ?>
                                                <option value="<?php echo $key; ?>" <?php if( trim($value) == trim($key) ): ?>selected="selected"<?php endif; ?>><?php echo $group; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="field-container">
                                            
                                            <?php 
                                                $value = ( !empty( $accountsTemplate[0]->number_of_characters ) )? $accountsTemplate[0]->number_of_characters:'4'; 
                                            ?>
                                            <input type="text" name="number_of_characters_field1" id="number_of_characters_field1" size="5"  value="<?php echo $value; ?>"/>
                                            <label for="number_of_characters_field1" >number of characters (max 10)</label>
                                        </div>
                                        <div class="field-container">
                                            <p>Duration:</p>
                                            <label for="duration_days1">Days:</label>
                                            <select name="duration_days1" id="duration_days1">
                                                <?php for( $iterator = 0; $iterator <= 30; $iterator++ ): ?>
                                                <option value="<?php echo $iterator; ?>" <?php if($accountsTemplate[0]->days_value == $iterator): ?>selected="selected"<?php endif; ?>><?php echo $iterator; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            &nbsp; 
                                            <label for="duration_hours1">Hours:</label>
                                            <select name="duration_hours1" id="duration_hours1">
                                                <?php for( $iterator = 0; $iterator <= 23; $iterator++ ): ?>
                                                <option value="<?php echo $iterator; ?>" <?php if($accountsTemplate[0]->hours_value == $iterator): ?>selected="selected"<?php endif; ?>><?php echo $iterator; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="field-container">
                                            <p class="grayed-out form-instruction">Optional</p>
                                            <div>
                                                <label for="prefix_field1">Prefix:</label>
                                                <?php 
                                                    $value = ( !empty( $accountsTemplate[0]->prefix ) )? $accountsTemplate[0]->prefix:''; 
                                                ?>
                                                <input type="text" id="prefix_field1" name="prefix_field1" value="<?php echo $value; ?>"/>
                                            </div>
                                        </div>
                                        <div class="field-container">
                                            <div>
                                                <label for="suffix_field1">Suffix:</label>
                                                <?php
                                                    $value = ( !empty( $accountsTemplate[0]->suffix ) )? $accountsTemplate[0]->suffix:''; 
                                                ?>
                                                <input type="text" id="suffix_field1" name="suffix_field1" value="<?php echo $value; ?>" />
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    
                                    <?php if( strtolower( $this->profile->account_plan ) == 'premium'): ?>
                                    <div class="hotel-manager-form-fields-container">
                                        <h3>Template 2</h3>
                                        <div class="field-container">
                                            <?php 
                                                $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                            ?>
                                            <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                            <?php
                                                $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                            ?>
                                            <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                        </div>
                                        
                                        <div class="field-container">
                                            <label for="account_plan">Qos:</label>
                                            <?php 
                                                $value = ( !empty( $accountsTemplate[1]->plan ) )? $accountsTemplate[1]->plan:NULL;
                                            ?>
                                            <select id="account_plan" name="account_plan">
                                                <?php foreach( $this->accountGroups as $key => $group ): ?>
                                                <option value="<?php echo $key; ?>" <?php if( trim($value) === trim($key) ): ?>selected="selected"<?php endif; ?>><?php echo $group; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="field-container">
                                            <?php 
                                                $value = ( !empty( $accountsTemplate[1]->number_of_characters ) )? $accountsTemplate[1]->number_of_characters:'4'; 
                                            ?>
                                            <input type="text" name="number_of_characters_field" id="number_of_characters_field" size="5"  value="<?php echo $value; ?>"/>
                                            <label for="number_of_characters_field" >number of characters (max 10)</label>
                                        </div>
                                        <div class="field-container">
                                            <p>Duration:</p>
                                            <label for="duration_days">Days:</label>
                                            <select name="duration_days" id="duration_days">
                                                <?php for( $iterator = 0; $iterator <= 30; $iterator++ ): ?>
                                                <option value="<?php echo $iterator; ?>" <?php if($accountsTemplate[1]->days_value == $iterator): ?>selected="selected"<?php endif; ?>><?php echo $iterator; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            &nbsp; 
                                            <label for="duration_hours">Hours:</label>
                                            <select name="duration_hours" id="duration_hours">
                                                <?php for( $iterator = 0; $iterator <= 23; $iterator++ ): ?>
                                                <option value="<?php echo $iterator; ?>" <?php if($accountsTemplate[1]->hours_value == $iterator): ?>selected="selected"<?php endif; ?>><?php echo $iterator; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="field-container">
                                            <p class="grayed-out form-instruction">Optional</p>
                                            <div>
                                                <label for="prefix_field">Prefix:</label>
                                                <?php 
                                                    $value = ( !empty( $accountsTemplate[1]->prefix ) )? $accountsTemplate[1]->prefix:''; 
                                                ?>
                                                <input type="text" id="prefix_field" name="prefix_field" value="<?php echo $value; ?>"/>
                                            </div>
                                        </div>
                                        <div class="field-container">
                                            <div>
                                                <label for="suffix_field">Suffix:</label>
                                                <?php
                                                    $value = ( !empty( $accountsTemplate[1]->suffix ) )? $accountsTemplate[1]->suffix:''; 
                                                ?>
                                                <input type="text" id="suffix_field" name="suffix_field" value="<?php echo $value; ?>" />
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="clear"></div>
                                </div>
                                
                                <div class="hotel-manager-form-buttons-container">
                                    <input type="submit" class="hotel-manager-save-button form-buttons save-button" value="" />
                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="accounts-template-loader" class="ajax-loader"/>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </form>
                        </div>
                        <!-- ACCOUNT TEMPLATES: end -->
                        
                        <!-- ACCOUNTS LIST: start -->
                        <div class="menu-section-head blue" id="accounts-list-container-trigger" style="width: 910px;">
                            Accounts List
                        </div>
                        
                        <div class="menu-section-content" id="accounts-list-container">
                            
                            <div class="field-container">
                                <input type="hidden" id="hsia-accounts-list-hotel-id-container" value="<?php echo $this->profile->hotel_id; ?>" />
                                <input type="hidden" id="hsia-accounts-list-options-container" value="<?php echo $this->option; ?>" />
                                <input type="hidden" id="hsia-accounts-list-profile-id-container" value="<?php echo $this->profile->id; ?>" />
                            </div>
                            <?php if( count( $this->profile->hsia_accounts ) > 0 && !empty( $this->profile->hsia_accounts ) ): ?>
                            <div class="field-container" style="margin-bototm: 10px !important;">
                                <a id="download-csv-link" href="index.php?option=<?php echo $option; ?>&hotel_id=<?php echo $this->profile->hotel_id; ?>&task=downloadAccountsCsv">Download CSV</a>
                            </div>
                            <?php endif; ?>

                            <h3>Filters:</h3>
                            <div id="hsia-accounts-list-filter-container" class="field-container">
                                <div>
                                    <label for="filter-userid">Access Code:</label>
                                    <input type="text" name="filter-userid" id="filter-userid" />
                                </div>
                                
                                <div>
                                    <label for="filter-qos">Qos:</label>
                                    <select name="filter-qos" id="filter-qos" >
                                        <option value="">N/A</option>
                                        <?php foreach( $this->accountGroups as $keyValue => $textValue ): ?>
                                        <option value="<?php echo $keyValue; ?>"><?php echo $textValue; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="hsia-created-on-filter">Created on:</label>
                                    <input type="text" name="hsia-created-on-filter" id="hsia-created-on-filter" class="overridden-search-fields" />
                                </div>
                                
                                <div class="bottom">
                                    <label for="hsia-first-login-filter">First Login:</label>
                                    <input type="text" name="hsia-first-login-filter" id="hsia-first-login-filter" class="overridden-search-fields"/>
                                </div> 

                                <div class="bottom">
                                    <label for="hsia-expires-on-filter">Expires On:</label>
                                    <input type="text" name="hsia-expires-on-filter" id="hsia-expires-on-filter" class="overridden-search-fields"/>
                                </div>
                                
                            </div>
                            
                            <div class="clear"></div>
                            
                            <div id="hsia-accounts-list-search-button-container" class="field-container">
                                <input type="button" value="Reset" id="hsia-accounts-reset-search"  />&nbsp;
                                <input type="button" value="Search" id="hsia-accounts-list-search"/>
                                &nbsp; <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="accounts-list-search-loader" class="ajax-loader"/>
                            </div>
                            
                            <div class="field-container" style="margin-top: 10px;">
                                <input type="hidden" name="hsia-order-by" id="hsia-order-by" />
                                <input type="hidden" name="hsia-sort-order" id="hsia-sort-order" />
                                <div id="accounts-display-table-container">
                                    
                                    <div id="hsia-table-head">
                                        <div id="head-access-code">
                                            <div class="sorting-toggler up">Access Code</div>
                                        </div>
                                        <div id="head-access-code">
                                            <div class="sorting-toggler up">QOS</div>
                                        </div>
                                        <div id="head-created-on">
                                            <div class="sorting-toggler up">Created On</div>
                                        </div>
                                        <div id="head-first-login">
                                            <div class="sorting-toggler up">First Login</div>
                                        </div>
                                        <div id="head-expires-on">
                                            <div class="sorting-toggler up">Expires On</div>
                                        </div>
                                        <div id="head-duration">
                                            <div>Duration</div>
                                        </div>
                                        <div id="head-created-by" style="width: 129px !important">
                                            <div>Created By</div>
                                        </div>
                                    </div>
                                    
                                    <div class="clear"></div>
                                    
                                    <div id="hsia-accounts-list-wrapper">
                                        <table id="hsia-accounts-list" cellspacing="0" border="0">
                                            <tbody id="accounts-list-body-container">
                                                <?php 
                                                    $additionalClass = ''; 
                                                ?>
                                                <?php foreach( $this->profile->hsia_accounts as $account ): ?>
                                                <tr class="<?php echo ( $additionalClass == 'even')? 'even':'' ?> accounts-list-row">
                                                    <td>
                                                        <?php echo $account[ 'stripped_access_code' ]; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $account[ 'qos' ]; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $account['created_on']; ?>
                                                    </td>
                                                    <td>
                                                    <?php echo $account[ 'first_login' ]; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            echo $account['expiry_text']; 
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            echo $account['human_readable_duration']; 
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            echo $account['created_by_whom']; 
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                    if ( $additionalClass == 'even' ) {
                                                        $additionalClass = ''; 
                                                    } else {
                                                        $additionalClass = 'even'; 
                                                    }
                                                ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>

                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class-="field-container">
                                Page: 
                                &nbsp;
                                <a id="accounts_page-backward" class="accounts-page-arrows">
                                    <img width="20" height="20" src="/templates/geoxity/template-resources/common/images/icons/backward.png">
                                </a>
                                &nbsp;
                                <select id="hsia-accounts-paging">
                                    <?php for( $iterator = 1; $iterator <= (int) $this->profile->hsia_pages; $iterator++ ): ?>
                                    <option class="accounts-paging-options" value="<?php echo $iterator; ?>"><?php echo $iterator; ?></option>
                                    <?php endfor; ?>
                                </select>
                                &nbsp;
                                <a id="accounts_page-forward" class="accounts-page-arrows">
                                    <img width="20" height="20" src="/templates/geoxity/template-resources/common/images/icons/forward.png">
                                    </a>
                                &nbsp out of <span id="total-hsia-accounts-pages-container"><?php echo $this->profile->hsia_pages; ?></span>
                                &nbsp;
                                <!--
                                |&nbsp;Found Matches: <span id="accounts-list-total-accounts-container"><?php echo $this->profile->number_of_accounts_matched; ?></span>
                                -->
                                &nbsp; <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="accounts-list-paging-loader" class="ajax-loader"/>
                            </div>
                        </div>
                        
                        <!-- ACCOUNTS LIST: end -->
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <!-- ACCOUNTS CREATION: end -->
        <?php endif; ?>
        
        <!-- NEWS SECTION: start -->
        <div class="section" id="news-menu-link-section">
            <div class="section-container">
                <div class="page-container">
                    <div class="forms-container" id="news-page-container">
                        <div class="page-header">News</div>
                        <div>
                            <p class="grayed-out form-instruction">You can add up to 5 news articles that will show up on your portal.</p>
                        </div>
                        
                        <div class="menu-section-head blue" id="news-section-title-container">
                            News Section Title
                        </div>
                        <div class="menu-section-content" id="news-form-container">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="news-section-title-config" id="news-section-title-config" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container">
                                    <div class="field-container">
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                        <input type="hidden" name="task" value="updateNewsSectionTitle" />
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                    </div>
                                    <div class="field-container">
                                        <div>
                                            <p class="grayed-out form-instruction">This is where you can modify the title for your posted news section.</p>
                                        </div>
                                        <?php 
                                            $value = ( !empty( $this->profile->news_section_title ) )? $this->profile->news_section_title:''; 
                                        ?>
                                        <input type="text" name="news_section_title" value="<?php echo $value; ?>" size="60" id="news_section_title"/>
                                        <div class="grayed-out smaller">
                                            You have <span id="news-section-title-character-count"></span> characters left
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="hotel-manager-form-buttons-container">
                                    <input type="submit" class="hotel-manager-save-button form-buttons save-button" value="" />
                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="news-section-title-config-loader" class="ajax-loader"/>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </form>
                        </div>
                    
                        <div class="menu-section-head gray">
                            View Articles
                        </div>
                        <div class="menu-section-content" id="news-list-container">
                            <form method="post" action="" name="news-view" id="news-view" class="hotel-manager-form">
                                <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                <input type="hidden" name="task" value="updateNewsDisplay" />
                                <?php 
                                    $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                ?>
                                <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                <?php
                                    $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                ?>
                                <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                <div>
                                    <p class="grayed-out form-instruction">Here's a list of your posted items.</p>
                                </div>
                                <table id="news-display-table" border="0" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="news-selector" />
                                            </th>
                                            <th style="text-align: left !important;">
                                                Title
                                            </th>
                                            <th>
                                                Date Added
                                            </th>
                                            <th style="text-align: left !important;">
                                                Options
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="news-body-container">
                                        <?php foreach( $this->news as $news ): ?>
                                        <form method="post" action="" name="news-list-form" id="news-list-form">
                                            <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="news-list-option-container"/>
                                            <?php 
                                                $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                            ?>
                                            <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="news-list-hotel-id-container"/>
                                            <?php
                                                $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                            ?>
                                            <input type="hidden" name="profile_id" value="<?php echo $value; ?>" id="news-list-profile-id-container"/>
                                            <tr id="news-table-row-<?php echo $news->id;?>" class="news-table-rows">
                                                <td>
                                                    <input type="checkbox" name="news_id[]" value="<?php echo $news->id; ?>" class="news-id-container" />
                                                </td>
                                                <td class="news-title-container" id="news-title-container-<?php echo $news->id; ?>">
                                                    <?php echo $news->title; ?>
                                                </td>
                                                <td>
                                                    <?php echo $news->created; ?>
                                                </td>
                                                <td>
                                                    <input id="#view_<?php echo $news->id; ?>" type="button" value="" class="form-buttons edit-button news-view-link" />
                                                </td>
                                            </tr>
                                        </form>
                                        <?php endforeach; ?>
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td>
                                                <input type="button" value="" id="delete-news-set-button" class="form-buttons delete-button" />
                                            </td>
                                            <?php if( $this->totalNumberOfNewsPages > 1 ) { ?>
                                            <td colspan="2" id="news-page-options-container">
                                                <span>Page:&nbsp;</span>
                                                <a href="#" id="news-page-backward">
                                                    <img src="<?php echo $this->imagePath; ?>icons/backward.png" width="20" height="20" /> 
                                                </a>
                                                <select name="news_page_select" id="news-page-select">
                                                    <?php for( $iterator = 1; $iterator <= $this->totalNumberOfNewsPages; $iterator++ ): ?>
                                                    <option value="<?php echo $iterator; ?>"><?php echo $iterator; ?> of <?php echo $this->totalNumberOfNewsPages; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                                <a href="#" id="news-page-forward">
                                                    <img src="<?php echo $this->imagePath; ?>icons/forward.png" width="20" height="20" />
                                                </a> 
                                                <div id="news-ajax-indicator" class="ajax-loader">
                                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-circle.gif" />
                                                </div>
                                                <div class="clear"></div>
                                            </td>
                                            <?php } else { ?>
                                            <td clospan="2">
                                                &nbsp; 
                                            </td>
                                            <?php } ?>
                                            <td>
                                                <input type="button" value="" id="add-new-news-button" class="form-buttons add-new-button" />
                                            </td>
                                        </tr>
                                    </tfoot>

                                </table>
                            </form>
                        </div>
                        
                        <div class="menu-section-head blue">
                            Article Form
                        </div>
                        <div class="menu-section-content" id="editor-container">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="news-submission" id="news-submission" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container" id="news-submission-fields">
                                    <div class="field-container">
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                        <input type="hidden" name="task" value="submitNews" />
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                        <input type="hidden" name="content_id" value="" id="content_id" />
                                        <input type="hidden" name="news_limit" value="<?php echo $this->profile->news_limit; ?>" />
                                    </div>
                                    <div class="field-container">
                                        <div class="hotel-manager-form-label">
                                            <label for ="news_title">Title</label>
                                        </div>
                                        <input type="text" name="news_title" id="news_title" size="60" >
                                    </div>
                                    <div class="field-container">
                                        <div class="hotel-manager-form-label">
                                            <label for ="news_content">News Content</label>
                                        </div>
                                        <textarea name="news_content" id="news_content" class="form_editors"rows="5" cols="65" ></textarea>
                                    </div>
                                </div>
                                <div class="hotel-manager-form-buttons-container">
                                    <input type="submit" class="hotel-manager-save-button form-buttons save-button" value="" />
                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="news-submission-loader" class="ajax-loader"/>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </form>
                        </div>
                        
                    </div>
                    <?php
                        include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'default-tab-illustration.php' ); 
                    ?>
                    <div class="clear"></div>

                </div>
            </div>
        </div>
        <!-- NEWS SECTION: end -->
        
        <!-- BROADCAST SECTION: start -->
        <div class="section" id="broadcast-menu-link-section">
            <div class="section-container">
                <div class="page-container">
                    <div class="forms-container" id="broadcast-page-container">
                         <div class="page-header">Broadcast Messages</div>
                         <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                         <div>
                             <p class="grayed-out form-instruction">
                                 This is where you can broadcast short messages. It will show up in the Announcements portion of the portal.
                             </p>
                         </div>
                        <form method="post" action="" name="broadcast-config" id="broadcast-config" class="hotel-manager-form">
                            <div class="hotel-manager-form-fields-container">
                                <div class="field-container">
                                    <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="broadcast-option-container" />
                                    <input type="hidden" name="task" value="sendBroadcastMessage" />
                                    <?php 
                                        $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                    ?>
                                    <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="broadcast-hotel-id-container"/>
                                    <?php
                                        $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                    ?>
                                    <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                </div>
                                <div class="field-container">
                                    <textarea id="broadcast" name="broadcast" rows="3" cols="50" class="editor-disabled"></textarea>
                                    <div id="broadcast-counter-container" class="grayed-out smaller">
                                        You have <span id="broadcast-character-count">140</span> characters left 
                                    </div>
                                </div>
                                <div class="field-container" >
                                    <div class="hotel-manager-form-buttons-container">
                                        <input type="submit" class="hotel-manager-save-button form-buttons send-button" value="" />
                                        <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="broadcast-config-loader" class="ajax-loader"/>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                <div class="field-container">
                                    <div>
                                        <p class="grayed-out form-instruction">Here's a list of your previous broadcast messages</p>
                                    </div>
                                    <div id="previous-broadcast-messages-container">
                                        <?php if( count($this->broadcastMessages) == 0 ) { ?>
                                        <div class="broadcast-message-container" id="temporary-broadcast-mesage-container">
                                            There are no contents yet. Please use the form above to post some.
                                        </div>
                                        <?php } else { ?>
                                            <?php foreach( $this->broadcastMessages as $message ): ?> 
                                            <div class="broadcast-message-container" id="broadcast-message-container_<?php echo $message->id; ?>">
                                                <span class="broadcast-date-posted">Posted Last: <?php echo $message->created_at; ?> </span>
                                                <p>
                                                    <?php echo $message->message; ?>
                                                </p>
                                                <a href="#" class="delete-broadcast-button" id="delete_<?php echo $message->id; ?>">Delete</a>
                                            </div>
                                        <?php endforeach; ?> 
                                        <?php } ?>
                                        
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </form>
                    </div>
                    <?php
                        include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'default-tab-illustration.php' ); 
                    ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <!-- BROADCAST SECTION: end -->
        
        <!-- GUEST RATING: start -->
        <div class="section" id="ratings-menu-link-section">
            <div class="section-container">
                <div class="page-container">
                    <div class="forms-container" id="ratings-page-container">
                    <div class="page-header">Ratings</div>
                    <div>
                        <p class="grayed-out form-instruction">Here are the ratings of your hotel that are submitted by your guests.</p>
                    </div>
                        <?php if( !empty( $this->ratings ) ) { ?>
                        <div id="average-rating-container">
                            <span>Average Rating:</span>
                            <?php for( $iterator = 0; $iterator < $this->averageRating; $iterator++ ): ?>
                                <img src="<?php echo $this->imagePath; ?>icons/star.png" />
                            <?php endfor; ?>
                            <span> from <?php echo $this->totalRatings; ?> submitted</span>
                        </div>
                        <div id="rating-list-container">
                            <?php foreach( $this->ratings as $rating ): ?>
                            <div class="rating-container">
                                Rating: 
                                <?php for( $iterator = 0; $iterator < $rating->rating; $iterator++ ): ?> 
                                    <img src="<?php echo $this->imagePath; ?>icons/star.png" />
                                <?php endfor; ?>
                                <br />
                                by: <span class="user-name-container"><?php echo $rating->rated_by->name; ?> </span>&nbsp;<span class="user-email-container" >(<?php echo $rating->rated_by->email; ?>)</span>
                                on <?php echo $rating->date_added; ?>
                                <input type="button" class="form-buttons hide-button user-comment-toggle" id="comment_<?php echo $rating->id; ?>" />
                                <div class="user-comment-containers" id="user-comment-container_<?php echo $rating->id; ?>" style="display: block;">
                                    <p>
                                        <?php echo $rating->comment; ?> 
                                    </p>
                                    <div>
                                        <?php
                                            $linkText = ( (int) $rating->was_read == 1 )? 'read':'unread'; 
                                        ?>
                                        Mark as <a href="#" id="#toggle_<?php echo $rating->id; ?>" class="ratings-read-toggler <?php echo $linkText; ?>"><?php echo $linkText; ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if( $this->totalRatingPages > 1 ): ?>
                        <div id="rating-pagination-container">
                            <form method="post" action="" name="hotel-ratings" id="hotel-ratings" class="hotel-manager-form">
                                <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="rating-option-container"/>
                                <input type="hidden" name="task" value="updateRatingDisplay" />
                                <?php 
                                    $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                ?>
                                <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="rating-hotel-id-container"/>
                                <?php
                                    $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                ?>
                                <input type="hidden" name="profile_id" value="<?php echo $value; ?>" id="rating-profile-id-container" />
                                <div id="sort-options-container">
                                    <span>Sort:</span>
                                    <select name="ratings_sort_options" id="ratings-sort-options">
                                        <option value="1">Date (Ascending)</option>
                                        <option value="2">Date (Descending)</option>
                                        <option value="3">Rating (Ascending)</option>
                                        <option value="4">Rating (Descending)</option>
                                    </select>
                                    <span>Page:&nbsp;</span>
                                    <a href="#" id="ratings-page-backward">
                                        <img src="<?php echo $this->imagePath; ?>icons/backward.png" width="20" height="20" /> 
                                    </a>
                                    <select name="ratings_page_select" id="ratings-page-select">
                                        <?php for( $iterator = 1; $iterator <= $this->totalRatingPages; $iterator++ ): ?>
                                        <option value="<?php echo $iterator; ?>"><?php echo $iterator; ?> of <?php echo $this->totalRatingPages; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <a href="#" id="ratings-page-forward">
                                        <img src="<?php echo $this->imagePath; ?>icons/forward.png" width="20" height="20" />
                                    </a> 
                                    <div id="rating-ajax-indicator" class="ajax-loader">
                                        <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-circle.gif" />
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                                <a href="#" id="toggle-rating-filter-link">Show Filter</a>
                                <div id="ratings-filter-container">
                                    <div>
                                        <span>Filter:</span>
                                        <input type="checkbox" name="rating_read" id="rating_read" value="1" /><label for="rating_read">Read</read>
                                        <input type="checkbox" name="rating_unread" id="rating_unread" value="0" /><label for="rating_unread">Unread</read>
                                    </div>
                                    <div>
                                        <label for="rating_date">Date:</label>
                                        <input type="text" name="rating_date" id="rating_date" /> 
                                    </div>
                                    <div>
                                        <span>Rating range</span>&nbsp; 
                                        <label for="rating_rage_from">From:</label>
                                        <input type="text" size="2" name="rating_rage_from" id="rating_rage_from" />
                                        <label for="rating_rage_to">To:</label>
                                        <input type="text" size="2" name="rating_rage_to" id="rating_rage_to" />
                                        <input type="button" value="" class="form-buttons set-filter-button" id="set-filter-button" />
                                        <input type="button" class="form-buttons clear-button" name="clear-filter-button" id="clear-filter-button" value="" />
                                    </div> 
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    <?php } else { ?>
                    <p class="grayed-out form-instruction">There are no ratings submitted</p>
                    <?php } ?>
                    </div>
                    <?php
                        include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'default-tab-illustration.php' ); 
                    ?>
                    <div class="clear"></div>
                </div>
                
            </div>
        </div>
        <!-- GUEST RATING: end -->
        
        <!-- PAGES SECTION: start -->
        <div class="section" id="custom-page-menu-link-section">
            <div class="section-container">
                <div class="page-container">
                        <div class="forms-container" id="pages-container">
                            <div class="page-header">Pages</div>
                            
                            <!-- HOTEL INFORMATION: start -->
                            <div class="menu-section-head blue" id="hotel-information-container">
                                Hotel Information
                            </div>
                            <div class="menu-section-content" id="settings-hotel-info">
                                <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                                <form method="post" action="" name="hotel-info-config" id="hotel-info-config" class="hotel-manager-form">
                                    <div class="hotel-manager-form-fields-container">
                                        <div class="field-container">
                                            <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="hotel-info-option-container"/>
                                            <input type="hidden" name="task" value="updateHotelInfo" />
                                            <?php 
                                                $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                            ?>
                                            <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="hotel-info-hotel-id-container" />
                                            <?php
                                                $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                            ?>
                                            <input type="hidden" name="profile_id" value="<?php echo $value; ?>" id="hotel-info-profile-id-container" />
                                            <?php
                                                $value = ( !empty( $this->hotelInfoCurrent->id ) )? $this->hotelInfoCurrent->id:NULL; 
                                            ?>
                                            <input type="hidden" name="current_hotel_info_content_id" id="current_hotel_info_content_id" value="<?php echo $value; ?>" />
                                        </div>
                                        <div class="field-container">
                                            <div>
                                                <p class="grayed-out form-instruction">Here you can place any information about your hotel. This will show up in the Hotel Info Page.</p>
                                            </div>
                                            <div class="versions-container">
                                                <div class="versions-select-box-container">
                                                    <select name="hotel-info-versions-list" class="version-select-box" id="hotel-info-versions">
                                                        <option value="0">Create new version</option>
                                                        <optgroup label="Current Versions" id="hotel-info-version-choices">
                                                            <?php foreach( $this->hotelInfoVersions as $version ): ?>
                                                            <?php 
                                                                $additionalClass    = ''; 
                                                                $versionLabel       = ''; 

                                                                if ( $version->is_current == 0 ) {
                                                                    $additionalClass = 'gray';
                                                                    $versionLabel = 'Draft'; 
                                                                }

                                                                if ( $version->is_current == 1 ) {
                                                                    $additionalClass = 'green';
                                                                    $versionLabel = 'Currently Published'; 
                                                                }

                                                                if ( $version->is_current == 2 ) {
                                                                    $additionalClass = 'gray'; 
                                                                    $versionLabel = 'Previously Published'; 
                                                                }
                                                            ?>
                                                            <option class="<?php echo $additionalClass; ?>" value="<?php echo $version->id; ?>" <?php if( $this->hotelInfoCurrent->id == $version->id ): ?>selected="selected"<?php endif; ?>><?php echo date( 'd M Y H:i A', strtotime( $version->date_added ) ); ?> [<span class="lighter"><?php echo $versionLabel; ?>]</option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    </select>
                                                </div>
                                                <div class="version-select-loader-container">
                                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="hotel-info-versions-loader" class="ajax-loader"/>
                                                </div>
                                            </div>
                                            <div class="clear"></div> 
                                        </div>
                                        <div class="field-container">
                                            <?php
                                                  $value = ( !empty( $this->hotelInfoCurrent->content ) )?  $this->hotelInfoCurrent->content:NULL;
                                            ?>
                                            <textarea name="hotel_info" id="hotel_info" class="form_editors" ><?php echo $value; ?></textarea>
                                            <input type="hidden" id="hotel-info-save-option" value="" />
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="hotel-manager-form-buttons-container">
                                        <input id="hotel-info-save-trigger" type="button" class="hotel-manager-save-button" style="display: none;" />
                                        <input id="hotel-info-save-button" type="button" class="form-buttons disabled-save-button" value="" />
                                        &nbsp; 
                                        <?php createCommonButtons($this->profile, $this->hotelInfoCurrent, 'hotel-info' ); ?>
                                        &nbsp; 
                                        <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="hotel-info-config-loader" class="ajax-loader"/>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                </form>
                            </div>
                            <!-- HOTEL INFORMATION: end -->
                            
                            
                            <!-- FACILITIES: start -->
                            <div class="menu-section-head gray">
                                Facilities
                            </div>
                            <div class="menu-section-content" id="settings-facilities">
                                <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                                <form method="post" action="" name="facilities-config" id="facilities-config" class="hotel-manager-form">
                                    <div class="hotel-manager-form-fields-container">
                                        <div class="field-container">
                                            <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                            <input type="hidden" name="task" value="updateFacilities" />
                                            <?php 
                                                $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                            ?>
                                            <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                            <?php
                                                $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                            ?>
                                            <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                        </div>
                                        <div class="field-container">
                                            <div>
                                                <p class="grayed-out form-instruction">Put a check on the facilities that you currently have.</p>
                                            </div>
                                            <div id="facilities-container">
                                                <?php 
                                                    $currentFacilities = array(); 
                                                    $currentFacilities = explode( ',' , $this->hotelGuideProfile->facilities ); 
                                                ?>
                                                <?php foreach( $this->facilities as $facility ): ?> 
                                                <div>
                                                    <input class="facility_checkbox" type="checkbox" name="facility[]" value="<?php echo $facility->id; ?>" id="facility_<?php echo $facility->id; ?>" <?php if( in_array( $facility->id, $currentFacilities ) ): ?>checked="checked"<?php endif; ?>/>
                                                    <label for="facility_<?php echo $facility->id; ?>"><?php echo $facility->name; ?></label>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="hotel-manager-form-buttons-container">
                                        <input type="submit" class="hotel-manager-save-button form-buttons save-button" value="" />
                                        <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="facilities-config-loader" class="ajax-loader"/>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                </form>
                            </div>
                            <!-- FACILITIES: end -->
                            <!-- FACILITIES CONTENT: start -->
                            <div class="menu-section-head blue">
                                Facilities Page Content
                            </div>
                            <div class="menu-section-content" id="settings-facilities-content">
                                <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                                <form method="post" action="" name="facilities-content-config" id="facilities-content-config" class="hotel-manager-form">
                                    <div class="hotel-manager-form-fields-container">
                                        <div class="field-container">
                                            <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="facilities-content-option-container"/>
                                            <input type="hidden" name="task" value="updateFacilitiesContent" />
                                            <?php 
                                                $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                            ?>
                                            <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="facilities-content-hotel-id-container" />
                                            <?php
                                                $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                            ?>
                                            <input type="hidden" name="profile_id" value="<?php echo $value; ?>" id="facilities-content-profile-id-container"/>
                                            <?php
                                                $value = ( !empty( $this->facilitiesContentCurrent->id ) )? $this->facilitiesContentCurrent->id:NULL; 
                                            ?>
                                            <input type="hidden" name="current_facilities_content_content_id" id="current_facilities_content_content_id" value="<?php echo $value; ?>" />
                                        </div>
                                        <div class="field-container">
                                            <div>
                                                <p class="grayed-out form-instruction">Here you can place the article that goes before the list of facilities.</p>
                                            </div>
                                            <div class="versions-container">
                                                <div class="versions-select-box-container">
                                                    <select class="version-select-box" id="facilities-content-versions">
                                                        <option value="0">Create new version</option>
                                                        <optgroup label="Current Versions" id="facilities-content-version-choices">
                                                            <?php foreach( $this->facilitiesContentVersions as $version ): ?>
                                                            <?php 
                                                                $additionalClass    = ''; 
                                                                $versionLabel       = ''; 

                                                                if ( $version->is_current == 0 ) {
                                                                    $additionalClass = 'gray';
                                                                    $versionLabel = 'Draft'; 
                                                                }

                                                                if ( $version->is_current == 1 ) {
                                                                    $additionalClass = 'green';
                                                                    $versionLabel = 'Currently Published'; 
                                                                }

                                                                if ( $version->is_current == 2 ) {
                                                                    $additionalClass = 'gray'; 
                                                                    $versionLabel = 'Previously Published'; 
                                                                }
                                                            ?>
                                                            <option class="<?php echo $additionalClass; ?>" value="<?php echo $version->id; ?>" <?php if( $facilitiesContentCurrent->id == $versions->id ): ?>selected="selected"<?php endif; ?>><?php echo date( 'd M Y H:i A', strtotime( $version->date_added ) ); ?> [<?php echo $versionLabel; ?>]</option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    </select>
                                                </div>
                                                <div class="version-select-loader-container" >
                                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="facilities-content-versions-loader" class="ajax-loader"/>
                                                </div>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                        <div class="field-container">
                                            <?php 
                                                  $value= ( !empty( $this->facilitiesContentCurrent->content ) )? $this->facilitiesContentCurrent->content:NULL;
                                            ?>
                                            <textarea name="facilities_content" id="facilities_content" class="form_editors" ><?php echo $value; ?></textarea>
                                            <input type="hidden" id="facilities-content-save-option" value="" />
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="hotel-manager-form-buttons-container">
                                        <input id="facilities-content-save-trigger" type="button" class="hotel-manager-save-button" style="display: none;"/>
                                        <input id="facilities-content-save-button" type="button" class="form-buttons disabled-save-button" value="" />
                                        &nbsp; 
                                        <?php createCommonButtons($this->profile, $this->facilitiesContentCurrent, 'facilities-content' ); ?>
                                        &nbsp;
                                        <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="facilities-content-config-loader" class="ajax-loader"/>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                </form>
                            </div>
                            <!-- FACILITIES CONTENT: end -->
                            
                            <!-- CUSTOMIZABLE PAGE: start -->
                            <div class="menu-section-head gray">
                                Customizable Page
                            </div>
                            <div class="menu-section-content" id="pages-custom-page">
                                <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                                <form method="post" action="" name="customizable-page-config" id="customizable-page-config" class="hotel-manager-form">
                                    <div class="hotel-manager-form-fields-container">
                                        <div class="field-container">
                                            <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="customizable-page-option-container"/>
                                            <input type="hidden" name="task" value="updateCustomPage" />
                                            <?php 
                                                $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                            ?>
                                            <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="customizable-page-hotel-id-container" />
                                            <?php
                                                $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                            ?>
                                            <input type="hidden" name="profile_id" value="<?php echo $value; ?>" id="customizable-page-profile-id-container" />
                                            <?php
                                                $value = ( !empty( $this->customizablePageCurrent->id ) )? $this->customizablePageCurrent->id:NULL; 
                                            ?>
                                            <input type="hidden" name="current_customizable_page_content_id" id="current_customizable_page_content_id" value="<?php echo $value; ?>" />
                                        </div>
                                        <div class="field-container">
                                            <p class="grayed-out form-instruction">
                                                Any article you place here will show up when you click on the red top menu item. It is the Contact Us page by default.
                                            </p>
                                            <p class="grayed-out form-instruction">
                                                Upon saving, your content will be saved as a version found below the form. To set a version as published, click on "Publish". To preview, click on the title. 
                                            </p>
                                            <div class="versions-container">
                                                <div class="versions-select-box-container">
                                                      <select name="customizable-page-versions-list" class="version-select-box" id="customizable-page-versions">
                                                          <option value="0">Create new version</option>
                                                          <optgroup label="Current Versions" id="customizable-page-version-choices">
                                                            <?php foreach( $this->customizablePageVersions as $version ): ?>
                                                                <?php 
                                                                    $additionalClass    = ''; 
                                                                    $versionLabel       = ''; 

                                                                    if ( $version->is_current == 0 ) {
                                                                        $additionalClass = 'gray';
                                                                        $versionLabel = 'Draft'; 
                                                                    }

                                                                    if ( $version->is_current == 1 ) {
                                                                        $additionalClass = 'green';
                                                                        $versionLabel = 'Currently Published'; 
                                                                    }

                                                                    if ( $version->is_current == 2 ) {
                                                                        $additionalClass = 'gray'; 
                                                                        $versionLabel = 'Previously Published'; 
                                                                    }
                                                                ?>
                                                                <option id="content_<?php echo $version->id; ?>" class="<?php echo $additionalClass; ?>" value="<?php echo $version->id; ?>" <?php if( $this->customizablePageCurrent->id == $version->id ): ?>selected="selected"<?php endif; ?>><?php echo $version->title.' - '.date( 'd M Y H:i A', strtotime( $version->date_added ) ); ?> [<?php echo $versionLabel; ?>]</option>
                                                            <?php endforeach; ?> 
                                                        </optgroup>
                                                      </select>
                                                </div>
                                                <div class="version-select-loader-container">
                                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="customizable-page-versions-loader" class="ajax-loader"/>
                                                </div>
                                            </div>
                                            <div class="clear" ></div>
                                            <div class="hotel-manager-form-label">
                                                <label for="page_title">Title</label>
                                            </div>
                                            <?php
                                                  $value = ( !empty( $this->customizablePageCurrent->title ) )? $this->customizablePageCurrent->title:NULL; 
                                            ?>
                                            <div class="hotel-manager-form-field">
                                                <input type="text" name="page_title" id="page_title" value="<?php echo $value; ?>" size="60"/>  
                                            </div>
                                            <div class="grayed-out smaller">
                                                You have <span id="custom-page-character-count"><?php if( strlen( $value ) >= 10 ): ?>0<?php endif; ?></span> characters left
                                            </div>
                                        </div>
                                        <div class="field-container">
                                            <div class="hotel-manager-form-label">
                                                <label for ="page_content">Page Content</label> 
                                            </div>
                                            <?php
                                                $value  = ( !empty( $this->customizablePageCurrent->content ) )? $this->customizablePageCurrent->content:NULL; 
                                            ?>
                                            <textarea name="page_content" id="customizable_page_content" class="form_editors" rows="4" cols="35" ><?php echo $value; ?></textarea>
                                            <input type="hidden" id="customizable-page-save-option" value="" />
                                        </div>
                                    </div>
                                    <div class="hotel-manager-form-buttons-container">
                                        <input id="customizable-page-save-trigger" type="button" class="hotel-manager-save-button" style="display: none;"/>
                                        <input id="customizable-page-save-button" type="button" class="form-buttons disabled-save-button" value="" />
                                        &nbsp;
                                        <?php createCommonButtons($this->profile, $this->customizablePageCurrent, 'customizable-page' ); ?>
                                        &nbsp;
                                        <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="customizable-page-config-loader" class="ajax-loader"/>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                </form>
                            </div>
                            <!-- CUSTOMIZABLE PAGE: end -->
                            
                    </div>
                    <?php
                        include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'default-tab-illustration.php' ); 
                    ?>
                    <div class="clear"></div>
                </div>
                
            </div>
        </div>
        <!-- PAGES SECTION: end -->
        
        <!-- SETTINGS SECTION: start -->
        <div class="section" id="settings-menu-link-section">
            <div class="section-container">
                <div class="page-container">
                    
                    <div class="forms-container" id="settings-pages-container">
                        <div class="page-header">Settings</div>
                        
                        <!-- IMAGE UPLOADER: start --> 
                        <div class="menu-section-head blue" id="slideshow-title-container">
                            Slideshow
                        </div>
                        <div class="menu-section-content" id="settings-slideshow">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="slideshow-config" id="slideshow-config" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container">
                                    <div>
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="slideshow-option-container" />
                                        <input type="hidden" name="task" value="updateSlideshow" />
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="slideshow-hotel-id-container"/>
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                        <input type="hidden" name="destination_folder" id="destination_folder" value="/images/hotelguide/gallery/hotel_<?php echo $this->profile->hotel_id; ?>/album/" />
                                        <input type="hidden" name="max_image_count" id="max_image_count" value="<?php echo $this->maxImageCount; ?>" />
                                    </div>
                                    <div>
                                        <p class="grayed-out form-instruction">Here, you can upload up to 5 images. Images should be 681 X 248  (supported formats: PNG and JPG)</p>
                                    </div>
                                    <?php 
                                        $canAddImage = FALSE; 
                                        if( count( $this->slideshowImages) < $this->maxImageCount ) {
                                            $canAddImage = TRUE; 
                                        }
                                    ?>
                                    <div id="upload-field-container" <?php if( $canAddImage == FALSE ): ?>style="display: none;"<?php endif; ?>>
                                        <input type="file" name="image_uplaod_field" id="image_upload_field" />
                                    </div>
                                    <div>
                                        <p>
                                            Current Images:<img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="slideshow-loader" class="ajax-loader"/>
                                        </p>
                                        <div id="slideshow-thumbnails-container">
                                            <?php if( !empty( $this->slideshowImages ) ): ?>
                                                <?php foreach( $this->slideshowImages as $image ): ?> 
                                                    <div class="thumbnail-container" id="thumbnail-container-<?php echo $image->id; ?>">
                                                        <div>
                                                            <img class="slideshow-image-thumbnail" src="images/hotelguide/gallery/hotel_<?php echo $this->profile->hotel_id; ?>/album/<?php echo $image->filename; ?>" alt="slideshow image"/>
                                                        </div>
                                                        <div class="slideshow-options-container">
                                                            <div class="delete-image-button-container">
                                                                <a href="#" class="delete-image-caption-button" id="delete_<?php echo $image->id; ?>">Delete Image</a>
                                                            </div>
                                                            <input style="vertical-align: middle; " type="text" class="slideshow-image-caption" name="image_description_<?php echo $image->id; ?>" id="image_description_<?php echo $image->id; ?>" <?php if( !empty( $image->description ) ): ?>value="<?php echo $image->description; ?>"<?php endif; ?>/>
                                                            <input style="vertical-align: middle; " type="button" class="update-image-caption-button form-buttons save-button" id="update_<?php echo $image->id; ?>" value="" />
                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- IMAGE UPLOADER: end --> 
                        
                        <!-- GREETING: start --> 
                        <div class="menu-section-head gray">
                            Greeting
                        </div>
                        <div class="menu-section-content" id="settings-greeting">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="greeting-config" id="greeting-config" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container">
                                    <div class="field-container">
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                        <input type="hidden" name="task" value="updateGreeting" />
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                    </div>
                                    <div class="field-container">
                                        <div>
                                            <p class="grayed-out form-instruction">This is where you can modify the greeting at your homepage.</p>
                                        </div>
                                        
                                        <?php
                                            $value = ( !empty( $this->profile->greeting ) )? $this->profile->greeting:NULL; 
                                        ?>
                                        <input type="text" name="greeting" value="<?php echo $value; ?>" size="80" id="greeting"/>
                                        <div class="grayed-out smaller">
                                            You have <span id="greeting-character-count"><?php if( strlen( $value ) >= 64 ): ?>0<?php endif; ?></span> characters left
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="hotel-manager-form-buttons-container">
                                    <input type="button" class="hotel-manager-save-button form-buttons save-button" value="" />
                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="greeting-config-loader" class="ajax-loader"/>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </form>
                        </div>
                        <!-- GREETING: end --> 
                        
                        <!-- HOTEL LOGO: start -->
                        <div class="menu-section-head blue">
                            Hotel Logo
                        </div>
                        <div class="menu-section-content" id="settings-hotel-logo">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="hotel-logo-config" id="hotel-logo-config" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container">
                                    <div class="field-container">
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" id="hotel-logo-option-container"/>
                                        <input type="hidden" name="task" value="updateHotelInfo" />
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" id="hotel-logo-hotel-id-container" />
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" id="hotel-logo-profile-id-container" />
                                    </div>
                                    <div class="field-container">
                                        <p class="grayed-out form-instruction">Upload your hotel logo here. For best results, please use a transparent background for your image, and limit the maximum height to 96px, or width of 214px.</p>
                                        <div id="logo_upload_field_container">
                                            <input type="file" name="logo_upload_field" id="logo_upload_field" />
                                        </div>
                                    </div>
                                    <div>
                                        <p>Current Logo:</p>
                                        <?php 
                                            $currentLogoLink = ''; 
                                            if( !empty( $this->profile->logo ) ) {
                                                $currentLogoLink = 'images/logos/'.$this->profile->hotel_id.'/'.$this->profile->logo; 
                                            } else {
                                                $currentLogoLink = '/templates/geoxity/images/logo-default.png'; 
                                            }
                                        ?>
                                        <img src="<?php echo $currentLogoLink; ?>" alt="Current Logo" id="current-logo-container" />
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </form>
                        </div>
                        <!-- HOTEL LOGO: end --> 
                        
                        <!-- SOCIAL MEDIA: start -->
                        <div class="menu-section-head gray">
                            Social Media
                        </div>
                        <div class="menu-section-content" id="settings-social-media">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="social-media-config" id="social-media-config" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container">
                                    <div class="field-container">
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                        <input type="hidden" name="task" value="updateSocialMediaSettings" />
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                    </div>
                                    <div>
                                        <p class="grayed-out form-instruction">Place your social media profile URLs here:</p>
                                    </div>
                                    <div class="field-container">
                                        <label for="twitter">
                                            <img src="<?php echo $this->imagePath; ?>icons/twitter-icon.png" alt="twitter" />
                                        </label>
                                        <?php 
                                            $value = ( !empty( $this->profile->twitter ) )? $this->profile->twitter:NULL; 
                                        ?>
                                        <input type="text" name="twitter" value="<?php echo $value; ?>" id="twitter" />
                                        <br /><span class="social-media-link-sample">(e.g. http://www.twitter.com/username )</span>
                                    </div>
                                    <div class="field-container">
                                        <label for="facebook">
                                            <img src="<?php echo $this->imagePath; ?>icons/facebook-icon.png" alt="facebook" />
                                        </label>
                                        <?php 
                                            $value = ( !empty( $this->profile->facebook ) )? $this->profile->facebook:NULL; 
                                        ?>
                                        <input type="text" name="facebook" value="<?php echo $value; ?>" id="facebook" />
                                        <br /><span class="social-media-link-sample">(e.g. https://www.facebook.com/username</span>
                                    </div>
                                    <div class="field-container">
                                        <label for="linkedin">
                                            <img src="<?php echo $this->imagePath; ?>icons/linked-in-icon.png" alt="linkedin" />
                                        </label>
                                        <?php
                                            $value = ( !empty( $this->profile->linkedin ) )? $this->profile->linkedin:NULL; 
                                        ?>
                                        <input type="text" name="linkedin" value="<?php echo $value; ?>" id="linkedin" />
                                        <br /><span class="social-media-link-sample">(e.g. http://sg.linkedin.com/in/username )</span>
                                        <div>
                                            <label for="enable_social_media">
                                                Enable social media widget?&nbsp;
                                            </label>
                                            <?php
                                                $value = ( $this->profile->social_media_enabled )? $this->profile->social_media_enabled:FALSE; 
                                            ?>
                                            <input type="checkbox" name="enable_social_media" id="enable_social_media" value="1" <?php if( $value == 1): ?>checked="checked"<?php endif; ?> />
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="hotel-manager-form-buttons-container">
                                    <input type="submit" class="hotel-manager-save-button form-buttons save-button" value="" />
                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="social-media-config-loader" class="ajax-loader"/>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </form>
                        </div>
                        <!-- SOCIAL MEDIA: end --> 

                        <!-- WEATHER: start --> 
                        <div class="menu-section-head blue">
                            Weather
                        </div>
                        <div class="menu-section-content" id="settings-weather">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <form method="post" action="" name="weather-config" id="weather-config" class="hotel-manager-form">
                                <div class="hotel-manager-form-fields-container">
                                    <div class="field-container">
                                        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                        <input type="hidden" name="task" value="updateWeather" />
                                        <?php 
                                            $value = ( !empty( $this->profile->hotel_id ) )? $this->profile->hotel_id:NULL; 
                                        ?>
                                        <input type="hidden" name="hotel_id" value="<?php echo $value; ?>" />
                                        <?php
                                            $value = ( !empty( $this->profile->id ) )? $this->profile->id:NULL; 
                                        ?>
                                        <input type="hidden" name="profile_id" value="<?php echo $value; ?>" />
                                    </div>
                                    <div class="field-container">
                                        
<!--                                        <div>
                                            <p>Set your weather Location</p>
                                        </div>
                                        <?php
                                            $value = ( !empty( $this->profile->weather_location ) )? $this->profile->weather_location:NULL; 
                                        ?>
                                        <select name="weather" id="weather_location">
                                            <?php foreach( $this->countries as $country ): ?> 
                                            <option value="<?php echo $country; ?>" <?php if( $value == $country ): ?>selected="selected"<?php endif; ?>><?php echo $country; ?></option>
                                            <?php endforeach; ?>
                                        </select>-->
                                        
                                        <div>
                                            <?php
                                                $value = ( $this->profile->weather_enabled )? $this->profile->weather_enabled:FALSE; 
                                            ?>
                                            <input type="checkbox" name="weather_enabled" id="weather_enabled" value="1" <?php if( $value == 1): ?>checked="checked"<?php endif; ?> /><label for="weather_enabled">Enable</label>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="hotel-manager-form-buttons-container">
                                    <input type="submit" class="hotel-manager-save-button form-buttons save-button" value="" />
                                    <img src="<?php echo $this->imagePath; ?>icons/ajax-loader-rectangle.gif" id="weather-config-loader" class="ajax-loader"/>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </form>
                        </div>
                        <!-- WETHER: end -->
                        
                        <!-- EMAIL AND PASSWORD: start -->
                        <div class="menu-section-head gray">
                            Change Email Address or Password
                        </div>
                        
                        <div class="menu-section-content" id="change-email-password-container">
                            <?php include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'form-messages.php' ); ?>
                            <div id="user-password-container">
                                <form method="post" action="">
                                    <label for="user-password" class="hotel-manager-form-label">Change your password:</label>
                                    <input type="password" name="password" id="user-password" /> 
                                    <label for="user-password-confirmation">Confirmation:</label>
                                    <input type="password" name="password2" id="user-password-confirmation" />
                                    <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                    <input type="hidden" name="task" value="changePassword" />
                                    <input type="submit" name="submit" value="" class="form-buttons change-button" />
                                </form>
                            </div>
                            <div id="user-email-container">
                                <form method="post" action="">
                                    <label for="user-password" class="hotel-manager-form-label">Change your email:</label>
                                    <input type="text" name="new_email" id="new_email" /> 
                                    <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                                    <input type="hidden" name="task" value="changeUserEmail" />
                                    <input type="submit" name="submit" value="" class="form-buttons change-button" />
                                </form>
                            </div>
                        </div>
                        <!-- EMAIL AND PASSWORD: end --> 
                        
                    </div>
                    <?php
                        include( JPATH_COMPONENT.DS.'views'.DS.'settings'.DS.'tmpl'.DS.'default-tab-illustration.php' ); 
                    ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <!-- SETTINGS SECTION: end --> 
    </div>
</div>

<?php } else { ?>
<h5>You need to choose the hotel that you want to manage before starting.</h5>
<?php } ?>
