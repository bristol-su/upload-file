<template>
    <div>
        <b-table :fields="fields" :items="processedFiles">
            <template v-slot:cell(download)="data">
                <a :href="downloadUrl(data.item.id)">
                    <b-button variant="secondary">Download</b-button>
                </a>
            </template>                
        </b-table>
    </div>
</template>

<script>
    export default {
        name: "ViewTabContent",

        props: {
            files: {
                type: Array,
                default: function() {
                    return [];
                }
            },
            download: {
                required: true,
                type: Boolean,
                default: false
            }
        },

        methods: {
            presentSize(size) {
                let i = size == 0 ? 0 : Math.floor( Math.log(size) / Math.log(1024) );
                return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
            },
            
            presentUploadedBy(user) {
                return user.forename + ' ' + user.surname;
            },
            
            downloadUrl(id) {
                return this.$url + '/files/' + id + '/download';
            }
            
        },

        computed: {
            processedFiles() {
                return this.files.map(file => {
                    file.size = this.presentSize(file.size);
                    file.uploaded_by = this.presentUploadedBy(file.uploaded_by);
                    return file;                    
                })
            },
            fields() {
                let fields = ['title', 'size', 'uploaded_by', 'created_at'];
                if(this.download) {
                    fields.push('download');
                }
                return fields;
            }
        }
    }
</script>

<style scoped>

</style>