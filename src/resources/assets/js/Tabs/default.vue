<template>
    <shared-content/>
</template>

<script>
import Ops from './ops'
import SharedContent from './shared/content'

export default {
    components: {SharedContent},
    name: 'default-tab',
    mixins: [Ops],
    mounted() {
        this.getFiles()
    },
    methods: {
        getFiles() {
            return axios.post(this.routes.filesRoute, {})
                .then(({data}) => {
                    if (data.success) {
                        this.files = data.message
                    }

                }).catch((err) => {
                    console.error(err)
                    this.failedAjax()
                })
        }
    }
}
</script>
