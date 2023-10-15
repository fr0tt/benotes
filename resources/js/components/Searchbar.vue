<template>
    <form
        class="flex relative searchbar w-full max-w-2xl shadow-sm bg-gray-200 rounded text-gray-600 border-2 focus-in:border-orange-500 focus-in:bg-white"
        @submit.prevent="search">
        <svg-vue
            icon="remix/search-line"
            class="flex-none w-5 mx-3 fill-current text-gray-600" />
        <div v-show="searchCollection" class="flex-none my-auto">
            <div
                class="px-2 py-0.5 rounded-xl font-medium text-white border-2 border-orange-500 bg-orange-500">
                {{ searchCollectionName }}
            </div>
        </div>
        <input
            id="search"
            placeholder="Type to search"
            class="flex-auto w-full px-2 py-1.5 bg-transparent text-gray-700 font-medium rounded outline-none duration-200 ease-in-out transition-colors"
            autocomplete="off"
            type="text"
            @keydown.down="onArrowDown"
            @keydown.up="onArrowUp"
            @keyup.delete="onBackspace"
            @click="searchInput"
            @input="searchInput" />
        <ul
            v-if="showOptions && options.length > 0"
            class="absolute w-full mt-10 -ml-0.5 py-1 bg-gray-200 z-40 rounded text-gray-800 shadow-sm"
            role="listbox">
            <li
                v-for="(item, i) in options"
                :key="item.id"
                class="pl-14 pr-4 py-1 font-medium searchOption cursor-pointer"
                :class="{ isActive: i === arrowCount }"
                role="option"
                @click="selectOption(item)">
                {{ item.name }}
            </li>
        </ul>
    </form>
</template>

<script>
export default {
    name: 'Searchbar',
    data() {
        return {
            showOptions: false,
            options: [],
            arrowCount: -1,
            lastSearchLength: -1,
            searchValue: '',
            searchCollection: null,
        }
    },
    computed: {
        collections() {
            return this.$store.state.collection.collectionNames
        },
        searchCollectionName() {
            // yes this is necessary in order to use v-show instead of v-if
            // (which seems to mess with focus)
            return this.searchCollection ? this.searchCollection.name : ''
        },
    },
    methods: {
        onBackspace() {
            if (this.lastSearchLength === 0 && this.searchValue.length === 0) {
                this.searchCollection = null
                this.showOptions = true
                this.arrowCount = -1
                document.querySelector('#search').focus()
            }
            this.lastSearchLength = this.searchValue.length
        },
        searchInput() {
            this.searchValue = document.querySelector('#search').value
            const input = this.searchValue

            if (this.searchCollection) {
                this.showOptions = false
                return
            }

            if (input.length === 0) {
                const entries = this.collections.entries()
                for (let i = 0; i < 4 && i < this.collections.size; i++) {
                    const collection = entries.next().value
                    this.options.push({
                        id: collection[0],
                        name: collection[1],
                    })
                }
            } else {
                const inputValue = input.toLowerCase()
                // this.options = this.collections.filter((col) => {
                //     return col.name.toLowerCase().includes(inputValue)
                // })
                this.options = []
                this.collections.forEach((name, id) => {
                    if (name.toLowerCase().includes(inputValue))
                        this.options.push({ id, name })
                })
            }
            if (!this.showOptions) {
                this.showOptions = true
                document.querySelector('#app').addEventListener('click', this.hideResults)
            }
        },
        hideResults(event) {
            if (document.querySelector('#search') == event.target) {
                return
            }
            if (!this.showOptions) {
                return
            }
            this.showOptions = false
            this.options = []
            document.querySelector('#app').removeEventListener('click', this.hideResults)
        },
        selectOption(option) {
            this.searchCollection = option
            this.searchValue = ''
            this.arrowCount = -1
            this.showOptions = false
            document.querySelector('#search').value = ''
            document.querySelector('#search').focus()
        },
        search() {
            if (this.arrowCount >= 0) {
                this.selectOption(this.options[this.arrowCount])
                return
            }
            this.$store.dispatch('post/fetchPosts', {
                collectionId: this.searchCollection ? this.searchCollection.id : null,
                filter: this.searchValue,
                limit: 0,
            })
        },
        onArrowDown(event) {
            if (this.arrowCount < this.options.length - 1) {
                this.arrowCount = this.arrowCount + 1
            }
            event.preventDefault()
        },
        onArrowUp(event) {
            if (this.arrowCount > -1) {
                this.arrowCount = this.arrowCount - 1
            }
            event.preventDefault()
        },
        toSnakeCase(value) {
            return value.toLowerCase().replace(/\s/g, '_')
        },
    },
}
</script>

<style lang="scss">
.searchbar .isActive,
.searchbar .searchOption:hover {
    @apply text-orange-600 bg-orange-200; //@apply bg-gray-400;
}

.focus-in\:border-orange-500:focus-within {
    @apply border-orange-500;
}

.focus-in\:bg-white:focus-within {
    @apply bg-white;
}

.pl-14 {
    padding-left: 3.5rem;
}
</style>
