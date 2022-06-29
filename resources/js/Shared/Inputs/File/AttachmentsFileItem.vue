<template>
  <div
    class="flex items-center rounded"
    :class="[isFullScreen ? 'flex-wrap shadow-md relative group':'p-2 justify-between bg-light']"
  >
    <div v-if="isFullScreen" class="w-full flex items-center justify-center">
      <!-- CoolLightBox -->
      <div v-if="file.thumb" class="w-full">
        <!-- img -->
        <CoolLightBox
          :items="items"
          :index="currentIndex"
          :overlay-color="'#26293b4d'"
          :use-zoom-bar="true"
          @close="currentIndex = null"
          @on-change="changedCurrentIndex"
        />
        <div class="cursor-pointer" @click="changeindex()">
          <div class="h-24 w-full images-wrapper rounded relative overflow-hidden">
            <div
              class="h-full w-full bg-gray bg-cover image"
              :class="{'opacity-70':showDropdown}"
              :style="{ backgroundImage: 'url(' + file.thumb + ')' }"
            />
            <div class="absolute top-0 left-0 h-full w-full bg-dark opacity-0 group-hover:opacity-40" :class="{'opacity-40':showDropdown}" />
          </div>
          <div class="py-2 px-3 w-full">
            <div class="text-gray font-bold text-sm mb-1">
              <p class="truncate">{{ file.name }}</p>
            </div>
            <div class="text-gray text-sm">{{ filesize(file.size) }}</div>
          </div>
          <!-- tooltip -->
          <p class="absolute -bottom-15 left-0 mt-1.5 opacity-0 group-hover:opacity-100 bg-dark text-white rounded py-0.5 px-2 text-sm leading-5 w-full z-1 break-all">{{ file.name }}</p>
        </div>
      </div>
      <!-- end img -->
      <!-- file -->
      <div v-else class="w-full">
        <CoolLightBox
          :items="items"
          :index="currentIndex"
          :overlay-color="'#26293b4d'"
          :use-zoom-bar="true"
          @close="currentIndex = null"
          @on-change="changedCurrentIndex"
        />
        <div class="cursor-pointer" @click="changeindex()">
          <div class="flex items-center justify-center h-24 w-full file-wrapper rounded relative overflow-hidden">
            <file-input-icon :src="file.thumb" class="mx-2" />
            <div class="absolute top-0 left-0 h-full w-full bg-dark opacity-0 group-hover:opacity-40" :class="{'opacity-40':showDropdown}" />
          </div>
          <div class="py-2 px-3 w-full">
            <div class="text-gray font-bold text-sm mb-1">
              <p class="truncate">{{ file.name }}</p>
            </div>
            <div class="text-gray text-sm">{{ filesize(file.size) }}</div>
          </div>
          <!-- tooltip -->
          <p class="absolute -bottom-15 left-0 mt-1.5 opacity-0 group-hover:opacity-100 bg-dark text-white rounded py-0.5 px-2 text-sm leading-5 w-full z-1 break-all">{{ file.name }}</p>
        </div>
      </div>
      <!-- end file -->
      <!-- CoolLightBox download button  -->
      <div v-if="currentIndex !== null"
           class="fixed top-0 right-24 md:mr-2 p-3 z-99999 cursor-pointer"
           @click="downloadFile(currentImageUrl)"
      >
        <icon name="download" class="h-6 w-6 md:h-7 md:w-7 fill-white" />
      </div>
      <!-- end CoolLightBox download button -->
      <!-- end CoolLightBox -->
    </div>
    <div v-else class="flex items-center">
      <file-input-icon :src="file.thumb" />
      <div class="flex-1 pr-1 ml-6">
        <div class="text-gray font-bold text-sm mb-1">
          <p class="break-all">{{ file.name }}</p>
        </div>
        <div class="text-gray text-sm">{{ filesize(file.size) }}</div>
      </div>
    </div>
    <div v-if="isFullScreen" class="absolute top-2 right-1">
      <dropdown placement="bottom-end" @show="isShowDropdown">
        <div class="opacity-0 group-hover:opacity-100" :class="{'opacity-100':showDropdown}">
          <icon name="more" class="block w-6 h-6 fill-light flex items-center hover:fill-gray-light" />
        </div>
        <div slot="dropdown"
             class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
        >
          <div class="flex items-center px-5 py-2 hover:bg-light text-dark text-sm cursor-pointer"
               @click="downloadFile()"
          >
            <icon name="download" class="h-4 w-4 sm:mr-2 fill-gray" />
            <span>{{ $t('Download') }}</span>
          </div>
          <div class="flex items-center px-5 py-2 hover:bg-light text-dark text-sm cursor-pointer"
               @click="$emit('remove')"
          >
            <icon name="trash" class="h-4 w-4 sm:mr-2 fill-gray"></icon>
            <span>{{ $t('Delete') }}</span>
          </div>
        </div>
      </dropdown>
    </div>
    <div v-else class="cursor-pointer" @click="$emit('remove')">
      <icon name="trash" class="w-4 h-4 fill-gray mr-2" />
    </div>
  </div>
</template>

<script>
import CoolLightBox from 'vue-cool-lightbox'
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css'
let FileSaver = require('file-saver')

export default {
  components: {
    CoolLightBox,
  },
  props: {
    file: Object,
    isFullScreen: {
      type: Boolean,
      default: false,
    },
    items: {
      type: Array,
      default: null,
    },
    index: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      showDropdown: false,
      currentIndex: null,
      currentImageUrl: this.index,
      currentImageName: '',
    }
  },
  watch: {
    currentIndex() {
      if (this.currentIndex === null) return
      this.currentImageUrl = this.items[this.currentIndex].src
      this.currentImageName = this.items[this.currentIndex].title
    },
  },
  methods: {
    isShowDropdown(val) {
      this.showDropdown = val
    },
    filesize(size) {
      const i = Math.floor(Math.log(size) / Math.log(1024))
      return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i]
    },
    changeindex() {
      this.currentIndex = this.index
    },
    downloadFile(fileUrl) {
      let url = ''
      let name = 'image.jpg'
      if(fileUrl) {
        url = fileUrl
        name = this.currentImageName
      } else {
        url = this.items[this.index].src
        name = this.items[this.index].title
      }
      FileSaver.saveAs(url, name)
    },
    changedCurrentIndex(val) {
      this.currentIndex = val
    },
  },
}
</script>

<style>
.cool-lightbox-toolbar .cool-lightbox-toolbar__btn {
  background: transparent;
}
.cool-lightbox svg path, .cool-lightbox svg rect {
  fill: #fff;
}
.cool-lightbox-toolbar__btn[title="Play slideshow"] {
  display: none;
}
.cool-lightbox-caption h6 {
  font-weight: 700;
}
</style>