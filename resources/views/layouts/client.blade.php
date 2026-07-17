<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Client Area') - {{ $companyName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://js.stripe.com/dahlia/stripe.js"></script>
    @if(!empty($stripePublishableKey))
    <script>window.STRIPE_PUBLISHABLE_KEY = '{{ $stripePublishableKey }}';</script>
    @endif
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81', 950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        * { scrollbar-width: thin; scrollbar-color: #4338ca #0f0e1a; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0e1a; }
        ::-webkit-scrollbar-thumb { background: #4338ca; border-radius: 3px; }
        .glass { background: rgba(30,27,75,0.5); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.06); }
        .sidebar-link { transition: all 0.2s; position: relative; }
        .sidebar-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar-link.active { background: rgba(99,102,241,0.15); color: #818cf8; }
        .sidebar-link.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 60%; background: linear-gradient(180deg, #6366f1, #8b5cf6); border-radius: 0 4px 4px 0; }
        .btn-primary { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); transition: all 0.3s; }
        .btn-primary:hover { background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%); transform: translateY(-1px); box-shadow: 0 8px 25px rgba(99,102,241,0.3); }
        .btn-outline { border: 1px solid rgba(255,255,255,0.12); transition: all 0.3s; }
        .btn-outline:hover { border-color: #6366f1; background: rgba(99,102,241,0.1); }
        .stat-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s; }
        .stat-card:hover { border-color: rgba(99,102,241,0.3); background: rgba(99,102,241,0.05); }
        .fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex flex-col" x-data="{ sidebarCollapsed: false, sidebarMobileOpen: false }">
    {{-- Mobile overlay --}}
    <div x-show="sidebarMobileOpen" x-cloak @click="sidebarMobileOpen = false" class="fixed inset-0 bg-black/60 z-40 lg:hidden" x-transition.opacity></div>

    {{-- Sidebar --}}
    <aside :class="sidebarCollapsed ? 'w-[72px]' : 'w-64'" class="fixed top-0 left-0 z-40 h-screen flex-shrink-0 transition-all duration-300 hidden lg:block"
        :style="!sidebarCollapsed ? 'width: 16rem' : 'width: 72px'">
        <div class="h-full flex flex-col bg-gray-950 border-r border-white/5">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 h-16 flex-shrink-0 border-b border-white/5">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if($companyLogo)
                        <img src="{{ asset($companyLogo) }}" alt="{{ $companyName }}" class="h-8 w-auto rounded-lg">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/25 flex-shrink-0">
                            <i data-lucide="zap" class="w-4 h-4 text-white"></i>
                        </div>
                    @endif
                    <span class="text-base font-bold tracking-tight" x-show="!sidebarCollapsed" x-cloak>{{ $companyName }}</span>
                </a>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2" x-show="!sidebarCollapsed" x-cloak>Overview</p>
                <a href="{{ route('client.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.dashboard') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="layout-dashboard" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-cloak>Dashboard</span>
                </a>

                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5" x-show="!sidebarCollapsed" x-cloak>Services</p>
                <a href="{{ route('client.services.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.services.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="server" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-cloak>My Services</span>
                </a>

                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5" x-show="!sidebarCollapsed" x-cloak>Billing</p>
                <a href="{{ route('client.invoices.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.invoices.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="file-text" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-cloak>Invoices</span>
                </a>
                <a href="{{ route('client.credits.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.credits.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="wallet" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-cloak>Credits</span>
                </a>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5" x-show="!sidebarCollapsed" x-cloak>Support</p>
                <a href="{{ route('client.tickets.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.tickets.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="life-buoy" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-cloak>Tickets</span>
                </a>
                <a href="{{ route('client.notifications.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.notifications.*') ? 'active' : 'text-gray-400' }}">
                    <div class="relative">
                        <i data-lucide="bell" class="w-[18px] h-[18px] flex-shrink-0"></i>
                        @if($unreadNotificationCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-brand-500 text-[9px] font-bold text-white flex items-center justify-center leading-none">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        @endif
                    </div>
                    <span x-show="!sidebarCollapsed" x-cloak>Notifications</span>
                </a>

                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5" x-show="!sidebarCollapsed" x-cloak>Account</p>
                <a href="{{ route('client.profile.edit') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.profile.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="user" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-cloak>Profile</span>
                </a>
                <a href="{{ route('client.two-factor.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.two-factor.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="shield" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-cloak>Security</span>
                </a>
            </nav>

            {{-- User section --}}
            <div class="border-t border-white/5 p-3">
                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0 border border-brand-500/20">
                            <span class="text-xs font-bold text-brand-400">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                        </div>
                        <div x-show="!sidebarCollapsed" x-cloak class="flex-1 text-left min-w-0">
                            <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                        <i data-lucide="chevron-up" x-show="!sidebarCollapsed" x-cloak x-bind:class="userMenuOpen ? 'rotate-180' : ''" class="w-4 h-4 text-gray-500 transition-transform flex-shrink-0"></i>
                    </button>
                    <div x-show="userMenuOpen" x-cloak x-transition @click.away="userMenuOpen = false" class="absolute bottom-full left-0 right-0 mb-2 glass rounded-xl shadow-2xl py-2 z-50">
                        <a href="{{ route('client.profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5"><i data-lucide="settings" class="w-4 h-4"></i> Settings</a>
                        <a href="{{ route('store.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5"><i data-lucide="shopping-cart" class="w-4 h-4"></i> Store</a>
                        @if(Auth::user()->is_admin)
                            <a href="{{ url('/admin') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-amber-400 hover:text-amber-300 hover:bg-amber-500/10"><i data-lucide="shield" class="w-4 h-4"></i> Admin Panel</a>
                        @endif
                        <hr class="border-white/5 my-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10"><i data-lucide="log-out" class="w-4 h-4"></i> Sign Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile sidebar --}}
    <aside x-show="sidebarMobileOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed top-0 left-0 z-50 h-screen w-64 lg:hidden">
        <div class="h-full flex flex-col bg-gray-950 border-r border-white/5">
            {{-- Logo --}}
            <div class="flex items-center justify-between px-5 h-16 flex-shrink-0 border-b border-white/5">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if($companyLogo)
                        <img src="{{ asset($companyLogo) }}" alt="{{ $companyName }}" class="h-8 w-auto rounded-lg">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/25 flex-shrink-0">
                            <i data-lucide="zap" class="w-4 h-4 text-white"></i>
                        </div>
                    @endif
                    <span class="text-base font-bold tracking-tight">{{ $companyName }}</span>
                </a>
                <button @click="sidebarMobileOpen = false" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Overview</p>
                <a href="{{ route('client.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.dashboard') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="layout-dashboard" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span>Dashboard</span>
                </a>

                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5">Services</p>
                <a href="{{ route('client.services.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.services.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="server" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span>My Services</span>
                </a>

                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5">Billing</p>
                <a href="{{ route('client.invoices.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.invoices.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="file-text" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span>Invoices</span>
                </a>
                <a href="{{ route('client.credits.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.credits.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="wallet" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span>Credits</span>
                </a>

                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5">Support</p>
                <a href="{{ route('client.tickets.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.tickets.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="life-buoy" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span>Tickets</span>
                </a>
                <a href="{{ route('client.notifications.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.notifications.*') ? 'active' : 'text-gray-400' }}">
                    <div class="relative">
                        <i data-lucide="bell" class="w-[18px] h-[18px] flex-shrink-0"></i>
                        @if($unreadNotificationCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-brand-500 text-[9px] font-bold text-white flex items-center justify-center leading-none">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        @endif
                    </div>
                    <span>Notifications</span>
                </a>

                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-5">Account</p>
                <a href="{{ route('client.profile.edit') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.profile.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="user" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span>Profile</span>
                </a>
                <a href="{{ route('client.two-factor.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('client.two-factor.*') ? 'active' : 'text-gray-400' }}">
                    <i data-lucide="shield" class="w-[18px] h-[18px] flex-shrink-0"></i>
                    <span>Security</span>
                </a>
            </nav>

            {{-- User section --}}
            <div class="border-t border-white/5 p-3">
                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0 border border-brand-500/20">
                            <span class="text-xs font-bold text-brand-400">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                        </div>
                        <div class="flex-1 text-left min-w-0">
                            <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </button>
                    <div x-show="userMenuOpen" x-cloak x-transition @click.away="userMenuOpen = false" class="absolute bottom-full left-0 right-0 mb-2 glass rounded-xl shadow-2xl py-2 z-50">
                        <a href="{{ route('client.profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5"><i data-lucide="settings" class="w-4 h-4"></i> Settings</a>
                        <a href="{{ route('store.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5"><i data-lucide="shopping-cart" class="w-4 h-4"></i> Store</a>
                        @if(Auth::user()->is_admin)
                            <a href="{{ url('/admin') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-amber-400 hover:text-amber-300 hover:bg-amber-500/10"><i data-lucide="shield" class="w-4 h-4"></i> Admin Panel</a>
                        @endif
                        <hr class="border-white/5 my-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10"><i data-lucide="log-out" class="w-4 h-4"></i> Sign Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 min-h-0 flex flex-col transition-all duration-300" :class="sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-64'">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 h-16 flex items-center gap-4 px-6 bg-gray-950/80 backdrop-blur-xl border-b border-white/5">
            <button @click="sidebarMobileOpen = true" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5">
                <i data-lucide="panel-left-close" x-show="!sidebarCollapsed" class="w-5 h-5"></i>
                <i data-lucide="panel-left-open" x-show="sidebarCollapsed" x-cloak class="w-5 h-5"></i>
            </button>
            <div class="flex-1"></div>
            <div class="flex items-center gap-2">
                <a href="{{ route('cart.index') }}" class="relative p-2.5 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                    <i data-lucide="shopping-cart" class="w-[18px] h-[18px]"></i>
                    @if($cartCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-brand-500 text-[9px] font-bold text-white flex items-center justify-center leading-none">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('client.tickets.create') }}" class="hidden sm:flex btn-primary px-4 py-2 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20">
                    <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5 inline"></i> New Ticket
                </a>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-6 fade-in">
            @yield('content')
        </main>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition x-cloak class="fixed top-6 right-6 z-[100] max-w-sm">
        <div class="glass rounded-2xl p-4 shadow-2xl border border-emerald-500/20">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i></div>
                <p class="text-sm text-gray-200">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition x-cloak class="fixed top-6 right-6 z-[100] max-w-sm">
        <div class="glass rounded-2xl p-4 shadow-2xl border border-red-500/20">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0"><i data-lucide="alert-circle" class="w-4 h-4 text-red-400"></i></div>
                <p class="text-sm text-gray-200">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    @include('components.confirm-modal')

    {{-- Footer --}}
    <footer class="border-t border-white/5 py-6 px-6 mt-auto">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <div class="flex items-center gap-2">
                @if($companyLogo)
                    <img src="{{ asset($companyLogo) }}" alt="{{ $companyName }}" class="h-4 w-auto">
                @endif
                <span>{{ $companyFooterText ?: $companyName . ' — All rights reserved.' }}</span>
            </div>
            <div class="flex items-center gap-3">
                @if($companyUrl)
                    <a href="{{ $companyUrl }}" target="_blank" class="hover:text-gray-300 transition-colors">Website</a>
                @endif
                @if($companyEmail)
                    <a href="mailto:{{ $companyEmail }}" class="hover:text-gray-300 transition-colors">Support</a>
                @endif
            </div>
        </div>
    </footer>

    @stack('scripts')
    <script>lucide.createIcons();</script>
</body>
</html>
