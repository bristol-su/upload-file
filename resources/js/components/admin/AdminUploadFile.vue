<template>
    <div style="padding-top: 20px;">
        <div v-if="$isLoading('getting-activity-instances')" class="text-center">
            Loading
        </div>
        <p-form-padding v-else-if="audience.length > 0">
            <p-api-form ref="form" :schema="form" @submit="submit" buttonText="Upload" :busy="$isLoading('uploading-file')" busy-text="Uploading File">

            </p-api-form>
        </p-form-padding>
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
            this.$http.post('file', formData, {headers: {'Content-Type': 'multipart/form-data'}, name: 'uploading-file'})
                .then(response => {
                    this.$emit('newFiles', response.data)
                    this.$notify.success('File uploaded!');
                    this.$refs.form.reset();
                })
                .catch(error => this.$notify.alert('There was a problem uploading your file: ' + error.message));
        },

        loadAudience() {
            this.$http.get('/activity-instance', {name: 'getting-activity-instances'})
                .then(response => this.audience = response.data)
                .catch(error => this.$notify.alert('Could not load the audience: ' + error.message));
        },
        calculateNameFromAudience(a) {
            if(a.resource_type === 'user') {
                return a.participant.data.first_name + ' ' + a.participant.data.last_name;
            }
            if(a.resource_type === 'group') {
                return a.participant.data.name;
            }
            if(a.resource_type === 'role') {
                return a.participant.data.role_name;
            }
            return null;
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
            this.audience.sort((a, b) => {
                let nameA = this.calculateNameFromAudience(a);
                let nameB = this.calculateNameFromAudience(b);
                return (nameA < nameB) ? -1 : ((nameA > nameB) ? 1 : 0);
            }).forEach(a => selectField.withOption(a.id, this.calculateNameFromAudience(a) + ' (' + a.name + ')'))

            // this.audience.forEach(a => selectField.withOption(a))
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
                                .label('The file' + (this.multipleFiles ? 's' : '') + ' to upload')
                                .required(true)
                                .multiple(this.multipleFiles)
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
