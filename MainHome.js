const candidates = {
    1: { name: "John Doe", details: "Experienced leader with a vision for the future." },
    2: { name: "Jane Smith", details: "Passionate about student welfare and inclusivity." }
};

function showProfile(id) {
    document.getElementById("profile-section").classList.remove("hidden");
    document.getElementById("candidate-name").innerText = candidates[id].name;
    document.getElementById("candidate-details").innerText = candidates[id].details;
}

function closeProfile() {
    document.getElementById("profile-section").classList.add("hidden");
}

function postComment() {
    const commentInput = document.getElementById("comment-input");
    const commentText = commentInput.value.trim();

    if (commentText === "") {
        alert("Please write a comment!");
        return;
    }

    const commentSection = document.getElementById("comments-section");
    const commentDiv = document.createElement("div");
    commentDiv.innerText = commentText;
    commentSection.appendChild(commentDiv);
    
    commentInput.value = ""; 
}
