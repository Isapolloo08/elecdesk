document.addEventListener("DOMContentLoaded", function () {
    // Handle posting comments
    document.querySelectorAll(".comment-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let commentInput = this.querySelector(".comment-input");
            let candidateId = this.getAttribute("data-candidate-id");
            let commentSection = document.getElementById(`comments-${candidateId}`);

            fetch("submit_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `candidate_id=${candidateId}&comment=${encodeURIComponent(commentInput.value)}`
            })
            .then(response => response.text())
            .then(data => {
                commentSection.innerHTML += data; // Append new comment
                commentInput.value = ""; // Clear input
            });
        });
    });

    // Handle posting replies
    document.querySelectorAll(".reply-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let replyInput = this.querySelector(".reply-input");
            let commentId = this.getAttribute("data-comment-id");
            let repliesContainer = this.previousElementSibling;

            fetch("post_reply.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `comment_id=${commentId}&reply=${encodeURIComponent(replyInput.value)}`
            })
            .then(response => response.text())
            .then(data => {
                repliesContainer.innerHTML += data; // Append new reply
                replyInput.value = ""; // Clear input
            });
        });
    });
});
