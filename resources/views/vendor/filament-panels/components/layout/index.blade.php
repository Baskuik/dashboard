@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;

    $hasTopbar = filament()->hasTopbar();
    $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
    $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
    $hasTopNavigation = filament()->hasTopNavigation();
    $hasNavigation = filament()->hasNavigation();
    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= filament()->getMaxContentWidth() ?? Width::SevenExtraLarge;

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire" @class([
    'fi-body-has-navigation' => $hasNavigation,
    'fi-body-has-sidebar-collapsible-on-desktop' => $isSidebarCollapsibleOnDesktop,
    'fi-body-has-sidebar-fully-collapsible-on-desktop' => $isSidebarFullyCollapsibleOnDesktop,
    'fi-body-has-topbar' => $hasTopbar,
    'fi-body-has-top-navigation' => $hasTopNavigation,
])>
    @if ($hasTopbar)
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_BEFORE, scopes: $renderHookScopes) }}

        <!-- Custom Navbar (Dashboard Style) -->
        <nav class="border-b border-gray-200 dark:border-white/5 bg-white dark:bg-[#1e3a5f] sticky top-0 z-50">
            <div class="max-w-full mx-auto px-6 lg:px-8">
                <div class="flex justify-between items-center h-14">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 hover:opacity-80 transition">
                        <div class="w-7 h-7 bg-blue-500 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white tracking-tight">EcoCheck</span>
                    </a>
                    <div class="flex items-center gap-3 relative">
                        <!-- Theme Toggle Button -->
                        <button type="button" id="theme-toggle-btn-filament"
                            class="flex items-center justify-center w-9 h-9 text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-white bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 rounded-lg transition"
                            title="Toggle dark mode">
                            <svg id="theme-sun-icon-filament" class="w-4 h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1m-16 0H1m15.364 1.636l.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg id="theme-moon-icon-filament" class="w-4 h-4 hidden" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>
                        @auth
                            <button type="button" id="user-menu-btn"
                                class="flex items-center gap-2 px-3 py-1.5 text-gray-900 dark:text-white text-sm font-semibold hover:bg-gray-100 dark:hover:bg-white/10 rounded-md transition">
                                <span style="font-family: 'DM Mono', monospace;">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </button>

                            <div id="user-menu-dropdown"
                                class="hidden absolute top-full right-0 mt-2 bg-white dark:bg-[#131928] border border-gray-200 dark:border-white/10 rounded-lg shadow-2xl z-50 w-48 py-1">
                                <a href="{{ route('dashboard.select-widgets') }}"
                                    class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition">
                                    Widgets beheren
                                </a>
                                @if (Auth::user()->is_admin)
                                    <a href="{{ route('dashboard') }}"
                                        class="block px-4 py-2.5 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition font-semibold">
                                        ← Terug naar dashboard
                                    </a>
                                    <hr class="border-gray-100 dark:border-white/5 my-1">
                                @endif
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition">
                                        Uitloggen
                                    </button>
                                </form>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Theme Toggle
                const themeToggleBtn = document.getElementById('theme-toggle-btn-filament');
                const themeSunIcon = document.getElementById('theme-sun-icon-filament');
                const themeMoonIcon = document.getElementById('theme-moon-icon-filament');

                // Initialize theme from localStorage
                const currentTheme = localStorage.getItem('theme') || 'dark';
                if (currentTheme === 'light') {
                    document.documentElement.classList.remove('dark');
                    themeSunIcon.classList.remove('hidden');
                    themeMoonIcon.classList.add('hidden');
                } else {
                    document.documentElement.classList.add('dark');
                    themeSunIcon.classList.add('hidden');
                    themeMoonIcon.classList.remove('hidden');
                }

                if (themeToggleBtn) {
                    themeToggleBtn.addEventListener('click', function() {
                        const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

                        if (newTheme === 'dark') {
                            document.documentElement.classList.add('dark');
                            themeSunIcon.classList.add('hidden');
                            themeMoonIcon.classList.remove('hidden');
                        } else {
                            document.documentElement.classList.remove('dark');
                            themeSunIcon.classList.remove('hidden');
                            themeMoonIcon.classList.add('hidden');
                        }

                        localStorage.setItem('theme', newTheme);
                        location.reload();
                    });
                }

                // User Menu Toggle
                const userMenuBtn = document.getElementById('user-menu-btn');
                const userMenuDropdown = document.getElementById('user-menu-dropdown');

                if (userMenuBtn && userMenuDropdown) {
                    userMenuBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        userMenuDropdown.classList.toggle('hidden');
                    });

                    document.addEventListener('click', function(e) {
                        if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                            userMenuDropdown.classList.add('hidden');
                        }
                    });
                }
            });
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_AFTER, scopes: $renderHookScopes) }}
    @elseif ($hasNavigation)
        <div @if ($isSidebarFullyCollapsibleOnDesktop) x-data="{}"
                x-bind:class="{ 'lg:fi-hidden': $store.sidebar.isOpen }" @endif
            @class([
                'fi-layout-sidebar-toggle-btn-ctn',
                'lg:fi-hidden' => !$isSidebarFullyCollapsibleOnDesktop,
            ])>
            <x-filament::icon-button color="gray" :icon="\Filament\Support\Icons\Heroicon::OutlinedBars3" :icon-alias="\Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON" icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                x-cloak x-data="{}" x-on:click="$store.sidebar.open()"
                class="fi-layout-sidebar-toggle-btn" />
        </div>
    @endif

    <div class="fi-layout">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::LAYOUT_START, scopes: $renderHookScopes) }}

        @if ($hasNavigation)
            <div x-cloak x-data="{}" x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen"
                x-transition.opacity.300ms class="fi-sidebar-close-overlay"></div>

            @livewire(filament()->getSidebarLivewireComponent())
        @endif

        <div @if ($isSidebarCollapsibleOnDesktop) x-data="{}"
                x-bind:class="{
                    'fi-main-ctn-sidebar-open': $store.sidebar.isOpen,
                }"
                x-bind:style="'display: flex; opacity:1;'"
                {{-- Mimics `x-cloak`, as using `x-cloak` causes visual issues with chart widgets --}}
            @elseif ($isSidebarFullyCollapsibleOnDesktop)
                x-data="{}"
                x-bind:class="{
                    'fi-main-ctn-sidebar-open': $store.sidebar.isOpen,
                }"
                x-bind:style="'display: flex; opacity:1;'"
                {{-- Mimics `x-cloak`, as using `x-cloak` causes visual issues with chart widgets --}}
            @elseif (!($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop || $hasTopNavigation || !$hasNavigation))
                x-data="{}"
                x-bind:style="'display: flex; opacity:1;'" {{-- Mimics `x-cloak`, as using `x-cloak` causes visual issues with chart widgets --}} @endif
            class="fi-main-ctn">
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::CONTENT_BEFORE, scopes: $renderHookScopes) }}

            <main @class([
                'fi-main',
                $maxContentWidth instanceof Width
                    ? "fi-width-{$maxContentWidth->value}"
                    : $maxContentWidth,
            ])>
                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::CONTENT_START, scopes: $renderHookScopes) }}

                {{ $slot }}

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::CONTENT_END, scopes: $renderHookScopes) }}
            </main>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::CONTENT_AFTER, scopes: $renderHookScopes) }}

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
