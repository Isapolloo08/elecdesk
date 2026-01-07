document.addEventListener("DOMContentLoaded", function () {
    const commentForm = document.getElementById("comment-form");
    const candidateId = document.getElementById("candidate-id")?.value; // Handle potential null value

    // ✅ POST Comment with SweetAlert
    if (commentForm) {
        commentForm.addEventListener("submit", function (e) {
            e.preventDefault();
            let commentText = document.getElementById("comment-input").value.trim();

            if (commentText === "") {
                Swal.fire({
                    icon: "warning",
                    title: "⚠️ Warning!",
                    text: "Comment cannot be empty.",
                    confirmButtonColor: "#3085d6",
                });
                return;
            }

            fetch("../api/add_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `candidate_id=${candidateId}&comment=${encodeURIComponent(commentText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "✅ Success!",
                        text: "Comment added!",
                        confirmButtonColor: "#3085d6",
                    }).then(() => {
                        // Append `comment_id` to the URL
                        window.location.href = `${window.location.pathname}?id=${candidateId}&comment_id=${data.comment_id}`;
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "❌ Error!",
                        text: data.message,
                        confirmButtonColor: "#d33",
                    });
                }
            })
            
            .catch(error => {
                console.error("Fetch error:", error);
                Swal.fire({
                    icon: "error",
                    title: "❌ Error!",
                    text: "Something went wrong. Please try again.",
                    confirmButtonColor: "#d33",
                });
            });
        });
    }

    // ✅ POST Reply with SweetAlert
    document.querySelectorAll(".reply-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let replyText = this.querySelector(".reply-input").value.trim();
            let commentId = this.getAttribute("data-comment-id");

            if (replyText === "") {
                Swal.fire({
                    icon: "warning",
                    title: "⚠️ Warning!",
                    text: "Reply cannot be empty.",
                    confirmButtonColor: "#3085d6",
                });
                return;
            }

            fetch("../api/post_reply.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `comment_id=${commentId}&reply=${encodeURIComponent(replyText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "✅ Success!",
                        text: "Reply added!",
                        confirmButtonColor: "#3085d6",
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "❌ Error!",
                        text: data.message,
                        confirmButtonColor: "#d33",
                    });
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                Swal.fire({
                    icon: "error",
                    title: "❌ Error!",
                    text: "Something went wrong. Please try again.",
                    confirmButtonColor: "#d33",
                });
            });
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const notifBell = document.getElementById("notif-bell");
    const notifDropdown = document.getElementById("notif-dropdown");
    const notifList = document.getElementById("notif-list");
    const notifCount = document.getElementById("notif-count");

    // Toggle dropdown visibility
    notifBell.addEventListener("click", function (event) {
        event.preventDefault();
        notifDropdown.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!notifBell.contains(event.target) && !notifDropdown.contains(event.target)) {
            notifDropdown.classList.remove("show");
        }
    });

    function fetchNotifications() {
        fetch("/api/fetch_notifications.php")
            .then(response => response.json())  
            .then(data => {
                if (data.status === "success" && Array.isArray(data.notifications)) {
                    notifList.innerHTML = "";
                    let count = data.notifications.length;

                    if (count === 0) {
                        notifList.innerHTML = "<p>No new notifications</p>";
                    } else {
                        data.notifications.forEach(notif => {
                            let candidateId = notif.candidate_id;
                            let commentId = notif.comment_id || "";
                            let replyId = notif.reply_id || "";
                            let targetId = replyId ? `reply-${replyId}` : `comment-${commentId}`;

                            let notifItem = document.createElement("div");
                            notifItem.classList.add("notif-item");

                            notifItem.innerHTML = `
                                <p>
                                    <a href="candidate.php?id=${candidateId}&comment=${commentId}&reply=${replyId}#${targetId}"
                                       class="notif-link"
                                       data-candidate="${candidateId}"
                                       data-comment="${commentId}"
                                       data-reply="${replyId}"
                                       data-target="${targetId}">
                                       ${notif.message}
                                    </a>
                                </p>
                                <small>${notif.created_at}</small>
                            `;

                            notifList.appendChild(notifItem);
                        });
                    }

                    notifCount.textContent = count;
                    notifCount.style.display = count > 0 ? "inline" : "none";
                }
            })
            .catch(error => console.error("Error fetching notifications:", error));
    }

    fetchNotifications();
    setInterval(fetchNotifications, 10000);
});

