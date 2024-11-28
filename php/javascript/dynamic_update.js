// dyanamic update need pa i optmize
function loadFriends() {

    fetch('friend_dashboard.php?action=get_friends')

        .then(response => response.text())
        .then(text => {

            console.log('respo:', text); 

            const data = JSON.parse(text); 

            const friendList = document.querySelector('.friend-list:nth-of-type(1) ul');
            friendList.innerHTML = '';
            if (data.friends.length > 0) {
                data.friends.forEach(friend => {
                    const li = document.createElement('li');
                    li.innerHTML = `<a href="private_chat.php?user_id=${friend.id}">${friend.username}</a>`;
                    friendList.appendChild(li);
                });
            } 
            else {
                friendList.innerHTML = '<li>You have no friends yet.</li>';
            }
        })
        .catch(error => console.error('Error loading friends:', error));
}

//=
function loadPendingRequests() {

    fetch('friend_dashboard.php?action=get_pending_requests')
        .then(response => response.text())

        .then(text => {


            console.log('Raw response for pending requests:', text); 

            const data = JSON.parse(text);
            
            const pendingRequestList = document.querySelector('.friend-list:nth-of-type(2) ul');
            
            pendingRequestList.innerHTML = ''; 
            //
            if (data.pending_requests.length > 0) {
                data.pending_requests.forEach(request => {
                    const li = document.createElement('li');
                    li.innerHTML = `${request.username} 
                        <a href="javascript:void(0);" onclick="handleRequest('${request.sender_id}', 'accept')">[Accept]</a>
                        <a href="javascript:void(0);" onclick="handleRequest('${request.sender_id}', 'decline')">[Decline]</a>`;
                    pendingRequestList.appendChild(li);
                });
            } 
            else {
                pendingRequestList.innerHTML = '<li>No pending friend requests.</li>';
            }
        })
        .catch(error => console.error('Error loading pending requests:', error));
}
