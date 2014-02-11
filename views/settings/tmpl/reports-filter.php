<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    function generateReportFilters( $reportName ) {
?>
        <div class="graph-data-filter-container">
            
            <div class="view-type-choices-container" id="<?php echo $reportName; ?>_view-type-choices-container">
                <span>Choose view type:</span>
                <input checked="checked" class="view-filter <?php echo $reportName; ?>_radio" type="radio" name="<?php echo $reportName; ?>-view-filter" value="monthly" id="<?php echo $reportName; ?>_monthly_view-filter"/>&nbsp;<label for="<?php echo $reportName; ?>_monthly_view-filter" >Monthly</label>
                <input class="view-filter <?php echo $reportName; ?>_radio" type="radio" name="<?php echo $reportName; ?>-view-filter" value="weekly" id="<?php echo $reportName; ?>_weekly_view-filter" />&nbsp;<label for="<?php echo $reportName; ?>_weekly_view-filter" >Weekly</label>
                <input class="view-filter <?php echo $reportName; ?>_radio" type="radio" name="<?php echo $reportName; ?>-view-filter" value="daily" id="<?php echo $reportName; ?>_daily_view-filter"/>&nbsp;<label for="<?php echo $reportName; ?>_daily_view-filter">Daily</label>
            </div>
            <!--
            <div id="<?php echo $reportName; ?>_week-choices-container" class="filter-subchoice-container" style="display: none;">
                <label for="<?php echo $reportName; ?>_week-choices">Choose week</label>
                <select name="" id="<?php echo $reportName; ?>_week-choices">
                    <option value="1">1st Week</option>
                    <option value="2">2nd Week</option>
                    <option value="3">3rd Week</option>
                    <option value="4">4th Week</option>
                    <option value="5">5th Week</option>
                </select>
            </div>  
            -->
            <div class="report-generate-reset-container" id="<?php echo $reportName; ?>_report-generate-reset-container" >
                <input type="button" value="Generate Graph" class="generate-report-button" id="<?php echo $reportName; ?>_generate-graph-button" /> 
<!--                <input type="button" value="Reset" class="reset-graph-button" id="<?php echo $reportName; ?>-reset-graph" />-->
            </div>
        </div>
        <div class="clear"></div>
<?php 
    }
?>