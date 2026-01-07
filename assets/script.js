function postComment(candidate_id) {
    let comment = document.getElementById("comment-input").value;
    if (comment.trim() === "") {
        alert("Enter a comment!");
        return;
    }

    fetch("api/add_comment.php", {
        method: "POST",
        body: new URLSearchParams({ candidate_id, comment }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(response => response.json())
      .then(data => {
          alert(data.message);
          fetchComments(candidate_id);
      });

    document.getElementById("comment-input").value = "";
}

function fetchComments(candidate_id) {
    fetch("api/get_comments.php?candidate_id=" + candidate_id)
        .then(response => response.json())
        .then(data => {
            let commentSection = document.getElementById("comments-section");
            commentSection.innerHTML = "";
            data.forEach(comment => {
                let div = document.createElement("div");
                div.textContent = comment.comment;
                commentSection.appendChild(div);
            });
        });
}
