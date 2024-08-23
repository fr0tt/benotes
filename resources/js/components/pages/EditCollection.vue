<template>
    <div class="py-12 px-12 md:px-40 md:pt-32 max-w-5xl">
        <div class="w-full collection">
            <h1 class="text-3xl font-bold mb-4">
                {{ headline }}
            </h1>
            <p class="text-lg mb-16">{{ description }}</p>
            <div class="mb-10">
                <label class="label">Name of Collection</label>
                <input v-model="name" placeholder="Name" autofocus class="input" />
            </div>

            <div v-if="isAllowed" class="mb-10">
                <label class="label">Subcollection of</label>
                <Treeselect
                    v-model="parentCollection"
                    :options="optionsCollections"
                    :close-on-select="true"
                    :clear-on-select="true"
                    :normalizer="
                        (node) => {
                            return {
                                id: node.id,
                                label: node.name,
                                children:
                                    typeof node.nested !== 'undefined' &&
                                    node.nested.length > 0
                                        ? node.nested
                                        : node.children,
                            }
                        }
                    "
                    placeholder="None"
                    class="inline-block w-80" />
            </div>

            <div class="mb-10 relative">
                <label class="label">Collection Icon</label>
                <p class="mt-2 mb-4">Select an optional icon for your collection.</p>
                <button
                    id="iconPickerButton"
                    class="border-2 border-gray-400 rounded py-2 px-2"
                    @click="openPicker()">
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
                    <IconPicker
                        v-if="showPicker"
                        class="mt-2"
                        @iconSelected="iconSelect" />
                </transition>
            </div>
            <div v-if="!isNew && isOwner" class="mb-10 relative private-share">
                <label class="label">Share with Users</label>
                <p class="mt-2 mb-4">Share your collection with other members.</p>
                <div>
                    <label class="label label-sm mt-6 mb-6">Active Members</label>
                    <ul class="mt-2 py-1 px-2">
                        <li class="my-2 show-button">
                            <svg-vue
                                icon="remix/user-star-fill"
                                class="icon w-4 h-4 fill-current text-orange-600" />
                            <span class="ml-1">{{ owner.name }} (me)</span>
                        </li>
                        <li
                            v-for="member in inheritedMembers"
                            :key="member.id"
                            class="my-2 show-button">
                            <svg-vue
                                icon="remix/user-fill"
                                class="icon w-4 h-4 fill-current text-orange-600" />
                            <span class="ml-1">{{ member.name }}</span>
                            <button
                                title="Parent collection is shared with this user"
                                class="cursor-not-allowed">
                                <svg-vue
                                    icon="remix/user-unfollow-line"
                                    class="icon w-4 h-4 fill-current text-gray-600" />
                            </button>
                        </li>
                    </ul>
                    <hr />
                    <ul class="mb-2 py-1 px-2">
                        <li
                            v-for="member in members"
                            :key="member.id"
                            class="my-2 show-button">
                            <svg-vue
                                icon="remix/user-fill"
                                class="icon w-4 h-4 fill-current text-orange-600" />
                            <span class="ml-1">{{ member.name }}</span>
                            <button title="Remove user" @click="removeUser(member)">
                                <svg-vue
                                    icon="remix/user-unfollow-line"
                                    class="icon w-4 h-4 fill-current text-red-600" />
                            </button>
                        </li>
                    </ul>
                    <label class="label label-sm mt-6 mb-4">Invite Members</label>
                </div>
                <input
                    placeholder="Search by Username"
                    class="input relative z-10"
                    @input="searchForUsers" />
                <ul v-if="searchResults.length > 0" class="filter-results">
                    <li v-for="user in searchResults" :key="user.id" class="py-1 px-2">
                        {{ user.name }}
                        <button
                            v-if="getMember(user)"
                            class="ml-1"
                            title="Remove user"
                            @click="removeUser(user)">
                            <svg-vue
                                icon="remix/user-unfollow-line"
                                class="icon w-4 h-4 fill-current text-red-600 cursor-pointer" />
                        </button>
                        <button
                            v-else
                            class="ml-1"
                            title="Add user"
                            @click="addUser(user)">
                            <svg-vue
                                icon="remix/user-add-line"
                                class="icon w-4 h-4 fill-current text-orange-600 cursor-pointer" />
                        </button>
                    </li>
                </ul>
            </div>

            <div v-if="!isNew && isAllowed" class="mt-16 mb-16">
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
                        <div
                            class="px-2 bg-gray-300 rounded cursor-pointer"
                            @click="generate()">
                            <svg-vue class="w-6 mt-2.5" icon="material/autorenew" />
                        </div>
                    </div>
                </div>

                <p class="mt-4">
                    Make this collection publicly available by visiting the specified URL.
                </p>
            </div>

            <div v-if="!isNew && isAllowed" class="mb-14 py-6 px-6 bg-red-400 rounded">
                <h3 class="text-xl font-semibold mb-1">Delete collection</h3>
                <p class="mb-4">Delete this collection and all its content.</p>
                <button
                    title="Delete Collection"
                    class="button red mb-2 bg-white"
                    @click="deleteCollection">
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
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'

