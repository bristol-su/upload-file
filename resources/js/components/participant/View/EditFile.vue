<template>
    <div>
        <b-form @reset.prevent="loadFile" @submit.prevent="update" v-if="file !== null">
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
                <span>Uploaded By: {{file.uploaded_by.forename}} {{file.uploaded_by.surname}}</span>
            </div>
            <br/>
            <div>
                <span>File: <a :href="downloadUrl(file.id)">{{file.filename}}</a></span>
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
            <b-button type="submit" variant="primary">Submit</b-button>
            <b-button type="reset" variant="danger">Reset</b-button>
        </b-form>
        <div v-else>
            Loading...
        </div>
    </div>
</template>

<script>
    import moment from 'moment';

    export default {
        name: "EditFile",

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

            downloadUrl(id) {
                return this.$url + '/file/' + id + '/download';
            },

            statusText(status) {
                let text = 'Changed to ' + status.status + ' by ' + status.created_by.forename + ' ' + status.created_by.surname;
                if (this.hover === status.id) {
                    text = text + ' on the ' + moment(status.created_at).format('lll');
                } else {
                    text = text + ' ' + moment(status.created_at).fromNow();
                }
                return text;
            },

            update() {
                this.$http.put('file/' + this.file.id, {
                    title: this.file.title, description: this.file.description
                })
                .then(response => {
                    this.$notify.success('File updated');
                    this.$emit('fileUpdated', response.data);
                })
                .catch(error => this.$notify.alert('File could not be updated: ' + error.message));
            }
        },

        computed: {}
    }
</script>

<style scoped>

</style>