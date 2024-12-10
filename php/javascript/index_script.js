let currentChatId = null;
let isGroupChat = false;

function showChat(chatType) {
    document.getElementById('privateChat').classList.remove('active');
    document.getElementById('groupChat').classList.remove('active');
    document.getElementById('privateTab').classList.remove('active');
    document.getElementById('groupTab').classList.remove('active');

    if (chatType === 'privateChat') {
        document.getElementById('privateChat').classList.add('active');
        document.getElementById('privateTab').classList.add('active');
        isGroupChat = false;
        document.getElementById('chat-header').innerText = 'Select a friend to chat with!';
    } else {
        document.getElementById('groupChat').classList.add('active');
        document.getElementById('groupTab').classList.add('active');
        isGroupChat = true;
        document.getElementById('chat-header').innerText = 'Select a group to chat with!';
    }

    document.getElementById('chat-box').innerHTML = ''; // Clear chat box
    currentChatId = null; // Reset chat ID
}

function startChat(userId, username) {
    currentChatId = userId;
    document.getElementById('chat-header').innerText = `Chat with ${username}`;
    fetchMessages();
}

function startGroupChat(groupId, groupName) {
    currentChatId = groupId;
    document.getElementById('chat-header').innerText = `Chat in ${groupName}`;
    fetchGroupMessages();
}

function sendMessage(event) {
    event.preventDefault();
    const message = document.getElementById('message-input').value;

    if (isGroupChat) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'send_group_message', group_id: currentChatId, message: message })
        }).then(response => response.json()).then(data => {
            if (data.status === 'success') {
                document.getElementById('message-input').value = '';
                fetchGroupMessages();
            } else {
                alert(data.message);
            }
        });
    } else {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'send_message', user_id: currentChatId, message: message })
        }).then(response => response.json()).then(data => {
            if (data.status === 'success') {
                document.getElementById('message-input').value = '';
                fetchMessages();
            } else {
                alert(data.message);
            }
        });
    }
}

function fetchMessages() {
    if (currentChatId) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'fetch_messages', user_id: currentChatId })
        }).then(response => response.json()).then(messages => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = '';
            messages.forEach(msg => {
                const messageElement = document.createElement('p');
                messageElement.innerHTML = `<span class="sender">${msg.sender_id == <?= $user_id ?> ? 'You' : 'Friend'}</span>: ${msg.message} <span class="timestamp">${new Date(msg.timestamp).toLocaleTimeString()}</span>`;
                chatBox.appendChild(messageElement);
            });
            chatBox.scrollTop = chatBox.scrollHeight; // Scroll to the bottom
        });
    }
}

function fetchGroupMessages() {
    if (currentChatId) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'fetch_group_messages', group_id: currentChatId })
        }).then(response => response.json()).then(messages => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = '';
            messages.forEach(msg => {
                const messageElement = document.createElement('p');
                messageElement.innerHTML = `<span class="sender">${msg.sender_username}: </span>${msg.message} <span class="timestamp">${new Date(msg.created_at).toLocaleTimeString()}</span>`;
                chatBox.appendChild(messageElement);
            });
            chatBox.scrollTop = chatBox.scrollHeight; // Scroll to the bottom
        });
    }
}
