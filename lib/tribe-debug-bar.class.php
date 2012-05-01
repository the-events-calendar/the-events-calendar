<?php
/**
 * Integrate Tribe debugging with debug bar plugin.
 *
 * @author Peter Chester
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

function load_tribe_debug_bar($panels) {
	if (!class_exists('TribeDebugBar') && class_exists('Debug_Bar_Panel')) {
		class TribeDebugBar extends Debug_Bar_Panel {

			private static $debug_log = array();
			
			function init() {
				$this->title( __('Tribe', 'tribe-events-calendar') );
				remove_action( 'tribe_debug', array( TribeEvents::instance(), 'renderDebug' ), 10, 2 );
				add_action( 'tribe_debug', array( $this, 'logDebug' ), 8, 3 );
				wp_enqueue_style( 'tribe-debugger', TribeEvents::instance()->pluginUrl . 'resources/debugger.css' );		
			}

			function prerender() {
				$this->set_visible( true );
			}

			function render() {
				echo '<div id="debug-bar-tribe">';
				if (count(self::$debug_log)) {
					echo '<ul>';
					foreach(self::$debug_log as $k => $logentry) {
						echo "<li class='tribe-debug-{$logentry['format']}'>";
						echo "<div class='tribe-debug-entry-title'>{$logentry['title']}</div>";
						if (isset($logentry['data']) && $logentry['data']) {
							echo '<div class="tribe-debug-entry-data"><pre>';
							print_r($logentry['data']);
							echo '</pre></div>';
						}
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '</div>';
			}

			/**
			 * log debug statements for display in debug bar
			 *
			 * @param string $title - message to display in log
			 * @param string $data - optional data to display
			 * @param string $format - optional format (log|warning|error|notice)
			 * @return void
			 * @author Peter Chester
			 */
			public function logDebug($title,$data=false,$format='log') {
				self::$debug_log[] = array(
					'title' => $title,
					'data' => $data,
					'format' => $format,
				);
			}
		}
		$panels[] = new TribeDebugBar;
	}
	return $panels;
}

add_filter( 'debug_bar_panels', 'load_tribe_debug_bar' );
