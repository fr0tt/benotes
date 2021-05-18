<template>
    <div v-if="Math.random() > 0.4" class="card animate-pulse">
        <div class="w-3/7 md:w-full h-cover bg-cover bg-gray-400 bg-center"></div>
        <div class="w-4/7 md:w-full px-6 py-4 space-y-4">
            <div class="h-6 bg-gray-400 rounded w-3/4"></div>
            <div class="h-4 bg-gray-400 rounded"></div>
            <div class="h-4 bg-gray-400 rounded w-5/6"></div>
            <div class="flex">
                <div class="rounded-full bg-gray-400 h-6 w-6"></div>
                <div class="flex-1 ml-3 h-4 my-auto bg-gray-400 rounded"></div>
            </div>
        </div>
    </div>
    <div v-else class="card animate-pulse">
        <div class="w-full px-6 py-8 space-y-4">
            <div class="h-8 md:h-10 bg-gray-400 rounded w-5/6 mb-8"></div>
            <div class="h-4 bg-gray-400 rounded"></div>
            <div class="h-4 bg-gray-400 rounded w-5/6"></div>
            <div class="hidden md:block h-4 bg-gray-400 rounded"></div>
            <div class="hidden md:block h-4 bg-gray-400 rounded w-3/4"></div>
            <div class="hidden md:block h-4 bg-gray-400 rounded w-1/2"></div>
        </div>
    </div>
</template>