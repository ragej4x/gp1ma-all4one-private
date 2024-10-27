<?php
// Include database connection
include '../php/db.php';

// Fetch assignments and modules for student view
$assignments = $pdo->query("SELECT * FROM assignments")->fetchAll(PDO::FETCH_ASSOC);
$modules = $pdo->query("SELECT * FROM modules")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        h2 {
            text-align: center;
            margin-top: 30px;
        }
        .assignments, .modules {
            margin: 20px auto;
            max-width: 800px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .assignment, .module {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .assignment h3, .module h3 {
            margin: 0;
            color: #4CAF50;
        }
        .assignment p, .module p {
            margin: 5px 0;
        }
        a {
            text-decoration: none;
            color: #4CAF50;
            transition: color 0.3s;
        }
        a:hover {
            color: #45a049;
        }
        .no-items {
            text-align: center;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>

<h1>Welcome to the Student Portal!</h1>

<!-- Assignments List -->
<h2>Assignments</h2>
<div class="assignments">
    <?php if (empty($assignments)): ?>
        <p class="no-items">No assignments available.</p>
    <?php else: ?>
        <?php foreach ($assignments as $assignment): ?>
            <div class="assignment">
                <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                <p><?php echo htmlspecialchars($assignment['description']); ?></p>
                <p>Deadline: <?php 
                    // Format the deadline date and time
                    $formattedDeadline = date("F j, Y, g:i a", strtotime($assignment['deadline']));
                    echo htmlspecialchars($formattedDeadline); 
                ?></p>
                <?php if ($assignment['file_attachment']): ?>
                    <p><a href="uploads/assignments/<?php echo htmlspecialchars($assignment['file_attachment']); ?>" target="_blank">Download Attachment</a></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modules List -->
<h2>Modules</h2>
<div class="modules">
    <?php if (empty($modules)): ?>
        <p class="no-items">No modules available.</p>
    <?php else: ?>
        <?php foreach ($modules as $module): ?>
            <div class="module">
                <h3><?php echo htmlspecialchars($module['title']); ?></h3>
                <p><?php echo htmlspecialchars($module['description']); ?></p>
                <p>Date: <?php 
                    // Format the module date
                    $formattedDate = date("F j, Y", strtotime($module['date']));
                    echo htmlspecialchars($formattedDate); 
                ?></p>
                <?php if ($module['file_attachment']): ?>
                    <p><a href="uploads/modules/<?php echo htmlspecialchars($module['file_attachment']); ?>" target="_blank">Download File</a></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
