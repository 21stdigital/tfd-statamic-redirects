<template>
  <div>
      <data-list :columns="columns" :rows="rows" v-if="rows.length">
        <div class="card p-0 relative" slot-scope="{ filteredRows: rows }">
            <data-list-table
                :rows="rows"
            >
                <template slot="cell-source" slot-scope="{ row: redirect }">
                    <a v-if="canCreate" :href="redirect.edit_url">{{ redirect.source }}</a>
                    <span v-else>{{ redirect.source }}</span>
                </template>
                
                <template slot="cell-active" slot-scope="{ row: redirect }">
                    <div class="w-4 h-4 rounded-full border" :class="redirect.active ? 'bg-green-light border-green' : 'bg-grey-40 border-grey-60'"></div>
                </template>

                <template slot="cell-notes" slot-scope="{ row: redirect }">
                    <span v-if="redirect.notes" class="badge-sm border rounded-full border-grey-60 text-black" v-tooltip="redirect.notes">?</span>
                </template>

                <template v-if="canDelete || canCreate" slot="actions" slot-scope="{ row: redirect }">
                    <dropdown-list>
                        <dropdown-item v-if="canCreate" :text="__('Edit')" :redirect="redirect.edit_url" />
                        <dropdown-item
                            v-if="canDelete"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${redirect.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${redirect.id}`"
                                :resource="redirect"
                                @deleted="removeRow(redirect)"
                            ></resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>

    <div v-else class="card p-3 content">
        <p class="text-grey-70 leading-normal text-lg antialiased">
            {{ __('statamic-redirects::default.explanations.index') }}
        </p>
        <div v-if="canCreate" class="mt-2">
            <a :href="createUrl" class="btn-primary btn-lg">{{ __('statamic-redirects::default.actions.create') }}</a>
        </div>
    </div>
  </div>
</template>

<script>
export default {

    mixins: [
        Listing,
    ],

    props: [
        'redirects',
        'columns',
        'create-url',
        'can-create',
        'can-delete',
    ],

    data() {
        return {
            rows: this.redirects,
            columns: this.columns,
        }
    },

}
</script>

<style>

</style>