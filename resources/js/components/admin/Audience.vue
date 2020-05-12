<template>
    <div>
        <v-select 
            :options="options"
            :value="value" 
            @input="$emit('input', $event)"
            :reduce="obj => obj.value"
        ></v-select>
    </div>
</template>

<script>
    import vSelect from 'vue-select';
    import 'vue-select/dist/vue-select.css';
    
    export default {
        name: "Audience",

        components: {
            vSelect
        },
        
        props: {
            value: {
                required: false,
                type: Number,
                default: null
            }
        },

        created() {
            this.loadAudience();
        },
        
        data() {
            return {
                audience: []
            }
        },

        methods: {
            loadAudience() {
                this.$http.get('/activity-instance')
                    .then(response => this.audience = response.data)
                    .catch(error => this.$notify.alert('Could not load the audience: ' + error.message));
            }
        },

        computed: {
            options() {
                return this.audience.map(a => {
                    let label = '';
                    if(a.resource_type === 'user') {
                        label = a.participant.data.first_name + ' ' + a.participant.data.last_name + ' (' + a.name + ')';
                    }
                    if(a.resource_type === 'group') {
                        label = a.participant.data.name + ' (' + a.name + ')';
                    }
                    if(a.resource_type === 'role') {
                        label = a.participant.data.role_name + ' (' + a.name + ')';
                    }
                    
                    return { value: a.id, label: label }
                })
            }
        }
    }
</script>

<style scoped>

</style>