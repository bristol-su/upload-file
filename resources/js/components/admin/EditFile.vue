<template>
    <div>
        <p-api-form :schema="form" v-if="audience.length > 0" @submit="update" :busy="$isLoading('updating-file-' + file.id)" busy-text="Updating File">

        </p-api-form>
        <div v-else>Loading</div>

        <div>
            <span>Uploaded By: {{file.uploaded_by.data.first_name}} {{file.uploaded_by.data.last_name}}</span>
        </div>

        <br/>
        <div>
            <span>Status: {{file.status}}</span>
        </div>
        <br/>

        <ul>
            <li v-for="status in file.statuses">{{status | statusHistory}}</li>
        </ul>
    </div>
</template>

<script>
import moment from 'moment';
import Audience from './Audience';

export default {
    name: "EditFile",
    components: {Audience},
    props: {
        file: {
            required: true,
            type: Object
        }
    },

    data() {
        return {
            audience: []
        }
    },

    created() {
        this.loadAudience();
    },

    filters: {
        statusHistory(status) {
            return 'Changed to ' + status.status + ' by ' + status.created_by.data.first_name + ' ' + status.created_by.data.last_name + ' ' + moment(status.created_at).fromNow();
        }
    },

    methods: {
        loadAudience() {
            this.$http.get('/activity-instance')
                .then(response => this.audience = response.data)
                .catch(error => this.$notify.alert('Could not load the audience: ' + error.message));
        },

        update(data) {
            this.$http.put('file/' + this.file.id, data, {name: 'updating-file-' + this.file.id})
                .then(response => {
                    this.$notify.success('File updated');
                    let updatedFile = Object.assign({}, this.file);
                    updatedFile.title = response.data.title;
                    updatedFile.description = response.data.description;
                    updatedFile.activity_instance_id = response.data.activity_instance_id;
                    this.$emit('fileUpdated', updatedFile);
                })
                .catch(error => this.$notify.alert('File could not be updated: ' + error.message));
        }
    },

    computed: {
        form() {
            let selectField = this.$tools.generator.field.select('activity_instance_id')
                .label('Upload for')
                .hint('Who is the document being uploaded on behalf of?')
                .required(true)
                .value(this.file.activity_instance.id);
            this.audience.forEach(a => {
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

                selectField.withOption(a.id, label);
            })

            this.audience.forEach(a => selectField.withOption(a))
            return this.$tools.generator.form.newForm('Edit File')
                .withGroup(
                    this.$tools.generator.group.newGroup()
                        .withField(selectField)
                        .withField(
                            this.$tools.generator.field.text('title')
                                .label('Name of the document')
                                .required(true)
                                .value(this.file.title)
                        )
                        .withField(
                            this.$tools.generator.field.text('description')
                                .label('A description for the document')
                                .required(false)
                                .value(this.file.description)
                        )
                );
        }
    }
}
</script>

<style scoped>

</style>