document.addEventListener("DOMContentLoaded", function () {
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    function highlightElement(element) {
        if (element) {
            element.classList.add("highlight");
            element.scrollIntoView({ behavior: "smooth", block: "center" });

            setTimeout(() => {
                element.classList.remove("highlight");
            }, 3000);
        }
    }

    function expandReplyContainer(commentId, replyId) {
        const replyContainer = document.getElementById(`replies-${commentId}`);
        const replyButton = document.querySelector(`.toggle-replies[data-comment-id="${commentId}"]`);

        if (replyContainer && replyButton) {
            const isHidden = replyContainer.style.display === "none" || window.getComputedStyle(replyContainer).display === "none";

            if (isHidden) {
                replyButton.click(); // Click the "View Replies" button to expand
                setTimeout(() => {
                    const replyElement = document.getElementById(`reply-${replyId}`);
                    highlightElement(replyElement);
                }, 500); // Highlight after expanding
            } else {
                const replyElement = document.getElementById(`reply-${replyId}`);
                highlightElement(replyElement);
            }
        }
    }

    function handleNotificationClick(event) {
        event.preventDefault();

        const replyId = this.getAttribute("data-reply");
        const commentId = this.getAttribute("data-comment");
        const candidateId = this.getAttribute("data-candidate");

        if (!commentId) return;

        const candidatePage = `/pages/candidate.php?id=${candidateId}`;
        const targetId = replyId ? `reply-${replyId}` : `comment-${commentId}`;

        if (!window.location.href.includes(candidatePage)) {
            window.location.href = `${candidatePage}#${targetId}`;
        } else if (replyId) {
            setTimeout(() => expandReplyContainer(commentId, replyId), 500);
        } else {
            highlightElement(document.getElementById(targetId));
        }
    }

    function attachReplyClickEvents() {
        document.querySelectorAll(".notif-link").forEach(link => {
            link.removeEventListener("click", handleNotificationClick);
            link.addEventListener("click", handleNotificationClick);
        });
    }

    attachReplyClickEvents();

    // Auto-expand reply when landing on a reply from a direct link
    const replyId = getQueryParam("reply_id");
    const commentId = getQueryParam("comment_id");

    if (replyId && commentId) {
        setTimeout(() => expandReplyContainer(commentId, replyId), 1000);
    }
});


    


// // View Replies Button Click Handling
// document.addEventListener("DOMContentLoaded", function () {
//     document.querySelectorAll(".toggle-replies").forEach(button => {
//         button.addEventListener("click", function () {
//             const commentId = this.dataset.commentId;
//             const repliesDiv = document.getElementById(`replies-${commentId}`);

//             if (repliesDiv.style.display === "none" || !repliesDiv.style.display) {
//                 repliesDiv.style.display = "block";
//                 this.textContent = "Hide Replies";
//             } else {
//                 repliesDiv.style.display = "none";
//                 this.textContent = `View Replies (${repliesDiv.children.length})`;
//             }
//         });
//     });
// });
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".replies-container").forEach(repliesDiv => {
        repliesDiv.style.display = "block"; // Show all replies by default
    });
});

    
    
function scrollToAndHighlight(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    // Check if the element is a reply and expand its parent container
    if (elementId.startsWith("reply-")) {
        const commentId = element.getAttribute("data-comment-id");
        toggleReplies(commentId, () => {
            setTimeout(() => {
                element.scrollIntoView({ behavior: "smooth", block: "center" });
                element.classList.add("highlight");
                setTimeout(() => element.classList.remove("highlight"), 3000);
            }, 500);
        });
    } else {
        // Directly scroll if it's not a reply
        setTimeout(() => {
            element.scrollIntoView({ behavior: "smooth", block: "center" });
            element.classList.add("highlight");
            setTimeout(() => element.classList.remove("highlight"), 3000);
        }, 500);
    }
}

function toggleReplies(commentId, callback) {
    const repliesContainer = document.getElementById(`replies-${commentId}`);

    if (repliesContainer) {
        repliesContainer.style.display = "block"; // Always show replies
        setTimeout(callback, 300); // Wait before scrolling
    } else {
        callback(); // If no replies, just scroll
    }
}

    


function attachReplyClickEvents() {
    document.querySelectorAll(".notif-link").forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();

            const replyId = this.getAttribute("data-reply");
            const commentId = this.getAttribute("data-comment");
            const candidateId = this.getAttribute("data-candidate");

            if (!commentId) return;

            const candidatePage = `/pages/candidate.php?id=${candidateId}`;
            const targetId = replyId ? `reply-${replyId}` : `comment-${commentId}`;

            if (!window.location.href.includes(candidatePage)) {
                window.location.href = `${candidatePage}#${targetId}`;
            } else {
                toggleReplies(commentId, () => {
                    scrollToAndHighlight(targetId);
                });
            }
        });
    });
}

