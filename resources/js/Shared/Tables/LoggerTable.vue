<template>
  <div>
    <Table :models="timeEntries"
           :headers="[
             $t('Time'),
             $t('Date'),
             $t('Description'),
             $t('User')
           ]"
           :empty-message="$t('No work logs.')"
           :show-checkboxes="showCheckboxes"
    >
      <template #body="{model}">
        <td class="border-t px-6 py-4 text-dark">
          {{ model.time }}
        </td>
        <td class="border-t px-6 py-4 text-dark">
          {{ model.date }}
        </td>
        <td class="border-t px-6 py-4 text-dark">
          {{ model.description }}
        </td>
        <td class="border-t px-6 py-4 text-dark">
          <div class="flex items-center">
            <user-icon
              :full-name="model.author.full_name"
              :src="model.author.image.url"
            />
            <p>{{ model.author.full_name }}</p>
          </div>
        </td>
        <td class="absolute top-1 right-0 sm:relative sm:top-auto sm:right-auto sm:text-right sm:w-px">
          <dropdown placement="bottom-end">
            <div class="flex items-center text-left py-4">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown"
                 class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
            >
              <div v-if="model.deleted_at == null"
                   class="block px-6 py-2 hover:bg-light text-dark cursor-pointer"
                   @click="deleteLogs(model.id)"
              >
                {{ $t('Delete') }}
              </div>
            </div>
          </dropdown>
        </td>
      </template>
    </Table>
  </div>
</template>

<script>
import Table from '@/Shared/Tables/Table'

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
  methods: {
    // delete work logs
    deleteLogs(id) {
      if (confirm(this.$t('Are you sure you want to delete this work logs?'))) {
        this.$emit('delete', id)
      }
    },
  },
}
</script>
