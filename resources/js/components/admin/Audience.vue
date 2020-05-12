<template>
    <div>
        <v-select 
            :options="options"
            :value="value" 
            @input="$emit('input', $event)"
        ></v-select>
    </di    v>
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
                    let text = '';
                    if(a.resource_type === 'user') {
                        text = a.participant.data.first_name + ' ' + a.participant.data.last_name + ' (' + a.name + ')';
                    }
                    if(a.resource_type === 'group') {
                        text = a.participant.data.name + ' (' + a.name + ')';
                    }
                    if(a.resource_type === 'role') {
                        text = a.participant.data.role_name + ' (' + a.name + ')';
                    }
                    
                    return { value: a.id, text: text }
                })
            }
        }
    }
</script>

<style scoped>

</style>