<template>
    <div>
        <b-form @reset.prevent="loadFile" @submit.prevent="update" v-if="file !== null">
            <b-form-group
                    id="for-label"
                    label-for="on-behalf-of"
                    description="Who is the document being uploaded on behalf of?"
            >
                <audience id="on-behalf-of" v-model="file.activity_instance_id"></audience>

            </b-form-group>
            
            <b-form-group
                    description="A name for the file."
                    id="title-label"
                    label="Title:"
                    label-for="title"
            >
                <b-form-input
                        id="title"
                        placeholder="File Name"
                        required
                        type="text"
                        v-model="file.title"
                ></b-form-input>
            </b-form-group>

            <b-form-group
                    description="A description of the file."
                    id="description-label"
                    label="Description:"
                    label-for="description"
            >
                <b-form-textarea
                        id="description"
                        placeholder="File Description"
                        required
                        rows="4"
                        v-model="file.description"
                ></b-form-textarea>
            </b-form-group>

            <div>
                <span>Uploaded By: {{file.uploaded_by.data.first_name}} {{file.uploaded_by.data.last_name}}</span>
            </div>

            <br/>
            <div>
                <span>Status: {{file.status}}</span>
            </div>
            <br/>

            <b-list-group v-if="file.statuses.length > 0">
                <b-list-group-item
                        :key="status.id"
                        @mouseenter="hover=status.id"
                        @mouseleave="hover = null"
                        v-for="status in file.statuses"
                >
                    {{statusText(status)}}
                </b-list-group-item>
            </b-list-group>
            <div style="text-align: right;">
                <b-button type="submit" variant="primary">Submit</b-button>
                <b-button type="reset" variant="danger">Reset</b-button>
            </div>

        </b-form>
        <div v-else>
            Loading...
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    import Audience from './Audience';

    export default {
        name: "EditFile",
        components: {Audience},
        props: {
            fileId: {
                required: true,
                type: Number
            }
        },

        data() {
            return {
                file: null,
                hover: null
            }
        },

        created() {
            this.loadFile();
        },

        methods: {
            loadFile() {
                this.$http.get('file/' + this.fileId)
                    .then(response => this.file = response.data)
                    .catch(error => this.$notify.alert('Could not load the file:' + error.message));
            },

            statusText(status) {
                let text = 'Changed to ' + status.status + ' by ' + status.created_by.data.first_name + ' ' + status.created_by.data.last_name;
                if (this.hover === status.id) {
                    text = text + ' on the ' + moment(status.created_at).format('lll');
                } else {
                    text = text + ' ' + moment(status.created_at).fromNow();
                }
                return text;
            },

            update() {
                this.$http.put('file/' + this.file.id, {
                    title: this.file.title, description: this.file.description, activity_instance_id: this.file.activity_instance_id
                })
                    .then(response => {
                        this.$notify.success('File updated');
                        this.$emit('fileUpdated', response.data);
                        window.location.reload();
                    })
                    .catch(error => this.$notify.alert('File could not be updated: ' + error.message));
            }
        },

        computed: {}
    }
</script>

<style scoped>

</style>