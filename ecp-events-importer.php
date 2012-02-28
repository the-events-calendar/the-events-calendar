<?php
/*
 Plugin Name: Events Calendar PRO Events Importer
 Version: 1.0
 Description: The Events Calendar PRO Events Importer is a premium add-on to the Events Calendar PRO plugin. This add-on enables import of organizers, venues and events via CSV file. Column mapping is chosen after the file is uploaded.
 Author: Modern Tribe, Inc.
 Author URI: http://tri.be?ref=importer-addon
 Text Domain: tribe-events-calendar-pro
*/

if (!class_exists('ECP_Events_Importer')) {
    class ECP_Events_importer {
	
	private static $instance;

	//instance variables
	public $pluginDir;
	public $pluginPath;
	public $pluginUrl;
	public $pluginSlug;
	public $pluginName;
	public $fileLocation;

	public $organizersLoaded = false;
	public $organizers = array();
	
	public $venuesLoaded = false;
	public $venues = array();
	
	public $eventsLoaded = false;
	public $events = array();

	public $eventColumnNames = array(// Event defaults.
					    'event_name' => 'Event Name',
					    'event_description' => 'Event Description',
					    'event_start_date' => 'Event Start Date',
					    'event_start_time' => 'Event Start Time',
					    'event_end_date' => 'Event End Date',
					    'event_end_time' => 'Event End Time',
					    //'event_all_day' => 'All Day Event',
					    'event_venue_name' => 'Event Venue Name',
					    'event_organizer_name' => 'Event Organizer Name',
					    'event_show_map_link' => 'Event Show Map Link',
					    'event_show_map' => 'Event Show Map',
					    'event_cost' => 'Event Cost',
					    'event_phone' => 'Event Phone',
					    //'event_hide' => 'Event Hide From Upcoming' 
					);
	public $venueColumnNames = array(// Venues
					    'venue_name' => 'Venue Name',
					    'venue_country' => 'Venue Country',
					    'venue_address' => 'Venue Address',
					    'venue_address2' => 'Venue Addres 2',
					    'venue_city' => 'Venue City',
					    'venue_state' => 'Venue State/Province',
					    'venue_zip' => 'Venue Zip',
					    'venue_phone' => 'Venue Phone' );
	public $organizerColumnNames = array(// Organizers
					    'organizer_name' => 'Organizer Name',
					    'organizer_email' => 'Organizer Email',
					    'organizer_website' => 'Organizer Website',
					    'organizer_phone' => 'Organizer Phone'
					    );
		
	private function __construct() {
		$this->pluginName = __( 'ECP Events Importer', 'tribe-events-importer' );
		$this->pluginDir = trailingslashit( basename( dirname(__FILE__) ) );
		$this->pluginPath = trailingslashit( dirname(__FILE__) );
		$this->pluginUrl = WP_PLUGIN_URL.'/'.$this->pluginDir;
		$this->pluginSlug = 'events-importer';
		$this->fileLocation = $this->pluginPath . 'ecp-import.csv';
	    
		add_action( 'admin_menu', array( $this, 'addImportOptionsPage' ) );
		add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
	}
	
	public function addImportOptionsPage() {
	    add_submenu_page( '/edit.php?post_type='.TribeEvents::POSTTYPE, __('CSV Import','tribe-events-importer'),  __('CSV Import','tribe-events-importer'), 'administrator', 'events-importer', array( $this, 'importPageView' ) );
	}
	
	public function importPageView() {
	    
	    //print_r($_POST);
	    
	    if(isset($_POST[ 'ecp_import_action' ])) $action = trim( $_POST[ 'ecp_import_action' ] );
		if(isset($_GET['action'])) $action = trim( $_GET[ 'action' ] );
	    
	    //if (isset($action)) die('act'.$action);
	    
	    $limit = 2500; //number of records in a batch

	    if ( ! isset( $action ) ) {

			include( $this->pluginPath . 'admin-views/import.php' );

	    } else {
			
			if ( $this->isValidAction( $action ) ) {
			    switch ( $action ) {
				case 'map':
				
				//die('map');
				
				    $import_type = $_POST[ 'import_type' ];
/*
echo '<pre>';
print_r($_POST);
print_r($_FILES);
echo '</pre>';
*/
//die('a');
				    $import_file = $_FILES[ 'import_file' ][ 'tmp_name' ];
//die('2');
				    if( isset($_POST[ 'import_header' ]) && $_POST[ 'import_header' ] ){
				    	$import_header = true;
				    }else{
				    	$import_header = false;
				    }
				    
				    //die('wee');
				    
				    $this->columnMapping( $import_type, $import_file, $import_header );
				    break;
				
				case 'import':
				    $import_type = $_POST[ 'import_type' ];
				    // Deconstruct mapping.
				    $column_mapping = array();
				    foreach( $_POST as $name => $value ) {
					if ( preg_match( '/^col_(\d+)$/', $name ) ) {
					    // Column definition.
					    $column_mapping[ str_replace( 'col_', '', $name ) ] = $value;
					}
				    }
				    
				    //save column mapping in WP options for future reference
				    update_option('tribe_events_import_column_mapping',$column_mapping);
				    update_option('tribe_events_import_type',$import_type);
				    
				    $this->importCsv( $import_type, $column_mapping, 0, $limit );
				    break;
				
				case 'continue':
				
				    $column_mapping = get_option('tribe_events_import_column_mapping');
				    $import_type = get_option('tribe_events_import_type');
				    
				    $offset = intval( $_GET['offset'] );
				    
					$this->importCsv( $import_type, $column_mapping, $offset, $limit );				
					
					
					break;
				
				default:
				    // Should never get here.
				    break;
			    }
			}
	    }
	}
	
	/**
	 * Actual import.
	 **/
	 
	private function importCsv( $import_type, $column_mapping, $offset, $limit ) {
	    
	    //set really big limits to avoid issues wth large files
	    ini_set('memory_limit', -1);
		ini_set('max_execution_time', 6000);
	    	    
	    $error_message = '';
	    $success_message = '';
	    $inverted_map = array();
	    
	    include_once( $this->pluginPath . 'lib/parsecsv.lib.php' );
	    // Bail right here and now if the file isn't available or we can't parse it.
	    if ( file_exists( $this->fileLocation ) && $csv = new parseCSV() ) {
	    
		//$csv->auto( $this->fileLocation );
		
		$csv->parseCSV($this->fileLocation, $offset, $limit);

/*
		echo 'IMPORT<pre>';
		print_r($csv);
		echo '</pre>';
*/
		
		if(!$csv->data){
		
					$results = get_option('tribe_events_import_results');
					$fail_rows = get_option('tribe_events_import_failed_rows');
					
					$error_message = '';
					$success_message = sprintf( __( "<strong>Import successfully completed!</strong><br/> <ul><li>Inserted: %d</li><li>Updated: %d</li><li>Failed: %d</li></ul>\n", 'tribe-events-importer' ),
					$results[ 'insert' ],
					$results[ 'update' ],
					$results[ 'fail' ] );
					if ( count( $fail_rows ) > 0 ) {
					$success_message .= sprintf( __( "<p>Failed Row Numbers: %s</p>", 'tribe-events-importer' ), implode( ', ', $fail_rows ) );
					}
	    
	    include( $this->pluginPath . 'admin-views/result.php' );
	    
	    //delete options
	    
	    delete_option('tribe_events_import_results');
	    delete_option('tribe_events_import_failed_rows');
	    
	    //delete files
	    unlink($this->fileLocation);
	    unlink($this->fileLocation.'.tmp');
		
		}else{
		
		
		
		// Invert the column mapping so we can grab CSV columns by name.
		// Columns that we're not importing will not be in the hash.
		foreach( $column_mapping as $col => $name ) {
		    if ( $name != '' ) {
		        $inverted_map[ $name ] = $col;		
		    }
		}
	    
		$import_function = null;
		switch( $import_type ) {
		    case 'events':
		        // We need at least an event name, and start/end date
		        if ( isset( $inverted_map[ 'event_name' ] ) &&
			     isset( $inverted_map[ 'event_start_date' ] ) ) {
			     $import_function = 'createEventFromRow';
			} else {
			    $success_message = '';
			    $error_message = __( 'Event import requires at least an Event Name and Event Start Date.', 'tribe-events-importer' );
			}
			break;
		
		    case 'organizers':
			// We at least need an organizer name.
			if ( isset( $inverted_map[ 'organizer_name' ] ) ) {
			    $import_function = 'createOrganizerFromRow';
			} else {
			    $success_message = '';
			    $error_message = __( 'Organizer import requires at least one column assigned to Organizer Name.', 'tribe-events-importer' );
			}
			break;
		
		    case 'venues':
			// We at least need a venue name.
			if ( isset( $inverted_map[ 'venue_name' ] ) ) {
			    $import_function = 'createVenueFromRow';
			} else {
			    $success_message = '';
			    $error_message = __( 'Venue import requires at least one column assigned to Venue Name.', 'tribe-events-importer' );
			}
			break;
	    
		    default:
		        break;
		}
		// Logic for the actual import. Since it's the same for all import types, abstracting
		// it up makes for cleaner code. That, or I've been spending too much time with
		// Common Lisp.
		if ( $error_message == '' && $import_function != null ) {
		    $method = array( $this, $import_function );
		    if ( isset( $csv->data ) && is_callable( $method ) ) {
			
			$results = get_option('tribe_events_import_results');
			$fail_rows = get_option('tribe_events_import_failed_rows');
			
			//initialize if not pulled from options
			if(empty($results)) $results = array( 'fail' => 0, 'update' => 0, 'insert' => 0 );
			if(empty($fail_rows)) $fail_rows = array();
			
			//$total_start = microtime();
			
			$num = $offset + 1;
			
			include 'admin-views/header.php';
			
			echo '<h3>Importing Data</h3>';
			
			echo '<p>';
			
			foreach( $csv->data as $row_num => $row ) {
			
				echo $num.'. ';
				$num++;
			
				set_time_limit(10); //increase script time limit by 10 seconds
			
			    $result = call_user_func( $method, array_values( $row ), $inverted_map );
			    $results[ $result ] = $results[ $result ] + 1;

			    // Record failed rows for report and make them 1-based.
			    if ( $result == 'fail' ) {
				$fail_rows []= ( $row_num + 1 );
			    }
			}
			
			// Save results for later display
			
			update_option('tribe_events_import_results', $results);
			update_option('tribe_events_import_failed_rows', $fail_rows);
			
			//redirect to continue processing
			
			echo '</p><p>Redirecting...</p>';

			$newoffset=$offset+$limit;
			$url = add_query_arg(array( 'post_type'=>TribeEvents::POSTTYPE, 'page'=>'events-importer', 'action'=>'continue', 'offset'=>$newoffset ), admin_url().basename($_SERVER['SCRIPT_NAME']));
		
			//echo $url;
			
			include 'admin-views/footer.php';
			echo "<script>window.location.href='".$url."';</script>";
			
		}
	    } else {
	    		$error_message = __( 'Could not import CSV file - either the file upload failed, or the file was not a CSV file.', 'tribe-events-importer' );
	    }
	    }

	    }			
	    
	}
	
	/**
	 * Creates a new venue based on a row from the CSV file, and an inverted column mapping.
	 * If a venue with the same name exists, an update will be performed instead.
	 **/
	 
	private function createVenueFromRow( $row, $inverted_mapping ) {

	    //remove save post action to avoid it being called twice
	    remove_action( 'save_post', array( TribeEvents::instance(), 'save_venue_data' ), 16, 2 );

	    $ret = 'fail';
	    if ( $this->isRowGood( $row, $inverted_mapping, 'venue_name' ) ) {
			$venue_name = $this->getFromRow( $row, $inverted_mapping, 'venue_name' );
			$venue = $this->generateVenue( $row, $inverted_mapping, $venue_name );
			
			if ( $id = $this->findVenueByName( $venue_name ) ) {
			    // Perform update
			    TribeEventsAPI::updateVenue( $id, $venue );
			    echo $venue_name . ' '.__('updated', 'tribe-events-importer').'<br>';
			    $ret = 'update';
			} else {
			    // Insert new venue.
			    $venue_id = TribeEventsAPI::createVenue( $venue );
			    echo $venue_name . ' '.__('created', 'tribe-events-importer').'<br>';
			    if ( $venue_id ) {
				// Insert so we don't dupe.
				$this->venues[ $venue_name ] = $venue_id;
			    }
			    $ret = 'insert';
			}
	    }
	    return $ret;
	}
	
	/**
	 * Utility method to go from CSV row to Tribe Events API.
	**/
	
	private function generateVenue( $row, $inverted_mapping, $venue_name ) {
	    $venue_address = trim( $this->getFromRow( $row, $inverted_mapping, 'venue_address' ) . ' ' .
				    $this->getFromRow( $row, $inverted_mapping, 'venue_address2' ) );
	    return array( 'Venue' => $venue_name,
			  'Address' => $venue_address,
			  'City' => $this->getFromRow( $row, $inverted_mapping, 'venue_city' ),
			  'Country' => $this->getFromRow( $row, $inverted_mapping, 'venue_country', 'United States' ),
			  'Province' => $this->getFromRow( $row, $inverted_mapping, 'venue_state' ),
			  'State' => $this->getFromRow( $row, $inverted_mapping, 'venue_state' ),
			  'Zip' => $this->getFromRow( $row, $inverted_mapping, 'venue_zip' ),
			  'Phone' => $this->getFromRow( $row, $inverted_mapping, 'venue_phone' ) );
	}
	
	/**
	 * Creates a new organizer based on a row from the CSV file, and an inverted column mapping.
	 * If the organizer is already found, we update it. Returns 'fail', 'insert' or 'update'
	 * depending on what was done.
	 **/
	 
	private function createOrganizerFromRow( $row, $inverted_mapping ) {
	    //remove save post action to avoid it being called twice
		remove_action( 'save_post', array( TribeEvents::instance(), 'save_organizer_data' ), 16, 2 );

	    $ret = 'fail';
	    if ( $this->isRowGood( $row, $inverted_mapping, 'organizer_name' ) ) {
		$organizer_name = $this->getFromRow( $row, $inverted_mapping, 'organizer_name' );
		$organizer = $this->generateOrganizer( $row, $inverted_mapping, $organizer_name );
		if ( $id = $this->findOrganizerByName( $organizer_name ) ) {
		    // Perform update.
		    TribeEventsAPI::updateOrganizer( $id, $organizer );
   		    echo $organizer_name . ' '.__('updated', 'tribe-events-importer').'<br>';
		    $ret = 'update';
		} else {
		    // Insert new organizer.
		    $organizer_id = TribeEventsAPI::createOrganizer( $organizer );
   		    echo $organizer_name . ' '.__('created', 'tribe-events-importer').'<br>';
		    if ( $organizer_id ) {
			$this->organizers[ $organizer_name ] = $organizer_id;
		    }
		    $ret = 'insert';
		}
	    }
	    return $ret;
	}
	
	/**
	 * Utility method to go from CSV row to Tribe Events API.
	**/
	
	private function generateOrganizer( $row, $inverted_mapping, $organizer_name ) {
	    return array( 'Organizer' => $organizer_name,
			  'Email' => $this->getFromRow( $row, $inverted_mapping, 'organizer_email' ),
			  'Phone' => $this->getFromRow( $row, $inverted_mapping, 'organizer_phone' ),
			  'Website' => $this->getFromRow( $row, $inverted_mapping, 'organizer_website') );
	}
	
	/**
	 * Creates a new event based on a row from the CSV file, and an inverted column mapping.
	**/
	
	private function createEventFromRow( $row, $inverted_mapping ) {
	    $ret = 'fail';
	    if ( $this->isRowGood( $row, $inverted_mapping, 'event_name' ) &&
		 $this->isRowGood( $row, $inverted_mapping, 'event_start_date' ) ) {
		$event_name = $this->getFromRow( $row, $inverted_mapping, 'event_name' );
		$event_start_date = $this->getDateTime( $row, $inverted_mapping, 'event_start_date', 'event_start_time', '', '9:00a' );
		// If no end date is given, we default to 5pm on the start date.
		$event_end_date = $this->getDateTime( $row, $inverted_mapping, 'event_end_date',
						      'event_end_time', date( 'Y-m-d', $event_start_date ), '5:00p' );
		$event = $this->generateEvent( $event_name, $event_start_date, $event_end_date, $row, $inverted_mapping );
		if ( $id = $this->findEventByNameAndDate( $event_name, $event_start_date, $event_end_date ) ) {
		    // Event already exists, so update.
			//$start = microtime();
		    TribeEventsAPI::updateEvent( $id, $event );
/*
			$end = microtime();
			$elapsed = $end - $start;
			echo "<pre>Update event: $elapsed</pre>";
*/
			$end = microtime();
			echo $event_name . ' '.__('updated', 'tribe-events-importer').'<br>';
			flush();
		    $ret = 'update';
		} else {
		    // Create new event.
			$start = microtime();
		    $id = TribeEventsAPI::createEvent( $event );
		    // Insert into hash table so we don't re-insert.
		    $this->events[ $this->generateEventKey( $event_name, $event_start_date, $event_end_date ) ] = $id;
/*
			$end = microtime();
                        $elapsed = $end - $start;
                        echo "<pre>Created event: $elapsed</pre>";
*/
			$end = microtime();
			echo $event_name . ' '.__('created', 'tribe-events-importer').'<br>';
			flush();
		    $ret = 'insert';
		}
	    }
	    return $ret;
	}
	
	/**
	 * Formats the event data from the row in an array that can be passed to the API.
	**/
	
	private function generateEvent( $event_name, $event_start, $event_end, $row, $inverted_mapping ) {
	    $ret = array( 'post_title' => $event_name,
			  'post_status' => 'publish',
			  'post_content' => $this->getFromRow( $row, $inverted_mapping, 'event_description' ),
			  'EventStartDate' => date( 'Y-m-d', $event_start ),
			  'EventStartHour' => date( 'h', $event_start ),
			  'EventStartMinute' => date( 'i', $event_start ),
			  'EventStartMeridian' => date( 'a', $event_start ),
			  'EventEndDate' => date( 'Y-m-d', $event_end ),
			  'EventEndHour' => date( 'h', $event_end ),
			  'EventEndMinute' => date( 'i', $event_end ),
			  'EventEndMeridian' => date( 'a', $event_end ),
			  'EventShowMapLink' => $this->getFromRow( $row, $inverted_mapping, 'event_show_map_link' ),
			  'EventShowMap' => $this->getFromRow( $row, $inverted_mapping, 'event_show_map' ),
			  'EventCost' => $this->getFromRow( $row, $inverted_mapping, 'event_cost' ),
			  'EventAllDay' => $this->getFromRow( $row, $inverted_mapping, 'event_all_day', false ),
			  'EventHideFromUpcoming' => $this->getFromRow( $row, $inverted_mapping, 'event_hide' ) );
	    
	    // Organizer & Venue IDs
	    $organizer_id = $this->findOrganizerByName( $this->getFromRow( $row, $inverted_mapping, 'event_organizer_name' ) );
	    $venue_id = $this->findVenueByName( $this->getFromRow( $row, $inverted_mapping, 'event_venue_name' ) );
	    if ( $organizer_id != false ) {
		$ret[ 'Organizer' ] = array( 'OrganizerID' => $organizer_id );
	    }
	    if ( $venue_id != false ) {
		$ret[ 'Venue' ] = array( 'VenueID' => $venue_id );
	    }
	    
	    return $ret;
	}
	
	/**
	 * Constructs a date with time from given parameters, using a default time
	 * if none is provided in the row.
	**/
	
	private function getDateTime( $row, $inverted_mapping, $date_key, $time_key, $time_default ) {
	    $ret = false;
	    $date = $this->getFromRow( $row, $inverted_mapping, $date_key );
	    $time = $this->getFromRow( $row, $inverted_mapping, $time_key, $time_default );
	    if ( $date != '' && $time != '' ) {
		$ret = strtotime( $date . ' ' . $time );
	    }
	    return $ret;
	}
	
	/**
	 * Attempts to find an event with the same name and start/end dates. If found,
	 * returns the ID, otherwise false. We use the DB here since there might
	 * be too many events for a hash table.
	**/
	
	private function findEventByNameAndDate( $name, $start_date, $end_date ) {
	    $ret = false;
	    if ( $this->eventsLoaded == false ) {
		$this->populateEventsTable();
		$this->eventsLoaded = true;
	    }
	    if ( $name != '' && $start_date && $end_date ) {
		$key = $this->generateEventKey( $name, $start_date, $end_date );
		if ( isset( $this->events[ $key ] ) ) {
			$ret = $this->events[ $key ];
		}
	    }
	    return $ret;
	}
	
	private function populateEventsTable() {
		$query_args = array( 'post_type' => 'tribe_events',
                                 'post_status' => 'publish',
				 'posts_per_page' => -1);
		$q = new WP_Query( $query_args );
		while( $q->have_posts() ) {
			$q->the_post();
			$id = get_the_ID();
	                $title = get_the_title();
			$start_date = strtotime( get_post_meta( $id, '_EventStartDate', true ) );
			$end_date = strtotime( get_post_meta( $id, '_EventEndDate', true ) );
			$this->events[ $this->generateEventKey( $title, $start_date, $end_date ) ] = $id;
		}
	}

	private function generateEventKey( $name, $start_date, $end_date ) {
		$s = date( 'Y-m-d h:ia', $start_date );
		$e = date( 'Y-m-d h:ia', $end_date );
		return md5( "$name $s $e" );
	}	

	/**
	 * Attempts to locate an organizer with the given name and returns the ID
	 * if found. Otherwise returns false.
	 **/
	
	private function findOrganizerByName( $name ) {
	    $ret = false;
	    if ( $this->organizersLoaded == false ) {
		$this->populateOrganizerTable();
	    }
	    
	    if ( $name != '' && isset( $this->organizers[ $name ] ) ) {
		$ret = $this->organizers[ $name ];
	    }
	    return $ret;
	}
	
	/**
	 * Attempts to locate a venue with the given name and returns the ID if found.
	 * Otherwise returns false.
	**/
	
	private function findVenueByName( $name ) {
	    $ret = false;
	    if ( $this->venuesLoaded == false ) {
		$this->populateVenueTable();
	    }
	    
	    if ( $name != '' && isset( $this->venues[ $name ] ) ) {
		$ret = $this->venues[ $name ];
	    }
	    return $ret;
	}
	
	/**
	 * Populates organizer table so we can do lookups without hitting the DB.
	 **/
	
	private function populateOrganizerTable() {
   	    $q = new WP_Query( 'post_type=tribe_organizer&post_status=publish&posts_per_page=-1' );
	    while ( $q->have_posts() ) {
		$q->the_post();
		$id = get_the_ID();
		$name = get_post_meta( $id, '_OrganizerOrganizer', true );
		$this->organizers[ $name ] = $id;
	    }
	    $this->organizersLoaded = true;
	}
	
	/**
	 * Populates venues table so we can do lookups without hitting the DB.
	**/
	
	private function populateVenueTable() {
	    $q = new WP_Query( 'post_type=tribe_venue&post_status=publish&posts_per_page=-1' );
	    while ( $q->have_posts() ) {
		$q->the_post();
		$id = get_the_ID();
		$name = get_post_meta( $id, '_VenueVenue', true );
		$this->venues[ $name ] = $id;
	    }
	    $this->venuesLoaded = true;
	}
	
	/**
	 * Checks that the key exists in the inverted mapping table and that the value
	 * exists in the row, and is not empty.
	**/
	
	private function isRowGood( $row, $inverted_mapping, $key ) {
	    $ret = false;
	    if ( isset( $inverted_mapping[ $key ] ) &&
		 isset( $row[ $inverted_mapping[ $key ] ] ) &&
		 trim( $row[ $inverted_mapping[ $key ] ] ) != '' ) {
		$ret = true;
	    }
	    return $ret;
	}
	
	/**
	 * Generic get function for row and inverted mapping. Not necessary, but it cleans
	 * up the code and makes for better error checking.
	 **/
	 
	private function getFromRow( $row, $inverted_mapping, $key, $default_value='' ) {
	    $ret = $default_value;
	    if ( isset( $inverted_mapping[ $key ] ) && isset( $row[ $inverted_mapping[ $key ] ] ) ) {
		$ret = trim( $row[ $inverted_mapping[ $key ] ] );
	    }
	    return $ret;
	}
	
	/**
	 * Column mapping functionality.
	 **/
	 
	 private function columnMapping( $import_type, $import_file, $import_header ) {
	    
	    ini_set("auto_detect_line_endings", true);
	    
	    // User has submitted an import request and file.
	    $error_message = '';
	    
	    // Are there duplicate fields on first line?
	    $dupes = false;
	    
	    //clear out old files
	  	if( file_exists($this->fileLocation.'.tmp') )
	    	unlink($this->fileLocation.'.tmp');

	  	if( file_exists($this->fileLocation) )
	    	unlink($this->fileLocation);
	    
	    include_once( $this->pluginPath . 'lib/parsecsv.lib.php' );
	    if ( file_exists( $import_file ) && $csv = new parseCSV() ) {
		
		// Move file to known location.
		if ( move_uploaded_file( $import_file, $this->fileLocation ) ) {
			
			    //copy first 5 lines to seperate file and parse that (fix for big files)
			    
			    $csvfile = fopen($this->fileLocation, 'rb');
			    
			    $tmpfile = fopen($this->fileLocation.'.tmp','wb');
			    
			    for($i = 1; $i <= 5; $i++){
			    
			    	//$line = stream_get_line($cfile, 1000000, "\r");
					$line = fgets($csvfile);
					
			    	if(1 == $i){
			    		$first_line = $this->csv2array($line, ',', '', '\\');
			    		//$first_line = str_getcsv($line);
			    		
						//print_r($first_line);
						
						$first_line_length = strlen($line);
						
						$line_end = substr($line,-1);
						
   					    //determine if we need to fix the file first
				    	if( (count($first_line) != count(array_unique($first_line))) || !$import_header ){
				    		
				    		$dupes = true;
				    		
				    		$new_line = '';
				    		$x = 1;
				    		
				    		foreach( $first_line as $item ){
				    		
				    			$item = stripslashes($item);
				    			
				    			$item = str_replace(array('\\','/','"'), '', $item);
				    			
				    			if( $import_header ){
				    				//fix field labels to be unique
				    				$new_line .= $x .'. '. $item . ',';
				    			}else{
				    				//add unique field labels
				    				$new_line .= $x .'. '.$item.',';
				    			}
				    			
				    			$x++;
				    			
				    		}
				    		
				    		if( $import_header ){
					    		$first_line_fixed = substr($new_line,0, strlen($new_line)-1);
					    		$line = $first_line_fixed;
					    	}else{
					    		$first_line_fixed = substr($new_line,0, strlen($new_line)-1) .$line_end;
					    		$line = $first_line_fixed . $line;
					    	}
				    	}

			    	
			    	}
					
					
			    	fwrite($tmpfile, $line);
			    

			    } //end for

			    //close files
				fclose($csvfile);
		    	fclose($tmpfile);
		    	
		    	if( $dupes ){
		    	
			    	//fix master CSV file
		    		if( $import_header ){
		    			//prepend, excluding first line from read
			    		$fileContents = file_get_contents($this->fileLocation, null, null, $first_line_length);
						file_put_contents($this->fileLocation, $first_line_fixed . $fileContents);
			    	}else{
			    		//prepend, overwriting existing file
			    		$fileContents = file_get_contents($this->fileLocation);
						file_put_contents($this->fileLocation, $first_line_fixed . $fileContents);	    	
			    	}
		    	
		    	}			    
						    
			    
			    $csv = new parseCSV( $this->fileLocation.'.tmp');
			    
/*
			    echo 'TMP<pre>';
			    print_r($csv);
			    echo '</pre>';
*/
			    
			    if ( !$csv ) {
				// Couldn't parse CSV.
				$error_message = __( 'Sorry, this file does not appear to be a valid CSV file.', 'tribe-events-importer' );
			    }
			} else {
			    // File couldn't be moved. Likely permissions issue.
			    $error_message = sprintf( __( 'Sorry, it looks like there is a permissions issue on your server. Please ensure that %s is writable by the webserver.', 'tribe-events-importer' ) );
			}
	    } else {
			// Hmm. File wasn't uploaded or something funky happened.
			$error_message = __( 'Error uploading file. Are you sure it was included?', 'tribe-events-importer' );
	    }
	    include( $this->pluginPath . 'admin-views/columns.php' );
	}
	
	/**
	 * Generates an HTML <select> with <option>s for a particular column.
	 **/
	
	public function generateColumnSelects( $col, $title, $type ) {
	    $ret = '<select name="col_' . $col . '">';
	    $defaults = array();
	    if ( $type == 'events' ) {
		$defaults = array_merge( $defaults, $this->eventColumnNames );
	    } elseif ( $type == 'venues' ) {
		$defaults = array_merge( $defaults, $this->venueColumnNames );
	    } elseif ( $type == 'organizers' ) {
		$defaults = array_merge( $defaults, $this->organizerColumnNames );
	    } else {
		// ??
	    }
	    
	    $ret .= '<option value="" selected="selected">' . __( 'Do Not Import', 'tribe-events-importer' ) . '</option>';
	    foreach( $defaults as $key => $value ) {
		$ret .= '<option value="' . $key . '">' . $value . '</option>';
	    }
	    $ret .= '</select>';
	    return $ret;
	}
	
        public static function instance() {
    	    if (!isset(self::$instance)) {
		$className = __CLASS__;
		self::$instance = new $className;
	    }

	    return self::$instance;
	}
	
	private function isValidAction( $action ) {
	    $ret = false;
	    if ( $action == 'import' || $action == 'map' || $action == 'continue' ) {
		$ret = true;
	    }
	    return $ret;
	}

	public function addMetaLinks( $links, $file ) {
		if ( $file == $this->pluginDir . 'ecp-events-importer.php' ) {
			$anchor = __( 'Support', 'tribe-events-importer' );
			$links []= '<a href="http://tri.be/support/?ref=importer-addon">' . $anchor . '</a>';
			$anchor = __( 'View All Add-Ons', 'tribe-events-importer' ); 
			$links []= '<a href="http://tri.be/shop/?ref=importer-addon">' . $anchor . '</a>';
		}
		return $links;
	}
	
	public function csv2array($input,$delimiter=',',$enclosure='"',$escape='\\'){ 
	    if( $enclosure ){
	    	$fields=explode($enclosure.$delimiter.$enclosure,substr($input,1,-1)); 
	    }else{
	    	$fields=explode($enclosure.$delimiter.$enclosure,$input); 
	    }
	    foreach ($fields as $key=>$value) 
	        $fields[$key]=str_replace($escape.$enclosure,$enclosure,$value); 
	    return($fields); 
	} 
	
}
    
    /** Load and dependecy checks. **/
    
    function Tribe_ECP_Events_Importer_Load() {
        if( class_exists('TribeEventsPro') ) {
	    ECP_Events_importer::instance();
	} else {
	    add_action( 'admin_notices', 'show_importer_fail_message' );
	}
    }
	
    add_action( 'plugins_loaded', 'Tribe_ECP_Events_Importer_Load' );
    
    function show_importer_fail_message() {
	$currentScript = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	if ( current_user_can( 'activate_plugins') && ( substr( $currentScript, -11 ) == 'plugins.php') ) {
	    echo '<div class="error"><p>' . __('The Events Calendar PRO - Events Importer requires the Events Calendar PRO plugin.', 'tribe-events-importer' ) . '</p></div>';
	}
    }
}