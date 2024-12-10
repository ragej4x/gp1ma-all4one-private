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

if (isset($_POST['action']) && $_POST['action'] == 'add_by_username' && isset($_POST['add_friend_username'])) {
    $username_to_add = trim($_POST['add_friend_username']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username_to_add]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($friend) {
        $friend_id = $friend['id'];

        $stmt = $pdo->prepare("SELECT * FROM friends WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
        $friendship = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$friendship) {
            $stmt = $pdo->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $friend_id]);
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    echo json_encode(['status' => 'fail']);
    exit;
}

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

if (isset($_POST['action']) && $_POST['action'] == 'fetch_updates') {
    $stmt = $pdo->prepare("SELECT users.id, users.username FROM friends 
                           JOIN users ON (friends.sender_id = users.id OR friends.receiver_id = users.id) 
                           WHERE (friends.sender_id = ? OR friends.receiver_id = ?) 
                             AND friends.status = 'accepted'
                             AND users.id != ?");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        body {
            font-family: Arial, sans-serif;
            background-color: #F1F6F9;
            color: #212A3E;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        h2 {
            color: #394867;
            margin-bottom: 20px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 90%;
            max-width: 600px;
            margin-bottom: 20px;
        }

        .friend-list, .user-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #9BA4B5;
            border-radius: 8px;
            padding: 10px;
            background-color: #F1F6F9;
        }

        .friend-list li, .user-list li {
            margin-bottom: 10px;
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #E5E5E5;
        }

        .friend-list li:last-child, .user-list li:last-child {
            border-bottom: none;
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #9BA4B5;
            border-radius: 4px;
        }

        button {
            background-color: #394867;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #212A3E;
        }

        #add-status {
            margin-top: 10px;
            color: #394867;
        }

        a {
            text-decoration: none;
            color: #394867;
            margin-top: 20px;
            display: inline-block;
        }

        a:hover {
            color: #212A3E;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
    <h2>Add Friend by Username</h2>
        <form id="add-friend-form" class="input-group">
            <input type="text" id="add_friend_username" name="add_friend_username" placeholder="Enter username" required>
            <button type="submit">Add Friend</button>
        </form>
        <p id="add-status"></p>
        
        <h2>Your Friends</h2>
        <div id="friend-list" class="friend-list">
            <ul></ul>
        </div>



        <h2>Pending Friend Requests</h2>
        <div id="pending-requests" class="friend-list">
            <ul></ul>
        </div>
    </div>

    <a href="index.php">Return</a>

    <script>
        function fetchUpdates() {
            const formData = new FormData();
            formData.append('action', 'fetch_updates');

            fetch('friend_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const friendList = document.getElementById('friend-list').querySelector('ul');
                friendList.innerHTML = ''; 
                if (data.friends.length > 0) {
                    data.friends.forEach(friend => {
                        friendList.innerHTML += `<li>${friend.username}</li>`;
                    });
                } else {
                    friendList.innerHTML = '<li>No friends yet.</li>';
                }

                const pendingList = document.getElementById('pending-requests').querySelector('ul');
                pendingList.innerHTML = '';
                if (data.incoming_requests.length > 0) {
                    data.incoming_requests.forEach(request => {
                        pendingList.innerHTML += `
                            <li>${request.username}
                                <div class="action-buttons">
                                    <button class="accept" data-id="${request.sender_id}">Accept</button>
                                    <button class="decline" data-id="${request.sender_id}">Decline</button>
                                </div>
                            </li>`;
                    });
                } else {
                    pendingList.innerHTML = '<li>No pending friend requests.</li>';
                }

                document.querySelectorAll('.accept, .decline').forEach(button => {
                    button.addEventListener('click', handleFriendRequest);
                });
            });
        }

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
                fetchUpdates(); 
            });
        }

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
                fetchUpdates(); 
            });
        });

        setInterval(fetchUpdates, 5000);

        fetchUpdates();
    </script>
</body>
</html>
