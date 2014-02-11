<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.model' );
    
    JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' ); 
    
    class GeoxityhotelmanagerModelReports extends JModel {

        const REVENUE_TABLE     = '#__geoxity_reports_revenue'; 
        const SESSION_TABLE     = '#__geoxity_reports_session'; 
        const PAGE_USE_TABLE    = '#__geoxity_reports_page_use'; 
        
        var $_total             = NULL; 
        var $_pagination        = NULL; 
        
        public function __construct() {
            
                parent::__construct(); 
        }
        
        
        /**
         * Returns an object with members..
         *  1. categories -> representing the categories, w/c will be displayed 
         *      as values for the x-coordinate. 
         *  2. series -> reprsenting a collection of values to be plotted on the
         *      graph
         */
        private function buildDataForMonthlyView( $inputRecordSet, $reportName ) {
            
            /*
            * Building the categories for X-Coordinates
            */
            $categories                 = array(); 
            $seriesToPlot               = array(); 
            $seriesValues               = array(); 
            $consolidationGrouping      = '';  
            foreach( $inputRecordSet as $key => $value ) {

                /*
                * Fetch a date and year from 1 value
                */
                $dateToSplit = ''; 
                switch( $reportName ) {
                    case 'revenue-reports':
                            $dateToSplit = $value[0]->date_created; 
                        break;
                    case 'site-usage-reports':
                            $dateToSplit = $value[0]->date_of_visit; 
                        break;
                    case 'active-users-reports':
                            $dateToSplit = $value[0]->generated_on; 
                        break; 
                }
                
                
                // TODO: implement checking for "current_count" field for
                // currently active users report
                
                
                list( $year, $foo, $bar ) = explode( '-', $dateToSplit ); 
                
                $weekDates = getWeekDates( $year, $key ); 

                $categories[] = 'Week '.$key.', '.$weekDates->from.' to '.$weekDates->to; 
                
                $consolidatedReports = $this->getConsolidatedReport( $value, $reportName );
                
                foreach( $consolidatedReports as $name => $accountsCreated ) {
                    $seriesValue = new stdClass(); 

                    $dataCollection     = array(); 
                    $currentIndex       = (int) array_search( $key, array_keys($inputRecordSet ) ); 
                    
                    for( $iterator = 0; $iterator < count( $inputRecordSet ); $iterator++ ) {
                        if ( $currentIndex == $iterator ) {
                            $dataCollection[ $iterator ] = $accountsCreated; 
                        } else {
                            $dataCollection[ $iterator ] = NULL; 
                        }
                    }

                    $seriesValue->name  = $name; 
                    $seriesValue->data  = $dataCollection; 

                    $seriesValues[]     = $seriesValue; 
                }
            }
            
            $returnData   = new stdClass(); 
            
            $returnData->categories = $categories; 
            $returnData->series     = $seriesValues; 
            
            return $returnData; 
        }
        
        private function buildDataForWeeklyView( $inputRecordSet, $reportName ) {
            
            /*
             * Creating Containers
             */
            $categories = array(); 
            foreach( $inputRecordSet as $report ){
                
                $dateToSplit = ''; 
                switch( $reportName ) {
                    case 'revenue-reports':
                            $dateToSplit = $report->date_created;
                        break; 
                    case 'site-usage-reports':
                            $dateToSplit = $report->date_of_visit; 
                        break; 
                    case 'active-users-reports':
                            $dateToSplit = $report->generated_on; 
                        break; 
                }

                list( $dateCreated, $time ) = explode( ' ', $dateToSplit ); 

                if( in_array( $dateCreated, $categories ) == FALSE ) {
                    $categories[] = $dateCreated; 
                }
            }
            
            /*
            * Creating series values
            */
            $seriesValues = array(); 
            foreach( $inputRecordSet as $report ) {
                
                $dateToSplit    = ''; 
                $seriesName     = ''; 
                $valueToPlot    = ''; 
                switch( $reportName ) {
                    case 'revenue-reports':
                            $dateToSplit    = $report->date_created; 
                            $seriesName     = $report->plan; 
                            $valueToPlot    = $report->number_of_accounts_created; 
                        break;
                    case 'site-usage-reports':
                            $dateToSplit    = $report->date_of_visit; 
                            $seriesName     = $report->page_name; 
                            $valueToPlot    = $report->visits; 
                        break;
                    case 'active-users-reports':
                            $dateToSplit    = $report->generated_on; 
                            $seriesName     = date( 'M d, Y', strtotime( $report->generated_on ) );  
                            $valueToPlot    = $report->current_count; 
                        break;
                }

                $seriesValue = new stdClass(); 
                $seriesValue->name = $seriesName; 

                $dataContainer = array(); 
                for( $iterator = 0; $iterator < count( $categories ); $iterator++ ) {

                    $category = $categories[ $iterator ]; 

                    list( $dateToMatch, $time ) = explode( ' ', $dateToSplit ); 

                    if( $dateToMatch == $category ) {

                        $dataContainer[] = (int) $valueToPlot; 
                    } else {

                        $dataContainer[] = NULL; 
                    }
                }

                $seriesValue->data  = $dataContainer; 

                $seriesValues[]     = $seriesValue; 
            }

            /*
                * Prettify categories
                */
            $prettyfiedCategories = array();
            foreach( $categories as $date ) {
                $prettyfiedCategories[] = date( 'D M-d', strtotime( $date ) ); 
            }
            
            $returnData             = new stdClass(); 
            $returnData->categories = $prettyfiedCategories; 
            $returnData->series     = $seriesValues; 
            
            return $returnData; 
        }
        
        /**
         * Returns an array where the keys are the number of week of a report, 
         * and the values are the report. This is to be used for weekly view
         * 
         * 
         * @param       Array       $dataCollection     Array of objects
         */
        private function filterDataForWeek( $dataCollection, $reportName ) {
            
            $strippedDownData = array(); 
            foreach( $dataCollection  as $report ) {
                
                $dateToEvaluate = ''; 
                switch( $reportName ) {
                    case 'revenue-reports':
                            $dateToEvaluate = $report->date_created; 
                        break; 
                    case 'site-usage-reports':
                            $dateToEvaluate = $report->date_of_visit; 
                        break; 
                    case 'active-users-reports':
                            $dateToEvaluate = $report->generated_on; 
                        break; 
                }
                

                $weekNumber                 = date('W', strtotime( $dateToEvaluate ) ); 
                
                list( $year, $foo, $bar )   = explode( '-', $dateToEvaluate ); 
                
                $strippedDownData[ $weekNumber ][] = $report; 
            }
            
            return $strippedDownData; 
        }

        /**
         * Returns a number representing the week number in a month, given a date.
         * For example ( 3, represents the 3rd week of August).
         * 
         * @param       String      Date formatted string
         * @return      Integer     the number of week on the month
         */
        private function getWeekNumberFromDate( $givenDate ) {
            
            assert( !empty( $givenDate ) ); 
            
            list( $year, $month, $day )     = explode( '-', $givenDate ); 

            $previousMonth                  = ( (int) $month != 1 )? ( (int) $month - 1 ):12; 

            $daysInPreviousMonth            = cal_days_in_month( CAL_GREGORIAN, $previousMonth, $year ); 

            $weekInPreviousMonth            = (int) date( 'W', strtotime( $year.'-'.$previousMonth.'-'.$daysInPreviousMonth ) ); 

            $timeStampOfGivenDate           = strtotime( $givenDate ); 

            $difference                     = $timeStampOfGivenDate - ( strtotime( $year.'-'.$previousMonth.'-'.$daysInPreviousMonth ) ); 

            $weekNumber = (int) date( 'W', $difference ); 
            
            return $weekNumber; 
        }
        
        public function getRevenueReport( $hotelID, $viewType ) {
            
            assert( !empty( $hotelID ) ); 
            
            $database   = &JFactory::getDBO(); 
                
                switch( $viewType ) {
                    case 'monthly':
                        
                            list( $year, $month, $day ) = explode( '-', date( 'Y-m-d' ) ); 
            
                            $query = 'SELECT 
                                                        *, 
                                                        DATE_FORMAT( date_created, "%b-%d-%Y" ) as date_purchased, 
                                                        count(*) as number_of_accounts_created 
                                                FROM 
                                                        '.self::REVENUE_TABLE.' 
                                                WHERE 
                                                        hotel_id="'.trim( $hotelID ).'" AND 
                                                        YEAR(date_created)='.$year.' AND MONTH(date_created)='.$month.' 
                                                GROUP BY date_purchased, plan ORDER BY date_created ASC'; 

                            $database->setQuery( $query );
                            $database->query();

                            $dataCollection = NULL; 

                            $dataCollection = $database->loadObjectList();
                            
                            $filteredData = $this->filterDataForWeek( $dataCollection, 'revenue-reports' ); 
                        
                            return $this->buildDataForMonthlyView( $filteredData, 'revenue-reports' ); 
                            
                        break;
                    case 'weekly':
                        
                            $currentWeek = date( 'W' ); 
                        
                            $query = 'SELECT 
                                                *, 
                                                DATE_FORMAT( date_created, "%b-%d-%Y" ) as date_purchased, 
                                                count(*) as number_of_accounts_created 
                                        FROM 
                                                '.self::REVENUE_TABLE.' 
                                        WHERE 
                                                hotel_id="'.trim( $hotelID ).'" AND 
                                                WEEK(date_created)='.$currentWeek.' 
                                        GROUP BY date_purchased, plan ORDER BY date_created ASC'; 
                            
                            $database->setQuery( $query ); 
                            $database->query();
                            
                            $dataCollection = $database->loadObjectList(); 
                            
                            return $this->buildDataForWeeklyView( $dataCollection, 'revenue-reports' ); 
                            
                        break;
                    case 'daily': 

                        break;
                }
            
            return $returnData; 
        }
        
        
        public function getActiveUsersReport( $hotelID, $viewFilter ) {
            
            assert( !empty( $hotelID ) ); 
            
            $database = &JFactory::getDBO(); 
            
            // TODO: implement monthly and weekly using already built implementation
            
            switch( $viewFilter ) {
                
                case 'monthly': 
                    
                            // TODO: implement with format below, using session reports 
                    
                            list( $year, $month, $day ) = explode( '-', date( 'Y-m-d' ) ); 
            
                            $query = 'SELECT * FROM '.self::SESSION_TABLE.' WHERE hotel_id="'.trim( $hotelID ).'" AND MONTH(generated_on)='.$month.' ORDER BY generated_on'; 

                            $database->setQuery( $query );
                            $database->query();

                            $dataCollection = NULL; 

                            $dataCollection = $database->loadObjectList();
                            
                            $filteredData = $this->filterDataForWeek( $dataCollection, 'active-users-reports' ); 
                            
                            return @$this->buildDataForMonthlyView( $filteredData, 'active-users-reports' ); 
                            
                    break; 
                case 'weekly': // display data in days
                            $currentWeek = date( 'W' ); 
                    
                            $query = 'SELECT * FROM '.self::SESSION_TABLE.' WHERE hotel_id="'.trim( $hotelID ).'" AND WEEK(generated_on)='.$currentWeek; 
                            
                            $database->setQuery( $query ); 
                            $database->query();
                            
                            $dataCollection = $database->loadObjectList(); 
                            
                            return @$this->buildDataForWeeklyView( $dataCollection, 'active-users-reports' ); 
                    break; 
                case 'daily': // display data in hours
                    break; 
            }
            
//            if ( empty( $dateFilter ) ) {
//                $query = 'SELECT * FROM '.self::SESSION_TABLE.' WHERE hotel_id="'.trim( $hotelID ).'" ORDER BY generated_on ASC'; 
//            } else {
//                
//                list( $lowerBoundDate, $higherBoundDate ) = explode( '-', $dateFilter ); 
//                
//                
//                if ( !empty( $lowerBoundDate )  && !empty( $higherBoundDate ) ) {
//                    
//                    $query = 'SELECT 
//                                    * 
//                              FROM 
//                                    '.self::SESSION_TABLE.' 
//                              WHERE 
//                                    hotel_id="'.trim( $hotelID ).'" AND 
//                                    generated_on BETWEEN "'.date( 'Y-m-d', strtotime( $lowerBoundDate ) ).'" AND "'.date( 'Y-m-d', strtotime( $higherBoundDate ) ).'" 
//                              ORDER BY generated_on ASC'; 
//                    
//                } else if( !empty( $lowerBoundDate ) && empty( $higherBoundDate ) ) {
//                    $query = 'SELECT 
//                                    * 
//                              FROM 
//                                    '.self::SESSION_TABLE.' 
//                              WHERE 
//                                    hotel_id="'.trim( $hotelID ).'" AND 
//                                    generated_on BETWEEN "'.date( 'Y-m-d', strtotime( $lowerBoundDate.' 00:00:00') ).'" AND "'.date( 'Y-m-d', strtotime( $lowerBoundDate.' 23:59:59' ) ).'" 
//                              ORDER BY generated_on ASC'; 
//                }
//            }
            
//            $database->setQuery( $query ); 
//            
//            $database->query(); 
//            
//            return $database->loadObjectList(); 
        }
        
        private function getConsolidatedReport( $reports, $reportName ) {
           /*
            * Consolidates report by matching their plan
            * names. If the plans are the same, then the
            * number of created accounts are to be added, 
            * otherwise, the object itself is to be added 
            * to the array as another entity
            */
            $reportContainer    = array(); 
            
            $keyElement         = NULL; 
            $elementToPlot      = NULL; 
            
            foreach( $reports as $report ) {
                
                switch( $reportName ) {
                    case 'revenue-reports':
                            $keyElement     = $report->plan;
                            $elementToPlot  = $report->number_of_accounts_created; 
                        break;
                    case 'site-usage-reports':
                            $keyElement     = $report->page_name; 
                            $elementToPlot  = $report->visits; 
                        break;
                    case 'active-users-reports':
                            $keyElement     = date( 'M d, Y', strtotime( $report->generated_on ) ); 
                            $elementToPlot  = $report->current_count; 
                        break;
                }

                if ( array_key_exists( $keyElement, $reportContainer ) ) {
                    $previousValue  = $reportContainer[ $keyElement ]; 

                    $newValue       = $previousValue + (int) $elementToPlot; 

                    $reportContainer[ $keyElement ] = $newValue; 

                } else {
                    $reportContainer[ $keyElement ] = (int) $elementToPlot; 
                }
            }
            
            return $reportContainer; 
        }
        
        public function getSiteUsageReport( $hotelID, $viewType ) {
            
            assert( $hotelID ); 
            
            $database = &JFactory::getDBO(); 
            
            switch( $viewType ) {
                case 'monthly':
                    
                            list( $year, $month, $day ) = explode( '-', date( 'Y-m-d' ) );  
                    
                            $query = '  SELECT 
                                        id, 
                                        hotel_id, 
                                        page_name,
                                        visited_on , 
                                        DATE_FORMAT( visited_on, "%b-%d-%Y" ) as date_of_visit,  
                                        count(*) as visits 
                                    FROM 
                                        #__geoxity_reports_page_use 
                                    WHERE 
                                        hotel_id="'.trim( $hotelID ).'"  AND 
                                        YEAR(visited_on)='.$year.' and MONTH(visited_on)='.$month.'
                                    GROUP BY 
                                        page_name, 
                                        visited_on 
                                    ORDER BY visited_on'; 
                            
                            $database->setQuery( $query ); 
                            $database->query(); 
                            
                            $dataCollection = $database->loadObjectList(); 
                            
                            $filteredData = $this->filterDataForWeek( $dataCollection, 'site-usage-reports' ); 
                            
                            return $this->buildDataForMonthlyView( $filteredData, 'site-usage-reports' ); 
                    break;
                case 'weekly':
                    
                            $currentWeek = date( 'W' ); 
                    
                            $query = '  SELECT 
                                            id, 
                                            hotel_id, 
                                            page_name,
                                            visited_on , 
                                            DATE_FORMAT( visited_on, "%b-%d-%Y" ) as date_of_visit,  
                                            count(*) as visits 
                                        FROM 
                                            #__geoxity_reports_page_use 
                                        WHERE 
                                            hotel_id="'.trim( $hotelID ).'"  AND 
                                            WEEK(visited_on)='.$currentWeek.' 
                                        GROUP BY 
                                            page_name, 
                                            visited_on 
                                        ORDER BY visited_on'; 
                            
                            $database->setQuery( $query ); 
                            $database->query();
                            
                            $dataCollection = $database->loadObjectList(); 
                            
                            return $this->buildDataForWeeklyView( $dataCollection, 'site-usage-reports' );  
                    break; 
                case 'daily':
                    break; 
            }
            
            
            
//            if ( empty( $dateFilter ) ) {
//                $query = 'SELECT id, hotel_id, page_name, DATE_FORMAT( visited_on, "%b-%d-%Y" ) as date_of_visit,  count(*) as visits FROM '.self::PAGE_USE_TABLE.' WHERE hotel_id="'.trim( $hotelID ).'" GROUP BY page_name, visited_on ORDER BY visited_on';
//            } else {
//                
//                list( $lowerBoundDate, $higherBoundDate ) = explode( '-', $dateFilter );
//                
//                if ( !empty( $lowerBoundDate ) && !empty( $higherBoundDate ) ) {
//                    /*
//                     * This means that a date range is used
//                     */
//                    $query = 'SELECT 
//                                id, 
//                                hotel_id, 
//                                page_name,
//                                visited_on , 
//                                DATE_FORMAT( visited_on, "%b-%d-%Y" ) as date_of_visit,  
//                                count(*) as visits 
//                              FROM 
//                                jos_geoxity_reports_page_use 
//                              WHERE 
//                                hotel_id="'.trim( $hotelID ).'"  AND 
//                                visited_on BETWEEN "'.date( 'Y-m-d', strtotime( $lowerBoundDate ) ).'" AND "'.date( 'Y-m-d', strtotime( $higherBoundDate ) ).'" 
//                              GROUP BY 
//                                page_name, 
//                                visited_on 
//                              ORDER BY visited_on
//';
//                } else if(  !empty ( $lowerBoundDate ) && empty( $higherBoundDate ) ) {
//                    /*
//                     * This means that there is only one date to evaluate
//                     */
//                    
//                    $query = 'SELECT 
//                                id, 
//                                hotel_id, 
//                                page_name, 
//                                DATE_FORMAT( visited_on, "%b-%d-%Y" ) as date_of_visit,  
//                                count(*) as visits FROM '.self::PAGE_USE_TABLE.' 
//                              WHERE 
//                                hotel_id="'.trim( $hotelID ).'" AND 
//                                visited_on BETWEEN "'.date( 'Y-m-d H:i:s', strtotime( $lowerBoundDate.' 00:00:00' ) ).'" AND "'.date( 'Y-m-d H:i:s', strtotime( $lowerBoundDate.' 23:59:59') ).'" 
//                              GROUP BY page_name, visited_on 
//                              ORDER BY visited_on';
//                }
//                
//            }
//            
//            $database->setQuery( $query ); 
//            
//            $database->query(); 
//            
//            return $database->loadObjectList(); 
        }
        
        /**
         * This is a method created specifically to handle generation of series
         * to be plotted on the graph, for the reporting feature.
         * 
         * This method is aimed to identify if a particular name is already in
         * side the series object for a graph. If the object name is indeed 
         * found inside the series, then the value is to be inserted to the data
         * array, otherwise, another object is to be created, with corresponding
         * name and data members, to represent data to be plotted.
         * 
         * @param           Array       $series         Array of series objects
         * @param           String      $objectName     Name of the object to find
         */
        private function isObjectInSeries( $series, $objectName ) {
            
            $isInObject = false; 
            
            if ( !empty( $series ) ) {
                foreach( $series as $objectToPlot ) {
                    if( $objectToPlot->name == $objectName ) {
                        $isInObject = true; 
                        break; 
                    }
                }
            }
            
            return $isInObject; 
        }
        
        public function saveCreatedAccountsLog( $log ) {
             
            $revenue = NULL; 
            $revenue = &$this->getTable( 'revenuereports' ); 
            
            return $revenue->save( $log ); 
        }
    }
?>
