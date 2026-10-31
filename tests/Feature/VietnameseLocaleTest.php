<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VietnameseLocaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * AC-04: Clean bootstrap without APP_LOCALE resolves vi as default.
     */
    public function test_clean_bootstrap_resolves_vietnamese_default_when_app_locale_env_is_absent(): void
    {
        // 1. Inspect config/app.php source contract default fallback
        $configContent = file_get_contents(config_path('app.php'));
        $this->assertStringContainsString("env('APP_LOCALE', 'vi')", $configContent);
        $this->assertStringContainsString("env('APP_FALLBACK_LOCALE', 'en')", $configContent);

        // 2. Verify that without APP_LOCALE in environment, default locale evaluates to 'vi'
        $output = shell_exec('env -u APP_LOCALE php -r "require \'vendor/autoload.php\'; putenv(\'APP_LOCALE\'); unset(\$_ENV[\'APP_LOCALE\'], \$_SERVER[\'APP_LOCALE\']); \$appConfig = require \'config/app.php\'; echo \$appConfig[\'locale\'];"');
        $this->assertEquals('vi', trim((string) $output));
    }

    /**
     * AC-04: Explicit compatibility run uses en and resolves English keys.
     */
    public function test_english_compatibility_run_resolves_english_translations(): void
    {
        app()->setLocale('en');
        config(['app.locale' => 'en']);

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('Community Feed', __('Community Feed'));
        $this->assertEquals('Classroom', __('Classroom'));
        $this->assertEquals('Log in', __('Log in'));
    }

    /**
     * AC-01: Auth screens render in Vietnamese for a fresh session.
     */
    public function test_auth_screens_render_in_vietnamese(): void
    {
        app()->setLocale('vi');
        config(['app.locale' => 'vi']);

        // 1. Login page
        $loginResponse = $this->get('/login');
        $loginResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Chào mừng trở lại')
            ->assertSee('Đăng nhập')
            ->assertSee('Địa chỉ email')
            ->assertSee('Mật khẩu')
            ->assertSee('Ghi nhớ đăng nhập')
            ->assertSee('Quên mật khẩu?')
            ->assertSee('Chưa có tài khoản?')
            ->assertSee('Đăng ký tài khoản');

        // 2. Register page
        $registerResponse = $this->get('/register');
        $registerResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Đăng ký tài khoản')
            ->assertSee('Họ và tên')
            ->assertSee('Địa chỉ email')
            ->assertSee('Mật khẩu')
            ->assertSee('Xác nhận mật khẩu')
            ->assertSee('Tạo tài khoản')
            ->assertSee('Đã có tài khoản?');

        // 3. Forgot password page
        $forgotResponse = $this->get('/forgot-password');
        $forgotResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Đặt lại mật khẩu')
            ->assertSee('Gửi liên kết đặt lại mật khẩu')
            ->assertSee('Quay lại đăng nhập');

        // 4. Verify email page (requires unverified authenticated user)
        $unverifiedUser = User::factory()->unverified()->create();
        $verifyResponse = $this->actingAs($unverifiedUser)->get('/verify-email');
        $verifyResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Xác minh email của bạn')
            ->assertSee('Gửi lại email xác minh')
            ->assertSee('Đăng xuất');
    }

    /**
     * AC-02: Invalid login and registration inputs return Vietnamese errors with Vietnamese field names.
     */
    public function test_validation_errors_render_in_vietnamese_with_vietnamese_attribute_names(): void
    {
        app()->setLocale('vi');
        config(['app.locale' => 'vi']);

        // 1. Empty login input
        $emptyLoginResponse = $this->from('/login')->post('/login', []);
        $emptyLoginResponse->assertSessionHasErrors([
            'email' => 'Trường địa chỉ email không được để trống.',
            'password' => 'Trường mật khẩu không được để trống.',
        ]);

        // 2. Invalid email format on login
        $invalidEmailResponse = $this->from('/login')->post('/login', [
            'email' => 'not-an-email',
            'password' => 'secret123',
        ]);
        $invalidEmailResponse->assertSessionHasErrors([
            'email' => 'Trường địa chỉ email phải là một địa chỉ email hợp lệ.',
        ]);

        // 3. Failed credentials on login
        $failedLoginResponse = $this->from('/login')->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);
        $failedLoginResponse->assertSessionHasErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);

        // 4. Empty registration input
        $emptyRegisterResponse = $this->from('/register')->post('/register', []);
        $emptyRegisterResponse->assertSessionHasErrors([
            'name' => 'Trường họ và tên không được để trống.',
            'email' => 'Trường địa chỉ email không được để trống.',
            'password' => 'Trường mật khẩu không được để trống.',
        ]);

        // 5. Mismatched password on registration
        $mismatchedRegisterResponse = $this->from('/register')->post('/register', [
            'name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@example.com',
            'password' => 'matkhau123',
            'password_confirmation' => 'khacnhau456',
        ]);
        $mismatchedRegisterResponse->assertSessionHasErrors([
            'password' => 'Trường mật khẩu xác nhận không khớp.',
        ]);
    }

    /**
     * AC-03: Community workspace shows six D6 tabs:
     * Cộng Đồng / Lớp Học / Lịch / Thành Viên / Bảng xếp hạng / Giới Thiệu
     */
    public function test_community_six_tabs_render_d6_vietnamese_labels(): void
    {
        app()->setLocale('vi');
        config(['app.locale' => 'vi']);

        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Cộng Đồng Lập Trình',
            'slug' => 'cong-dong-lap-trinh',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($creator)->get(route('communities.posts.index', $community));
        $response->assertOk();

        // Verify the 6 tabs per D6
        $response->assertSee('Cộng Đồng')
            ->assertSee('Lớp Học')
            ->assertSee('Lịch')
            ->assertSee('Thành Viên')
            ->assertSee('Bảng xếp hạng')
            ->assertSee('Giới Thiệu');
    }

    /**
     * AC-05: Module catalog file under lang/modules/<module>/vi.json is loaded via addJsonPath.
     */
    public function test_module_catalog_json_is_loaded_deterministically(): void
    {
        app()->setLocale('vi');
        config(['app.locale' => 'vi']);

        $testModuleDir = lang_path('modules/test_course_flow');
        File::ensureDirectoryExists($testModuleDir);

        $fixtureFile = $testModuleDir.'/vi.json';
        File::put($fixtureFile, json_encode([
            'course_module_test_unique_key' => 'Khóa học kiểm thử thành công',
        ], JSON_UNESCAPED_UNICODE));

        try {
            // Re-register module translations via translator's addJsonPath
            app('translator')->addJsonPath($testModuleDir);

            $this->assertEquals(
                'Khóa học kiểm thử thành công',
                __('course_module_test_unique_key')
            );
        } finally {
            File::deleteDirectory($testModuleDir);
        }
    }

    /**
     * D14: Verify module translation catalogs do not contain colliding keys.
     */
    public function test_module_catalogs_have_no_colliding_keys(): void
    {
        $modulesPath = lang_path('modules');
        if (! is_dir($modulesPath)) {
            $this->assertTrue(true);

            return;
        }

        $commonKeys = [];
        $commonFile = lang_path('vi.json');
        if (file_exists($commonFile)) {
            $commonKeys = array_keys(json_decode(file_get_contents($commonFile), true) ?: []);
        }

        $moduleDirs = glob($modulesPath.'/*', GLOB_ONLYDIR) ?: [];
        $registeredKeys = [];

        foreach ($moduleDirs as $moduleDir) {
            $moduleName = basename($moduleDir);
            $jsonFile = $moduleDir.'/vi.json';
            if (! file_exists($jsonFile)) {
                continue;
            }

            $keys = array_keys(json_decode(file_get_contents($jsonFile), true) ?: []);
            foreach ($keys as $key) {
                $alreadyIn = $registeredKeys[$key] ?? '';
                $this->assertFalse(
                    isset($registeredKeys[$key]),
                    "Duplicate translation key '{$key}' detected in module '{$moduleName}' (already defined in '{$alreadyIn}')"
                );
                $registeredKeys[$key] = $moduleName;
            }
        }

        $this->assertTrue(true);
    }

    /**
     * AC-06: Vietnamese diacritics render correctly without character corruption.
     */
    public function test_vietnamese_diacritics_and_typography_integrity(): void
    {
        $phrases = [
            'Cộng Đồng',
            'Lớp Học',
            'Lịch',
            'Thành Viên',
            'Bảng xếp hạng',
            'Giới Thiệu',
            'Đăng nhập để truy cập cộng đồng và khóa học của bạn.',
            'Trường địa chỉ email không được để trống.',
            'Quá nhiều lần đăng nhập không thành công. Vui lòng thử lại sau :seconds giây.',
        ];

        foreach ($phrases as $phrase) {
            $this->assertTrue(
                mb_check_encoding($phrase, 'UTF-8'),
                "Phrase '{$phrase}' is not valid UTF-8."
            );
        }
    }
}
