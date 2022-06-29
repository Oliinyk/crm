<template>
  <div>
    <Table :models="timeEntries"
           :headers="[
             $t('Estimate'),
             $t('Date'),
             $t('User'),
             $t('Reason')
           ]"
           :empty-message="$t('No estimate added')"
           :show-checkboxes="showCheckboxes"
    >
      <template #body="{model}">
        <td class="sm:border-t text-dark h-16 sm:h-auto bg-light sm:bg-transparent">
          <div class="flex items-center px-6 py-4 h-full">
            <p>{{ model.time }}</p>
          </div>
        </td>
        <td class="sm:border-t text-dark h-16 sm:h-auto">
          <div class="flex items-center px-6 py-4 h-full">
            {{ model.date }}
          </div>
        </td>
        <td class="sm:border-t text-dark h-16 sm:h-auto">
          <div class="flex items-center px-6 py-4 h-full min-w-100">
            <user-icon
              :full-name="model.author.full_name"
              :src="model.author.image.url"
            />
            <p>{{ model.author.full_name }}</p>
          </div>
        </td>
        <td class="sm:border-t text-dark h-16 sm:h-auto">
          <div v-if="model.description" class="flex items-center px-6 py-4 h-full">
            {{ model.description }}
          </div>
          <p v-else class="flex items-center px-6 py-4 h-full text-gray">{{ $t('Initial estimate') }}</p>
        </td>
      </template>
    </Table>
  </div>
</template>

<script>
import Table from '@/Shared/Tables/Table'
import estimateFormat from '@/Setup/estimateFormat'

export default {
  components: {
    Table,
  },
  props: {
    showCheckboxes: Boolean,
    timeEntries: Object,
  },
  data() {
    return {
      //
    }
  },
  computed: {
    workingHours() {
      return this.$page.props.project.working_hours
    },
  },
  methods: {
    isUp(model){
      return estimateFormat.toMin( model.lastValue, this.workingHours) > 
      estimateFormat.toMin( model.currentValue, this.workingHours) 
    },
  },
}
</script>
