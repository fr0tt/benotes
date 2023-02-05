<template>
    <div class="py-12 px-12 md:px-40 md:pt-32 max-w-5xl">
        <div class="w-full collection">
            <h1 class="text-3xl font-bold mb-4">
                {{ headline }}
            </h1>
            <p class="text-xl mb-16">
                {{ description }}
            </p>

            <div class="mb-10">
                <label class="label">Name of Collection</label>
                <input v-model="name" placeholder="Name" autofocus class="input" />
            </div>

            <div class="mb-10">
                <label class="label">Subcollection of</label>

                <Select
                    ref="parentCollection"
                    v-model="parentCollection"
                    class="inline-block w-80"
                    label="name"
                    :options="optionsCollections"
                    @close="
                        () => {
                            $refs.parentCollection?.$el.querySelector('input[type=search]')?.blur()
                        }
                    " />
            </div>

            <div class="mb-10 relative">
                <label class="label">Collection Icon</label>
                <button class="border-2 border-gray-400 rounded py-2 px-2" @click="openPicker()">
                    <svg-vue
                        v-if="collectionIconIsInline(iconId)"
                        :icon="'glyphs/' + iconId"
                        class="w-6 h-6" />
                    <svg v-else-if="iconId" class="w-6 h-6">
                        <use :xlink:href="'/glyphs.svg#' + iconId" />
                    </svg>
                    <svg-vue
                        v-else
                        icon="remix/folder-fill"
                        class="w-6 text-gray-500 fill-current align-text-bottom" />
                </button>
                <transition name="fade">
                    <IconPicker v-if="showPicker" @iconSelected="iconSelect" />
                </transition>
                <p class="mt-4">Select an optional icon for your collection.</p>
            </div>

            <div v-if="!isNew" class="mt-16 mb-16">
                <label class="label inline-block">Collection Url</label>
                <button
                    class="switch"
                    :class="[
                        is_active
                            ? 'bg-orange-600 border-orange-600 text-white'
                            : 'border-gray-600 text-gray-600',
                    ]"
                    @click="is_active = !is_active">
                    {{ switchValue }}
                </button>

                <div class="w-full mt-4 md:mt-0 md:flex">
                    <input class="input readonly" :value="domain" readonly />
                    <div class="flex flex-1 mt-1 md:mt-0">
                        <input
                            v-model="token"
                            class="input flex-1 mx-1"
                            placeholder="Collection Url" />
                        <div
                            v-if="isSupported"
                            class="bg-gray-300 px-2 mr-1 rounded cursor-pointer"
                            @click="copy">
                            <svg-vue class="w-6 mt-2.5" icon="material/link" />
                        </div>
                        <div class="px-2 bg-gray-300 rounded cursor-pointer" @click="generate()">
                            <svg-vue class="w-6 mt-2.5" icon="material/autorenew" />
                        </div>
                    </div>
                </div>

                <p class="mt-4">
                    Make this collection publicly available by visiting the specified URL.
                </p>
            </div>

            <div v-if="!isNew" class="mb-14 py-6 px-6 bg-red-400 rounded">
                <h3 class="text-xl font-semibold mb-1">Delete collection</h3>
                <p class="mb-4">Delete this collection and all its content.</p>
                <button title="Delete Collection" class="button red mb-2" @click="deleteCollection">
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
import { collectionIconIsInline } from './../../api/collection'
import IconPicker from '../IconPicker.vue'
import Select from 'vue-select'
import OpenIndicator from '../OpenIndicator.vue'
import Deselect from '../Deselect.vue'
import 'vue-select/dist/vue-select.css'
export default {
    components: {
        IconPicker,
        Select,
        OpenIndicator,
        Deselect,
    },
    props: ['id', 'isNew'],
    data() {
        return {
            name: '',
            token: '',
            share: null,
            is_active: false,
            switch: this.is_active ? 'active' : 'inactive',
            headline: this.isNew ? 'Create Collection' : 'Collection Settings',
            description: this.isNew
                ? 'Specify a name for your new collection.'
                : "Update your collection's title and public available URL.",
            isSupported: null,
            iconId: null,
            showPicker: false,
            optionsCollections: [],
            parentCollection: null,
        }
    },
    methods: {
        create() {
            axios
                .post('/api/collections', {
                    name: this.name,
                    parent_id: this.parentCollection?.id, // @TODO check if this works or not
                    icon_id: this.iconId,
                })
                .then((response) => {
                    this.$store.dispatch('collection/addCollection', response.data.data)
                    this.$router.push({ path: '/c/' + response.data.data.id })
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
        },
        update() {
            this.$store.dispatch('collection/updateCollection', {
                id: this.id,
                name: this.name,
                parentId: this.parentCollection?.id,
                iconId: this.iconId,
            })
            this.handleShare()
            this.$router.push({ path: '/c/' + this.id })
        },
        deleteCollection() {
            this.$store.dispatch('collection/deleteCollection', { id: this.id, nested: true })
            this.$store.dispatch('notification/setNotification', {
                type: 'deletion',
                title: 'Collection deleted',
                description: 'Collection was successfully deleted.',
            })
            this.$router.push({ path: '/' })
        },
        copy(event) {
            navigator.clipboard.writeText(this.domain + this.token).catch((error) => {
                console.log(error)
            })
        },
        generate() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
            const charsLength = chars.length
            let value = ''
            for (let i = 0; i < 32; i++) {
                value += chars.charAt(Math.floor(Math.random() * charsLength))
            }
            this.token = value
        },
        getShares() {
            axios
                .get('/api/shares/', {
                    params: {
                        collection_id: this.id,
                    },
                })
                .then((response) => {
                    if (response.data.data.length === 0) {
                        return
                    }
                    const share = response.data.data[0]
                    this.share = share
                    this.token = share.token
                    this.is_active = share.is_active
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
        },
        handleShare() {
            if (this.share === null && this.is_active === false) {
                return
            }

            if (this.is_active && this.token !== '') {
                if (this.share) {
                    axios
                        .patch('/api/shares/' + this.share.id, {
                            token: this.token,
                            collection_id: this.id,
                            is_active: this.is_active,
                        })
                        .catch((error) => {
                            console.log(error.response.data)
                        })
                } else {
                    axios
                        .post('/api/shares', {
                            token: this.token,
                            collection_id: this.id,
                            is_active: this.is_active,
                        })
                        .catch((error) => {
                            console.log(error.response.data)
                        })
                }
            } else if (!this.is_active && this.share) {
                axios.delete('/api/shares/' + this.share.id).catch((error) => {
                    console.log(error.response.data)
                })
            }
        },
        openPicker() {
            this.showPicker = true
            document.querySelector('#app').addEventListener('click', this.hidePicker, true)
        },
        hidePicker() {
            if (document.querySelector('#iconPicker').contains(event.target)) {
                return
            }
            this.showPicker = false
            document.querySelector('#app').removeEventListener('click', this.hidePicker, true)
        },
        iconSelect(event) {
            this.iconId = Number(event.id)
            this.showPicker = false
            document.querySelector('#app').removeEventListener('click', this.hidePicker, true)
        },
        collectionIconIsInline,
    },
    computed: {
        switchValue() {
            if (this.is_active) {
                return 'active'
            } else {
                return 'inactive'
            }
        },
        domain() {
            return origin + '/s?token='
        },
        ...mapState('collection', ['collections']),
    },
    created() {
        if (!this.isNew) {
            if (parseInt(this.id) === 0) {
                this.$router.push({ path: '/' })
                return
            }
            axios
                .get('/api/collections/' + this.id)
                .then((response) => {
                    const collection = response.data.data
                    this.name = collection.name
                    this.iconId = collection.icon_id
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
            this.getShares()

            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit Collection',
                button: {
                    label: 'Save',
                    callback: this.update,
                    icon: 'checkmark',
                },
            })
        } else {
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create Collection',
                button: {
                    label: 'Save',
                    callback: this.create,
                    icon: 'checkmark',
                },
            })
        }
        Select.props.components.default = () => ({ OpenIndicator, Deselect })
        this.$store.dispatch('collection/fetchCollections', {}).then(() => {
            this.optionsCollections = this.optionsCollections.concat(this.collections)
        })
        navigator.permissions
            .query({ name: 'clipboard-write' })
            .then((result) => {
                this.isSupported = result.state === 'granted' || result.state === 'prompt'
            })
            .catch(() => {
                this.isSupported = false
            })
    },
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
        @apply text-white border-gray-700 bg-gray-600 w-auto;
    }
    .label.inline-block {
        @apply inline-block;
    }
    .vs__dropdown-toggle {
        @apply border-gray-400 border-2;
    }
    .vs__dropdown-menu {
        @apply p-0 order-2 border-gray-400 shadow-none;
        .vs__dropdown-option--highlight {
            @apply bg-orange-500;
        }
    }
}
.button.red:hover {
    border-color: #fff;
}
</style>
