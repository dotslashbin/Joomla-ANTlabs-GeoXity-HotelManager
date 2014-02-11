<?php
    defined( '_JEXEC' ) or die ( 'Restricted Access' ); 
?>
<div class="form-messages-container">
    <?php if( !empty( $currentMessageType ) && $currentMessageType === 'info' ) { ?>
        <div class="form-message info" id="form-message-info" style="display: block;">
            <?php echo $currentMessage; ?>
        </div>
    <?php } else { ?>
        <div class="form-message info" id="form-message-info"></div>
    <?php } ?>
    
    <?php if( !empty( $currentMessageType) && $currentMessageType === 'notice' ) { ?>
        <div class="form-message success" id="form-message-success" style="display: block;"><?php echo $currentMessage; ?></div>
    <?php } else { ?>
        <div class="form-message success" id="form-message-success"></div>
    <?php } ?>
    
    <?php if( !empty( $currentMessageType) && $currentMessageType === 'warning' ) { ?>
        <div class="form-message warning" id="form-message-warning" style="display: block;"><?php echo $currentMessage; ?></div>
    <?php } else  { ?>
        <div class="form-message warning" id="form-message-warning"></div>
    <?php } ?>
    
    <?php if( !empty( $currentMessageType) && $currentMessageType === 'error' ) { ?>
        <div class="form-message error" id="form-message-error" style="display: block;"><?php echo $currentMessage; ?></div>
    <?php } else { ?>
        <div class="form-message error" id="form-message-error"></div>
    <?php } ?>
</div>