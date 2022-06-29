<template>
  <div class="vs__selected_inner flex items-center text-dark">
    <user-icon
      v-if="userImage"
      size="small"
      :full-name="name"
      :src="image.url"
    />
    {{ title }}
    {{ name }}
    <span v-if="multiple" class="deselect ml-2">
      <icon name="close" class="feather feather-x cursor-pointer hover:text-teal-400 w-4 h-4 fill-dark"></icon>
    </span>
  </div>
</template>

<script>
export default {
  props: {
    multiple: Boolean,
    userImage: Boolean,
    name: String,
    image: Object,
    title: String,
  },
}
</script>

