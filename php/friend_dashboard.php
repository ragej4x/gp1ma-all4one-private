<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Add Friend by Username via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'add_by_username' && isset($_POST['add_friend_username'])) {
    $username_to_add = trim($_POST['add_friend_username']);

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username_to_add]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($friend) {
        $friend_id = $friend['id'];

        // Check if a friendship already exists
        $stmt = $pdo->prepare("SELECT * FROM friends WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
        $friendship = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$friendship) {
            // Send friend request
            $stmt = $pdo->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $friend_id]);
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    echo json_encode(['status' => 'fail']);
    exit;
}

// Handle Accept or Decline Friend Requests
if (isset($_POST['action']) && isset($_POST['friend_id'])) {
    $action = $_POST['action'];
    $friend_id = $_POST['friend_id'];

    switch ($action) {
        case 'accept':
            $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$friend_id, $user_id]);
            echo json_encode(['status' => 'accepted']);
            break;
        case 'decline':
            $stmt = $pdo->prepare("DELETE FROM friends WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$friend_id, $user_id]);
            echo json_encode(['status' => 'declined']);
            break;
    }
    exit;
}

// Handle fetching updates (friends list and pending requests)
if (isset($_POST['action']) && $_POST['action'] == 'fetch_updates') {
    // Fetch confirmed friends
    $stmt = $pdo->prepare("SELECT users.id, users.username FROM friends 
                           JOIN users ON (friends.sender_id = users.id OR friends.receiver_id = users.id) 
                           WHERE (friends.sender_id = ? OR friends.receiver_id = ?) 
                             AND friends.status = 'accepted'
                             AND users.id != ?");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch incoming friend requests
    $stmt = $pdo->prepare("SELECT sender_id, users.username FROM friends 
                           JOIN users ON users.id = friends.sender_id
                           WHERE friends.receiver_id = ? AND friends.status = 'pending'");
    $stmt->execute([$user_id]);
    $incoming_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'friends' => $friends,
        'incoming_requests' => $incoming_requests
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Friend Dashboard</title>
    <style>
        .friend-list, .user-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }
        .friend-list li, .user-list li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Your Friends</h2>
    <div id="friend-list" class="friend-list">
        <ul></ul>
    </div>

    <h2>Add Friend by Username</h2>
    <form id="add-friend-form">
        <input type="text" id="add_friend_username" name="add_friend_username" placeholder="Enter username" required>
        <button type="submit">Add Friend</button>
    </form>
    <p id="add-status"></p>

    <h2>Pending Friend Requests</h2>
    <div id="pending-requests" class="friend-list">
        <ul></ul>
    </div>

    <script>
        // Function to update the friend list and pending requests dynamically
        function fetchUpdates() {
            const formData = new FormData();
            formData.append('action', 'fetch_updates');

            fetch('friend_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Update friends list
                const friendList = document.getElementById('friend-list').querySelector('ul');
                friendList.innerHTML = ''; // Clear existing list
                if (data.friends.length > 0) {
                    data.friends.forEach(friend => {
                        friendList.innerHTML += `<li>${friend.username}</li>`;
                    });
                } else {
                    friendList.innerHTML = '<li>No friends yet.</li>';
                }

                // Update pending friend requests
                const pendingList = document.getElementById('pending-requests').querySelector('ul');
                pendingList.innerHTML = ''; // Clear existing list
                if (data.incoming_requests.length > 0) {
                    data.incoming_requests.forEach(request => {
                        pendingList.innerHTML += `
                            <li>${request.username}
                                <button class="accept" data-id="${request.sender_id}">Accept</button>
                                <button class="decline" data-id="${request.sender_id}">Decline</button>
                            </li>`;
                    });
                } else {
                    pendingList.innerHTML = '<li>No pending friend requests.</li>';
                }

                // Add event listeners for accept/decline buttons
                document.querySelectorAll('.accept, .decline').forEach(button => {
                    button.addEventListener('click', handleFriendRequest);
                });
            });
        }

        // Function to handle accepting or declining friend requests
        function handleFriendRequest(event) {
            const action = event.target.classList.contains('accept') ? 'accept' : 'decline';
            const friend_id = event.target.getAttribute('data-id');
            const formData = new FormData();
            formData.append('action', action);
            formData.append('friend_id', friend_id);

            fetch('friend_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                fetchUpdates(); // Refresh the lists after action
            });
        }

        // Add friend form submission
        document.getElementById('add-friend-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('add_friend_username').value;
            const formData = new FormData();
            formData.append('action', 'add_by_username');
            formData.append('add_friend_username', username);

            fetch('friend_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('add-status').textContent = 'Friend request sent!';
                } else {
                    document.getElementById('add-status').textContent = 'Failed to send friend request.';
                }
                fetchUpdates(); // Refresh the lists after adding a friend
            });
        });

        // Poll the server every 5 seconds for updates
        setInterval(fetchUpdates, 5000);

        // Initial fetch on page load
        fetchUpdates();
    </script>
    <a href="index.php">Return</a>
</body>
</html>
