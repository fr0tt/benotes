<template>
    <NodeViewWrapper class="unfurling-link max-w-md block whitespace-normal" >
        <a class="shadow-md flex my-1"
            target="_blank" rel="noopener noreferrer nofollow"
            :href="node.attrs.href"
            :data-title="node.attrs['data-title']">
            <img class="w-16 h-16" :src="image">
            <div class="flex-1 overflow-hidden px-2 bg-white text-gray-900">
                <span class="block truncate mt-1 text-lg font-semibold">
                    {{ node.attrs['data-title'] }}
                </span>
                <span class="ul-link block mt-1 truncate text-sm text-orange-600 font-medium">
                    {{ node.attrs.href }}
                </span>
            </div>
        </a>
    </NodeViewWrapper>
</template>

<script>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-2'
import axios from 'axios'

export default {
    props: {
        ...nodeViewProps,
        updateAttributes: {
            type: Function,
            required: true,
        }
    },
    components: {
        NodeViewWrapper
    },
    data () {
        return {
            title: '',
            base_url: '',
            image: ''
        }
    },
    created () {

        if (this.node.attrs['data-title'] === null || 
            this.node.attrs['data-title'] === '') {
            axios.get('/api/meta', {
                params: {
                    url: encodeURI(this.node.attrs.href)
                }
            }).then(response => {
                const data = response.data
                this.base_url = data.base_url
                this.title = data.title
                const domain = this.base_url.replace(/(https|http):\/\//, '')
                this.image = `https://external-content.duckduckgo.com/ip3/${domain}.ico`
                this.updateAttributes({
                    'data-title': data.title,
                })
            }).catch(error => {
                console.log(error)
            })
        
        } else {
            const domain = new URL(this.node.attrs.href).origin.replace(/https?:\/\//, '')
            this.image = `https://external-content.duckduckgo.com/ip3/${domain}.ico`
        }
        
    }
}
</script>
<style>
    .has-focus {
        box-shadow: 0 1px 3px 0 rgb(255 119 0 / 42%), 0 1px 2px 0 rgb(255 119 0 / 38%);
    }
</style>