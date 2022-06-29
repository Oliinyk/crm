<template>
  <div>
    <div v-for="activityLog in ticket.activity_log" :key="activityLog.id" class="pb-7 flex">
      <user-icon
        size="small"
        :full-name="activityLog.user.full_name"
        :src="activityLog.user.image.url"
      />
      <div>
        <p class="mt-1.5 mb-2">
          {{ activityLog.user.full_name }}
          <span class="text-gray">{{ activityLog.description }} a</span>
          {{ activityLog.subject }}
        </p>
        <p class="text-gray">{{ activityLog.created_at }}</p>
        <p v-show="activityLog.event !== 'created'">
          {{ activityLog.changes }}
        </p>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  components: {},
  props: {
    ticket: Object,
  },
  data() {
    return {
      //
    }
  },
  methods: {
    //
  },
}
</script>
