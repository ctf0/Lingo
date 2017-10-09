<template>
   <div>
        <!-- select file -->
       <shared-content></shared-content>
   </div>
</template>

<script>
import Ops from './ops'
import SharedContent from './../shared/content'

export default {
    components: {SharedContent},
    name: 'default-tab',
    mixins: [Ops],
    mounted() {
        this.getFiles()
    },
    methods: {
        getFiles() {
            $.post(this.routes.filesRoute, {}, (data) => {
                if (data.success) {
                    this.files = data.message
                }

            }).fail(() => {
                this.$parent.failedAjax()
            })
        }
    }
}
</script>
