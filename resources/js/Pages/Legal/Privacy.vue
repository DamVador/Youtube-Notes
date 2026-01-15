<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Privacy Policy" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Privacy Policy</h1>
                
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                    <p class="mb-4"><strong>Last updated:</strong> January 2026</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">1. Information We Collect</h2>
                    <p class="mb-4">We collect information you provide directly to us, including:</p>
                    <ul class="list-disc pl-6 mb-4">
                        <li>Account information (name, email address)</li>
                        <li>Notes and documents you create</li>
                        <li>YouTube video references you save</li>
                    </ul>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">2. Google Authentication</h2>
                    <p class="mb-4">If you choose to sign in with Google, we receive your basic profile information (name, email, profile picture) from Google. We do not have access to your Google password.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">3. How We Use Your Information</h2>
                    <p class="mb-4">We use the information we collect to:</p>
                    <ul class="list-disc pl-6 mb-4">
                        <li>Provide, maintain, and improve our services</li>
                        <li>Create and manage your account</li>
                        <li>Store and sync your notes across devices</li>
                        <li>Send you technical notices and support messages</li>
                    </ul>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">4. Data Storage</h2>
                    <p class="mb-4">Your data is stored securely on our servers. We implement appropriate technical and organizational measures to protect your personal information.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">5. Data Sharing</h2>
                    <p class="mb-4">We do not sell, trade, or otherwise transfer your personal information to third parties. Your notes and documents remain private and are only accessible to you.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">6. YouTube API</h2>
                    <p class="mb-4">VidNotes uses the YouTube API to search for videos and display video information. By using our service, you also agree to be bound by the <a href="https://www.youtube.com/t/terms" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">YouTube Terms of Service</a>.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">7. Your Rights</h2>
                    <p class="mb-4">You have the right to:</p>
                    <ul class="list-disc pl-6 mb-4">
                        <li>Access your personal data</li>
                        <li>Correct inaccurate data</li>
                        <li>Request deletion of your data</li>
                        <li>Export your notes and documents</li>
                    </ul>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">8. Cookies</h2>
                    <p class="mb-4">We use essential cookies to maintain your session and remember your preferences. We do not use tracking or advertising cookies.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">9. Changes to This Policy</h2>
                    <p class="mb-4">We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">10. Contact</h2>
                    <p class="mb-4">For any questions about this Privacy Policy, please contact us at privacy@vidnotes.com.</p>
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