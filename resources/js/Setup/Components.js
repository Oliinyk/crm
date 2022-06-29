import Vue from 'vue'
import {Head, Link} from '@inertiajs/inertia-vue'
import CheckboxInput from '@/Shared/Inputs/CheckboxInput'
import FileInput from '@/Shared/Inputs/File/FileInput'
import MultipleFileInput from '@/Shared/Inputs/File/MultipleFileInput'
import SelectInput from '@/Shared/Inputs/SelectInput'
import TextareaInput from '@/Shared/Inputs/TextareaInput'
import TextInput from '@/Shared/Inputs/TextInput'
import TextLine from '@/Shared/Inputs/TextLine'
import VselectSearch from '@/Shared/Inputs/Search/VselectSearch'

import FileInputIcon from '@/Shared/Icons/FileInputIcon'
import Icon from '@/Shared/Icons/Icon'
import Logo from '@/Shared/Icons/Logo'
import ProjectIcon from '@/Shared/Icons/ProjectIcon'
import UserIcon from '@/Shared/Icons/UserIcon'
import WorkspaceIcon from '@/Shared/Icons/WorkspaceIcon'

import Dropdown from '@/Shared/Dropdowns/Dropdown'
import WorkspacesDropdown from '@/Shared/Dropdowns/WorkspacesDropdown'
import VueUploadComponent from 'vue-upload-component'
import RangeInput from '@/Shared/Inputs/RangeInput'
import DatePicker from 'vue2-datepicker'
import Comments from '@/Shared/Tabs/Comments'

import TicketDetailsModal from '@/Shared/Modals/TicketDetailsModal'
import LoggerModal from '@/Shared/Modals/LoggerModal'
import ChangeEstimateModal from '@/Shared/Modals/ChangeEstimateModal'

/**
 * Setup global Components.
 */
Vue.component('Head', Head)
Vue.component('Link', Link)

/**
 * Inputs
 */
Vue.component('CheckboxInput', CheckboxInput)
Vue.component('FileInput', FileInput)
Vue.component('MultipleFileInput', MultipleFileInput)
Vue.component('SelectInput', SelectInput)
Vue.component('TextareaInput', TextareaInput)
Vue.component('TextInput', TextInput)
Vue.component('TextLine', TextLine)
Vue.component('VselectSearch', VselectSearch)
Vue.component('FileUpload', VueUploadComponent)
Vue.component('RangeInput', RangeInput)
Vue.component('DatePicker', DatePicker)

/**
 * Icons
 */
Vue.component('FileInputIcon', FileInputIcon)
Vue.component('Icon', Icon)
Vue.component('Logo', Logo)
Vue.component('ProjectIcon', ProjectIcon)
Vue.component('UserIcon', UserIcon)
Vue.component('WorkspaceIcon', WorkspaceIcon)

/**
 * Dropdowns
 */
Vue.component('Dropdown', Dropdown)
Vue.component('WorkspacesDropdown', WorkspacesDropdown)

/**
 * Tabs
 */
Vue.component('Comments', Comments)

/**
 * Modals
 */
Vue.component('TicketDetailsModal', TicketDetailsModal)
Vue.component('LoggerModal', LoggerModal)
Vue.component('ChangeEstimateModal', ChangeEstimateModal)
