<template>
    <div v-if="showSpinner">
        <div class="absolute bottom-0 pb-6 left-0 right-0 text-center animate-pulse text-gray-700">
            <svg-vue icon="remix/refresh-line"
                class="button-icon w-6 inline-block align-top animate-spin fill-current"/>
            <span class="ml-1">loading...</span>
        </div>
    </div>
</template>
<script>
import { mapState } from 'vuex'
export default {
    props: {
        collectionId: Number,
        isArchived: Boolean,
    },
    data () {
        return {
            showSpinner: false
        }
    },
    methods: {
        handleScroll () {
            if (this.showSpinner || this.isCompletelyLoaded) {
                return
            }
            const view = document.querySelector('#view')
            if (view.scrollTop <= 0) {
                return
            }
            if (view.scrollHeight === window.innerHeight + view.scrollTop) {
                this.showSpinner = true
                let data = {
                    collectionId: this.collectionId
                }
                if (this.isArchived) {
                    data = {
                        isArchived: true
                    }
                }
                this.$store.dispatch('post/fetchMorePosts', data).then(() => {
                    this.showSpinner = false
                })
            }
        }
    },
    computed: {
        ...mapState('post', [
            'isCompletelyLoaded'
        ]),
    },
    mounted () {
        document.querySelector('#view').addEventListener('scroll', this.handleScroll)
    },
    beforeDestroy () {
        document.querySelector('#view').removeEventListener('scroll', this.handleScroll)
    },
}
</script>
