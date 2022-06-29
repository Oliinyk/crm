<template>
  <div>
    <label :class="{
      'text-gray font-bold min-w-100': editable,
      'form-label': !editable,
      'form-error': error
    }"
    >
      {{ $t($fieldTypes.TYPE_WATCHERS) }}:
    </label>
    <vselect-search
      :value="value"
      :class="{
        'flex items-center rounded cursor-pointer w-full placeholder-italic': editable,
        'w-full form-select py-0.5 flex items-center': !editable,
        'error': error
      }"
      :error="error"
      :multiple="multiple"
      :close-on-select="false"
      :url="route('user.search',{workspace: $page.props.auth.workspace_id, project_id: $page.props.project.id})"
      :selected-options="selectedOptions"
      :editable="editable"
      :placeholder="placeholder"
      :user-image="true"
      @input="$emit('input', $event)"
    />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>

export default {
  props: {
    value: [Object, Array],
    selectedOptions: {
      type: [Object, Array],
      default: () => {
        return []
      },
    },
    multiple: {
      type: Boolean,
      default: true,
    },
    error: String,
    placeholder: {
      type: String,
      default: '',
    },
    editable: Boolean,
  },
}
</script>
