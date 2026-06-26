<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function index(): View
    {
        $checks = $this->requirementsCheck();
        return view('install.index', ['checks' => $checks]);
    }

    public function check(): JsonResponse
    {
        return response()->json($this->requirementsCheck());
    }

    public function database(Request $request): JsonResponse
    {
        $data = $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|integer',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            $pdo = new \PDO(
                "mysql:host={$data['db_host']};port={$data['db_port']};dbname={$data['db_name']}",
                $data['db_user'],
                $data['db_password'] ?? ''
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Write .env
            $this->writeEnv([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $data['db_host'],
                'DB_PORT' => $data['db_port'],
                'DB_DATABASE' => $data['db_name'],
                'DB_USERNAME' => $data['db_user'],
                'DB_PASSWORD' => $data['db_password'] ?? '',
            ]);

            Artisan::call('config:clear');
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder', '--force' => true]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Connection failed. Check your credentials.'], 422);
        }
    }

    public function business(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15',
            'currency' => 'required|string|max:5',
            'timezone' => 'required|string',
        ]);

        BusinessSetting::updateOrCreate([], $data);
        return response()->json(['success' => true]);
    }

    public function admin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:15',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('admin');

        return response()->json(['success' => true]);
    }

    public function finish(Request $request): JsonResponse
    {
        BusinessSetting::instance()->update(['installed' => true]);
        // Write installed lock
        file_put_contents(storage_path('installed'), now()->toDateTimeString());
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return response()->json(['success' => true, 'redirect' => route('login')]);
    }

    private function requirementsCheck(): array
    {
        return [
            'php_version' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'gd' => extension_loaded('gd'),
            'zip' => extension_loaded('zip'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'storage_writable' => is_writable(storage_path()),
        ];
    }

    private function writeEnv(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);
        foreach ($values as $key => $value) {
            $value = str_contains((string) $value, ' ') ? "\"{$value}\"" : $value;
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        file_put_contents($envPath, $content);
    }
}
