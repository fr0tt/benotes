export default {
    namespaced: true,
    state: {
        type: '',
        title: '',
        description: '',
        isVisible: false,
    },
    mutations: {
        setNotification(state, notification) {
            state.type = notification.type
            state.title = notification.title
            state.description = notification.description
        },
        showNotification(state, isVisible) {
            state.isVisible = isVisible
        }
    },
    actions: {
        setNotification(context, notification) {
            context.commit('setNotification', notification)
            context.commit('showNotification', true)
            setTimeout(function () {
                context.commit('showNotification', false)
            }, 3 * 1000)
        },
    }
}
