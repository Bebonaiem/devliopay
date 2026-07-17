<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="{{ $companyName }} - Premium Hosting & Game Server Solutions">
    <title>@yield('title', $companyName)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
        * { scrollbar-width: thin; scrollbar-color: #4338ca #1e1b4b; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1e1b4b; }
        ::-webkit-scrollbar-thumb { background: #4338ca; border-radius: 4px; }
        .hero-gradient {
            background: radial-gradient(ellipse at top, rgba(99,102,241,0.15) 0%, transparent 60%),
                        radial-gradient(ellipse at bottom right, rgba(139,92,246,0.1) 0%, transparent 50%);
        }
        .glass { background: rgba(30,27,75,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .glass-light { background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.06); }
        .glow-brand { box-shadow: 0 0 40px rgba(99,102,241,0.3); }
        .btn-primary { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); transition: all 0.3s; }
        .btn-primary:hover { background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%); transform: translateY(-1px); box-shadow: 0 8px 25px rgba(99,102,241,0.4); }
        .btn-outline { border: 1px solid rgba(255,255,255,0.15); transition: all 0.3s; }
        .btn-outline:hover { border-color: #6366f1; background: rgba(99,102,241,0.1); }
        .nav-link { position: relative; transition: all 0.3s; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 50%; width: 0; height: 2px; background: linear-gradient(90deg, #6366f1, #8b5cf6); transition: all 0.3s; transform: translateX(-50%); }
        .nav-link:hover::after { width: 100%; }
        .card-hover { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .shimmer { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent); background-size: 200% 100%; animation: shimmer 2s infinite; }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex flex-col">
    {{-- Navigation --}}
    <header x-data="{ mobileOpen: false, scrolled: false }" x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)" :class="scrolled ? 'bg-gray-950/80 backdrop-blur-xl shadow-2xl shadow-black/20 border-b border-white/5' : 'bg-transparent'" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/25 group-hover:shadow-brand-500/40 transition-shadow">
                        <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                    </div>
                    @if($companyLogo)
                        <img src="{{ $companyLogo }}" alt="{{ $companyName }}" class="h-8 w-auto">
                    @else
                        <span class="text-lg font-bold tracking-tight">{{ $companyName }}</span>
                    @endif
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="nav-link px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 {{ request()->routeIs('home') ? 'text-white bg-white/10' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('store.index') }}" class="nav-link px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 {{ request()->routeIs('store.*') ? 'text-white bg-white/10' : '' }}">
                        Store
                    </a>
                    <a href="{{ route('knowledgebase.index') }}" class="nav-link px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 {{ request()->routeIs('knowledgebase.*') ? 'text-white bg-white/10' : '' }}">
                        KB
                    </a>
                    <a href="{{ route('announcements.index') }}" class="nav-link px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 {{ request()->routeIs('announcements.*') ? 'text-white bg-white/10' : '' }}">
                        News
                    </a>
                </nav>

                {{-- Right side --}}
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <a href="{{ route('cart.index') }}" class="relative p-2.5 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                            @if($cartCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-brand-500 text-[9px] font-bold text-white flex items-center justify-center leading-none">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                            @endif
                        </a>

                        {{-- Profile Dropdown --}}
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl hover:bg-white/5 transition-all">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-300">{{ Auth::user()->name }}</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 -translate-y-1" class="absolute right-0 mt-2 w-56 py-2 glass rounded-xl shadow-2xl shadow-black/40 border border-white/10">
                                <div class="px-4 py-2.5 border-b border-white/5">
                                    <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ Auth::user()->email }}</p>
                                </div>

                                <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-colors">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                                </a>
                                <a href="{{ route('client.profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-colors">
                                    <i data-lucide="user" class="w-4 h-4"></i> Profile
                                </a>

                                @if(Auth::user()->is_admin)
                                <div class="border-t border-white/5 my-1"></div>
                                <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-amber-400 hover:text-amber-300 hover:bg-amber-500/10 transition-colors">
                                    <i data-lucide="shield" class="w-4 h-4"></i> Admin Panel
                                </a>
                                @endif

                                <div class="border-t border-white/5 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-300 hover:text-white transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary px-5 py-2 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/25">
                            Get Started
                        </a>
                    @endauth
                </div>

                {{-- Mobile toggle --}}
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/5">
                    <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                    <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden bg-gray-950/95 backdrop-blur-xl border-t border-white/5">
            <div class="px-4 py-4 space-y-1">
                <a href="{{ route('home') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5">Home</a>
                <a href="{{ route('store.index') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5">Store</a>
                <a href="{{ route('knowledgebase.index') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5">Knowledge Base</a>
                <a href="{{ route('announcements.index') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5">Announcements</a>
                <hr class="border-white/5 my-2">
                @auth
                    <div class="px-4 py-2">
                        <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-brand-400 hover:bg-brand-500/10">Dashboard</a>
                    <a href="{{ route('client.profile.edit') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5">Profile</a>
                    @if(Auth::user()->is_admin)
                        <a href="{{ url('/admin') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-amber-400 hover:bg-amber-500/10">Admin Panel</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-red-400 hover:bg-red-500/10">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5">Sign In</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2.5 rounded-xl text-sm font-semibold text-brand-400 hover:bg-brand-500/10">Get Started</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 pt-16">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="relative border-t border-white/5 bg-gray-950">
        <div class="absolute inset-0 hero-gradient opacity-30 pointer-events-none"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                {{-- Brand --}}
                <div class="lg:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/25">
                            <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="text-lg font-bold">{{ $companyName }}</span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">{{ $companyFooterText ?: 'Premium hosting and game server solutions powered by cutting-edge infrastructure.' }}</p>
                    <div class="flex gap-3">
                        <a href="#" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-brand-500/20 flex items-center justify-center text-gray-400 hover:text-brand-400 transition-all"><i class="fab fa-discord"></i></a>
                        <a href="#" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-brand-500/20 flex items-center justify-center text-gray-400 hover:text-brand-400 transition-all"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-brand-500/20 flex items-center justify-center text-gray-400 hover:text-brand-400 transition-all"><i class="fab fa-github"></i></a>
                    </div>
                </div>

                {{-- Products --}}
                <div>
                    <h3 class="text-sm font-semibold text-white mb-4 uppercase tracking-wider">Products</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('store.index') }}" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Web Hosting</a></li>
                        <li><a href="{{ route('store.index') }}" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Game Servers</a></li>
                        <li><a href="{{ route('store.index') }}" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">VPS Servers</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h3 class="text-sm font-semibold text-white mb-4 uppercase tracking-wider">Company</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('announcements.index') }}" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Affiliates</a></li>
                    </ul>
                </div>

                {{-- Support --}}
                <div>
                    <h3 class="text-sm font-semibold text-white mb-4 uppercase tracking-wider">Support</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('knowledgebase.index') }}" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Knowledge Base</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Open Ticket</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            {{-- Bottom --}}
            <div class="mt-12 pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-500 text-xs">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('terms') }}" class="text-gray-500 hover:text-gray-300 text-xs transition-colors">Terms of Service</a>
                    <a href="{{ route('privacy') }}" class="text-gray-500 hover:text-gray-300 text-xs transition-colors">Privacy Policy</a>
                    <a href="{{ route('sla') }}" class="text-gray-500 hover:text-gray-300 text-xs transition-colors">SLA</a>
                </div>
            </div>
        </div>
    </footer>

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

    @stack('scripts')
    <script>lucide.createIcons();</script>
</body>
</html>
