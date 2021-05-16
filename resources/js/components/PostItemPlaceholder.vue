<template>
    <div class="card">
        <div v-if="Math.random() > 0.4" class="animate-pulse">
            <div class="h-cover w-full bg-cover bg-gray-400 bg-center"></div>
            <div class="px-6 py-4 space-y-4">
                <div class="h-6 bg-gray-400 rounded w-3/4"></div>
                <div class="h-4 bg-gray-400 rounded"></div>
                <div class="h-4 bg-gray-400 rounded w-5/6"></div>
                <div class="flex">
                    <div class="rounded-full bg-gray-400 h-6 w-6"></div>
                    <div class="flex-1 ml-3 h-4 my-auto bg-gray-400 rounded"></div>
                </div>
            </div>
        </div>
        <div v-else class="animate-pulse">
            <div class="px-6 py-8 space-y-4">
                <div class="h-10 bg-gray-400 rounded w-5/6 mb-8"></div>
                <div class="h-4 bg-gray-400 rounded"></div>
                <div class="h-4 bg-gray-400 rounded w-5/6"></div>
                <div class="h-4 bg-gray-400 rounded"></div>
                <div class="h-4 bg-gray-400 rounded w-3/4"></div>
                <div class="h-4 bg-gray-400 rounded w-1/2"></div>
            </div>
        </div>
    </div>
</template>