<template>
    <EditorMenuBar :editor="editor" v-slot="{ commands, isActive }">
        <div class="menubar inline-block bg-gray-200 px-4 py-1">

            <button class="menubar-button styles" @click="showStyles = !showStyles">
                {{ style }}
                <svg-vue class="editor-icon inline-block ml-2" icon="material/arrow_drop_down"/>
                <transition name="fade">
                    <ol v-if="showStyles" class="absolute bg-white shadow-lg z-50 text-left
                        w-full mt-1 left-0">
                        <li class="style font-normal" @click="styleClick('Normal', 1)">
                            Normal
                        </li>
                        <li class="style font-black" @click="styleClick('Headline 1', 1)"
                            :class="{ 'is-active': isActive.heading({ level: 1 }) }">
                            Headline 1
                        </li>
                        <li class="style font-bold" @click="styleClick('Headline 2', 2)"
                            :class="{ 'is-active': isActive.heading({ level: 2 }) }">
                            Headline 2
                        </li>
                        <li class="style font-medium" @click="styleClick('Headline 3', 3)"
                            :class="{ 'is-active': isActive.heading({ level: 3 }) }">
                            Headline 3
                        </li>
                    </ol>
                </transition>
            </button>

            <i class="delimiter"></i>

            <button class="menubar-button" :class="{ 'is-active': isActive.bold() }"
                @click="commands.bold">
                <svg-vue class="editor-icon" icon="material/format_bold"/>
            </button>

            <button class="menubar-button" :class="{ 'is-active': isActive.italic() }"
                @click="commands.italic">
                <svg-vue class="editor-icon" icon="material/format_italic"/>
            </button>

            <button
                class="menubar-button" :class="{ 'is-active': isActive.underline() }"
                @click="commands.underline">
                <svg-vue class="editor-icon" icon="material/format_underlined"/>
            </button>

            <i class="delimiter"></i>

            <button class="menubar-button" :class="{ 'is-active': isActive.blockquote() }"
                @click="commands.blockquote">
                <svg-vue class="editor-icon" icon="material/format_quote"/>
            </button>

            <button class="menubar-button" :class="{ 'is-active': isActive.code() }"
                @click="commands.code">
                <svg-vue class="editor-icon" icon="material/code"/>
            </button>

            <button class="menubar-button" :class="{ 'is-active': isActive.paragraph() }"
                @click="commands.paragraph">
                <svg-vue class="editor-icon" icon="zondicons/text-decoration"/>
            </button>

            <i class="delimiter"></i>

            <button class="menubar-button" @click="commands.undo">
                <svg-vue class="editor-icon" icon="material/undo"/>
            </button>

            <button class="menubar-button" @click="commands.redo">
                <svg-vue class="editor-icon" icon="material/redo"/>
            </button>

        </div>
    </EditorMenuBar>
</template>

<script>
import { EditorMenuBar } from 'tiptap'
export default {
    props: ['editor'],
    components: {
        EditorMenuBar
    },
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
                this.editor.commands.heading({ level: level })
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
        @apply text-gray-900;
    }
    .menubar-button.styles {
        @apply relative px-3;
    }
</style>
