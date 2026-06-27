<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'OpenRetail ERP')</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  [x-cloak] { display: none !important; }
  .nav-active { background: linear-gradient(135deg,#4f46e5,#6366f1) !important; color:#fff !important; box-shadow:0 4px 12px rgba(99,102,241,.3); }
  ::-webkit-scrollbar{width:4px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:2px}
</style>
</head>
<body class="bg-slate-50 min-h-screen">
<div class="flex min-h-screen">

{{-- ── SIDEBAR ──────────────────────────────────────────── --}}
<aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
  class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-[#0f172a] border-r border-white/5 shadow-2xl transform transition-transform duration-300 lg:static lg:flex">

  <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-5 py-4 border-b border-white/10 hover:bg-white/5 transition-colors">
    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-sm shadow-lg">OR</div>
    <div><div class="text-white font-bold text-sm">OpenRetail ERP</div><div class="text-slate-500 text-xs">v1.0</div></div>
  </a>

  <nav class="flex-1 overflow-y-auto py-3 space-y-0.5 text-sm">

    @php
    function navLink($label, $routeName, $icon, $active = false) {
        $url = route($routeName);
        $cls = $active ? 'nav-active font-semibold' : 'text-slate-400 hover:text-white hover:bg-white/10';
        return "<a href=\"$url\" class=\"flex items-center gap-3 px-4 py-2.5 mx-2 rounded-xl font-medium transition-all $cls\"><span>$icon</span> $label</a>";
    }
    @endphp

    {!! navLink('Dashboard', 'dashboard', '📊', request()->routeIs('dashboard')) !!}

    <div class="px-4 pt-4 pb-1"><span class="text-[10px] font-bold text-slate-600 tracking-widest uppercase">Sales</span></div>

    {{-- Invoices --}}
    <div x-data="{ open: {{ request()->routeIs('invoices.*') ? 'true' : 'false' }} }">
      <button @click="open=!open" class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-xl font-medium transition-all {{ request()->routeIs('invoices.*') ? 'text-indigo-300' : 'text-slate-400 hover:text-white hover:bg-white/10' }}" style="width:calc(100% - 1rem)">
        <span class="flex items-center gap-3">🧾 Invoices</span>
        <svg :class="open?'rotate-180':''" class="w-4 h-4 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div x-show="open" x-transition class="pl-8 pr-2 mt-0.5 space-y-0.5">
        <a href="{{ route('invoices.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('invoices.create') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">⚡ New Invoice</a>
        <a href="{{ route('invoices.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('invoices.index') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">📄 All Invoices</a>
      </div>
    </div>

    {{-- Customers --}}
    <div x-data="{ open: {{ request()->routeIs('customers.*') ? 'true' : 'false' }} }">
      <button @click="open=!open" class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-xl font-medium transition-all {{ request()->routeIs('customers.*') ? 'text-indigo-300' : 'text-slate-400 hover:text-white hover:bg-white/10' }}" style="width:calc(100% - 1rem)">
        <span class="flex items-center gap-3">👥 Customers</span>
        <svg :class="open?'rotate-180':''" class="w-4 h-4 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div x-show="open" x-transition class="pl-8 pr-2 mt-0.5 space-y-0.5">
        <a href="{{ route('customers.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('customers.create') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">➕ Add Customer</a>
        <a href="{{ route('customers.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('customers.index') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">👤 All Customers</a>
      </div>
    </div>

    <div class="px-4 pt-4 pb-1"><span class="text-[10px] font-bold text-slate-600 tracking-widest uppercase">Inventory</span></div>

    {{-- Products --}}
    <div x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }">
      <button @click="open=!open" class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-xl font-medium transition-all {{ request()->routeIs('products.*') ? 'text-indigo-300' : 'text-slate-400 hover:text-white hover:bg-white/10' }}" style="width:calc(100% - 1rem)">
        <span class="flex items-center gap-3">📦 Products</span>
        <svg :class="open?'rotate-180':''" class="w-4 h-4 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div x-show="open" x-transition class="pl-8 pr-2 mt-0.5 space-y-0.5">
        <a href="{{ route('products.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('products.create') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">➕ Add Product</a>
        <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('products.index') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">📋 All Products</a>
        <a href="{{ route('products.index', ['low_stock'=>1]) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-slate-500 hover:text-white hover:bg-white/8 transition-colors">⚠️ Low Stock</a>
      </div>
    </div>

    {{-- Categories --}}
    <div x-data="{ open: {{ request()->routeIs('categories.*') ? 'true' : 'false' }} }">
      <button @click="open=!open" class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-xl font-medium transition-all {{ request()->routeIs('categories.*') ? 'text-indigo-300' : 'text-slate-400 hover:text-white hover:bg-white/10' }}" style="width:calc(100% - 1rem)">
        <span class="flex items-center gap-3">🗂️ Categories</span>
        <svg :class="open?'rotate-180':''" class="w-4 h-4 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div x-show="open" x-transition class="pl-8 pr-2 mt-0.5 space-y-0.5">
        <a href="{{ route('categories.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('categories.create') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">➕ Add Category</a>
        <a href="{{ route('categories.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs {{ request()->routeIs('categories.index') ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/8' }} transition-colors">📋 All Categories</a>
      </div>
    </div>

    {!! navLink('Units', 'units.index', '⚖️', request()->routeIs('units.*')) !!}

    <div class="px-4 pt-4 pb-1"><span class="text-[10px] font-bold text-slate-600 tracking-widest uppercase">System</span></div>
    {!! navLink('Settings', 'settings.index', '⚙️', request()->routeIs('settings.*')) !!}
    {!! navLink('Audit Log', 'audit-log.index', '📋', request()->routeIs('audit-log.*')) !!}
  </nav>

  {{-- User menu --}}
  <div class="p-4 border-t border-white/10" x-data="{ open: false }">
    <button @click="open=!open" class="w-full flex items-center gap-3 hover:bg-white/5 rounded-xl p-2 transition-colors text-left">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
        {{ strtoupper(substr(auth()->user()->name,0,2)) }}
      </div>
      <div class="min-w-0 flex-1">
        <div class="text-white text-xs font-semibold truncate">{{ auth()->user()->name }}</div>
        <div class="text-slate-500 text-[11px] truncate">{{ auth()->user()->email }}</div>
      </div>
    </button>
    <div x-show="open" @click.away="open=false" x-transition class="mt-2 bg-slate-800 rounded-xl border border-white/10 overflow-hidden">
      <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-300 hover:bg-white/10 transition-colors">👤 Profile</a>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors">🚪 Log Out</button>
      </form>
    </div>
  </div>
</aside>

{{-- overlay --}}
<div x-show="sidebarOpen" @click="sidebarOpen=false" x-cloak class="fixed inset-0 z-40 bg-black/60 lg:hidden"></div>

{{-- ── MAIN ─────────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col min-w-0">
  <header class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 h-14 bg-white/90 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="flex items-center gap-3">
      <button @click="sidebarOpen=true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <h1 class="font-semibold text-slate-700 text-base">@yield('header','Dashboard')</h1>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('invoices.create') }}" class="hidden sm:flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors">⚡ New Invoice</a>
      <div x-data="{ open:false }" class="relative">
        <button @click="open=!open" class="flex items-center gap-2 rounded-xl hover:bg-slate-100 px-2 py-1.5 transition-colors">
          <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
          <span class="hidden sm:block text-sm font-medium text-slate-700 max-w-28 truncate">{{ auth()->user()->name }}</span>
          <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" @click.away="open=false" x-transition class="absolute right-0 top-10 w-44 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden z-50">
          <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">👤 Profile</a>
          <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">⚙️ Settings</a>
          <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50">🚪 Log Out</button>
          </form>
        </div>
      </div>
    </div>
  </header>

  @if(session('success'))
  <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)" x-transition class="mx-4 sm:mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center justify-between">
    <span>✅ {{ session('success') }}</span><button @click="show=false" class="text-green-400 ml-4">✕</button>
  </div>
  @endif
  @if(session('error'))
  <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,6000)" x-transition class="mx-4 sm:mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm flex items-center justify-between">
    <span>❌ {{ session('error') }}</span><button @click="show=false" class="text-red-400 ml-4">✕</button>
  </div>
  @endif

  <main class="flex-1 p-4 sm:p-6">@yield('content')</main>

  <footer class="border-t border-slate-200 bg-white px-6 py-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400">
    <span>© {{ date('Y') }} <span class="text-indigo-500 font-medium">OpenRetail ERP</span> · Open Source</span>
    <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-mono">v1.0.0</span>
  </footer>
</div>
</div>
@stack('scripts')
</body>
</html>
