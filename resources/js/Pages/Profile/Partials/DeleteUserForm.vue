<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <section>
        <header class="mb-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Delete Account
            </h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Once your account is deleted, all of its resources and data will be permanently deleted.
            </p>
        </header>

        <button
            @click="confirmUserDeletion"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
        >
            Delete Account
        </button>

        <!-- Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="confirmingUserDeletion" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-900/50" @click="closeModal"></div>

                    <!-- Modal Content -->
                    <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                            Are you sure you want to delete your account?
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                            Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.
                        </p>

                        <div class="mb-6">
                            <label for="delete_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Password
                            </label>
                            <input
                                id="delete_password"
                                ref="passwordInput"
                                type="password"
                                v-model="form.password"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Enter your password"
                                @keyup.enter="deleteUser"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button
                                @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="deleteUser"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>