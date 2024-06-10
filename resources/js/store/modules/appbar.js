export default {
    namespaced: true,
    state: {
        title: '',
        hint: '',
        button: {
            label: '',
            icon: '',
            callback: null,
        },
        options: [],
    },
    mutations: {
        setAppbar(state, appbar) {
            state.title = appbar.title
            state.hint = appbar.hint
            if (typeof appbar.button !== 'undefined' && appbar.button) {
                state.button = {
                    label: appbar.button.label,
                    callback: appbar.button.callback,
                    icon: appbar.button.icon,
                }
            } else {
                state.button = {
                    label: '',
                    icon: '',
                    callback: null,
                }
            }
            state.options = appbar.options
        },
        setTitle(state, title) {
            state.title = title
            if (title) {
                document.title = 'Benotes - ' + title
            }
        },
        setOptions(state, options) {
            state.options = options
        },
    },
    actions: {
        setAppbar(context, appbar) {
            context.commit('setAppbar', appbar)
            if (appbar && appbar.title) {
                document.title = 'Benotes - ' + appbar.title
            } else if (appbar.title) {
                document.title = 'Benotes'
            }
        },
        setOptions(context, options) {
            context.commit('setOptions', options)
        },
    },
}
