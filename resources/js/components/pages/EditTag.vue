<template>
    <div class="py-12 px-12 md:px-40 md:pt-32 max-w-5xl">
        <div class="w-full">
            <div class="mb-10">
                <label class="label">Name of your Tag</label>
                <input v-model="name" placeholder="Name" autofocus class="input" />
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
export default {
    props: {
        id: {
            type: [Number, String],
            default: 0,
        },
        isNew: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            name: '',
        }
    },
    created() {
        if (!this.isNew) {
            if (parseInt(this.id) === 0) {
                this.$router.push({ path: '/' })
                return
            }
            axios
                .get('/api/tags/' + this.id)
                .then((response) => {
                    const tag = response.data.data
                    this.name = tag.name
                })
                .catch((error) => {
                    console.log(error.response.data)
                })

            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit Tag',
                button: {
                    label: 'Save',
                    callback: this.update,
                    icon: 'checkmark',
                },
            })
        } else {
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create Tag',
                button: {
                    label: 'Save',
                    callback: this.create,
                    icon: 'checkmark',
                },
            })
        }
    },
    methods: {
        create() {
            axios
                .post('/api/tags', {
                    name: this.name,
                })
                .then(() => {
                    this.$router.back()
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
        },
        update() {
            axios
                .patch('/api/tags/' + this.id, {
                    name: this.name,
                })
                .then(() => {
                    this.$router.back()
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
        },
    },
}
</script>
