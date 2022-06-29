<template>
  <div>
    <Head>
      <title />
    </Head>
    <portal-target name="dropdown" slim />
    <url-based-modal />
    <div class="md:flex md:flex-col">
      <div class="md:h-screen md:flex md:flex-col">
        <div class="fixed inset-x-0 md:flex md:flex-shrink-0 bg-dark z-50">
          <div class="md:flex-shrink-0 flex items-center justify-between md:justify-center h-16">
            <workspaces-dropdown />
            <div class="md:hidden mr-7 flex items-center">
              <dropdown placement="bottom-end">
                <user-icon
                  size="small"
                  :full-name="user.data.full_name"
                  :src="user.data.image.url"
                  class="mr-3"
                />
                <div slot="dropdown"
                     class="sm:mt-3 py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
                >
                  <div class="block px-6 py-2 hover:bg-light cursor-pointer" @click="showUserUpdateModal">
                    {{ $t('My Profile') }}
                  </div>
                  <Link class="block px-6 py-2 hover:bg-light"
                        :href="route('user.index', {workspace: workspaceId})"
                  >
                    {{
                      $t('Manage Users')
                    }}
                  </Link>
                  <Link class="block px-6 py-2 w-full text-left hover:bg-light"
                        :href="route('logout',workspaceId)" method="delete"
                        as="button"
                  >
                    {{ $t('Logout') }}
                  </Link>
                </div>
              </dropdown>
              <div @click="showMainMenuModal">
                <icon name="menu" class="h-10 sm:h-20 fill-light" />
              </div>
            </div>
          </div>
          <div class="bg-white border-b w-full justify-between items-center hidden sm:flex">
            <top-menu />
            <dropdown placement="bottom-end">
              <user-icon
                id="user-profile-menu"
                :full-name="user.data.full_name"
                :src="user.data.image.url"
                class="mr-14"
              />
              <div slot="dropdown" class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded">
                <div class="block px-6 py-2 hover:bg-light cursor-pointer" @click="showUserUpdateModal">
                  {{ $t('My Profile') }}
                </div>
                <Link class="block px-6 py-2 hover:bg-light"
                      :href="route('user.index',{workspace: workspaceId})"
                >
                  {{ $t('Manage Users') }}
                </Link>
                <Link class="block px-6 py-2 w-full text-left hover:bg-light text-red relative"
                      :href="route('logout',workspaceId)"
                      method="delete"
                      as="button"
                >
                  <span class="absolute w-4/6 h-px bg-light -top-px left-1/6" />
                  {{ $t('Logout') }}
                </Link>
              </div>
            </dropdown>
          </div>
        </div>
        <div class="md:flex md:flex-grow md:overflow-hidden">
          <slot name="project" />
          <div class="md:flex-1 px-6 sm:px-14 pb-8 pt-20 md:pb-9 md:pt-20 md:overflow-y-auto">
            <slot />
            <flash-messages />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import FlashMessages from '@/Shared/Messages/FlashMessages'
import TopMenu from '@/Shared/Menues/TopMenu'
import UserUpdateModal from '@/Shared/Modals/UserUpdateModal'
import MainMenuModal from '@/Shared/Modals/MainMenuModal'
import UrlBasedModal from '@/Shared/Modals/UrlBasedModal'

export default {
  components: {
    UrlBasedModal,
    TopMenu,
    FlashMessages,
  },
  computed: {
    user() {
      return this.$page.props.auth.user
    },
    workspaceId() {
      return this.$page.props.auth.workspace_id
    },
  },
  mounted() {
    this.connectToTheUserNotificationsChannel()
  },
  methods: {
    showUserUpdateModal() {
      this.$modal.show(UserUpdateModal, {user: this.user})
    },

    showMainMenuModal() {
      this.$modal.show(MainMenuModal, {}, {
        width: '100%',
        height: '100%',
        classes: 'modal-menu',
        shiftY: 0,
        transition: '',
        overlayTransition: '',
      })
    },

    connectToTheUserNotificationsChannel() {
      window.Echo.private(`App.Models.User.${this.user.data.id}`)
        .notification((notification) => {
          this.$notify({
            title: 'notification',
            text: notification,
            type: 'info',
            duration: 10000,
          })
        })
    },
  },
}
</script>