<template>
    <div>
        <p-api-form @submit="createStatus" :schema="form" :busy="$isLoading('updating-status-file-' + file.id)" busy-text="Updating Status">

        </p-api-form>
        <br/><hr/><br/>

        <p-table :items="statusItems" :columns="fields"></p-table>
    </div>
</template>

<script>
    import moment from 'moment';

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
                fields: ['Status Changed To', 'On', 'By']
            }
        },

        methods: {
            createStatus(data) {
                this.$http.post('file/' + this.file.id + '/status', {
                    status: data.status
                }, {name: 'updating-status-file-' + file.id})
                .then(response => {
                    this.$notify.success('Status change successful');
                    this.$emit('statusAdded', response.data);
                })
                .catch(error => this.$notify.alert('Could not change the status of the document'));
            }
        },

        computed: {
            statusItems() {
                return this.file.statuses.sort((a, b) => {
                    a = new Date(a.created_at);
                    b = new Date(b.created_at);
                    return a>b ? -1 : a<b ? 1 : 0;
                }).map(status => {
                    return {
                        'Status Changed To': status.status,
                        'On': moment(status.created_at).format('lll'),
                        'By': status.created_by.data.first_name + ' ' + status.created_by.data.last_name
                    }
                });
            },
            form() {
                let selectField = this.$tools.generator.field.select('status')
                    .label('New Status')
                    .hint('Choose a new status for the file')
                    .required(true)

                this.statuses.forEach(s => selectField.withOption(s, s))

                return this.$tools.generator.form.newForm()
                    .withGroup(this.$tools.generator.group.newGroup()
                        .withField(selectField)
                    );
            }
        }
    }
</script>

<style scoped>

</style>
