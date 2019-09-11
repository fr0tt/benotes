<template>
    <div class="fixed bottom-0 w-full left-0 bg-gray-200">
        <form @submit.prevent="create" @keyup.ctrl.enter="create" class="flex mx-6 my-8">
            <div class="w-1/6"></div>
            <div class="w-4/6">
                <textarea ref="content" class="w-full rounded-full border-2 outline-none border-gray-400 
                    bg-white py-2 px-5 text-lg leading-snug focus:border-blue-400"></textarea>
                <!--<div class="">
                    <span> save in: </span> <input class="text-bold text-blue-600">Uncateogirzed</input>
                </div>-->
            </div>
            <div class="w-1/6">
                <button class="border-2 border-blue-500 rounded-full py-2 px-3 text-blue-600 bg-white
                    leading-tight text-xl font-semibold mx-4 my-1 px-6 hover:bg-blue-500 hover:text-white">
                    Save
                </button>
                <p class="ml-6 text-sm text-gray-600">Strg + Enter</p>
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
            if (this.$refs.content.value === '' || this.collection === null) {
                return
            }
            axios.post('/api/posts', {
                content: this.$refs.content.value,
                collection_id: this.currentCollection.id
            })
                .then(response => {
                    this.$store.dispatch('post/addPost', response.data.data)
                })
                .catch(error => {
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
        ])
    },
    watch: {
        currentPost (post) {
            this.$refs.content.value = post.content
            this.$refs.content.focus()
        }
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

</style>