<template>
  <div>
      <redirect-listing
        :redirects="redirects"
        :columns="columns"
        :createUrl="createUrl"
        :canCreate="canCreate"
        :canDelete="canDelete"
      ></redirect-listing>
  </div>
</template>

<script>
import RedirectListing from './RedirectListing'

export default {

  components: {
    RedirectListing,
  },

  props: [
    'redirects',
    'columns',
    'create-url',
    'can-create',
    'can-delete',
  ],

  data() {
    return {
      
    }
  },

  methods:{
  },

}
</script>

<style>

</style>