<template>
  <div>
      <publish-form
        :title="title"
        :action="action"
        :blueprint="blueprint"
        :meta="meta"
        :method="method"
        :values="values"
        @saved="redirectOnSaved"
    ></publish-form>
  </div>
</template>

<script>
export default {

  props: [
    'title',
    'action',
    'blueprint',
    'method',
    'meta',
    'values',
  ],

  data() {
    return {
      
    }
  },

  methods: {
    redirectOnSaved(response) {
      this.$nextTick(() => {
        window.location = cp_url('statamic-redirects')
      });
    }
  }

}
</script>

<style>

</style>