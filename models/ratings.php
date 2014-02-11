<?php
    defined( '_JEXEC' ) or die( 'Restricted Access' ); 
    
    jimport( 'joomla.application.component.model' );
    
    JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' ); 
    
    class GeoxityhotelmanagerModelRatings extends JModel {
        
        const RATINGS_TABLE     = '#__geoxity_hotel_ratings'; 
        
        const ROW_LIMIT         = 5; 
        
        var $_total             = NULL; 
        var $_pagination        = NULL; 
        
        public function __construct() {
            parent::__construct();
            
            $mainframe = JFactory::getApplication(); 
        }
        
        /**
         * Returns an array of objects representing a rating record
         */
        public function getData( $hotelID, $page = 1, $sort = 1, $wasRead = NULL, $dateRange = NULL, $rateRangeFrom = NULL, $rateRangeTo = NULL, $noLimit = FALSE ) {
            assert( !empty( $hotelID ) ); 
            
            $database = &JFactory::getDBO(); 
            
            $offset = ( $page - 1 ) * self::ROW_LIMIT; 
            
            $sortString = ''; 
            
            switch( $sort ) {
                case 1:
                        $sortString = 'ORDER BY date_added ASC'; 
                    break; 
                case 2:
                        $sortString = 'ORDER BY date_added DESC'; 
                    break; 
                case 3:
                        $sortString = 'ORDER BY rating ASC'; 
                    break; 
                case 4:
                        $sortString = 'ORDER BY rating DESC'; 
                    break; 
            }
            
            $whereParameters = array(); 
            
            $whereParameters[] = 'hotel_id='.$hotelID; 
            
            if( $wasRead != NULL ) {
                $whereParameters[] = 'was_read='.$wasRead; 
            }
            
            if( !empty( $dateRange ) ) {
                $dateRangeContainer = explode( ' - ', $dateRange ); 
                
                $whereParameters[] = 'date_added BETWEEN "'.date( 'Y-m-d', strtotime( $dateRangeContainer[0] ) ).'" AND "'.date( 'Y-m-d', strtotime( $dateRangeContainer[1] ) ).'"'; 
            }
            
            if( !empty( $rateRangeFrom )  ) {
                $whereParameters[] = 'rating >= '.$rateRangeFrom; 
            }
            
            if( !empty( $rateRangeTo ) ) {
                $whereParameters[] = 'rating <='.$rateRangeTo; 
            }
            
            $query = 'SELECT * FROM '.self::RATINGS_TABLE.' WHERE  '.implode( ' AND ', $whereParameters ).' '.$sortString; 
            
            $limit = ( $noLimit == FALSE )? self::ROW_LIMIT:NULL; 
            
            $database->setQuery( $query , $offset, $limit ); 
            
            $database->query(); 
            
            $results = @$database->loadObjectList(); 
            
            foreach( $results as $rating ) {
                $user = &JFactory::getUser( $rating->rated_by ); 
                
                $rating->rated_by = $user; 
            }   
            
            return $results; 
        }
        
        public function getRating( $ID ) {
            assert( !empty( $ID ) ); 
            
            $rating = $this->getTable( 'ratings' ); 
            
            $rating->load( $ID ) ; 
            
            return $rating;
        }
        
        /**
         * Returns an integer representing the total number of ratings
         */
        public function getTotalCount( $hotelID ) {
            assert( !empty( $hotelID ) ); 
            
            $database = &JFactory::getDBO(); 
            $database->setQuery( 'SELECT * FROM '.self::RATINGS_TABLE.' WHERE hotel_id='.$hotelID ); 
            
            $database->query(); 
            
            return $database->getNumRows(); 
        }
        
        public function getNewRatingsCount( $hotelID ) {
            assert( !empty( $hotelID ) ); 
            
            $database = &JFactory::getDBO(); 
            $database->setQuery( 'SELECT * FROM '.self::RATINGS_TABLE.' WHERE hotel_id='.$hotelID.' AND was_read=0' ); 
            
            $database->query(); 
            
            return $database->getNumRows(); 
        }
        
        public function saveRating( $ratingInput ) {
            assert( !empty( $ratingInput ) ); 
            
            $rating = NULL; 
            $rating = $this->getTable( 'ratings' ); 
            
            return $rating->save( $ratingInput ); 
        }
    }
?>
