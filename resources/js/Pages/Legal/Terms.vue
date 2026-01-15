<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Terms of Service" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Terms of Service</h1>
                
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                    <p class="mb-4"><strong>Last updated:</strong> January 2026</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">1. Acceptance of Terms</h2>
                    <p class="mb-4">By accessing and using VidNotes, you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">2. Description of Service</h2>
                    <p class="mb-4">VidNotes is a note-taking application that allows users to take and organize notes while watching YouTube videos. The service includes features such as timestamped notes, rich text editing, and document export.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">3. User Accounts</h2>
                    <p class="mb-4">To use VidNotes, you must create an account. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">4. User Content</h2>
                    <p class="mb-4">You retain ownership of all notes and content you create using VidNotes. You are solely responsible for the content you create and store on our platform.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">5. Acceptable Use</h2>
                    <p class="mb-4">You agree not to use VidNotes for any unlawful purpose or in any way that could damage, disable, or impair the service.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">6. Third-Party Services</h2>
                    <p class="mb-4">VidNotes integrates with YouTube and Google services. Your use of these third-party services is subject to their respective terms of service.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">7. Termination</h2>
                    <p class="mb-4">We reserve the right to terminate or suspend your account at any time for violations of these terms or for any other reason at our discretion.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">8. Disclaimer</h2>
                    <p class="mb-4">VidNotes is provided "as is" without warranties of any kind. We do not guarantee that the service will be uninterrupted or error-free.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">9. Contact</h2>
                    <p class="mb-4">For any questions regarding these Terms of Service, please contact us at support@vidnotes.com.</p>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <Link href="/" class="text-blue-600 dark:text-blue-400 hover:underline">
                        ← Back to home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>