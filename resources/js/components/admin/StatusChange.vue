<template>
    <div>
        <b-form inline>
            <label class="mr-sm-2" for="inline-form-custom-select-pref">Status</label>
            <b-form-select
                    class="mb-2 mr-sm-2 mb-sm-0"
                    v-model="status"
                    :options="statuses"
                    id="inline-form-custom-select-pref"
            >
                <template v-slot:first>
                    <option :value="null">Choose...</option>
                </template>
            </b-form-select>

            <b-button variant="primary" v-if="status !== null" @click="createStatus">Save</b-button>
        </b-form>
        <br/><hr/><br/>
        
        <b-table :items="statusItems" :fields="fields">
            <template v-slot:cell(created_by)="data">
                {{data.item.created_by.data.first_name}} {{data.item.created_by.data.last_name}}
            </template>
        </b-table>
    </div>
</template>

<script>
    export default {
        name: "StatusChange",
        
        props: {
            file: {
                required: true,
                type: Object
            },
            statuses: {
                required: true,
                type: Array
            }
        },
        
        data() {
            return {
                status: null,
                fields: [
                    {key: 'status', label: 'Status Changed To'},
                    {key: 'created_at', label: 'On'},
                    {key: 'created_by', label: 'By'},
                    
                ]
            }
        },
        
        methods: {
            createStatus() {
                if(this.status !== null) {
                    this.$http.post('file/' + this.file.id + '/status', {
                        status: this.status
                    })
                    .then(response => {
                        this.$notify.success('Status change successful');
                        this.$emit('statusAdded', response.data);
                    })
                    .catch(error => this.$notify.alert('Could not change the status of the document'));
                }
            }
        },
        
        computed: {
            statusItems() {
                return this.file.statuses.sort((a, b) => {
                    a = new Date(a.created_at);
                    b = new Date(b.created_at);
                    return a>b ? -1 : a<b ? 1 : 0;
                });
            }
        }
    }
</script>

<style scoped>

</style>