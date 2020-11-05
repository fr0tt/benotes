export default {
    namespaced: true,
    state: {
        title: '',
        allowPaste: false,
        hint: '',
        button: {
            label: '',
            icon: '',
            callback: null
        }
    },
    mutations: {
        setAppbar(state, appbar) {
            state.title = appbar.title
            state.allowPaste = appbar.allowPaste
            state.hint = appbar.hint
            state.button = {
                label: appbar.button.label,
                callback: appbar.button.callback,
                icon: appbar.button.icon
            }
        },
        setTitle(state, title) {
            state.title = title
        }
    },
    actions: {
        setAppbar(context, appbar) {
            context.commit('setAppbar', appbar)
        }
    }
}
