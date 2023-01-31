<template>
    <div v-if="editor" class="menubar inline-block bg-gray-200 px-4 py-1">
        <button class="menubar-button styles" @click="showStyles = !showStyles">
            {{ style }}
            <svg-vue class="editor-icon inline-block ml-2" icon="material/arrow_drop_down" />
            <transition name="fade">
                <ol
                    v-if="showStyles"
                    class="absolute bg-white shadow-lg z-50 text-left w-full mt-1 left-0">
                    <li class="style font-normal" @click="styleClick('Normal', 0)">Normal</li>
                    <li
                        class="style font-black"
                        :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }"
                        @click="styleClick('Headline 1', 1)">
                        Headline 1
                    </li>
                    <li
                        class="style font-bold"
                        :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"
                        @click="styleClick('Headline 2', 2)">
                        Headline 2
                    </li>
                    <li
                        class="style font-medium"
                        :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }"
                        @click="styleClick('Headline 3', 3)">
                        Headline 3
                    </li>
                </ol>
            </transition>
        </button>

        <i class="delimiter" />

        <button
            class="menubar-button"
            :class="{ 'is-active': editor.isActive('bold') }"
            title="Bold"
            @click="editor.chain().focus().toggleBold().run()">
            <svg-vue class="editor-icon" icon="material/format_bold" />
        </button>

        <button
            class="menubar-button"
            :class="{ 'is-active': editor.isActive('italic') }"
            title="Italic"
            @click="editor.chain().focus().toggleItalic().run()">
            <svg-vue class="editor-icon" icon="material/format_italic" />
        </button>

        <button
            class="menubar-button"
            :class="{ 'is-active': editor.isActive('underline') }"
            title="Underline"
            @click="editor.chain().focus().toggleUnderline().run()">
            <svg-vue class="editor-icon" icon="material/format_underlined" />
        </button>

        <i class="delimiter" />

        <button
            class="menubar-button"
            :class="{ 'is-active': editor.isActive('bulletList') }"
            title="Bullet List"
            @click="editor.chain().focus().toggleBulletList().run()">
            <svg-vue class="editor-icon" icon="material/list_bulleted" />
        </button>

        <button
            class="menubar-button"
            :class="{ 'is-active': editor.isActive('orderedList') }"
            title="Ordered List"
            @click="editor.chain().focus().toggleOrderedList().run()">
            <svg-vue class="editor-icon" icon="material/list_numbered" />
        </button>

        <button
            class="menubar-button"
            title="Task List"
            @click="editor.chain().focus().toggleTaskList().run()">
            <svg-vue class="editor-icon" icon="material/check_box" />
        </button>

        <i class="delimiter" />

        <button
            class="menubar-button"
            :class="{ 'is-active': editor.isActive('blockquote') }"
            title="Blockquote"
            @click="editor.chain().focus().toggleBlockquote().run()">
            <svg-vue class="editor-icon" icon="material/format_quote" />
        </button>

        <button
            class="menubar-button"
            :class="{ 'is-active': editor.isActive('codeBlock') }"
            title="Code Block"
            @click="editor.chain().focus().toggleCodeBlock().run()">
            <svg-vue class="editor-icon" icon="material/code" />
        </button>

        <button
            class="menubar-button"
            title="Horizontal Rule"
            @click="editor.chain().focus().setHorizontalRule().run()">
            <svg-vue class="editor-icon" icon="material/horizontal_rule" />
        </button>

        <i class="delimiter" />

        <button
            class="menubar-button"
            title="Unfurling Link"
            @click="editor.chain().focus().setUnfurlingLink().run()">
            <svg-vue class="editor-icon" icon="material/art_track" />
        </button>

        <i class="delimiter" />

        <button class="menubar-button" title="Undo" @click="editor.chain().focus().undo().run()">
            <svg-vue class="editor-icon" icon="material/undo" />
        </button>

        <button class="menubar-button" title="Redo" @click="editor.chain().focus().redo().run()">
            <svg-vue class="editor-icon" icon="material/redo" />
        </button>
    </div>
</template>

<script>
export default {
    props: ['editor'],
    data() {
        return {
            style: 'Normal',
            showStyles: false,
        }
    },
    methods: {
        styleClick(style, level) {
            this.style = style
            if (level > 0) {
                this.editor.chain().focus().toggleHeading({ level: level }).run()
            } else {
                this.editor.chain().focus().setParagraph().run()
            }
        },
    },
}
</script>
<style lang="scss">
.py-1\.5 {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
}
.menubar {
    position: sticky;
    z-index: 100;   
    top: 63px;
}
@media (max-width: 768px) {
    .menubar {
        overflow-x: scroll;
        white-space: nowrap;
        @apply py-1.5;
        top: 58px;
    }
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
.menubar-button.is-active,
.menubar-button .is-active {
    @apply text-gray-800 bg-white;
}
.menubar-button.styles {
    @apply relative px-3;
}
</style>
