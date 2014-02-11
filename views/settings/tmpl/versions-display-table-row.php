<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
?>
<?php function createVersionsRow( $profile, $content, $section ) { 
    
    $rowClass = ''; 
    
    switch( $section ) {
        case 'hotel-info':
                $rowClass = 'hotel-info-data-container'; 
            break; 
        case 'facilities-content':
                $rowClass = 'facilities-content-data-container'; 
            break; 
        case 'customizable-page':
                $rowClass = 'customizable-page-data-container'; 
            break; 
        default:
            break; 
    }
?>
<tr id="geoxitycontentdata_<?php echo $content->id; ?>" class="<?php echo $rowClass; ?>">
    <input type="hidden" id="contenttitlecontainer_<?php echo $content->id; ?>" value="<?php echo $content->title; ?>">
    <input type="hidden" id="geoxitycontentcontainer_<?php echo $content->id; ?>" value="<?php echo $content->content; ?>" />
    <td id="contenttitle_<?php echo $content->id; ?>" >
        <?php if( $section === 'hotel-info' ): ?>
        <a class="content-preview-link" target="_blank" href="http://portal.geoxity.com/index.php?hotel_id=<?php echo $profile->hotel_id; ?>&option=com_geoxityhelpers&task=viewHotelInfo&content_id=<?php echo $content->id; ?>">
            Preview
        </a>
        <?php endif; ?>
        <?php if( $section === 'facilities-content' ): ?>
        <a class="content-preview-link" target="_blank" href="http://portal.geoxity.com/index.php?hotel_id=<?php echo $profile->hotel_id; ?>&option=com_geoxityhelpers&task=viewFacilities&content_id=<?php echo $content->id; ?>">
            Preview
        </a>
        <?php endif; ?>
        <?php if( $section === 'customizable-page' ): ?>
        <a class="content-preview-link" target="_blank" href="http://portal.geoxity.com/index.php?hotel_id=<?php echo $profile->hotel_id; ?>&option=com_geoxityhelpers&task=viewCustomizablePage&content_id=<?php echo $content->id; ?>">
            <?php echo $content->title; ?>
        </a>
        <?php endif; ?>
    </td>
    <td><?php echo date( 'Y-m-d H:i:s', strtotime( $content->date_added ) ); ?></td>
    <td>
        <input type="button" id="loadgeoxitycontent_<?php echo $content->id; ?>" class="form-buttons edit-button" value="">
        &nbsp;
        <?php
            $state  = ( $content->is_current == 0 )? 'enabled':'disabled'; 
        ?>
        <input type="button" id="deletegeoxitycontent_<?php echo $content->id; ?>" class="delete-geoxity-content-link form-buttons delete-button" value="" <?php if( $content->is_current == 1 ): ?>DISABLED<?php endif; ?>/>
        &nbsp; 
        <input type="button" id="setcurrent_<?php echo $content->id; ?>" class="current-geoxity-content-link form-buttons publish-button" value="" <?php if( $content->is_current == 1 ): ?>DISABLED<?php endif; ?>>
    </td>
    <td class="label-container">
        <?php $label = ( $content->is_current == 1 )? 'active':'inactive' ; ?>
        <span id="label-container-content_<?php echo $content->id; ?>" class="<?php echo $label; ?>"><?php echo ucwords( $label ); ?></span>
    </td>
</tr>
<?php } ?>