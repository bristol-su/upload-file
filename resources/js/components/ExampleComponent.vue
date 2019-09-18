<template>
    <div>
        This is an example component
    </div>
</template>

<script>
    export default {
        name: "ExampleComponent",

        props: {},

        data() {
            return {}
        },

        created() {
            this.$http.get('http://portal.local/api/uploadfile/a/welcome-fair/some-module-informatino-here/me')
                .then(response => console.log(response))
                .catch(error => console.log(error));
        },

        computed: {}
    }
</script>

<style scoped>

</style>