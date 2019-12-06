<template>
    <div>
        <b-form>
            <b-form @submit.prevent="submit" @reset="reset">
                <b-form-group
                        id="title-label"
                        label="Title:"
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

                <b-form-file
                        v-model="file"
                        :state="Boolean(file)"
                        placeholder="Choose a file or drop it here..."
                        drop-placeholder="Drop file here..."
                ></b-form-file>
                
                <b-button type="submit" variant="primary">Upload</b-button>
                <b-button type="reset" variant="danger">Reset</b-button>
            </b-form>
        </b-form>
    </div>
</template>

<script>
    export default {
        name: "UploadTabContent",

        props: {},

        data() {
            return {
                title: "",
                file: null
            }
        },

        methods: {
            submit() {
                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('title', this.title);
                this.$http.post('file', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(response => {
                        this.$notify.success('File uploaded!');
                        this.$emit('file-uploaded', response.data);
                        this.reset();
                    })
                    .catch(error => this.$notify.alert('There was a problem uploading your file: ' + error.message));
                
            },
            
            reset() {
                this.title = "";
                this.file = null;
            }
        },

        computed: {}
    }
</script>

<style scoped>

</style>