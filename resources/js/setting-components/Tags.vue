<template>
    <div>
        <b-form-tags input-id="tags" v-model="value">
            
        </b-form-tags>
    </div>
</template>

<script>
    import {abstractField} from 'vue-form-generator';
    export default {
        name: "Tags",

        mixins: [abstractField],
        
        props: {},

        data() {
            return {}
        },

        methods: {},

        computed: {}
    }
</script>

<style scoped>

</style>