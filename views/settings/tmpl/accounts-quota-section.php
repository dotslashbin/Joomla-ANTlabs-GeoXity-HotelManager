<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
?>
<div class="accounts-quota-container">
    Quota: <span class="accounts-count-container"><?php echo $this->profile->accounts_used; ?></span> / <span class="accounts-max-container"><?php echo $this->profile->accounts_quota; ?></span>
</div>
