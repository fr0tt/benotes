<template>
    <div class="py-12 px-12 md:px-40 md:pt-32 max-w-5xl">
        <div class="w-full collection">
            <h1 class="text-3xl font-bold mb-4">{{ headline }}</h1>
            <p class="text-xl mb-16">{{ description }}</p>
            <div class="mb-10">
                <label class="label">Name of Collection</label>
                <input v-model="name" placeholder="Name" autofocus class="input"/>
            </div>
            <div v-if="!isNew" class="mb-16">
                <label class="label inline-block">Collection Url</label>
                <button @click="is_active = !is_active" class="switch"
                    :class="[is_active ? 'bg-orange-600 border-orange-600 text-white' 
                    : 'border-gray-600 text-gray-600']">
                    {{ switchValue }}
                </button>
                <div class="w-full mt-4 md:mt-0 md:flex">
                    <input class="input readonly" :value="domain" readonly/>
                    <div class="flex flex-1 mt-1 md:mt-0">
                        <input v-model="token" class="input flex-1 mr-1" placeholder="Collection Url"/>
                        <div v-if="isSupported" @click="copy" 
                            class="bg-gray-300 px-2 mr-1 rounded cursor-pointer">
                            <svg-vue class="w-6 mt-3" icon="material/link"/>
                        </div>
                        <div @click="generate()" 
                            class="px-2 bg-gray-300 rounded cursor-pointer">
                            <svg-vue class="w-6 mt-3" icon="material/autorenew"/>
                        </div>
                    </div>
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
            name: '',
            token: '',
            share: null,
            is_active: false,
            switch: (this.is_active) ? 'active' : 'inactive',
            headline: (this.isNew) ? 'Create Collection' : 'Collection Settings',
            description: (this.isNew) ? 'Specify a name for your new collection.'
                : 'Update your collection\'s title and public available URL.',
            isSupported: null
        }
    },
    methods: {
        create () {
            axios.post('/api/collections', {
                name: this.name
            })
                .then(response => {
                    this.$store.dispatch('collection/addCollection', response.data.data)
                    this.$router.push({ path: '/c/' + response.data.data.id })
                })
                .catch(error => {
                    console.log(error.response.data)
                })
        },
        update () {
            this.$store.dispatch('collection/updateCollection', { id: this.id, name: this.name })
            this.handleShare()
            this.$router.push({ path: '/c/' + this.id })
        },
        copy (event) {
            navigator.clipboard.writeText(this.domain + this.token).catch((error) => {
                console.log(error)
            })
        },
        generate () {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
            const charsLength = chars.length
            let value = ''
            for (let i = 0; i < 32; i++) {
                value += chars.charAt(Math.floor(Math.random() * charsLength))
            }
            this.token = value
        },
        getShares () {
            axios.get('/api/shares/', {
                params: {
                    collection_id: this.id
                }
            })
                .then(response => {
                    if (response.data.data.length === 0) {
                        return
                    }
                    const share = response.data.data[0]
                    this.share = share
                    this.token = share.token
                    this.is_active = share.is_active
                })
                .catch(error => {
                    console.log(error.response.data)
                })
        },
        handleShare () {
            if (this.share === null) {
                axios.post('/api/shares', {
                    token: this.token,
                    collection_id: this.id,
                    is_active: this.is_active
                })
                    .catch(error => {
                        console.log(error.response.data)
                    })
            } else if (this.token === '') {
                axios.delete('/api/shares/' + this.share.id)
                    .catch(error => {
                        console.log(error.response.data)
                    })
            } else if (this.token !== '') {
                axios.patch('/api/shares/' + this.share.id, {
                    token: this.token,
                    collection_id: this.id,
                    is_active: this.is_active
                })
                    .catch(error => {
                        console.log(error.response.data)
                    })
            }
        }
    },
    computed: {
        switchValue () {
            if (this.is_active) {
                return 'active'
            } else {
                return 'inactive'
            }
        },
        domain () {
            return window.location.protocol + '//' + window.location.hostname + '/s?token='
        },
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
            this.getShares()

            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit Collection',
                button: {
                    label: 'Save',
                    callback: this.update,
                    icon: 'checkmark'
                }
            })
        } else {
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create Collection',
                button: {
                    label: 'Save',
                    callback: this.create,
                    icon: 'checkmark'
                } 
            })
        }
        navigator.permissions.query({ name: 'clipboard-write' }).then(result => {
            this.isSupported = result.state === 'granted' || result.state === 'prompt'
        }).catch(() => {
            this.isSupported = false
        })
    }
}
</script>
<style lang="scss">
    button.switch {
        @apply float-right border-2 uppercase font-medium tracking-wide text-sm px-2 mb-1;
        padding-top: 0.125rem;
        padding-bottom: 0.125rem;
        transition: color, background-color 0.2s;
    }
    button.switch:hover {
        @apply bg-white text-orange-600;
    }
    .collection {
        input.readonly {
            @apply text-white bg-gray-600 border-none w-auto;
        }
        .label.inline-block {
            @apply inline-block;
        }
    }
    .px-1\.5 {
        padding-right: 0.375rem;
        padding-left: 0.375rem;
    }
</style>