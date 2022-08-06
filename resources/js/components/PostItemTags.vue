<template>
    <div class="tags">
        <ul class="truncate mr-2">
            <li v-for="tag in tags" :key="tag.id" class="tag">
                <router-link :to="'/tags/' + tag.id"> #{{ tag.name }} </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        tags: {
            type: Array,
            default: null,
        },
    },
}
</script>

<style lang="scss">
.card {
    .tags {
        @apply bg-gray-100;
        margin-left: -0.25rem;
        margin-right: -0.25rem;
        .tag {
            @apply inline text-gray-800 font-medium text-sm;
            padding: 0.125rem 0.3rem;
        }
    }
    .tags.item-text {
        margin-top: -0.875rem;
    }
    .tags .tag:hover {
        @apply text-orange-600;
    }
}
</style>
