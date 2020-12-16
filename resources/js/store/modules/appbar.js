export default {
    namespaced: true,
    state: {
        title: '',
        hint: '',
        button: {
            label: '',
            icon: '',
            callback: null
        },
        options: []
    },
    mutations: {
        setAppbar (state, appbar) {
            state.title = appbar.title
            state.hint = appbar.hint
            state.button = {
                label: appbar.button.label,
                callback: appbar.button.callback,
                icon: appbar.button.icon
            }
            state.options = appbar.options
        },
        setTitle (state, title) {
            state.title = title
        },
        setOptions (state, options) {
            state.options = options
        }
    },
    actions: {
        setAppbar (context, appbar) {
            context.commit('setAppbar', appbar)
        },
        setOptions(context, options) {
            context.commit('setOptions', options)
        }
    }
}
