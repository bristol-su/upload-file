<template>
    <div>
        <div v-if="comments.length > 0">
            <ul class="commentList">
                <li v-for="comment in comments" v-if="comments.length > 0">
                    <div class="commenterImage">
                        <img src="document-comment-avatar.png"/>
                    </div>
                    <div class="commentText">
                        <span class="commenterName">{{ comment.posted_by.data.first_name }} {{comment.posted_by.data.last_name}}</span> <span
                            class="date sub-text">{{ comment.created_at }}</span>
                        <p>{{comment.comment}}</p>

                    </div>
                </li>
            </ul>
        </div>
        <div v-else>
            No comments have been left.
        </div>
        
        <b-form @submit.prevent="postComment" inline>
            <label class="sr-only" for="comment">Comment: </label>
            <b-input
                    id="comment"
                    class="mb-2 mr-sm-2 mb-sm-0"
                    placeholder="Leave a comment..."
                    v-model="newComment"
            ></b-input>

            <b-button type="submit" variant="outline-success">Post Comment</b-button>

        </b-form>
    </div>
</template>

<script>
    export default {
        name: "Comments",

        props: {
            fileId: {
                required: false
            }
        },

        data() {
            return {
                comments: [],
                newComment: ''
            }
        },

        created() {
            this.loadComments();
        },
        
        methods: {
            loadComments() {
                this.$http.get('/file/' + this.fileId + '/comment')
                    .then(response => this.comments = response.data)
                    .catch(error => this.$notify.alert('Could not load comments'));
            },

            postComment() {
                this.$http.post('/file/' + this.fileId + '/comment', {comment: this.newComment})
                .then(response => {
                    this.comments.push(response.data);
                    this.newComment = '';
                })
                .catch(error => this.$notify.alert('Could not post the comment'));
            }
        },

        computed: {}
    }
</script>

<style scoped>

    .commentList {
        padding: 0;
        list-style: none;
        max-height: 200px;
        overflow: auto;
    }

    .commentList li {
        margin: 0;
        margin-top: 10px;
    }

    .commentList li > div {
        display: table-cell;
    }

    .commenterImage {
        width: 30px;
        margin-right: 5px;
        height: 100%;
        float: left;
    }

    .commenterImage.owner {
        float: right;
    }

    .commenterImage img {
        width: 100%;
        border-radius: 50%;
    }

    .commentText p {
        margin: 0;
    }

    .sub-text {
        color: #aaa;
        font-family: verdana;
        font-size: 11px;
    }

    .commenterName {

    }

</style>
