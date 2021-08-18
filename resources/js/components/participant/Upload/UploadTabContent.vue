<template>
    <div style="padding-top: 20px;">
        <p-api-form :schema="form" @submit="submit">

        </p-api-form>
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

    methods: {
        submit(data) {
            let formData = new FormData();
            if(data.file.length > 0) {
                for (let file of data.file) {
                    formData.append('file[]', file)
                }
            } else {
                formData.append('file[]', data.file[0]);
            }
            formData.append('title', data.title);
            formData.append('description', data.description);
            this.$http.post('file', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                .then(response => {
                    this.$notify.success('File uploaded!');
                    this.$emit('file-uploaded', response.data);
                    this.reset();
                })
                .catch(error => this.$notify.alert('There was a problem uploading your file: ' + error.message));

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
