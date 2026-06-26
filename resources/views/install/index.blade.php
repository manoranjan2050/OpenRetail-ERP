<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install — OpenRetail ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>body { font-family: 'Figtree', sans-serif; }</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center px-4 py-10">

<div x-data="installer(@json($checks))" class="w-full max-w-lg">
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl mx-auto mb-3">OR</div>
        <h1 class="text-2xl font-bold text-white">OpenRetail ERP</h1>
        <p class="text-slate-400 text-sm mt-1">Web Installer</p>
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center justify-center gap-2 mb-8">
        <template x-for="(s, i) in steps" :key="i">
            <div class="flex items-center gap-2">
                <div :class="step > i ? 'bg-emerald-600' : step === i ? 'bg-indigo-600' : 'bg-slate-800'"
                     class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white transition-colors"
                     x-text="step > i ? '✓' : i + 1"></div>
                <span class="text-xs text-slate-400 hidden sm:block" x-text="s"></span>
                <div x-show="i < steps.length - 1" class="w-6 h-px bg-slate-700"></div>
            </div>
        </template>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">

        {{-- Step 0: Requirements --}}
        <div x-show="step === 0">
            <h2 class="text-base font-semibold text-white mb-4">System Requirements</h2>
            <div class="space-y-3">
                <template x-for="[key, ok] in Object.entries(checks)" :key="key">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-300 capitalize" x-text="key.replace(/_/g,' ')"></span>
                        <span :class="ok ? 'text-emerald-400' : 'text-red-400'" class="text-xs font-medium" x-text="ok ? '✓ OK' : '✗ Failed'"></span>
                    </div>
                </template>
            </div>
            <div class="mt-5 text-xs text-slate-500" x-show="!allChecksPassed()">
                Please fix the failing requirements before proceeding.
            </div>
            <button @click="step = 1" :disabled="!allChecksPassed()"
                    class="mt-5 w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                Next: Database Setup
            </button>
        </div>

        {{-- Step 1: Database --}}
        <div x-show="step === 1">
            <h2 class="text-base font-semibold text-white mb-4">Database Configuration</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Host</label>
                    <input type="text" x-model="db.host" value="127.0.0.1"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Port</label>
                    <input type="number" x-model="db.port" value="3306"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Database Name</label>
                    <input type="text" x-model="db.name"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Username</label>
                    <input type="text" x-model="db.user"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Password</label>
                    <input type="password" x-model="db.password"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div x-show="error" class="mt-3 text-red-400 text-xs" x-text="error"></div>
            <button @click="setupDatabase()" :disabled="loading"
                    class="mt-5 w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                <span x-show="!loading">Connect &amp; Run Migrations</span>
                <span x-show="loading">Running migrations...</span>
            </button>
        </div>

        {{-- Step 2: Business Info --}}
        <div x-show="step === 2">
            <h2 class="text-base font-semibold text-white mb-4">Business Information</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Business Name</label>
                    <input type="text" x-model="business.name"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">GSTIN (optional)</label>
                    <input type="text" x-model="business.gstin"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Currency Symbol</label>
                    <input type="text" x-model="business.currency" value="₹"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Timezone</label>
                    <input type="text" x-model="business.timezone" value="Asia/Kolkata"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div x-show="error" class="mt-3 text-red-400 text-xs" x-text="error"></div>
            <button @click="setupBusiness()" :disabled="loading"
                    class="mt-5 w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                Next: Admin Account
            </button>
        </div>

        {{-- Step 3: Admin Account --}}
        <div x-show="step === 3">
            <h2 class="text-base font-semibold text-white mb-4">Admin Account</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Name</label>
                    <input type="text" x-model="admin.name"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Email</label>
                    <input type="email" x-model="admin.email"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Password</label>
                    <input type="password" x-model="admin.password"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Confirm Password</label>
                    <input type="password" x-model="admin.password_confirmation"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div x-show="error" class="mt-3 text-red-400 text-xs" x-text="error"></div>
            <button @click="setupAdmin()" :disabled="loading"
                    class="mt-5 w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                Create Admin &amp; Finish
            </button>
        </div>

        {{-- Step 4: Done --}}
        <div x-show="step === 4" class="text-center py-6">
            <div class="text-5xl mb-4">🎉</div>
            <h2 class="text-lg font-bold text-white mb-2">Installation Complete!</h2>
            <p class="text-slate-400 text-sm mb-6">OpenRetail ERP has been installed successfully.</p>
            <a href="/login" class="inline-block bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                Go to Login
            </a>
        </div>
    </div>
</div>

<script>
function installer(initialChecks) {
    return {
        step: 0,
        steps: ['Requirements', 'Database', 'Business', 'Admin', 'Done'],
        checks: initialChecks,
        loading: false,
        error: '',
        db: { host: '127.0.0.1', port: 3306, name: '', user: '', password: '' },
        business: { name: '', gstin: '', currency: '₹', timezone: 'Asia/Kolkata' },
        admin: { name: '', email: '', password: '', password_confirmation: '' },

        allChecksPassed() {
            return Object.values(this.checks).every(v => v === true);
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        },

        async post(url, data) {
            const r = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
                body: JSON.stringify(data),
            });
            return r.json();
        },

        async setupDatabase() {
            this.loading = true; this.error = '';
            const res = await this.post('/install/database', {
                db_host: this.db.host, db_port: this.db.port,
                db_name: this.db.name, db_user: this.db.user, db_password: this.db.password
            });
            this.loading = false;
            if (res.success) { this.step = 2; } else { this.error = res.error || 'Connection failed.'; }
        },

        async setupBusiness() {
            this.loading = true; this.error = '';
            const res = await this.post('/install/business', {
                business_name: this.business.name, gstin: this.business.gstin,
                currency: this.business.currency, timezone: this.business.timezone
            });
            this.loading = false;
            if (res.success) { this.step = 3; } else { this.error = res.error || 'Failed to save.'; }
        },

        async setupAdmin() {
            this.loading = true; this.error = '';
            if (this.admin.password !== this.admin.password_confirmation) {
                this.error = 'Passwords do not match.'; this.loading = false; return;
            }
            const res = await this.post('/install/admin', this.admin);
            this.loading = false;
            if (res.success) {
                await this.post('/install/finish', {});
                this.step = 4;
            } else {
                this.error = res.error || 'Failed to create admin.';
            }
        }
    }
}
</script>
</body>
</html>
