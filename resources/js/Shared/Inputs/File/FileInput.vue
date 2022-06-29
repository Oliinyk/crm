<template>
  <div @dragover.prevent="dragging = true"
       @dragleave.prevent="dragging = false"
  >
    <label v-if="label" class="form-label" :class="{ 'label-error': files.length && files[0].error }">
      {{ label }}:
    </label>
    <div class="p-0 form-file" :class="formFileClasses">
      <div v-show="!files.length && !(value && value.id) " class="p-2 pl-4 flex">
        <file-upload ref="upload"
                     v-model="files"
                     class="btn-accent"
                     :headers="{'X-CSRF-Token': token }"
                     :post-action="route('media.store')"
                     :accept="accept"
                     :multiple="false"
                     :size="maxSize"
                     :drop="true"
                     :extensions="extensions"
                     @input-filter="inputFilter"
                     @input-file="inputFile"
        >
          {{ $t('Browse') }}
        </file-upload>
        <span class="text-gray pl-2 py-2">{{ $t('or drag files here') }}</span>
      </div>
      <div v-if="value && value.name" class="flex items-center justify-between p-2 bg-light rounded">
        <file-input-icon :src="value.thumb" />
        <div class="flex-1 pr-1">
          <div class="flex-1 text-dark">
            <p class="break-all">{{ value.name }}</p>
          </div>
          <div class="text-gray text-sm">{{ filesize(value.size) }}</div>
        </div>
        <div class="cursor-pointer" @click="remove">
          <icon name="trash" class="w-4 h-4 fill-gray mr-2" />
        </div>
      </div>
      <div v-for="file in files" :key="file.id">
        <div class="flex items-center justify-between p-2 bg-light rounded"
             :class="file.error?'border-red border':''"
        >
          <file-input-icon v-if="file.thumb" :src="file.thumb" />
          <div class="flex-1 pr-1">
            <div class="flex-1 text-dark">
              <p class="break-all">{{ file.name }}</p>
            </div>
            <div class="text-gray text-sm">
              {{ filesize(file.size) }} {{ file.progress }}%
            </div>
            <div v-if="file.active || file.progress !== '0.00'" class="progress">
              <div class="w-1/2 bg-gray-light rounded-full h-1.5">
                <div class="bg-secondary h-1.5 rounded-full" :style="{width: file.progress + '%'}"
                     :class="{ 'bg-red': file.error}"
                />
              </div>
            </div>
          </div>
          <div v-if="!file.error" class="btn-spinner-gray" />
          <div v-if="file.error" class="cursor-pointer" @click="$refs.upload.remove(file)">
            <icon name="trash" class="w-4 h-4 fill-gray mr-2" />
          </div>
        </div>
      </div>
    </div>
    <div v-if="files.length && files[0].error" class="form-error">{{ getError(files[0]) }}</div>
  </div>
</template>

<script>

export default {
  props: {
    value: Object,
    label: String,
    error: String,
    accept: {
      type: String,
      default: null,
    },
    extensions: {
      type: Array,
      default: null,
    },
  },
  data() {
    return {
      uploadAuto: true,
      dragging: false,
      files: [],
      maxSize: 1024 * 1024 * 5,
    }
  },
  computed: {
    formFileClasses() {
      return {
        'border-accent-dark': this.dragging,
        'border-0': this.files.length || (this.value && this.value.id),
      }
    },
    token() {
      return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
  },
  mounted() {
    this.$watch(
      () => this.$refs.upload.active,
      (val) => this.$emit('processing', val),
    )
  },
  methods: {
    filesize(size) {
      const i = Math.floor(Math.log(size) / Math.log(1024))
      return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i]
    },
    remove() {
      this.$emit('input', null)
    },
    preview() {
      if (this.value && this.acceptedImageTypes.includes(this.value['type'])) {
        if (Object.keys(this.value).includes('url')) {
          return this.value.url
        }
        return URL.createObjectURL(this.value)
      }
      return null
    },
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
        if (this.value && this.value.id) {
          this.remove()
        }
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
          let temp = []
          temp.push(newFile.response)
          this.$emit('input', temp[0])
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
    getError(file) {
      if (file.error === 'denied') {
        if (file.response) {
          return file.response.file.join('\n')
        }
        return this.$t('Server error')
      }
      if (file.error === 'size') {
        return this.$t('Max file size error', {size: this.filesize(this.maxSize)})
      }
      if (file.error === 'extension') {
        return this.$t('Extension file error', {values: this.extensions.join(', ')})
      }
      return file.error
    },

  },
}
</script>
