<template>
    <div class="tags">
        <ul class="truncate">
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
        .tag {
            @apply inline text-gray-800 font-medium text-sm;
            padding-top: 0.3rem;
            padding-bottom: 0.3rem;
            padding-right: 0.125rem;
        }
    }
    .tags.item-text {
        position: absolute;
        width: calc(100% - 3rem);
        margin-top: -0.875rem;
    }
    .tags .tag:hover {
        @apply text-orange-600;
    }
}
</style>
