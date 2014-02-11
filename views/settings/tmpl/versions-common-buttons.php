<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 

    function createCommonButtons( $profile, $content, $section = NULL ) {
?>
    &nbsp; 
    <input type="button" id="<?php echo $section; ?>_<?php echo $content->id; ?>" class="<?php echo $section.'-preview-button'; ?> content-preview-buttons form-buttons disabled-preview-button" />
    &nbsp; 
    <?php if( $profile->hotel_account_status == 'production'): ?>
    <input type="button" id="<?php echo $section; ?>_<?php echo $content->id; ?>" class="<?php echo $section.'-publish-button'; ?> current-geoxity-content-link form-buttons disabled-publish-button" />
    <?php endif; ?>
<?php } ?>

