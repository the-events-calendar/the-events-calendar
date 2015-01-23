<?php


	class Tribe__Events__Asset__Factory {

		/**
		 * @return Tribe__Events__Asset__Factory
		 */
		public static function instance() {
			return new self;
		}

		/**
		 * @param string $name
		 *
		 * @return Tribe__Events__Asset__Abstract_Asset|false Either a new instance of the asset class or false.
		 */
		public function make_for_name( $name ) {
			// `jquery-resize` to `Jquery_Resize`
			$class_name = $this->get_asset_class_name( $name );
			// `Jquery_Resize` to `Tribe__Events__Asset__Jquery_Resize`
			$full_class_name = $this->get_asset_full_class_name( $class_name );

			//@todo remove when autoloading in place
			require_once dirname( __FILE__ ) . '/Abstract_Asset.php';
			$class_path = dirname( __FILE__ ) . '/' . $class_name . '.php';

			if ( ! file_exists( $class_path ) ) {
				return false;
			}

			//@todo remove when autoloading in place
			require_once $class_path;

			return new $full_class_name();
		}

		protected function get_asset_class_name( $name ) {
			// `jquery-resize` to `Jquery_Resize`
			$class_name = str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $name ) ) );

			return $class_name;
		}

		/**
		 * @param string $class_name
		 *
		 * @return string
		 */
		private function get_asset_full_class_name( $class_name ) {
			// `Jquery_Resize` to `Tribe__Events__Asset__Jquery_Resize`
			$full_class_name = $this->get_asset_class_name_prefix() . $class_name;

			return $full_class_name;
		}

		/**
		 * @return string
		 */
		private function get_asset_class_name_prefix() {
			return 'Tribe__Events__Asset__';
		}
	}