import {
    Node,
    nodeInputRule,
    mergeAttributes,
} from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-2'

import { Plugin, PluginKey } from 'prosemirror-state'
import { DecorationSet, Decoration } from 'prosemirror-view'

import Component from './components/UnfurlingLink.vue'

const inputRegex = /^<https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z]{2,}\b(?:[-a-zA-Z0-9@:%._+~#=?!&/]*)(?:[-a-zA-Z0-9@:%._+~#=?!&/]*)>$/ // /^<.*>$/

export default Node.create({

    name: 'unfurlingLink',
    group: 'block',
    atom: true, // "aren’t directly editable and should be treated as a single unit"
    allowGapCursor: false,
    draggable: true,
    //defining: true, // not sure if its important
    selectable: true,
    //isolating: true, // not sure if its important

    addAttributes() {
        return {
            href: {
                default: null
            },
            'data-title': {
                default: null
            }
        }
    },

    parseHTML() {
        return [
            { tag: 'unfurling-link[data-title]' },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['unfurling-link', mergeAttributes(HTMLAttributes, this.options.HTMLAttributes)/*, 0*/]
    },

    addNodeView() {
        return VueNodeViewRenderer(Component)
    },

    addCommands() {
        return {
            setUnfurlingLink: options => ({ tr, dispatch }) => {
                const { selection } = tr
                const node = this.type.create(options)

                if (dispatch) {
                    tr.replaceRangeWith(selection.from, selection.to, node)
                }

                return true
            },
        }
    },

    addInputRules() {
        return [
            nodeInputRule(inputRegex, this.type, match => {
                const link = match[0].replace(/<|>/g, '')
                return {
                    href: link
                }
            })
        ]
    },

    addProseMirrorPlugins() {
        const mode = 'shallowest' // 'all' | 'deepest' | 'shallowest'
        return [
            new Plugin({
                key: new PluginKey('focus-ufl'),
                props: {
                    decorations: ({ doc, selection }) => {
                        const { isEditable, isFocused } = this.editor
                        const { anchor } = selection
                        const decorations = []

                        if (!isEditable || !isFocused) {
                            return DecorationSet.create(doc, [])
                        }

                        // Maximum Levels
                        let maxLevels = 0

                        // Loop through current
                        let currentLevel = 0

                        doc.descendants((node, pos) => {
                            if (node.isText) {
                                return false
                            }

                            if (node.type.name !== this.name) {
                                return false
                            }

                            const isCurrent = anchor >= pos && anchor <= (pos + node.nodeSize - 1)

                            if (!isCurrent) {
                                return false
                            }

                            currentLevel += 1

                            const outOfScope = (mode === 'deepest' && maxLevels - currentLevel > 0)
                                || (mode === 'shallowest' && currentLevel > 1)

                            if (outOfScope) {
                                return mode === 'deepest'
                            }

                            decorations.push(Decoration.node(pos, pos + node.nodeSize, {
                                class: 'has-focus',
                            }))
                        })

                        return DecorationSet.create(doc, decorations)
                    },
                },
            }),
        ]
    },

})