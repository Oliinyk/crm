<template>
  <div>
    <!--    TODO: :show-delete-button="can.delete_ticketType" -->
    <form-modal :form="form"
                :name="$t('Ticket type details')"
                :submit-button-name="$t('Update')"
                :show-delete-button="true"
                @submit="store"
                @close="$emit('close')"
                @delete="destroy"
    >
      <trashed-message v-if="ticketType.deleted_at" class="mb-7" @restore="restore">
        {{ $t('This ticket type has been deleted.') }}
      </trashed-message>
      <text-input v-model="form.name" :error="form.errors.name" class="pb-7 w-full" id="ticket-type-udpate-mane" :label="$t('Ticket type name')" />
      <hr class="w-full pb-2" />
      <div class="w-full pb-6">
        <p class="text-xs font-bold uppercase text-dark">{{ $t('Ticket fields') }}</p>
      </div>
      <div class="w-full relative">
        <icon name="dragndrop"
              class="w-5 h-5 fill-gray-light inline absolute top-2.5 left-0 column-drag-handle cursor-not-allowed"
        />
        <text-input v-model="form.title"
                    :error="form.errors.title"
                    class="pb-8 w-full pl-8"
                    :selected="true"
        />
      </div>
      <div class="w-full">
        <!-- New field -->
        <Container v-if="form.fields.length" drag-handle-selector=".column-drag-handle" @drop="onDrop">
          <Draggable v-for="(item, index) in form.fields" :key="item.id" class="relative px-1 -mx-1">
            <icon name="dragndrop"
                  class="w-5 h-5 fill-gray-light inline absolute top-9 left-0 column-drag-handle cursor-move"
                  :class="{'top-2.5': item.selected}"
            />
            <text-input v-model="item.name"
                        class="pb-8 w-full pl-8 ttu-input-name"
                        :error="form.errors[`fields.${index}.name`] || form.errors[`fields.${index}.type`]"
                        :label="item.type"
                        :placeholder="item.placeholder"
                        :disabled="item.disabled"
                        :selected="item.selected"
                        icon="trash"
                        @iconClick="deleteField(item.id)"
            />
          </Draggable>
        </Container>
      </div>
      <div class="w-full mb-7">
        <dropdown placement="top-end">
          <div class="inline-flex text-secondary font-semibold form-input border-0 hover:bg-white"
               :class="{'error': form.errors.fields}"
          >
            <icon name="plus" class="w-6 h-6 mr-0.5 fill-secondary inline"></icon>
            <span>{{ $t('Add field') }}</span>
          </div>
          <div slot="dropdown" class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded">
            <p class="px-6 py-2 uppercase font-bold text-gray text-xs">{{ $t('Default fields') }}</p>
            <ul class="text-sm">
              <li v-for="(item, index) in defaultFields" :key="'default-field-' + index"
                  class="block px-6 py-2 hover:bg-light text-dark cursor-pointer"
                  :class="{'bg-light': checkActive(item.type)}"
                  @click="addInputDefault(item)"
              >
                {{ $t(item.name) }}
              </li>
              <li class="block px-6 py-2">
                <hr class="w-full" />
              </li>
            </ul>
            <p class="px-6 py-2 uppercase font-bold text-gray text-xs">{{ $t('Custom fields') }}</p>
            <ul class="text-sm">
              <li v-for="(item, index) in customFields" :key="'custom-field-' + index"
                  class="block px-6 py-2 hover:bg-light text-dark cursor-pointer"
                  @click="addInputCustom(item)"
              >
                {{ $t(item.type) }}
              </li>
              <li class="block px-6 py-2">
                <hr class="w-full" />
              </li>
              <li v-for="(item, index) in spFields" :key="'separator-field-' + index"
                  class="block px-6 py-2 hover:bg-light text-dark cursor-pointer"
                  @click="addSeparator(item)"
              >
                {{ $t(item.type) }}
              </li>
            </ul>
          </div>
        </dropdown>
        <div v-if="form.errors.fields" class="form-error">{{ form.errors.fields }}</div>
      </div>
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'
import {Container, Draggable} from 'vue-dndrop'
import TicketTypeFields from '@/Setup/TicketTypeFields'
import TrashedMessage from '../Messages/TrashedMessage'

export default {
  name: 'LargeModal',
  components: {
    TrashedMessage,
    FormModal,
    Container,
    Draggable,
  },
  props: {
    ticketType: Object,
    can: Object,
  },
  data() {
    return {
      defaultFields: TicketTypeFields.defaultFields,
      customFields: TicketTypeFields.customFields,
      spFields: TicketTypeFields.spFields,
      form: this.$inertia.form({
        name: this.ticketType.name,
        title: this.ticketType.title,
        fields: this.ticketType.fields,
      }),
    }
  },
  methods: {
    store() {
      this.form.put(this.route('ticket-type.update', {
        workspace: this.$page.props.auth.workspace_id,
        ticketType: this.ticketType.id,
        //'_query': this.route().params,
      }), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
    destroy() {
      if (confirm(this.$t('Are you sure you want to disable this ticket type?'))) {
        this.$inertia.delete(
          this.route('ticket-type.destroy', {
            workspace: this.$page.props.auth.workspace_id,
            '_query': this.route().params,
          }),
          {
            data: {ids: [this.ticketType.id]},
            onSuccess: () => {
              this.$emit('close')
            },
          })
      }
    },
    addInputDefault: function (field) {
      const index = this.form.fields.findIndex(item => item.id === field.id)
      if (index === -1) {
        this.form.fields.push({
          id: Date.now(),
          name: field.name,
          type: field.type,
          disabled: field.disabled,
          selected: field.selected,
          placeholder: field.placeholder,
        })
      }
    },
    addInputCustom: function (field) {
      this.form.fields.push({
        id: Date.now(),
        name: field.name,
        type: field.type,
        disabled: field.disabled,
        selected: field.selected,
        placeholder: field.placeholder,
      })
    },
    addSeparator: function (field) {
      this.form.fields.push({
        id: Date.now(),
        name: field.name,
        type: field.type,
        disabled: field.disabled,
        selected: field.selected,
        placeholder: field.placeholder,
      })
    },
    onDrop(dropResult) {
      const elem = this.form.fields.splice(dropResult.removedIndex, 1)
      this.form.fields.splice(dropResult.addedIndex, 0, ...elem)
    },
    deleteField(id) {
      const index = this.form.fields.findIndex(item => item.id === id)
      this.form.fields.splice(index, 1)
    },
    checkActive(type) {
      return this.form.fields.findIndex(item => item.type === type) !== -1
    },
    restore() {
      this.$inertia.put(this.route('ticket-type.restore', {
        workspace: this.$page.props.auth.workspace_id,
        ticketType: this.ticketType.id,
        '_query': this.route().params,
      }), {}, {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
