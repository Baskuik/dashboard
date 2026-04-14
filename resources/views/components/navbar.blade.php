{{-- Reusable Navbar Component --}}
<nav class="border-b border-white/5 bg-[#0f1421]/80 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex justify-between items-center h-14">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-blue-500 rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span class="font-semibold text-white tracking-tight">EcoCheck</span>
                {{ $slot }}
            </div>
            <div class="flex items-center gap-3 relative">
                @auth
                    <button type="button" id="user-menu-btn"
                        class="flex items-center gap-2 px-3 py-1.5 text-white text-sm font-semibold hover:bg-white/10 rounded-md transition">
                        <span class="mono">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>

                    <div id="user-menu-dropdown"
                        class="hidden absolute top-full right-0 mt-2 bg-[#131928] border border-white/10 rounded-lg shadow-2xl z-50 w-48 py-1">
                        <a href="{{ route('dashboard.select-widgets') }}"
                            class="block px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                            Widgets beheren
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit"
                                class="block w-full text-left px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                                Uitloggen
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="px-3 py-1.5 text-xs font-medium text-blue-400 hover:text-blue-300 border border-blue-500/30 rounded-md transition">
                        Inloggen
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userMenuBtn = document.getElementById('user-menu-btn');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');

        if (userMenuBtn && userMenuDropdown) {
            // Toggle dropdown on button click
            userMenuBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                userMenuDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                    userMenuDropdown.classList.add('hidden');
                }
            });
        }
    });
</script>
