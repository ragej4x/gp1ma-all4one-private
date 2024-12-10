// Function to fetch new messages
function fetchMessages() {
    const groupId = "<?php echo $group_id; ?>"; // Get the group ID dynamically
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'group.php?group_id=' + groupId, true);
    xhr.onload = function() {
        if (this.status === 200) {
            const messages = JSON.parse(this.responseText);
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = ''; // Clear current chat

            messages.forEach(function(msg) {
                const messageHTML = `
                    <p>
                        <strong>${msg.username}:</strong> 
                        ${msg.message} 
                        <em>${msg.created_at}</em>
                    </p>`;
                chatBox.innerHTML += messageHTML;
            });

            // Scroll to the bottom of the chat
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    };
    xhr.send();
}

// Polling the server every 3 seconds for new messages
setInterval(fetchMessages, 3000);

// Initial fetch of messages on page load
fetchMessages();
