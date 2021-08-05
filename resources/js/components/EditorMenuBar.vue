<template>
    <div v-if="editor" class="menubar inline-block bg-gray-200 px-4 py-1">

        <button class="menubar-button styles" @click="showStyles = !showStyles">
            {{ style }}
            <svg-vue class="editor-icon inline-block ml-2" icon="material/arrow_drop_down"/>
            <transition name="fade">
                <ol v-if="showStyles" class="absolute bg-white shadow-lg z-50 text-left
                    w-full mt-1 left-0">
                    <li class="style font-normal" @click="styleClick('Normal', 0)">
                        Normal
                    </li>
                    <li class="style font-black" @click="styleClick('Headline 1', 1)"
                        :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }">
                        Headline 1
                    </li>
                    <li class="style font-bold" @click="styleClick('Headline 2', 2)"
                        :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }">
                        Headline 2
                    </li>
                    <li class="style font-medium" @click="styleClick('Headline 3', 3)"
                        :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }">
                        Headline 3
                    </li>
                </ol>
            </transition>
        </button>

        <!--
            really neccessary ?
        <button class="menubar-button" :class="{ 'is-active': editor.isActive('paragraph') }"
            @click="editor.chain().focus().setParagraph().run()">
            <svg-vue class="editor-icon" icon="zondicons/text-decoration"/>
        </button>
        -->

        <i class="delimiter"></i>

        <button class="menubar-button" :class="{ 'is-active': editor.isActive('bold') }"
            @click="editor.chain().focus().toggleBold().run()">
            <svg-vue class="editor-icon" icon="material/format_bold"/>
        </button>

        <button class="menubar-button" :class="{ 'is-active': editor.isActive('italic') }"
            @click="editor.chain().focus().toggleItalic().run()">
            <svg-vue class="editor-icon" icon="material/format_italic"/>
        </button>

        <button
            class="menubar-button" :class="{ 'is-active': editor.isActive('underline') }"
            @click="editor.chain().focus().toggleUnderline().run()">
            <svg-vue class="editor-icon" icon="material/format_underlined"/>
        </button>

        <i class="delimiter"></i>

        <button class="menubar-button" :class="{ 'is-active': editor.isActive('bulletList') }"
            @click="editor.chain().focus().toggleBulletList().run()" >
            <svg-vue class="editor-icon" icon="material/list_bulleted"/>
        </button>

        <button class="menubar-button" :class="{ 'is-active': editor.isActive('orderedList') }"
            @click="editor.chain().focus().toggleOrderedList().run()">
            <svg-vue class="editor-icon" icon="material/list_numbered"/>
        </button>
        
        <i class="delimiter"></i>

        <button class="menubar-button" :class="{ 'is-active': editor.isActive('blockquote') }"
            @click="editor.chain().focus().toggleBlockquote().run()">
            <svg-vue class="editor-icon" icon="material/format_quote"/>
        </button>

        <button class="menubar-button" :class="{ 'is-active': editor.isActive('codeBlock') }"
            @click="editor.chain().focus().toggleCodeBlock().run()">
            <svg-vue class="editor-icon" icon="material/code"/>
        </button>
        
        <button class="menubar-button" @click="editor.chain().focus().setHorizontalRule().run()">
            <svg-vue class="editor-icon" icon="material/horizontal_rule"/>
        </button>

        <i class="delimiter"></i>

        <button class="menubar-button" @click="editor.chain().focus().undo().run()">
            <svg-vue class="editor-icon" icon="material/undo"/>
        </button>

        <button class="menubar-button" @click="editor.chain().focus().redo().run()">
            <svg-vue class="editor-icon" icon="material/redo"/>
        </button>

    </div>
</template>
<script>
export default {
    props: ['editor'],
    data () {
        return {
            style: 'Normal',
            showStyles: false
        }
    },
    methods: {
        styleClick (style, level) {
            this.style = style
            if (level > 0) {
                this.editor.chain().focus().toggleHeading({ level: level }).run()
            } else {
                this.editor.chain().focus().setParagraph().run()
            }
        }
    }
}
</script>
<style lang="scss">
    .py-1\.5 {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
    }
    .delimiter {
        @apply inline-block align-middle h-6 mx-2 border border-gray-400;
    }
    .menubar-button {
        @apply px-1 py-1 align-middle text-gray-600 font-semibold;
        .editor-icon {
            @apply w-6 fill-current;
        }
        .style {
            @apply px-3 py-1.5;
        }
        .style:hover {
            @apply bg-gray-200;
        }
    }
    .menubar-button:hover {
        @apply bg-gray-100;
    }
    .menubar-button.is-active, .menubar-button .is-active {
        @apply text-gray-800 bg-white;
    }
    .menubar-button.styles {
        @apply relative px-3;
    }
</style>