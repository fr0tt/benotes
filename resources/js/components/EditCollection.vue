<template>
    <div class="my-8 lg:mx-20 mx-10">

        <div class="m-auto mt-40 max-w-5xl">

            <div class="w-full">
                <div class="mb-10">
                    <label class="block uppercase text-gray-600 font-medium">Name of Collection</label>
                    <input v-model="name" placeholder="Name" autofocus
                        class="w-full text-4xl text-gray-800 font-bold bg-gray-300 outline-none py-1 px-2"/>
                </div>
            </div>
            <div class="w-full">
                <div class="text-right mt-6">
                    <button v-if="isNew" @click="create()" class="button">
                        <svg-vue class="button-icon" icon="zondicons/add-outline"/>
                        Create
                    </button>
                    <button v-else @click="update()" class="button">
                        <svg-vue class="button-icon" icon="zondicons/checkmark-outline"/>
                        Save
                    </button>
                </div>
            </div>

        </div>

    </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
export default {
    props: ['id', 'isNew'],
    data () {
        return {
            name: ''
        }
    },
    methods: {
        create () {
            axios.post('/api/collections', {
                name: this.name
            })
                .then(response => {
                    this.$store.dispatch('collection/addCollection', response.data.data)
                    this.$router.push({ path: '/' })
                })
                .catch(error => {
                    console.log(error.response.data)
                })
        },
        update () {
            this.$store.dispatch('collection/updateCollection', { id: this.id, name: this.name })
            this.$router.push({ path: '/' })
        }
    },
    computed: {
        ...mapState('collection', [
            'collections'
        ])
    },
    created () {
        if (!this.isNew) {
            if (parseInt(this.id) === 0) {
                this.$router.push({ path: '/' })
                return
            }
            axios.get('/api/collections/' + this.id)
                .then(response => {
                    const collection = response.data.data
                    this.name = collection.name
                })
                .catch(error => {
                    console.log(error.response.data)
                })
        }
    }
}
</script>
