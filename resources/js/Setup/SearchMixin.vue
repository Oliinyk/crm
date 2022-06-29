<script>
import throttle from 'lodash/throttle'
import axios from 'axios'
import pickBy from 'lodash/pickBy'

export default {
  data() {
    return {
      cancelSource: null,
      searchForm: {
        search: null,
        selectedOptions: null,
      },
    }
  },
  methods: {
    /**
     * Start the search process.
     */
    search() {
      console.log('search')

      this.fetchOptions(this.searchForm.search)
    },

    /**
     * Cancel search.
     */
    cancelSearch() {
      if (this.cancelSource) {
        this.cancelSource.cancel('')
      }
    },
    
    /**
     * Fetching process.
     */
    fetchOptions: throttle(function (search) {
      console.log('search fetchOptions')

      this.searchForm.search = search
      this.searchForm.selectedOptions = this.currentValue

      this.cancelSearch()
      this.cancelSource = axios.CancelToken.source()

      axios.get(this.url, {
        params: pickBy(this.searchForm),
        cancelToken: this.cancelSource.token,
      }).then((...args) => {
        this.onSuccessFetch(...args)
        this.cancelSource = null
      })
        .catch((error) => {
          if (error.response) {
            if (error.response.status === 401) {
              this.$emit('close')
              this.$inertia.reload()
            }
          } else if (error.request) {
            // The request was made but no response was received
            console.log(error.request)
          } else {
            // Something happened in setting up the request that triggered an Error
            console.log('Error', error.message)
          }
        })
    }, 350),
  },
}
</script>