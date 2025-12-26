<template>
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center font-bold">JA</div>
                        <span class="text-xl font-bold">JA-CMS</span>
                    </div>
                    <h5 class="text-white font-semibold mb-4">Stay Updated</h5>
                    <p class="text-gray-400 text-sm mb-4">
                        Subscribe to our newsletter for the latest updates and articles.
                    </p>
                    <form class="flex gap-2" @submit.prevent>
                        <input 
                            type="email" 
                            placeholder="Email address" 
                            class="flex-1 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors"
                        >
                        <button type="submit" class="p-2 bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-16 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">
                    &copy; 2024 JA-CMS. All rights reserved.
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-gray-500 hover:text-white text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-gray-500 hover:text-white text-sm transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
</template>
