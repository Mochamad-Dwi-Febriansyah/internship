import {defineStore} from 'pinia'

interface Notification {
    id: number
    type: 'success' | 'error' | 'warning' | 'info'
    message: string
}

export const useNotification = defineStore('notification', {
    state: () => ({
        notifications: [] as Notification[]
    }),
    actions: {
        addNotification(type: Notification['type'], message: string, timeout = 3000) {
            const id = Date.now()
            this.notifications.push({id, type, message})
            setTimeout(() => this.removeNotification(id), timeout)
        },
        removeNotification(id: number) {
            this.notifications = this.notifications.filter(n => n.id !== id)
        }
    }
})
