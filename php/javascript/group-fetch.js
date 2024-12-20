function fetchMessages() {
    const groupId = "<?php echo $group_id; ?>"; 
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'group.php?group_id=' + groupId, true);
    xhr.onload = function() {
        if (this.status === 200) {
            const messages = JSON.parse(this.responseText);
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = ''; 

            messages.forEach(function(msg) {
                const messageHTML = `
                    <p>
                        <strong>${msg.username}:</strong> 
                        ${msg.message} 
                        <em>${msg.created_at}</em>
                    </p>`;
                chatBox.innerHTML += messageHTML;
            });

            chatBox.scrollTop = chatBox.scrollHeight;
        }
    };
    xhr.send();
}

setInterval(fetchMessages, 3000);

fetchMessages();
