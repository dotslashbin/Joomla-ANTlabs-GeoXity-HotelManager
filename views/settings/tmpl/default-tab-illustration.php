<?php 
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
?>
<div class="illustration-container">
                        
    <div class="page-header">View Current Portal</div>
    <img id="default-portal-preview-pic" src="<?php echo $this->imagePath; ?>illustrations/Message-Center.jpg " atl="Page Illustration"/>
    <div>
        <input type="button" style="margin-top: 10px;" onclick="window.open( 'http://portal.geoxity.com/?hotel_id=<?php echo $this->profile->hotel_id; ?>' );" class="form-buttons preview-button" />
    </div>

    <!--
    <p class="illustration-label">
        Things you can change on your geoXity portal
    </p>
    <img class="smaller" src="<?php echo $this->imagePath; ?>illustrations/Settings.jpg" atl="Page Illustration"/>
    <div class="illustration-preview-container">
        <input type="button" onclick="window.open( 'http://portal.geoxity.com/?hotel_id=<?php echo $this->profile->hotel_id; ?>' );" class="form-buttons preview-button" />
    </div>
    -->

</div>