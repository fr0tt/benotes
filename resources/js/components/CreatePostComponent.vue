<template>
    <div class="fixed bottom-0 w-full left-0 bg-gray-200" v-if="!isMobile || (isMobile && currentPost === null)">
        <form @submit.prevent="create" @keyup.ctrl.enter="create" class="flex md:mx-6 mx-1 md:my-6 mt-2">
            <div class="lg:w-1/6"></div>
            <div class="lg:w-4/6 flex-1">
                <textarea ref="content" class="w-full rounded-full border-2 outline-none border-gray-400 
                    bg-white py-2 px-5 text-lg leading-snug focus:border-blue-400" 
                    :rows="isMobile ? '1' : '2'"></textarea>
                <!--<div class="">
                    <span> save in: </span> <input class="text-bold text-blue-600">Uncateogirzed</input>
                </div>-->
            </div>
            <div class="lg:w-1/6">
                <button class="border-2 border-blue-500 rounded-full outline-none
                    leading-tight md:text-xl font-semibold md:mx-4 ml-1 md:px-6 px-5 py-2.5
                    bg-blue-500 text-white hover:text-blue-600 hover:bg-white">
                    Save
                </button>
                <p class="ml-6 text-sm text-gray-600 hidden md:block">Strg + Enter</p>
            </div>
        </form>
    </div>
</template>
<script>
import axios from 'axios'
import { mapState } from 'vuex'
export default {
    methods: {
        create () {
            if (this.$refs.content.value === '' || this.currentCollection === null) {
                return
            }
            axios.post('/api/posts', {
                content: this.$refs.content.value,
                collection_id: this.currentCollection.id
            }).then(response => {
                this.$store.dispatch('post/addPost', response.data.data)
            }).catch(error => {
                console.log(error)
            })
        }
    },
    computed: {
        ...mapState('collection', [
            'currentCollection'
        ]),
        ...mapState('post', [
            'currentPost'
        ]),
        ...mapState(['isMobile'])
    }
}
</script>
<style lang="scss" scoped>

textarea {
    resize: none;
}

::-webkit-scrollbar {
	width: 4px;
	background-color: #F5F5F5;
}

::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	background-color: rgba(255,255,255,0.8);
}

::-webkit-scrollbar-thumb {
	@apply bg-blue-600;
    border-radius: 8px;
}

.py-2\.5 {
    padding-top: 0.625rem;
    padding-bottom: 0.625rem;
}

</style>