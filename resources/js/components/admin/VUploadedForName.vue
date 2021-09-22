<template>
    <span>
        {{identifier}}
    </span>
</template>

<script>
    export default {
        name: "VUploadedForName",

        props: {
            activityInstance: {
                required: true,
                type: Object
            }
        },

        data() {
            return {}
        },

        methods: {},

        computed: {
            resourceType() {
                return this.activityInstance.resource_type;
            },
            participant() {
                return this.activityInstance.participant;
            },
            identifier() {
                if(this.resourceType === 'user') {
                    return this.participant.data.preferred_name ?? (this.participant.data.first_name + ' ' + this.particiant.data.last_name);
                }
                if(this.resourceType === 'group') {
                    return this.participant.data.name;
                }
                if(this.resourceType === 'role') {
                    return this.participant.data.role_name;
                }
                return 'N/A';
            }
        }
    }
</script>

<style scoped>

</style>
