<template>
    <div style="padding-top: 20px;">
        <p-api-form :schema="form" v-if="audience.length > 0" @submit="submit" buttonText="Upload">

        </p-api-form>
        <div v-else>
            There is no-one you can upload a file on behalf of.
        </div>
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
                audience: []
            }
        },

        created() {
            this.loadAudience();
        },

        methods: {
            submit(data) {
                console.log(data);
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
                formData.append('activity_instance_id', data.activity_instance_id);
                this.$http.post('file', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(response => {
                        this.$notify.success('File uploaded!');
                        window.location.reload();
                    })
                    .catch(error => this.$notify.alert('There was a problem uploading your file: ' + error.message));
            },

            loadAudience() {
                this.$http.get('/activity-instance')
                    .then(response => this.audience = response.data)
                    .catch(error => this.$notify.alert('Could not load the audience: ' + error.message));
            }
        },

        computed: {
            allowedExtensionsText() {
                if(this.allowedExtensions.length === 0) {
                    return 'None';
                }
                return this.allowedExtensions.map(ext => '.' + ext).join(', ');
            },
            form() {
                let selectField = this.$tools.generator.field.select('activity_instance_id')
                    .label('Upload for')
                    .hint('Who is the document being uploaded on behalf of?')
                    .required(true);
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
                return this.$tools.generator.form.newForm('Upload a new file')
                    .withGroup(
                        this.$tools.generator.group.newGroup()
                            .withField(selectField)
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
            }
        }
    }
</script>

<style scoped>

</style>
