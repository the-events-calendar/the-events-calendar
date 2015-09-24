<?php
/**
 * @var string[] $messages
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


require_once 'header.php';
?>

<h2>Importing Data</h2>

<ul>
	<?php foreach ( $messages as $message ): ?>
		<li><?php esc_html_e( $message ); ?></li>
	<?php endforeach; ?>
</ul>

<p>Redirecting...</p>

<?php
require_once 'footer.php';
?>
<script>window.location.href = '<?php echo esc_url_raw( add_query_arg( array( 'action' => 'continue' ) ) ); ?>';</script>
