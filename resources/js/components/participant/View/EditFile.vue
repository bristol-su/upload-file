<template>
    <div>
        <p-api-form :schema="form" @submit="update">

        </p-api-form>
        <div>
            <span>Uploaded By: {{file['uploaded by']}}</span>
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

export default {
    name: "EditFile",
    props: {
        file: {
            required: true,
            type: Object
        }
    },

    filters: {
        statusHistory(status) {
            return  'Changed to ' + status.status + ' by ' + status.created_by.data.first_name + ' ' + status.created_by.data.last_name + ' ' + moment(status.created_at).fromNow();
        }
    },

    methods: {
        update(data) {
            this.$http.put('file/' + this.file.id, data)
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
            return this.$tools.generator.form.newForm('Upload a new file')
                .withGroup(
                    this.$tools.generator.group.newGroup()
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
                )
                .generate()
                .asJson();
        }
    }
}
</script>

<style scoped>

</style>
