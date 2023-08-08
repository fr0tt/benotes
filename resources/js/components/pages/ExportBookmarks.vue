<template>
    <div class="min-h-full import">
        <div class="sm:ml-8 px-2 md:px-8 pb-12">
            <div class="py-4 md:pt-16 mb-6">

                <h1 class="text-3xl mb-8 font-medium text-gray-800">Export Bookmarks</h1>

                <div class="max-w-lg mb-8">
                    <p>Export bookmarks from Benotes. You can use them in your favorite
                        web browser or somewhere else accepting NETSCAPE-Bookmark files.</p>
                </div>

                <div v-if="inProgress" class="mb-8">
                    <p class="inline-block mr-2 italic text-gray-800">
                        This may take a minute.
                    </p>
                    <svg-vue icon="remix/refresh-line" class="icon -mt-1 animate-spin text-gray-800" />
                </div>
                <div v-if="error" class="mb-8">
                    <p class="text-red-500">{{ error }}</p>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
export default {
    data: () => {
        return {
            inProgress: false,
            error: null
        }
    },
    methods: {
        export() {
            this.inProgress = true

            axios
                .get('/api/exports', { responseType: 'blob' })
                .then((response) => {
                    this.inProgress = false

                    const link = document.createElement('a')
                    link.href = URL.createObjectURL(response.data)
                    link.setAttribute('download', 'benotes-bookmarks.html')
                    link.click()
                    URL.revokeObjectURL(href)

                    this.$router.push({ path: '/import' })
                })
                .catch((error) => {
                    this.inProgress = false
                    this.error = 'Failed. Error ' + error.response.status
                    console.log(error)
                })
        }
    },
    created() {
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Export Bookmarks',
            button: {
                label: 'Export',
                callback: this.export,
                icon: 'checkmark',
            },
        })
    }
}
</script>
