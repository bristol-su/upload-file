<template>
    <div style="padding-top: 20px;">
        <b-form>
            <b-form @submit.prevent="submit" @reset="reset">
                <b-form-group
                        id="for-label"
                        label-for="on-behalf-of"
                        description="Who is the document being uploaded on behalf of?"
                >
                    <audience id="on-behalf-of" v-model="activity_instance_id"></audience>

                </b-form-group>
                
                <b-form-group
                        id="title-label"
                        label-for="title"
                        description="Name of the document."
                >
                    <b-form-input
                            id="title"
                            v-model="title"
                            type="text"
                            required
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                        id="description-label"
                        label-for="title"
                        description="Description of the document."
                >
                    <b-form-input
                            id="description"
                            v-model="description"
                            type="text"
                    ></b-form-input>
                </b-form-group>

                <b-form-group
                        id="file-label"
                        label-for="file"
                        :description="'You can upload files of the type: ' + allowedExtensionsText"
                >
                    <b-form-file
                            v-model="file"
                            id="file"
                            :state="Boolean(file)"
                            :placeholder="'Choose a file or drop it here' + (multipleFiles?'. You can select multiple files at the same time.':'')"
                            drop-placeholder="Drop file here..."
                            :multiple="multipleFiles"
                    />
                </b-form-group>

                <b-button type="submit" variant="primary">Upload</b-button>
                <b-button type="reset" variant="danger">Reset</b-button>
            </b-form>
        </b-form>
    </div>
</template>

<script>
    import Audience from './Audience';
    export default {
        name: "AdminUploadFile",
        components: {Audience},
        props: {
            defaultDocumentTitle: {
                required: false,
                default: '',
                type: String
            },
            multipleFiles: {
                required: false,
                default: true,
                type: Boolean
            },
            allowedExtensions: {
                required: false,
                type: Array,
                default: function() {
                    return [];
                }
            }
        },

        data() {
            return {
                title: "",
                file: null,
                description: '',
                activity_instance_id: null
            }
        },

        created() {
            this.title = this.defaultDocumentTitle
        },

        methods: {
            submit() {
                let formData = new FormData();
                if(Array.isArray(this.file)) {
                    for(let file of this.file) {
                        formData.append('file[]', file)
                    }
                } else {
                    formData.append('file[]', this.file);
                }
                formData.append('title', this.title);
                formData.append('description', this.description);
                formData.append('activity_instance_id', this.activity_instance_id);
                this.$http.post('file', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(response => {
                        this.$notify.success('File uploaded!');
                        this.reset();
                        window.location.reload();
                    })
                    .catch(error => this.$notify.alert('There was a problem uploading your file: ' + error.message));

            },

            reset() {
                this.title = this.defaultDocumentTitle;
                this.file = null;
            }
        },

        computed: {
            allowedExtensionsText() {
                if(this.allowedExtensions.length === 0) {
                    return 'None';
                }
                return this.allowedExtensions.map(ext => '.' + ext).join(', ');
            }
        }
    }
</script>

<style scoped>

</style>