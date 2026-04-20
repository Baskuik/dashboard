@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <x-filament-panels::avatar.user size="lg" :user="$user" loading="lazy" />

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
            </h2>

            <p class="fi-account-widget-user-name">
                {{ filament()->getUserName($user) }}
            </p>
        </div>

        <form action="{{ filament()->getLogoutUrl() }}" method="post" class="fi-account-widget-logout-form">
            @csrf

            <div class="flex gap-3 items-center">
                <button type="button" id="theme-toggle-admin-btn"
                    class="inline-flex items-center justify-center p-2 rounded-lg border border-gray-300/50 bg-gray-100 text-gray-700 transition-colors hover:bg-gray-200 dark:border-white/20 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    onclick="toggleThemeAdmin()" title="Toggle dark/light mode">
                    <svg id="theme-icon-admin" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 118.646 3.646 9 9 0 0120.354 15.354z" />
                    </svg>
                </button>

                <x-filament::button color="gray" :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle" :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON" labeled-from="sm" tag="button"
                    type="submit">
                    {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>

<script>
    function toggleThemeAdmin() {
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon-admin');

        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            if (icon) {
                icon.setAttribute('d',
                    'M12 3v1m6.364 1.636l-.707.707M21 12h-1m-1.636-6.364l-.707-.707M12 21v-1m-6.364-1.636l.707-.707M3 12h1m1.636 6.364l.707.707M7.05 6.636l.707-.707m10.606 10.606l.707.707M16.95 7.05l.707.707m-10.606 10.606l-.707.707'
                );
            }
        } else {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            if (icon) {
                icon.setAttribute('d', 'M20.354 15.354A9 9 0 118.646 3.646 9 9 0 0120.354 15.354z');
            }
        }
    }

    // Initialize icon based on current theme
    document.addEventListener('DOMContentLoaded', function() {
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon-admin');
        if (icon) {
            if (html.classList.contains('dark')) {
                icon.setAttribute('d', 'M20.354 15.354A9 9 0 118.646 3.646 9 9 0 0120.354 15.354z');
            } else {
                icon.setAttribute('d',
                    'M12 3v1m6.364 1.636l-.707.707M21 12h-1m-1.636-6.364l-.707-.707M12 21v-1m-6.364-1.636l.707-.707M3 12h1m1.636 6.364l.707.707M7.05 6.636l.707-.707m10.606 10.606l.707.707M16.95 7.05l.707.707m-10.606 10.606l-.707.707'
                );
            }
        }
    });
</script>
