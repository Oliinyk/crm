<template>
  <div class="w-full bg-accent rounded-full h-2.5">
    <div class="bg-primary h-2.5 rounded-full"
         :class="{'bg-red':isError}"
         :style="'width:'+percent+'%'"
    />
  </div>
</template>

<script>

export default {
  name: 'ProgressBar',
  props: {
    isError: {
      type: Boolean,
      default: false,
    },
    percent:{
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      //
    }
  },
}
</script>