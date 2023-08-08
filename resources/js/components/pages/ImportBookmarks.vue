<template>
    <div class="min-h-full import">
        <div class="sm:ml-8 px-2 md:px-8 pb-12">
            <div class="py-4 md:pt-16 mb-6">

                <h1 class="text-3xl mb-8 font-medium text-gray-800">Import Bookmarks</h1>

                <div class="max-w-lg mb-8">
                    <p>Import bookmarks from your favorite browser as .html file
                        (by exporting them from your browser).</p>
                    <br>
                    <p>You should find an option in your browser under:
                        <i>Bookmarks > Export bookmarks.</i>
                    </p>
                </div>

                <div class="mb-8">
                    <label class="label">Bookmark File</label>
                    <button class="button relative -mr-1 cursor-pointer">
                        <input class="absolute left-0 right-0 top-0 opacity-0" type="file" />
                        <svg-vue class="button-icon" icon="remix/upload-2-fill" />
                        Upload
                    </button>
                </div>
                <div v-if="inProgress" class="mb-8">
                    <p class="inline-block mr-2 italic text-gray-800">
                        This may take several minutes.
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
        import() {
            this.inProgress = true
            const file = document.querySelector('input[type=file]').files[0]

            if (typeof file === 'undefined') {
                this.inProgress = false
                return
            }
            const formData = new FormData();
            formData.append('file', file);

            axios
                .post('/api/imports', formData)
                .then(() => {
                    this.inProgress = false
                    this.$router.push({ path: '/import' })
                    this.$store.dispatch('notification/setNotification', {
                        type: 'success',
                        title: 'Bookmarks imported',
                        description: 'Bookmarks were successfully imported.',
                    })
                })
                .catch((error) => {
                    this.inProgress = false
                    console.log(error)
                    this.error = 'Failed. Error ' + error.response.status
                })
        }
    },
    created() {
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Import Bookmarks',
            button: {
                label: 'Import',
                callback: this.import,
                icon: 'checkmark',
            },
        })
    }
}
</script>
