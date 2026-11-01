<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class VietnameseLocaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * AC-04: Clean bootstrap without APP_LOCALE resolves vi as default.
     */
    public function test_clean_bootstrap_resolves_vietnamese_default_when_app_locale_env_is_absent(): void
    {
        $result = $this->bootSeparateApplication(null);

        $this->assertSame('vi', $result['locale']);
        $this->assertSame(200, $result['status']);
        $this->assertStringContainsString('Đăng nhập', $result['body']);
        $this->assertStringContainsString('lang="vi"', $result['body']);
    }

    /**
     * AC-04: English compatibility is a separate explicit bootstrap, not setLocale after boot.
     */
    public function test_english_compatibility_run_resolves_english_translations(): void
    {
        $result = $this->bootSeparateApplication('en');

        $this->assertSame('en', $result['locale']);
        $this->assertSame(200, $result['status']);
        $this->assertStringContainsString('Log in', $result['body']);
        $this->assertStringContainsString('lang="en"', $result['body']);
    }

    /**
     * Boot without the repository's dotenv or config cache. Only isolated test DB/mail settings
     * enter the child; this never reads host secrets or connects to the development database.
     *
     * @return array{locale: string, status: int, body: string}
     */
    private function bootSeparateApplication(?string $locale): array
    {
        $directory = sys_get_temp_dir().'/scool-locale-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        file_put_contents($directory.'/hot', 'http://127.0.0.1:5173');
        $script = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->useEnvironmentPath($argv[1]);
$app->loadEnvironmentFrom('.env.testing');
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$app->make(Illuminate\Foundation\Vite::class)->useHotFile($argv[1].'/hot');
$response = $kernel->handle(Illuminate\Http\Request::create('/login', 'GET'));
echo json_encode(['locale' => $app->getLocale(), 'status' => $response->getStatusCode(), 'body' => $response->getContent()], JSON_THROW_ON_ERROR);
PHP;
        $connection = config('database.connections.mysql');
        $environment = [
            'APP_ENV' => 'testing',
            'APP_KEY' => 'base64:'.base64_encode(str_repeat('x', 32)),
            'APP_LOCALE' => $locale ?? false,
            'APP_FALLBACK_LOCALE' => 'en',
            'APP_CONFIG_CACHE' => $directory.'/config.php',
            'DB_URL' => false,
            'DATABASE_URL' => false,
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $connection['host'],
            'DB_PORT' => (string) $connection['port'],
            'DB_DATABASE' => 'scool_test',
            'DB_USERNAME' => $connection['username'],
            'DB_PASSWORD' => $connection['password'],
            'MAIL_MAILER' => 'array',
            'SESSION_DRIVER' => 'array',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ];
        try {
            $process = new Process([PHP_BINARY, '-r', $script, $directory], base_path(), $environment);
            $process->mustRun();

            return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        } finally {
            unlink($directory.'/hot');
            rmdir($directory);
        }
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

    public function test_application_validation_uses_vietnamese_field_names(): void
    {
        app()->setLocale('vi');
        foreach ([
            'video_url' => 'liên kết video',
            'skill_label' => 'nhãn kỹ năng',
            'price_vnd' => 'giá tính bằng VNĐ',
            'recipient_id' => 'người nhận',
            'starts_at' => 'thời điểm bắt đầu',
        ] as $field => $label) {
            $message = validator([], [$field => 'required'])->errors()->first($field);
            $this->assertSame('Trường '.$label.' không được để trống.', $message);
        }
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
        // These keys exist only in module catalogs. Provider registration must have loaded
        // them during the real application boot; manually addJsonPath() would hide a defect.
        $common = json_decode(file_get_contents(lang_path('vi.json')), true, flags: JSON_THROW_ON_ERROR);
        $catalogs = glob(lang_path('modules/*/vi.json')) ?: [];
        sort($catalogs, SORT_STRING);
        $this->assertNotEmpty($catalogs);
        $expectedPaths = array_map('dirname', $catalogs);
        $registeredPaths = array_values(array_filter(
            app('translator')->getLoader()->jsonPaths(),
            fn ($path) => str_starts_with($path, lang_path('modules').DIRECTORY_SEPARATOR)
        ));
        $this->assertSame($expectedPaths, $registeredPaths);

        foreach ($catalogs as $catalog) {
            $entries = json_decode(file_get_contents($catalog), true, flags: JSON_THROW_ON_ERROR);
            $moduleOnly = array_diff_key($entries, $common);
            $this->assertNotEmpty($moduleOnly, $catalog);
            $key = array_key_first($moduleOnly);
            $this->assertSame($moduleOnly[$key], __($key, [], 'vi'), $catalog);
        }
    }

    /**
     * D14: Verify module translation catalogs do not contain colliding keys.
     */
    public function test_module_catalogs_have_no_colliding_keys(): void
    {
        $catalogs = [lang_path('vi.json'), ...(glob(lang_path('modules/*/vi.json')) ?: [])];
        $registered = [];
        foreach ($catalogs as $catalog) {
            $source = file_get_contents($catalog);
            $entries = json_decode($source, true, flags: JSON_THROW_ON_ERROR);
            $this->assertIsArray($entries);
            // All catalogs are flat JSON string maps. Inspect raw keys before json_decode's
            // last-key-wins behavior can conceal an accidental duplicate in one file.
            $keyPattern = <<<'JSON_KEYS'
/^\h*("(?:\\.|[^"\\])*")\h*:/m
JSON_KEYS;
            preg_match_all($keyPattern, $source, $matches);
            $rawKeys = array_map(fn ($key) => json_decode($key, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
            $this->assertCount(count($entries), $rawKeys, 'Every flat catalog entry must have its own JSON key line: '.$catalog);
            $this->assertCount(count(array_unique($rawKeys)), $rawKeys, 'Duplicate JSON key in '.$catalog);

            foreach ($entries as $key => $value) {
                $this->assertIsString($value, $catalog.': '.$key);
                $this->assertArrayNotHasKey($key, $registered, 'Cross-catalog duplicate: '.$key);
                $registered[$key] = $catalog;
            }
        }
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
