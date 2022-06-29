<template>
  <div>
    <Head :title="$t('Project')" />
    {{ project }}
  </div>
</template>

<script>
import ProjectLayout from '@/Shared/Layouts/ProjectLayout'

export default {
  layout: ProjectLayout,
  props: {
    project: Object,
  },
  remember: 'form',
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
