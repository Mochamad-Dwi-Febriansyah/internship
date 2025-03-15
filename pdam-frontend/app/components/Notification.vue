<script setup lang="ts"> 
const notificationStore = useNotification();
const iconMap = {
  success: "material-symbols:check-circle-rounded",
  error: "material-symbols:error-circle-rounded",
  warning: "material-symbols:warning-rounded",
  info: "material-symbols:info-rounded",
};
const bgMap = {
  success: "bg-green-500 text-white",
  error: "bg-red-500 text-white",
  warning: "bg-yellow-500 text-black",
  info: "bg-blue-500 text-white",
};
const bgIconMap = {
  success: "bg-green-500",
  error: "bg-red-500",
  warning: "bg-yellow-500",
  info: "bg-blue-500",
};
</script>

<template>
  <div class="fixed top-5 right-5 space-y-2 z-50">
    <TransitionGroup name="fade">
      <div
        v-for="notification in notificationStore.notifications"
        :key="notification.id"
        :class="['rounded-md shadow-md flex items-center w-80', bgMap[notification.type] || 'bg-blue-500 text-white']"
      >
        <div class="ms-2 py-2 px-4 rounded-r-md bg-white flex items-center justify-between w-full text-gray-600">
          <div class="flex items-center gap-2">
            <Icon :name="iconMap[notification.type] || 'material-symbols:info'" :class="bgIconMap[notification.type]  || 'bg-gray-500'" class="h-7 w-7"/>
            <div>
              <span class="font-medium text-sm capitalize">{{ notification.type }}</span>
              <span class="text-sm block">{{ notification.message }}</span>
            </div>
          </div>
          <button @click="notificationStore.removeNotification(notification.id)" class="ml-4 font-bold text-2xl">×</button>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

