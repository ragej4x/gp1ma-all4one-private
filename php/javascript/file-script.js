let currentFolderId = null; 
let folderStack = []; 


function fetchFolderContents(folderId) {
    fetch(`get_folder_contents.php?folderId=${folderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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

function selectFile(fileId) {
    const fileItem = document.querySelector(`.file-item[data-id="${fileId}"]`);
    const isFolder = fileItem.querySelector('.icon') !== null;

    fileItem.classList.toggle('selected');

    if (isFolder) {
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
}

function updateFileContainer(files) {
    const fileContainer = document.getElementById('file-container');
    fileContainer.innerHTML = ''; 

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


function uploadFile() {
    console.log("Upload file function called."); 
    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;

    if (files.length === 0) {
        alert('Please select a file to upload.');
        return;
    }

    const formData = new FormData();
    formData.append('fileToUpload', files[0]);
    if (currentFolderId) {
        formData.append('parent_id', currentFolderId); 
    }

    console.log("Uploading file to server..."); 

    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("Response from server:", data);
        if (data.success) {
            alert("File uploaded successfully.");
            selectFile(currentFolderId); 
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
            formData.append('parent_id', currentFolderId); 
        }

        fetch('create_folder.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json(); 
        })
        .then(data => {
            if (data.success) {
                alert("Folder created successfully.");
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
                selectFile(currentFolderId); 
            } else {
                alert("Failed to rename the file.");
            }
        });
    }
}

function goBack() {
    if (folderStack.length > 0) {
        const parentFolderId = folderStack.pop(); 
        fetch(`get_folder_contents.php?folderId=${parentFolderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentFolderId = parentFolderId; 
                updateFileContainer(data.files);
                toggleBackButton(folderStack.length > 0); 
            } else {
                alert("Failed to load previous folder contents.");
            }
        });
    } else {
        alert("No previous folder to go back to.");
    }
}

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
                    fileItem.remove();
                    return Promise.resolve(); 
                } else {
                    alert("Failed to delete the file: " + data.message);
                    return Promise.reject(); 
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
