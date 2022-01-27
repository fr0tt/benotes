<template>
    <div class="inline-block relative searchbar w-full max-w-2xl shadow-sm
        bg-gray-200 rounded text-gray-600 focus:text-orange-500">
        <svg-vue class="absolute w-5 mt-2.5 ml-3 fill-current text-gray-600 focus:text-orange-500" icon="remix/search-line"/>
        <input @keyup="onChange" 
            @keydown.down="onArrowDown"
            @keydown.up="onArrowUp"
            @click="onChange"
            v-model="searchValue"
            id="search"
            placeholder="Type / to search in a specific collection"
            class="w-full pl-10 pr-2 py-1.5 bg-transparent text-gray-700 font-medium
                rounded outline-none border-2 focus:border-orange-500 focus:bg-white
                duration-200 ease-in-out transition-colors" 
            type="text">
        <ul v-if="showOptions && options.length > 0" class="absolute w-full mt-1 py-1
            bg-gray-200 z-40 rounded text-gray-800 shadow-sm" role="listbox">
            <li v-for="(item, i) in options" :key="item.id" 
                @click="selectOptionWithClick(item)"
                class="pl-10 pr-4 py-1 font-medium searchOption cursor-pointer" 
                :class="{ 'isActive': i === arrowCount }"
                role="option">
                {{ item.name }}
            </li>
        </ul>
    </div>
</template>

<script>

export default {
    name: 'Searchbar',
    data () {
        return {
            showOptions: false,
            options: [],
            arrowCount: -1,
            searchValue: '',
            searchCollection: null
        }
    },
    methods: {
        onChange (event) {
            if (event.key === 'Enter') {
                this.search()
                return
            }

            const input = this.searchValue
            if (input == null || input == '') {
                this.showOptions = false
                return
            }
            if (input.charAt(0) !== '/') {
                this.showOptions = false
                return
            }

            if (input.length === 1) {
                this.options = this.collections.slice(0, 4)
            } else {
                const inputValue = input.replace('/', '').toLowerCase()
                this.options = this.collections.filter(col => {
                    return col.name.toLowerCase().includes(inputValue)
                })
            }

            if (!this.showOptions) {
                this.showOptions = true
                document.querySelector('#app').addEventListener('click', this.hideResults)
            }
        },
        hideResults (event) {
            if (this.$el.contains(event.target)) {
                return
            }
            this.showOptions = false
            document.querySelector('#app').removeEventListener('click', this.hideResults)
        },
        selectOptionWithClick (option) {
            this.searchValue = '/' + this.toSnakeCase(option.name)
            this.searchCollection = option
            this.arrowCount = -1
            this.showOptions = false
            document.querySelector('#search').focus()
        },
        selectOptionWithKey () {
            this.searchValue = '/' + this.toSnakeCase(this.options[this.arrowCount].name)
            this.searchCollection = this.options[this.arrowCount]
            this.arrowCount = -1
            this.showOptions = false
        },
        search () {
            if (this.arrowCount >= 0) {
                this.selectOptionWithKey()
                return
            }
            let searchString = this.searchValue
            
            // first word is either collection or the actual first search word
            if (searchString.startsWith('/')) {
                const firstWordIndex = searchString.indexOf(' ') - 1
                if (firstWordIndex > 0) {
                    const firstWord = searchString.substr(1, firstWordIndex)
                    searchString = searchString.substr(firstWordIndex + 2)
                    if (this.searchCollection == null || 
                        firstWord !== this.searchCollection.name) {
                        // use the first matching collection 
                        // for now disregard if there are multiple collections with the same name
                        this.searchCollection = this.collections.find(col => {
                            return col.name == firstWord
                        })
                    }
                }
            }
            this.$store.dispatch('post/fetchPosts', { 
                collectionId: this.searchCollection ? this.searchCollection.id : null, 
                filter: searchString
            })
        },
        onArrowDown (event) {
            if (this.arrowCount < this.options.length - 1) {
                this.arrowCount = this.arrowCount + 1
            }
            event.preventDefault()
        },
        onArrowUp (event) {
            if (this.arrowCount > -1) {
                this.arrowCount = this.arrowCount - 1
            }
            event.preventDefault()
        },
        toSnakeCase(value) {
            return value.toLowerCase().replace(/\s/g, '_')
        }
    },
    computed: {
        collections () {
            return [{
                'id': null,
                'name': 'Uncategorized'
            }].concat(this.$store.state.collection.collections)
        },
        /*
        currentCollectionName () {
            return this.toSnakeCase(this.$store.state.collection.currentCollection.name)
        }
        */
    },/*
    watch: {
        currentCollectionName(value) {
            this.searchValue = '/' + value
        }
    }*/
}
</script>

<style lang="scss">
    .searchbar .isActive, .searchbar .searchOption:hover {
        @apply text-orange-600 bg-orange-200;//@apply bg-gray-400;
    }
</style>