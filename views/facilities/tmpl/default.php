<?php
    defined( '_JEXEC' ) or die( 'Restricted Acces' ); 
?>
<h1><?php echo $this->title; ?></h1>
<div id="facilities-content-container">
    <?php echo $this->facilitiesContent; ?>
</div>
<?php if( !empty( $this->facilities ) ) { ?> 
<div id="facilities-list-container">
    <ul>
    <?php foreach( $this->facilities as $facility ): ?>
        <li><?php echo $facility->name; ?></li>
    <?php endforeach; ?>
    </ul>
</div>
<?php } else { ?>
<div>
    <p>There are no facilities listed as of the moment</p>
</div>
<?php } ?>
