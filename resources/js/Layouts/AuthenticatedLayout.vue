<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Logo from '@/Components/Logo.vue';
import { Link } from '@inertiajs/vue3';
import CookieConsent from '@/Components/CookieConsent.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-900">
        <!-- Navbar -->
        <nav class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <Link :href="route('dashboard')" class="flex items-center">
                            <Logo size="md" :showText="true" customClass="hidden sm:flex" />
                            <Logo size="md" :showText="false" customClass="flex sm:hidden" />
                        </Link>

                        <!-- Navigation Links -->
                        <div class="hidden sm:flex sm:items-center sm:ml-10 space-x-1">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </NavLink>
                            <NavLink :href="route('videos.index')" :active="route().current('videos.*')">
                                Videos
                            </NavLink>
                            <NavLink :href="route('notes.index')" :active="route().current('notes.*')">
                                Notes
                            </NavLink>
                            <NavLink :href="route('subscription.pricing')" :active="route().current('subscription.*')">
                                Pricing
                            </NavLink>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="hidden sm:flex sm:items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                >
                                    <div class="w-8 h-8 bg-slate-200 dark:bg-slate-600 rounded-full flex items-center justify-center">
                                        <span class="text-slate-600 dark:text-slate-300 text-sm font-medium">
                                            {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <span class="hidden md:block">{{ $page.props.auth.user.name }}</span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink 
                                    v-if="$page.props.auth.user.is_admin" 
                                    :href="route('admin.dashboard')"
                                >
                                    🛡️ Admin
                                </DropdownLink>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink 
                                    v-if="$page.props.auth.user.isPremium" 
                                    :href="route('subscription.billing')"
                                >
                                    Manage Subscription
                                </DropdownLink>
                                <DropdownLink 
                                    v-else 
                                    :href="route('subscription.pricing')" 
                                    class="text-blue-600 dark:text-blue-400"
                                >
                                    ⭐ Upgrade to Premium
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile Hamburger -->
                    <div class="flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="p-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    v-if="!showingNavigationDropdown"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div v-show="showingNavigationDropdown" class="sm:hidden border-t border-slate-200 dark:border-slate-700">
                <div class="py-2 space-y-1">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('videos.index')" :active="route().current('videos.*')">
                        Videos
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('notes.index')" :active="route().current('notes.*')">
                        Notes
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('subscription.pricing')" :active="route().current('subscription.*')">
                        Pricing
                    </ResponsiveNavLink>
                </div>

                <div class="py-3 border-t border-slate-200 dark:border-slate-700">
                    <div class="px-4 py-2">
                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>
                    <ResponsiveNavLink 
                        v-if="$page.props.auth.user.is_admin" 
                        :href="route('admin.dashboard')"
                    >
                        🛡️ Admin
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('profile.edit')">
                        Profile
                    </ResponsiveNavLink>
                    <ResponsiveNavLink 
                        v-if="$page.props.auth.user.isPremium" 
                        :href="route('subscription.billing')"
                    >
                        Manage Subscription
                    </ResponsiveNavLink>
                    <ResponsiveNavLink 
                        v-else 
                        :href="route('subscription.pricing')"
                        class="text-blue-600 dark:text-blue-400"
                    >
                        ⭐ Upgrade to Premium
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                        Log Out
                    </ResponsiveNavLink>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <Logo size="sm" />
                    <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400">
                        <Link :href="route('legal.terms')" class="hover:text-slate-700 dark:hover:text-slate-200">
                            Terms
                        </Link>
                        <Link :href="route('legal.privacy')" class="hover:text-slate-700 dark:hover:text-slate-200">
                            Privacy
                        </Link>
                        <span>© {{ new Date().getFullYear() }} VidNotes</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <CookieConsent />
</template>