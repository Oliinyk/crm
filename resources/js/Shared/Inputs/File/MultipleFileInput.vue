<template>
  <div @dragover.prevent="dragging = true"
       @dragleave.prevent="dragging = false"
  >
    <label v-if="label" class="form-label" :class="{ 'label-error': error }">{{ label }}:</label>
    <!-- toolbar -->
    <div v-if="isFullScreen" class="flex justify-between mb-5">
      <p class="text-xs font-bold uppercase text-dark">{{ $t('Attachments') }} <span>({{ value.length }})</span></p>
      <div class="flex items-center">
        <button type="button" class="mr-2">
          <icon name="cloud" class="w-6 h-6 fill-gray flex items-center" />
        </button>
        <file-upload
          ref="upload"
          v-model="files"
          :headers="{'X-CSRF-Token': token }"
          :post-action="route('media.store')"
          :accept="accept"
          :multiple="true"
          :size="maxSize"
          @input-filter="inputFilter"
          @input-file="inputFile"
        >
          <icon name="plus" class="w-6 h-6 fill-gray flex items-center cursor-pointer" />
        </file-upload>
      </div>
    </div>
    <!-- end toolbar -->
    <div :class="{'grid grid-cols-2 sm:grid-cols-3 gap-5':isFullScreen}">
      <div :class="
        [isFullScreen?'absolute top-0 left-0 h-0 w-0 border-0':'p-0 mb-3 form-file',
         {
           'error': error,
           'border-accent-dark': dragging,
           'border-0': false,
         }]"
      >
        <div v-show="$refs.upload && $refs.upload.dropActive && isFullScreen" class="fixed top-0 bottom-0 left-0 right-0 z-100 drop-active">
          <div class="absolute w-full h-full top-0 bottom-0 left-0 right-0 bg-dark opacity-60" />
          <h3 class="text-2xl text-white absolute top-0 left-0 right-0 bottom-0 flex items-center justify-center">{{ $t("Drag your files here") }}</h3>
        </div>
        <div class="p-2 pl-4 flex" :class="{'hidden': isFullScreen}">
          <file-upload
            ref="upload"
            v-model="files"
            class="btn-accent"
            :headers="{'X-CSRF-Token': token }"
            :post-action="route('media.store')"
            :accept="accept"
            :multiple="true"
            :size="maxSize"
            :drop="true"
            @input-filter="inputFilter"
            @input-file="inputFile"
          >
            {{ $t('Browse') }}
          </file-upload>
          <div class="text-gray pl-2 py-2">{{ $t("or drag files here") }}</div>
        </div>
      </div>
      <!-- downloading -->
      <div v-for="file in files" :key="file.id" :class="!isFullScreen?'py-3':''">
        <div class="flex items-center rounded overflow-hidden"
             :class="[
               file.error?'border-red border':'',
               !isFullScreen?'p-2 justify-between bg-light':'flex-wrap shadow-md relative group'
             ]"
        >
          <div v-if="isFullScreen" class="w-full h-24 flex items-center justify-center relative overflow-hidden">
            <img v-if="file.thumb" :src="file.thumb" class="h-full w-full object-cover" />
            <file-input-icon v-else :src="file.thumb" />
            <div class="absolute h-full w-full bg-dark opacity-0 group-hover:opacity-30" :class="{'opacity-30':showDropdown}" />
          </div>
          <div v-else>
            <file-input-icon :src="file.thumb" />
          </div>
          <div v-if="isFullScreen" class="absolute top-2 right-1">
            <dropdown placement="bottom-end">
              <div class="opacity-0 group-hover:opacity-100" :class="{'opacity-100':showDropdown}">
                <icon name="more" class="block w-6 h-6 fill-light flex items-center"></icon>
              </div>
              <div slot="dropdown"
                   class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
              >
                <a :href="file.thumb" download class="flex items-center px-5 py-2 hover:bg-light text-dark text-sm cursor-pointer">
                  <icon name="download" class="h-4 w-4 sm:mr-2 fill-gray"></icon>
                  <span>{{ $t('Download') }}</span>
                </a>
                <div class="flex items-center px-5 py-2 hover:bg-light text-dark text-sm cursor-pointer"
                     @click="remove(file)"
                >
                  <icon name="trash" class="h-4 w-4 sm:mr-2 fill-gray"></icon>
                  <span>{{ $t('Delete') }}</span>
                </div>
              </div>
            </dropdown>
          </div>
          <div :class="!isFullScreen?'flex-1 pr-1 ml-6':'py-2 px-3 w-full'">
            <div :class="!isFullScreen?'flex-1 text-dark':'text-gray font-bold text-sm mb-1'">
              <p class="break-all">{{ file.name }}</p>
            </div>
            <div class="text-gray text-sm">
              {{ filesize(file.size) }} {{ file.progress }}%
            </div>
            <div v-if="(file.active && file.progress !== '100.00') || (file.active && file.progress !== '0.00')" class="progress">
              <div class="w-1/2 bg-gray-light rounded-full h-1.5">
                <div class="bg-secondary h-1.5 rounded-full" :style="{width: file.progress + '%'}"
                     :class="{ 'bg-red': file.error}"
                />
              </div>
            </div>
          </div>
          <div v-if="!file.error" :class="{'absolute left-0 right-0 top-0 bottom-0 w-full h-full flex items-center justify-center bg-dark opacity-30':isFullScreen}">
            <div class="btn-spinner-gray" />
          </div>
          <div v-if="file.error" class="cursor-pointer" @click="$refs.upload.remove(file)">
            <icon v-if="!isFullScreen" name="trash" class="w-4 h-4 fill-gray mr-2" />
          </div>
        </div>
        <div v-if="file.error" class="form-error">{{ getError(file) }}</div>
      </div>
      <!-- end downloading -->
      <div v-for="(file,index) in value" :key="file.id" :class="!isFullScreen?'py-3':''">
        <AttachmentsFileItem
          :items="items"
          :index="index"
          :is-full-screen="isFullScreen"
          :file="file"
          @remove="remove(file)"
        />
      </div>
    </div>
  </div>