export default {
    components: {
        IconPicker,
        Treeselect,
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
            description: '',
            isSupported: null,
            iconId: null,
            showPicker: false,
            optionsCollections: [],
            parentCollection: null,
            userId: null,
            isBeingShared: false,
            users: [],
            owner: null,
            members: [],
            inheritedMembers: [],
            searchResults: [],
        }
    },
    methods: {
        create() {
            axios
                .post('/api/collections', {
                    name: this.name,
                    parent_id: this.parentCollection,
                    icon_id: this.iconId,
                })
                .then((response) => {
                    //this.$store.dispatch('collection/addCollection', response.data.data)
                    this.$store.dispatch('collection/fetchCollections', {
                        nested: true,
                        force: true,
                    })
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
                parentId: this.parentCollection,
                iconId: this.iconId,
            })
            this.handleShare()
            this.$router.push({ path: '/c/' + this.id })
        },
        deleteCollection() {
            this.$store.dispatch('collection/deleteCollection', {
                id: this.id,
                nested: true,
            })
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
                .get('/api/shares/public', {
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
                        .patch('/api/shares/public/' + this.share.id, {
                            token: this.token,
                            collection_id: this.id,
                            is_active: this.is_active,
                        })
                        .catch((error) => {
                            console.log(error.response.data)
                        })
                } else {
                    axios
                        .post('/api/shares/public', {
                            token: this.token,
                            collection_id: this.id,
                            is_active: this.is_active,
                        })
                        .catch((error) => {
                            console.log(error.response.data)
                        })
                }
            } else if (!this.is_active && this.share) {
                axios.delete('/api/shares/public/' + this.share.id).catch((error) => {
                    console.log(error.response.data)
                })
            }
        },
        openPicker() {
            if (this.showPicker) {
                this.showPicker = false
                return
            }
            this.showPicker = true
            document
                .querySelector('#app')
                .addEventListener('click', this.hidePicker, true)
        },
        hidePicker(event) {
            if (document.querySelector('#iconPicker').contains(event.target)) {
                return
            }
            if (!document.querySelector('#iconPickerButton').contains(event.srcElement)) {
                this.showPicker = false
            }
            document
                .querySelector('#app')
                .removeEventListener('click', this.hidePicker, true)
        },
        iconSelect(event) {
            this.iconId = Number(event.id)
            this.showPicker = false
            document
                .querySelector('#app')
                .removeEventListener('click', this.hidePicker, true)
        },
        searchForUsers(event) {
            if (!event.target.value) {
                this.searchResults = []
                return
            }
            const needle = event.target.value.toLowerCase()
            this.searchResults = this.users.filter(
                (user) =>
                    user.name.toLowerCase().includes(needle) && user.id !== this.owner.id
            )
        },
        addUser(user) {
            axios
                .post('/api/shares/private', {
                    collection_id: this.id,
                    user_id: user.id,
                })
                .then((response) => {
                    this.members.push(response.data.data.user)
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
        },
        removeUser(user) {
            axios
                .delete('/api/shares/private/' + this.getMember(user).id)
                .then(() => {
                    this.members.splice(
                        // index could have changed in the meantime
                        this.members.findIndex((m) => m.user.id === user.id),
                        1
                    )
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
        },
        getMember(user) {
            return this.members.find((m) => m.id === user.id)
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
        isAllowed() {
            if (this.isNew) return true
            if (this.isBeingShared) return true
            if (this.isOwner) return true
            return false
        },
        isOwner() {
            return this.userId === this.authUser.id
        },
        ...mapState('collection', ['collections', 'sharedCollections']),
        ...mapState('auth', ['authUser']),
    },
    created() {
        this.description = this.isNew
            ? 'Specify a name, its parent and and icon for your new collection.'
            : this.isAllowed
            ? "Update your collection's title, icon and share it with other members or friends."
            : "Update this collection's title and icon and parent."
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
                    this.parentCollection = collection.parent_id
                    this.isBeingShared = collection.is_being_shared
                    this.userId = collection.user_id
                    if (this.isOwner) this.owner = this.authUser
                    else {
                        axios.get('/api/users/' + this.userId).then((response) => {
                            this.owner = response.data.data
                        })
                    }
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
            this.owner = this.authUser
        }
        this.$store.dispatch('collection/fetchCollections', {}).then(() => {
            this.optionsCollections = [...this.sharedCollections, ...this.collections]
        })
        axios.get('/api/users').then((response) => (this.users = response.data.data))
        axios
            .get('/api/shares/private', {
                params: {
                    collection_id: this.id,
                },
            })
            .then((response) => {
                response.data.data.forEach((share) => {
                    if (share.root_collection_id === Number(this.id))
                        this.members.push(share.user)
                    else this.inheritedMembers.push(share.user)
                })
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
        display: inline-block;
    }
}

.button.red:hover {
    border-color: #fff;
}

label.label-sm {
    @apply text-xs text-orange-600;
}

.private-share {
    .filter-results {
        @apply absolute w-full px-2 py-2 border-2 border-gray-400 bg-gray-100;
        margin-top: -2px;
        border-bottom-left-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }

    svg {
        margin-top: -0.15rem;
    }

    .show-button button {
        @apply opacity-0 transition-opacity;
    }

    .show-button:hover button {
        @apply opacity-100;
    }
}
</style>
