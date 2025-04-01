/**
 * Tests for the Migration_Notice class.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Notice
 */

namespace TEC\Events\Category_Colors\Migration\Notice;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Common\StellarWP\AdminNotices\AdminNotices;
use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Scheduler\Abstract_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Preprocessing_Action;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;

/**
 * Class Migration_Notice_Test
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Notice
 */
class Migration_Notice_Test extends WPTestCase
{
    use With_Uopz;
    use MatchesSnapshots;

    /**
     * @var Migration_Notice
     */
    private Migration_Notice $notice;

    /**
     * @var array<string> List of notice IDs used in tests
     */
    private array $notice_ids = [
        'tec_category_colors_migration_notice',
        'tec_category_colors_migration_success',
        'tec_category_colors_migration_error',
    ];

    /**
     * @var string The test nonce value
     */
    private string $test_nonce = '1234567890';

    /**
     * @before
     */
    public function set_up(): void
    {
        parent::setUp();

        // Clear any existing notices
        $this->clear_admin_notices();

        $this->notice = new Migration_Notice();

        // Reset migration status before each test
        Status::update_migration_status(Status::$not_started);

        // Create a mock option for the old plugin
        update_option('teccc_options', [ 'some' => 'value' ]);

        // Mock is_plugin_active to return true
        $this->set_fn_return('is_plugin_active', true);

        // Mock tribe_exit to do nothing
        $this->set_fn_return('tribe_exit', null);

        // Mock wp_create_nonce to return our test nonce
        $this->set_fn_return('wp_create_nonce', $this->test_nonce);

        // Mock wp_verify_nonce to return 1 (valid) for our test nonce
        $this->set_fn_return('wp_verify_nonce', function ($nonce) {
            return $nonce === $this->test_nonce ? 1 : false;
        });

        // Mock check_admin_referer to return true
        $this->set_fn_return('check_admin_referer', function ($action, $nonce = null) {
            return $nonce === $this->test_nonce || $nonce === null;
        });

        // Mock the schedule method on the parent class to update status
        $this->set_class_fn_return(Abstract_Action::class, 'schedule', function() {
            Status::update_migration_status(Status::$preprocessing_scheduled);
            return 123;
        }, true);
    }

    /**
     * @after
     */
    public function tear_down(): void
    {
        parent::tearDown();
        delete_option('teccc_options');
        Status::update_migration_status(Status::$not_started);
        $this->clear_admin_notices();
        delete_option(Config::MIGRATION_DATA_OPTION);
        delete_option(Config::MIGRATION_PROCESSING_OPTION);
    }

    /**
     * Clears all registered admin notices used in these tests.
     */
    private function clear_admin_notices(): void
    {
        // Clear any dismissed notices for the current user
        AdminNotices::resetAllNoticesForUser(get_current_user_id());

        // Remove each notice we know about
        foreach ($this->notice_ids as $notice_id) {
            AdminNotices::removeNotice($notice_id);
        }
    }

    /**
     * Captures the admin notices output by triggering the admin_notices action.
     */
    private function capture_admin_notices(): string
    {
        ob_start();
        do_action('admin_notices');
        return ob_get_clean();
    }

    /**
     * @test
     */
    public function should_show_migration_notice_when_conditions_are_met(): void
    {
        // Force show the notice
        add_filter('tec_events_category_colors_force_migration_notice', '__return_true');

        $this->notice->maybe_show_migration_notice();
        $output = $this->capture_admin_notices();

        $this->assertMatchesSnapshot($output);
    }

    /**
     * @test
     */
    public function should_not_show_migration_notice_when_migration_completed(): void
    {
        Status::update_migration_status(Status::$postprocessing_completed);

        $this->notice->maybe_show_migration_notice();
        $output = $this->capture_admin_notices();

        $this->assertMatchesSnapshot($output);
    }

    /**
     * @test
     */
    public function should_not_show_migration_notice_when_old_plugin_not_active(): void
    {
        $this->set_fn_return('is_plugin_active', false);

        $this->notice->maybe_show_migration_notice();
        $output = $this->capture_admin_notices();

        $this->assertMatchesSnapshot($output);
    }

    /**
     * @test
     */
    public function should_not_show_migration_notice_when_no_old_data(): void
    {
        delete_option('teccc_options');

        $this->notice->maybe_show_migration_notice();
        $output = $this->capture_admin_notices();

        $this->assertMatchesSnapshot($output);
    }

    /**
     * @test
     */
    public function should_update_status_when_migration_starts(): void
    {
        // Mock check_admin_referer to return true
        $this->set_fn_return('check_admin_referer', true);
        $this->set_fn_return('current_user_can', true);
        $this->set_fn_return('wp_safe_redirect', true);

        $this->notice->handle_migration();

        // Verify migration status was updated
        $status = Status::get_migration_status();
        $this->assertEquals(Status::$preprocessing_scheduled, $status['status']);
    }

    /**
     * @test
     */
    public function should_show_success_notice_when_migration_starts(): void
    {
        // Mock check_admin_referer to return true
        $this->set_fn_return('check_admin_referer', true);
        $this->set_fn_return('current_user_can', true);
        $this->set_fn_return('wp_safe_redirect', true);

        $this->notice->handle_migration();
        $output = $this->capture_admin_notices();

        $this->assertMatchesSnapshot($output);
    }

    /**
     * @test
     */
    public function should_show_error_notice_when_scheduling_fails(): void
    {
        // Mock check_admin_referer to return true
        $this->set_fn_return('check_admin_referer', true);
        $this->set_fn_return('current_user_can', true);
        $this->set_fn_return('wp_safe_redirect', true);

        // Set the schedule method to return an error
        $this->set_class_fn_return(
            Abstract_Action::class,
            'schedule',
            new \WP_Error('test_error', 'Test error message')
        );

        $this->notice->handle_migration();
        $output = $this->capture_admin_notices();

        $this->assertMatchesSnapshot($output);
    }
}
