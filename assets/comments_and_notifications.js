document.addEventListener('DOMContentLoaded', function() {
    // Add highlight styles
    addHighlightStyles();
    
    // Elements
    const commentForm = document.getElementById('comment-form');
    const commentsContainer = document.querySelector('.comments-container');
    const notifDropdown = document.getElementById('notif-dropdown');
    const notifCount = document.getElementById('notif-count');
    const notifList = document.getElementById('notif-list');
    const deleteAllNotifBtn = document.getElementById('delete-all-notif');
    const notifBell = document.getElementById('notif-bell');
    
    // Load notifications on page load
    loadNotifications();
    
    // Set interval to check for new notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Check for hash in URL to highlight specific comment/reply
    highlightCommentOrReply();
    
    // Comment form submission
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const commentInput = document.getElementById('comment-input');
            const candidateId = document.getElementById('candidate-id').value;
            
            if (commentInput.value.trim()) {
                submitComment(candidateId, commentInput.value);
            }
        });
    }
    
    // Toggle reply forms
    document.querySelectorAll('.toggle-reply-form').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            replyForm.style.display = replyForm.style.display === 'none' || !replyForm.style.display ? 'block' : 'none';
        });
    });
    
    // Cancel reply
    document.querySelectorAll('.cancel-reply').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.reply-form').style.display = 'none';
        });
    });
    
    // Handle reply form submission
    document.querySelectorAll('.reply-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const commentId = this.getAttribute('data-comment-id');
            const replyInput = this.querySelector('.reply-input');
            
            if (replyInput.value.trim()) {
                submitReply(commentId, replyInput.value, this);
            }
        });
    });
    
    // Delete all notifications
    if (deleteAllNotifBtn) {
        deleteAllNotifBtn.addEventListener('click', function() {
            deleteAllNotifications();
        });
    }
    
    // Mark notifications as read when dropdown is opened
    if (notifBell) {
        notifBell.addEventListener('click', function() {
            setTimeout(() => {
                markAllNotificationsRead();
            }, 1000); // Slight delay to ensure dropdown is visible
        });
    }
    
    // Functions
    function submitComment(candidateId, comment) {
        const formData = new FormData();
        formData.append('candidate_id', candidateId);
        formData.append('comment', comment);
        
        fetch('../api/add_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Comment Posted!',
                    text: 'Your comment has been submitted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#1a73e8'
                }).then(() => {
                    document.getElementById('comment-input').value = '';
                    
                    // If there's a "no comments yet" message, remove it
                    const noCommentsMsg = commentsContainer.querySelector('.text-center.py-5');
                    if (noCommentsMsg) {
                        commentsContainer.innerHTML = '';
                    }
                    
                    // Add new comment to DOM
                    const newComment = createCommentElement(data.data);
                    commentsContainer.insertAdjacentHTML('afterbegin', newComment);
                    
                    // Add event listeners to new elements
                    const newToggleBtn = document.querySelector(`#comment-${data.data.id} .toggle-reply-form`);
                    const newReplyForm = document.getElementById(`reply-form-${data.data.id}`);
                    const newCancelBtn = document.querySelector(`#reply-form-${data.data.id} .cancel-reply`);
                    
                    if (newToggleBtn) {
                        newToggleBtn.addEventListener('click', function() {
                            const commentId = this.getAttribute('data-comment-id');
                            const replyForm = document.getElementById(`reply-form-${commentId}`);
                            replyForm.style.display = replyForm.style.display === 'none' || !replyForm.style.display ? 'block' : 'none';
                        });
                    }
                    
                    if (newCancelBtn) {
                        newCancelBtn.addEventListener('click', function() {
                            this.closest('.reply-form').style.display = 'none';
                        });
                    }
                    
                    if (newReplyForm) {
                        newReplyForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            const commentId = this.getAttribute('data-comment-id');
                            const replyInput = this.querySelector('.reply-input');
                            
                            if (replyInput.value.trim()) {
                                submitReply(commentId, replyInput.value, this);
                            }
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to post comment. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#1a73e8'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred. Please try again.',
                icon: 'error',
                confirmButtonColor: '#1a73e8'
            });
        });
    }
    
    function submitReply(commentId, reply, formElement) {
        const formData = new FormData();
        formData.append('comment_id', commentId);
        formData.append('reply', reply);
        
        fetch('../api/add_reply.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Reply Posted!',
                    text: 'Your reply has been submitted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#1a73e8'
                }).then(() => {
                    formElement.querySelector('.reply-input').value = '';
                    formElement.style.display = 'none';
                    
                    // Check if replies container exists, create if not
                    let repliesContainer = document.getElementById(`replies-${commentId}`);
                    if (!repliesContainer) {
                        const commentElement = document.getElementById(`comment-${commentId}`);
                        commentElement.insertAdjacentHTML('beforeend', `<div id="replies-${commentId}" class="replies"></div>`);
                        repliesContainer = document.getElementById(`replies-${commentId}`);
                    }
                    
                    // Add new reply to DOM
                    const newReply = createReplyElement(data.data);
                    repliesContainer.insertAdjacentHTML('beforeend', newReply);
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to post reply. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#1a73e8'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred. Please try again.',
                icon: 'error',
                confirmButtonColor: '#1a73e8'
            });
        });
    }
    
    function loadNotifications() {
        if (!notifList) return;
        
        fetch('../api/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Get user role from data response or from a hidden input field
                    const userRole = data.user_role || document.getElementById('user-role')?.value || 'student';
                    
                    // Update notification count
                    notifCount.textContent = data.unread_count;
                    
                    // Display notifications or "no notifications" message
                    if (data.data.length === 0) {
                        notifList.innerHTML = `
                            <div class="text-center p-3">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                <p>No notifications</p>
                            </div>
                        `;
                    } else {
                        let notifHtml = '';
                        data.data.forEach(notif => {
                            notifHtml += createNotificationElement(notif, userRole);
                        });
                        notifList.innerHTML = notifHtml;
                        
                        // Add event listeners for notification items
                        document.querySelectorAll('.notification-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const notifId = this.getAttribute('data-notif-id');
                                const link = this.getAttribute('data-link');
                                markNotificationRead(notifId);
                                if (link) {
                                    window.location.href = link;
                                }
                            });
                            
                            const deleteBtn = item.querySelector('.delete-notif');
                            if (deleteBtn) {
                                deleteBtn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    const notifId = this.getAttribute('data-notif-id');
                                    deleteNotification(notifId);
                                });
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
    }
    
    function markNotificationRead(notifId) {
        const formData = new FormData();
        formData.append('notification_id', notifId);
        
        fetch('../api/mark_notification_read.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update UI to mark notification as read
                const notifItem = document.querySelector(`.notification-item[data-notif-id="${notifId}"]`);
                if (notifItem) {
                    notifItem.classList.add('read');
                    const badge = notifItem.querySelector('.badge');
                    if (badge) badge.remove();
                }
                
                // Refresh notification count
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    
    function markAllNotificationsRead() {
        const formData = new FormData();
        formData.append('notification_id', 0); // 0 means all notifications
        
        fetch('../api/mark_notifications_read.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update UI to mark all notifications as read
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.add('read');
                    const badge = item.querySelector('.badge');
                    if (badge) badge.remove();
                });
                
                // Update notification count
                notifCount.textContent = '0';
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }
    
    function deleteNotification(notifId) {
        const formData = new FormData();
        formData.append('notification_id', notifId);
        
        fetch('../api/delete_notification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove notification from DOM
                const notifItem = document.querySelector(`.notification-item[data-notif-id="${notifId}"]`);
                if (notifItem) {
                    notifItem.remove();
                }
                
                // If no notifications left, show "no notifications" message
                if (notifList.children.length === 0) {
                    notifList.innerHTML = `
                        <div class="text-center p-3">
                            <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                            <p>No notifications</p>
                        </div>
                    `;
                }
                
                // Refresh notification count
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
    
    function deleteAllNotifications() {
        Swal.fire({
            title: 'Delete All Notifications?',
            text: 'Are you sure you want to delete all notifications? This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete all'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('notification_id', 0); // 0 means all notifications
                
                fetch('../api/delete_notifications.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Clear notifications from DOM
                        notifList.innerHTML = `
                            <div class="text-center p-3">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                <p>No notifications</p>
                            </div>
                        `;
                        
                        // Update notification count
                        notifCount.textContent = '0';
                        
                        Swal.fire(
                            'Deleted!',
                            'All notifications have been deleted.',
                            'success'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error deleting all notifications:', error);
                    Swal.fire(
                        'Error!',
                        'Failed to delete notifications. Please try again.',
                        'error'
                    );
                });
            }
        });
    }
    
    // Highlight comment or reply based on URL hash
    function highlightCommentOrReply() {
        if (window.location.hash) {
            const hash = window.location.hash.substring(1); // Remove the # character
            const element = document.getElementById(hash);
            
            if (element) {
                // Scroll to the element
                element.scrollIntoView({ behavior: 'smooth' });
                
                // Add highlight class
                element.classList.add('highlight-item');
                
                // Start pulsing animation
                startPulsingAnimation(element);
                
                // Remove highlight after 5 seconds
                setTimeout(() => {
                    element.classList.remove('highlight-item');
                    stopPulsingAnimation(element);
                }, 5000);
            }
        }
    }
    
    // Add pulsing animation to highlighted element
    function startPulsingAnimation(element) {
        // Add class for animation
        element.classList.add('pulsing-highlight');
    }
    
    // Stop pulsing animation
    function stopPulsingAnimation(element) {
        element.classList.remove('pulsing-highlight');
    }
    
    // Add highlight styles to document
    function addHighlightStyles() {
        // Create a style element
        const style = document.createElement('style');
        style.textContent = `
            .highlight-item {
                background-color: #FFFF00 !important; /* Brighter yellow */
                border: 2px solid #FFA500 !important; /* Orange border */
                border-radius: 6px !important;
                box-shadow: 0 0 15px rgba(255, 165, 0, 0.7) !important; /* Stronger orange glow */
                position: relative;
                z-index: 1;
            }
            
            @keyframes pulse-highlight {
                0% { box-shadow: 0 0 15px rgba(255, 165, 0, 0.7); }
                50% { box-shadow: 0 0 25px rgba(255, 165, 0, 0.9); }
                100% { box-shadow: 0 0 15px rgba(255, 165, 0, 0.7); }
            }
            
            .pulsing-highlight {
                animation: pulse-highlight 1s infinite ease-in-out;
            }
        `;
        // Append the style to the document head
        document.head.appendChild(style);
    }
    
    // Helper functions to create HTML elements
    function createCommentElement(comment) {
        return `
            <div class="comment" id="comment-${comment.id}">
                <div class="comment-header">
                    <div class="comment-avatar">
                        ${comment.username.substr(0, 1)}
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">${escapeHtml(comment.username)}</h5>
                        <small class="text-muted">${comment.created_at}</small>
                    </div>
                </div>
                <div class="comment-body">
                    <p>${escapeHtml(comment.comment)}</p>
                </div>
                
                <div class="comment-actions mt-2">
                    <button class="btn btn-sm btn-outline-primary toggle-reply-form" data-comment-id="${comment.id}">
                        <i class="fas fa-reply me-1"></i> Reply
                    </button>
                </div>
                
                <!-- Reply Form -->
                <form class="reply-form" id="reply-form-${comment.id}" data-comment-id="${comment.id}" style="display: none;">
                    <div class="mb-3">
                        <textarea class="form-control reply-input" name="reply" rows="2" placeholder="Write a reply..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-secondary cancel-reply">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Reply
                        </button>
                    </div>
                </form>
            </div>
        `;
    }
    
    function createReplyElement(reply) {
        return `
            <div class="reply" id="reply-${reply.id}">
                <div class="comment-header">
                    <div class="comment-avatar" style="background-color: var(--secondary-blue);">
                        ${reply.username.substr(0, 1)}
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">${escapeHtml(reply.username)}</h6>
                        <small class="text-muted">${reply.created_at}</small>
                    </div>
                </div>
                <div class="comment-body">
                    <p>${escapeHtml(reply.reply)}</p>
                </div>
            </div>
        `;
    }
    
    function createNotificationElement(notif, userRole = 'student') {
        let message = escapeHtml(notif.message);
        let link = '';
    
        // Determine the link based on notification type and user role
        if (notif.comment_id) {
            if (notif.candidate_id) {
                // Set appropriate links based on user role
                if (userRole === 'student') {
                    link = `./candidate_user.php?id=${notif.candidate_id}#comment-${notif.comment_id}`;
                } else if (userRole === 'candidate') {
                    link = `../page/candidate.php?id=${notif.candidate_id}#comment-${notif.comment_id}`;
                } else if (userRole === 'admin') {
                    link = `./manage_comments.php?candidate_id=${notif.candidate_id}#comment-${notif.comment_id}`;
                }
            }
        }
        
        // If it's a reply notification, link to the reply
        if (notif.reply_id) {
            const targetId = notif.reply_id ? `reply-${notif.reply_id}` : `comment-${notif.comment_id}`;
            
            if (userRole === 'student') {
                link = `./candidate_user.php?id=${notif.candidate_id}#${targetId}`;
            } else if (userRole === 'candidate') {
                link = `../pages/candidate.php?id=${notif.candidate_id}#${targetId}`;
            } else if (userRole === 'admin') {
                link = `./manage_comments.php?candidate_id=${notif.candidate_id}#${targetId}`;
            }
        }
    
        // Format notification message
        if (notif.source_username) {
            message = message.replace('New comment', `<strong>${escapeHtml(notif.source_username)}</strong> commented`);
            message = message.replace('New reply', `<strong>${escapeHtml(notif.source_username)}</strong> replied`);
        }
    
        // Add badge for unread notifications
        const unreadBadge = notif.is_read === '0' ? `<span class="badge bg-primary">New</span>` : '';
    
        return `
            <div class="notification-item ${notif.is_read === '0' ? 'unread' : 'read'}" data-notif-id="${notif.id}" data-link="${link}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0">${message}</p>
                        <small class="text-muted">${notif.created_at}</small>
                    </div>
                    ${unreadBadge}
                    <button class="btn btn-sm btn-link text-danger delete-notif" data-notif-id="${notif.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }
    
    // Helper function to escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return str.toString()
                  .replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
});