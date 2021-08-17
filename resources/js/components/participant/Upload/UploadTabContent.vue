<template>
    <div style="padding-top: 20px;">
        <p-submit-form :schema="form">

        </p-submit-form>
    </div>
</template>

<script>
export default {
    name: "UploadTabContent",

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
            default: function () {
                return [];
            }
        }
    },

    data() {
        return {
            formData: {
                title: '',
                file: null,
                description: ''
            }
        }
    },

    methods: {
        submit() {
            let formData = new FormData();
            if (Array.isArray(this.file)) {
                for (let file of this.file) {
                    formData.append('file[]', file)
                }
            } else {
                formData.append('file[]', this.file);
            }
            formData.append('title', this.title);
            formData.append('description', this.description);
            this.$http.post('file', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                .then(response => {
                    this.$notify.success('File uploaded!');
                    this.$emit('file-uploaded', response.data);
                    this.reset();
                })
                .catch(error => this.$notify.alert('There was a problem uploading your file: ' + error.message));

        },

        reset() {
            this.title = this.defaultDocumentTitle;
            this.file = null;
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
                                .value(this.defaultDocumentTitle)
                        )
                        .withField(
                            this.$tools.generator.field.text('description')
                                .label('A description for the document')
                                .required(false)
                        )
                        .withField(
                            this.$tools.generator.field.file('file')
                                .label('The file(s) to upload')
                                .required(true)
                                .hint('You can upload files of the type ' + this.allowedExtensionsText)
                        )
                )
                .generate()
                .asJson();
        },
        allowedExtensionsText() {
            if (this.allowedExtensions.length === 0) {
                return 'None';
            }
            return this.allowedExtensions.map(ext => '.' + ext).join(', ');
        }
    }
}
</script>

<style scoped>

</style>