</template>

<script>
import AttachmentsFileItem from '@/Shared/Inputs/File/AttachmentsFileItem'

export default {
  components: {
    AttachmentsFileItem,
  },
  props: {
    value: Array,
    label: String,
    accept: {
      type: String,
      default: undefined,
    },
    error: String,
    isFullScreen: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      files: [],
      dragging: false,
      uploadAuto: true,
      maxSize: 1024 * 1024 * 5,
      showDropdown: false,
    }
  },
  computed: {
    token() {
      return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    items() {
      return this.value.map(item => {
        return {
          title: item.name,
          description: item.size,
          src: item.url,
        }
      })
    },
  },
  mounted() {
    this.$watch(
      () => this.$refs.upload.active,
      (val) => this.$emit('processing', val),
    )
  },
  methods: {
    inputFilter(newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        // Before adding a file
        // Filter system files or hide files
        if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
          return prevent()
        }
        // Filter php html js file
        if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
          return prevent()
        }
      }
      if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
        // Create a blob field
        newFile.blob = ''
        let URL = window.URL || window.webkitURL
        if (URL && URL.createObjectURL) {
          newFile.blob = URL.createObjectURL(newFile.file)
        }
        // Thumbnails
        newFile.thumb = ''
        if (newFile.blob && newFile.type.substr(0, 6) === 'image/') {
          newFile.thumb = newFile.blob
        }
      }
    },
    inputFile(newFile, oldFile) {
      if (newFile && !oldFile) {
        // add
        this.dragging = false
        console.log('add', newFile)
        //check if new file was already loaded
        if (newFile.file.alreadyUploaded) {
          newFile.success = true
        }
      }
      if (newFile && oldFile) {
        // update
        console.log('update', newFile)
        if (newFile.active && !oldFile.active) {
          // beforeSend
          // min size
          if (newFile.size >= 0 && this.minSize > 0 && newFile.size < this.minSize) {
            this.$refs.upload.update(newFile, {error: 'size'})
          }
        }
        if (newFile.progress !== oldFile.progress) {
          // progress
          console.log('progress', newFile.progress)
        }
        if (newFile.error && !oldFile.error) {
          // error
          console.log('error', newFile.error)
        }
        if (newFile.success && !oldFile.success) {
          // success
          console.log('success', newFile.success)
          let temp = this.value || []
          temp.push(newFile.response)
          this.$emit('input', temp)
          this.$refs.upload.remove(newFile)
        }
      }
      if (!newFile && oldFile) {
        // remove
        console.log('remove', oldFile)
        if (oldFile.success && oldFile.response.id) {
          // $.ajax({
          //   type: 'DELETE',
          //   url: '/upload/delete?id=' + oldFile.response.id,
          // })
        }
      }
      // Automatically activate upload
      if (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
        if (this.uploadAuto && !this.$refs.upload.active) {
          this.$refs.upload.active = true
        }
      }
    },
    filesize(size) {
      const i = Math.floor(Math.log(size) / Math.log(1024))
      return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i]
    },
    remove(file) {
      let temp = this.value
      temp.splice(this.value.findIndex(item => item.id === file.id), 1)
      this.$emit('input', temp)
    },
    getError(file) {
      if (file.error === 'denied') {
        return file.response.file.join('\n')
      }
      if (file.error === 'size') {
        console.log(this.maxSize)
        return this.$t('Max file size error',{ size: this.filesize(this.maxSize) })
      }
      return file.error
    },
  },
}
</script>
