<template>
    <li class="inline-block m-4">
        <div v-if="post.type === 'link'" class="card">
            <svg class="three-dots-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>                    
            <a :href="post.url" target="_blank">
                <div v-if="post.image_path" class="h-cover w-full bg-cover bg-center" :style="image"></div>
                <div v-else class="h-cover w-full flex items-center justify-center" :style="color">
                    <span class="text-white text-2xl font-medium">{{ domain }}</span>
                </div>
            </a>
            <div class="px-6 pt-4">
                <div class="font-bold text-xl mb-2 truncate" :title="post.title">
                    {{ post.title }}
                </div>
                <p v-if="post.description !== null" class="text-gray-700 text-base overflow-hidden description">
                    {{ post.description }}
                </p>
                <p v-else class="text-gray-700 text-base italic overflow-hidden description">
                    No description
                </p>
            </div>
            <div class="px-6 py-4 truncate">
                <img :src="'https://www.google.com/s2/u/0/favicons?domain=' + domain" class="inline img-vertical-align">
                <a :href="post.url" :title="post.url" target="_blank" class="text-blue-600">{{ post.url }}</a>
            </div>
        </div>
        <div v-else class="card bg-gray-100">
            <svg class="three-dots-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>
            <div class="p-6">
                <p class="text-gray-900 text-xl overflow-hidden">{{ post.content }}</p>
            </div>
        </div>
    </li>
</template>
<script>
export default {
    name: 'Post',
    props: ['post'],
    computed: {
        image () {
            return 'background-image: url(\'' + this.post.image_path + '\')'
        },
        color () {
            return 'background-color: ' + this.post.color
        },
        domain () {
            return this.post.base_url.replace(/(https|http):\/\//,'')
        }
    }
}
</script>
<style lang="scss" scoped>
    .card {
        @apply rounded relative overflow-hidden shadow-lg;
        width: 22rem;
        height: 22.5rem;
    }
    .h-cover {
        height: 12.5rem;
    }
    .description {
        height: 3rem;
    }
    .img-vertical-align {
        margin-top: -4px;
    }
    .three-dots-svg {
        @apply w-5 h-5 absolute;
        /* right: 0.5rem;
        top: 0.75rem; */
        right: 0.75rem;
        bottom: 1.25rem;
    }
</style>