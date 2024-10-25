let currentFolderId = null; // Variable to keep track of the current folder
let folderStack = []; // Stack to keep track of the folder history


function fetchFolderContents(folderId) {
    fetch(`get_folder_contents.php?folderId=${folderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateFileContainer(data.files); // Update the display with the new folder contents
                toggleBackButton(true); // Show back button if we're in a folder
            } else {
                alert("Failed to load folder contents.");
            }
        })
        .catch(error => {
            console.error('Error fetching folder contents:', error);
        });
}

function selectFile(fileId) {
    const fileItem = document.querySelector(`.file-item[data-id="${fileId}"]`);
    const isFolder = fileItem.querySelector('.icon') !== null;

    // Toggle selected class
    fileItem.classList.toggle('selected');

    if (isFolder) {
        // Fetch the contents of the selected folder
        fetch(`get_folder_contents.php?folderId=${fileId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (currentFolderId) {
                    folderStack.push(currentFolderId);
                }
                currentFolderId = fileId;
                updateFileContainer(data.files);
                toggleBackButton(true);
            } else {
                alert("Failed to load folder contents.");
            }
        })
        .catch(error => {
            console.error('Error fetching folder contents:', error);
        });
    } 
    // Remove the alert for selected files
}

// Function to update the file container with new files
function updateFileContainer(files) {
    const fileContainer = document.getElementById('file-container');
    fileContainer.innerHTML = ''; // Clear existing items

    files.forEach(file => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.setAttribute('data-id', file.id);
        fileItem.onclick = () => selectFile(file.id);

        fileItem.innerHTML = `
            ${file.is_folder ? '<div class="icon">📁</div>' : `<img src="${file.filepath}" class="file-icon" alt="${file.filename}" />`}
            <p>${file.filename}</p>
        `;

        fileContainer.appendChild(fileItem);
    });
}


// Function to upload a new file
function uploadFile() {
    console.log("Upload file function called."); // Debugging line
    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;

    if (files.length === 0) {
        alert('Please select a file to upload.');
        return;
    }

    const formData = new FormData();
    formData.append('fileToUpload', files[0]);
    if (currentFolderId) {
        formData.append('parent_id', currentFolderId); // Include the current folder ID
    }

    console.log("Uploading file to server..."); // Debugging line

    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("Response from server:", data); // Debugging line
        if (data.success) {
            alert("File uploaded successfully.");
            selectFile(currentFolderId); // Refresh the folder contents
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error uploading file:', error);
    });
}

function createFolder() {
    const folderName = prompt("Enter the name of the new folder:");
    if (folderName) {
        const formData = new FormData();
        formData.append('folderName', folderName);
        if (currentFolderId) {
            formData.append('parent_id', currentFolderId); // Include current folder ID if necessary
        }

        fetch('create_folder.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if the response is okay
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json(); // Parse JSON
        })
        .then(data => {
            if (data.success) {
                alert("Folder created successfully.");
                // Fetch the current folder contents again to update the display
                fetchFolderContents(currentFolderId);
            } else {
                alert("Failed to create folder: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error creating folder:', error);
            alert("An error occurred while creating the folder: " + error.message);
        });
    }
}

// Function to delete a file
function deleteFile(fileId) {
    if (confirm("Are you sure you want to delete this file?")) {
        fetch('delete_files.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: fileId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("File deleted successfully.");
                document.querySelector(`.file-item[data-id="${fileId}"]`).remove();
            } else {
                alert("Failed to delete the file.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while deleting the file.");
        });
    }
}

// Function to rename a file
function renameFile(fileId) {
    const newName = prompt("Enter the new name:");
    if (newName) {
        fetch('rename.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${fileId}&newName=${newName}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("File renamed successfully.");
                selectFile(currentFolderId); // Refresh the folder contents
            } else {
                alert("Failed to rename the file.");
            }
        });
    }
}

// Function to go back to the previous folder
function goBack() {
    if (folderStack.length > 0) {
        const parentFolderId = folderStack.pop(); // Get the last folder from the stack
        fetch(`get_folder_contents.php?folderId=${parentFolderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentFolderId = parentFolderId; // Update current folder
                updateFileContainer(data.files);
                toggleBackButton(folderStack.length > 0); // Show back button if there's still history
            } else {
                alert("Failed to load previous folder contents.");
            }
        });
    } else {
        alert("No previous folder to go back to.");
    }
}

// Function to toggle the visibility of the back button
function toggleBackButton(show) {
    const backButton = document.getElementById('backButton');
    backButton.style.display = show ? 'block' : 'none';
}


function deleteSelectedFiles() {
    const selectedFiles = document.querySelectorAll('.file-item.selected');

    if (selectedFiles.length === 0) {
        alert("No files selected for deletion.");
        return;
    }

    if (confirm("Are you sure you want to delete the selected files?")) {
        const deletePromises = Array.from(selectedFiles).map(fileItem => {
            const fileId = fileItem.getAttribute('data-id');
            return fetch('delete_files.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: fileId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fileItem.remove(); // Remove the item from the UI
                    return Promise.resolve(); // Return a resolved promise
                } else {
                    alert("Failed to delete the file: " + data.message);
                    return Promise.reject(); // Return a rejected promise
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred while deleting the file.");
            });
        });

        Promise.all(deletePromises).then(() => {
            alert("Selected files deleted successfully.");
        });
    }
}