// Attach click events for notifications
attachReplyClickEvents();
  
    
    

    // function expandRepliesAndHighlight(commentId, replyId) {
    //     console.log("Expanding comment:", commentId, "Reply ID:", replyId);
    
    //     const commentContainer = document.getElementById(`comment-${commentId}`);
    //     if (!commentContainer) return;
    
    //     const repliesContainer = document.getElementById(`replies-${commentId}`);
    //     const viewRepliesBtn = commentContainer.querySelector(".toggle-replies");
    
    //     function highlightReply() {
    //         setTimeout(() => {
    //             if (replyId) {
    //                 const specificReply = document.getElementById(`reply-${replyId}`);
    //                 if (specificReply) {
    //                     specificReply.scrollIntoView({ behavior: "smooth", block: "center" });
    
    //                     // Add highlight effect
    //                     specificReply.classList.add("highlight");
    //                     specificReply.style.display = "block"; // Ensure it's visible
    
    //                     setTimeout(() => specificReply.classList.remove("highlight"), 3000);
    //                 } else {
    //                     console.warn("Reply not found:", replyId);
    //                 }
    //             }
    //         }, 500);
    //     }
    
    //     if (repliesContainer) {
    //         // If replies are hidden, click the toggle button to show them
    //         if (repliesContainer.style.display === "none" || !repliesContainer.style.display) {
    //             if (viewRepliesBtn) {
    //                 viewRepliesBtn.click(); // Show replies
    
    //                 // Wait for replies to expand, then highlight
    //                 setTimeout(() => {
    //                     repliesContainer.style.display = "block";
    //                     highlightReply();
    //                 }, 500); // Adjust delay if needed
    //             }
    //         } else {
    //             highlightReply();
    //         }
    //     } else {
    //         console.warn("Replies container not found for comment:", commentId);
    //     }
    // }
    

    document.addEventListener("DOMContentLoaded", function () {
        function scrollToAndHighlight(elementId) {
            const element = document.getElementById(elementId);
            if (!element) return;
    
            // Check if the element is a reply and expand its parent container
            if (elementId.startsWith("reply-")) {
                const commentId = element.getAttribute("data-comment-id");
                toggleReplies(commentId, () => {
                    setTimeout(() => {
                        element.scrollIntoView({ behavior: "smooth", block: "center" });
                        element.classList.add("highlight");
                        setTimeout(() => element.classList.remove("highlight"), 3000);
                    }, 500);
                });
            } else {
                // Directly scroll if it's not a reply
                setTimeout(() => {
                    element.scrollIntoView({ behavior: "smooth", block: "center" });
                    element.classList.add("highlight");
                    setTimeout(() => element.classList.remove("highlight"), 3000);
                }, 500);
            }
        }
    
        function toggleReplies(commentId, callback) {
            const replyButton = document.querySelector(`.toggle-replies[data-comment-id="${commentId}"]`);
            const repliesContainer = document.getElementById(`replies-${commentId}`);
        
            if (repliesContainer && replyButton) {
                // Check if replies are already open
                const isClosed = repliesContainer.style.display === "none" || !repliesContainer.style.display;
        
                if (isClosed) {
                    replyButton.click(); // Expand replies
                    
                    // Wait for the replies to be fully displayed before scrolling
                    const checkVisibility = setInterval(() => {
                        if (repliesContainer.style.display !== "none") {
                            clearInterval(checkVisibility); // Stop checking
                            setTimeout(callback, 300); // Scroll after a short delay
                        }
                    }, 100);
                } else {
                    callback(); // If already open, scroll immediately
                }
            } else {
                callback(); // If no reply container, just scroll
            }
        }
            
        
    
        function attachReplyClickEvents() {
            document.querySelectorAll(".notif-link").forEach(link => {
                link.addEventListener("click", function (event) {
                    event.preventDefault();
        
                    const replyId = this.getAttribute("data-reply");
                    const commentId = this.getAttribute("data-comment");
                    const candidateId = this.getAttribute("data-candidate");
        
                    if (!commentId) return;
        
                    const candidatePage = `/pages/candidate.php?id=${candidateId}`;
                    const targetId = replyId ? `reply-${replyId}` : `comment-${commentId}`;
        
                    if (!window.location.href.includes(candidatePage)) {
                        window.location.href = `${candidatePage}#${targetId}`;
                    } else {
                        toggleReplies(commentId, () => {
                            scrollToAndHighlight(targetId);
                        });
                    }
                });
            });
        }
        
        // Attach click events for notifications
        attachReplyClickEvents();
    
        // Auto-scroll and highlight if page loads with a hash
        if (window.location.hash) {
            const targetId = window.location.hash.substring(1);
            setTimeout(() => scrollToAndHighlight(targetId), 500);
        }
    });
    



