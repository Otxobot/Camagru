let camera = null;
let selectedSticker = null;
let capturedImage = null;
let userPhotos = [];

// Available stickers - you can add more
const availableStickers = [
    { id: 1, name: 'Sunglasses', file: 'sunglasses.png' },
    { id: 2, name: 'Mustache', file: 'mustache.png' },
    { id: 3, name: 'Hat', file: 'hat.png' },
    { id: 4, name: 'Bow Tie', file: 'bowtie.png' },
    { id: 5, name: 'Crown', file: 'crown.png' },
    { id: 6, name: 'Glasses', file: 'glasses.png' }
];

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupEventListeners();
    loadUserPhotos();
    loadStickers();
});

function initializeDashboard() {
    // Check camera support
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showMessage('Camera not supported in this browser', 'error');
        document.getElementById('start-camera-btn').disabled = true;
    }
}

function setupEventListeners() {
    // Camera controls
    document.getElementById('start-camera-btn').addEventListener('click', startCamera);
    document.getElementById('stop-camera-btn').addEventListener('click', stopCamera);
    document.getElementById('capture-btn').addEventListener('click', capturePhoto);
    document.getElementById('reset-btn').addEventListener('click', resetCamera);
    
    // File upload
    document.getElementById('image-upload').addEventListener('change', handleImageUpload);
    
    // Delete confirmation
    document.getElementById('confirm-delete-btn').addEventListener('click', confirmDelete);
}

async function startCamera() {
    try {
        camera = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 640 }, 
                height: { ideal: 480 } 
            } 
        });
        
        const video = document.getElementById('camera-preview');
        video.srcObject = camera;
        
        video.onloadedmetadata = () => {
            document.getElementById('camera-placeholder').classList.add('d-none');
            video.classList.remove('d-none');
            
            document.getElementById('start-camera-btn').classList.add('d-none');
            document.getElementById('stop-camera-btn').classList.remove('d-none');
            
            updateCaptureButton();
        };
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        showMessage('Unable to access camera. Please check permissions.', 'error');
    }
}

function stopCamera() {
    if (camera) {
        camera.getTracks().forEach(track => track.stop());
        camera = null;
    }
    
    const video = document.getElementById('camera-preview');
    video.srcObject = null;
    video.classList.add('d-none');
    
    document.getElementById('camera-placeholder').classList.remove('d-none');
    document.getElementById('start-camera-btn').classList.remove('d-none');
    document.getElementById('stop-camera-btn').classList.add('d-none');
    document.getElementById('capture-btn').classList.add('d-none');
    document.getElementById('reset-btn').classList.add('d-none');
}

function capturePhoto() {
    const video = document.getElementById('camera-preview');
    const canvas = document.getElementById('camera-canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    ctx.drawImage(video, 0, 0);
    
    // If sticker is selected, add it to the image
    if (selectedSticker) {
        addStickerToCanvas(ctx, canvas);
    }
    
    // Convert to blob and save
    canvas.toBlob(async (blob) => {
        await savePhoto(blob);
    }, 'image/jpeg', 0.8);
    
    // Show captured image
    video.classList.add('d-none');
    canvas.classList.remove('d-none');
    document.getElementById('capture-btn').classList.add('d-none');
    document.getElementById('reset-btn').classList.remove('d-none');
}

function resetCamera() {
    const video = document.getElementById('camera-preview');
    const canvas = document.getElementById('camera-canvas');
    
    canvas.classList.add('d-none');
    video.classList.remove('d-none');
    
    document.getElementById('capture-btn').classList.remove('d-none');
    document.getElementById('reset-btn').classList.add('d-none');
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file
    if (!file.type.startsWith('image/')) {
        showMessage('Please select a valid image file', 'error');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        showMessage('Image too large. Maximum size is 5MB', 'error');
        return;
    }
    
    // Process uploaded image
    const reader = new FileReader();
    reader.onload = (e) => {
        processUploadedImage(e.target.result);
    };
    reader.readAsDataURL(file);
}

function processUploadedImage(imageSrc) {
    const canvas = document.getElementById('camera-canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = () => {
        // Resize if needed
        let { width, height } = calculateImageSize(img.width, img.height, 640, 480);
        
        canvas.width = width;
        canvas.height = height;
        
        // Draw image
        ctx.drawImage(img, 0, 0, width, height);
        
        // Add sticker if selected
        if (selectedSticker) {
            addStickerToCanvas(ctx, canvas);
        }
        
        // Convert to blob and save
        canvas.toBlob(async (blob) => {
            await savePhoto(blob);
        }, 'image/jpeg', 0.8);
        
        // Show result
        document.getElementById('camera-placeholder').classList.add('d-none');
        document.getElementById('camera-preview').classList.add('d-none');
        canvas.classList.remove('d-none');
        document.getElementById('reset-btn').classList.remove('d-none');
    };
    
    img.src = imageSrc;
}

function calculateImageSize(originalWidth, originalHeight, maxWidth, maxHeight) {
    let width = originalWidth;
    let height = originalHeight;
    
    if (width > maxWidth) {
        height = (height * maxWidth) / width;
        width = maxWidth;
    }
    
    if (height > maxHeight) {
        width = (width * maxHeight) / height;
        height = maxHeight;
    }
    
    return { width, height };
}

function addStickerToCanvas(ctx, canvas) {
    // This is a simplified version - in a real app you'd position stickers based on face detection
    const img = new Image();
    img.onload = () => {
        const stickerWidth = 100;
        const stickerHeight = 100;
        const x = (canvas.width - stickerWidth) / 2;
        const y = (canvas.height - stickerHeight) / 3;
        
        ctx.drawImage(img, x, y, stickerWidth, stickerHeight);
    };
    img.src = `/stickers/${selectedSticker.file}`;
}

async function savePhoto(blob) {
    if (!selectedSticker) {
        showMessage('Please select a sticker before taking a photo', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('image', blob, 'photo.jpg');
    formData.append('sticker_id', selectedSticker.id);
    
    try {
        const response = await fetch('/api/dashboard/save-photo', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Photo saved successfully!', 'success');
            loadUserPhotos(); // Refresh thumbnails
            
            // Clear file input
            document.getElementById('image-upload').value = '';
        } else {
            showMessage(data.message || 'Failed to save photo', 'error');
        }
    } catch (error) {
        console.error('Error saving photo:', error);
        showMessage('Error saving photo', 'error');
    }
}

function loadStickers() {
    const container = document.getElementById('stickers-container');
    
    availableStickers.forEach(sticker => {
        const stickerDiv = document.createElement('div');
        stickerDiv.className = 'sticker-item';
        stickerDiv.innerHTML = `
            <img src="/stickers/${sticker.file}" alt="${sticker.name}" 
                 onerror="this.src='/stickers/placeholder.png'">
            <span>${sticker.name}</span>
        `;
        
        stickerDiv.addEventListener('click', () => selectSticker(sticker, stickerDiv));
        container.appendChild(stickerDiv);
    });
}

function selectSticker(sticker, element) {
    // Remove previous selection
    document.querySelectorAll('.sticker-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Select current sticker
    element.classList.add('selected');
    selectedSticker = sticker;
    
    document.getElementById('selected-sticker-name').textContent = sticker.name;
    updateCaptureButton();
}

function updateCaptureButton() {
    const captureBtn = document.getElementById('capture-btn');
    const video = document.getElementById('camera-preview');
    
    if (camera && !video.classList.contains('d-none') && selectedSticker) {
        captureBtn.disabled = false;
        captureBtn.classList.remove('d-none');
    } else {
        captureBtn.disabled = true;
        if (!selectedSticker) {
            captureBtn.title = 'Please select a sticker first';
        }
    }
}

async function loadUserPhotos() {
    try {
        const response = await fetch('/api/dashboard/photos');
        const data = await response.json();
        
        if (data.success) {
            userPhotos = data.photos;
            renderThumbnails(data.photos);
        } else {
            showMessage(data.message || 'Failed to load photos', 'error');
        }
    } catch (error) {
        console.error('Error loading photos:', error);
        showMessage('Error loading photos', 'error');
    }
}

function renderThumbnails(photos) {
    const container = document.getElementById('thumbnails-container');
    const noPhotos = document.getElementById('no-photos');
    
    container.innerHTML = '';
    
    if (photos.length === 0) {
        noPhotos.classList.remove('d-none');
        return;
    }
    
    noPhotos.classList.add('d-none');
    
    photos.forEach(photo => {
        const thumbnail = document.createElement('div');
        thumbnail.className = 'thumbnail-item';
        thumbnail.innerHTML = `
            <img src="${photo.file_path}" alt="Photo ${photo.id}">
            <div class="thumbnail-overlay">
                <button class="btn btn-danger btn-sm" onclick="showDeleteModal(${photo.id}, '${photo.file_path}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(thumbnail);
    });
}

function showDeleteModal(photoId, filePath) {
    document.getElementById('delete-preview').src = filePath;
    document.getElementById('confirm-delete-btn').setAttribute('data-photo-id', photoId);
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

async function confirmDelete() {
    const photoId = document.getElementById('confirm-delete-btn').getAttribute('data-photo-id');
    
    try {
        const response = await fetch('/api/dashboard/delete-photo', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ photo_id: photoId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Photo deleted successfully', 'success');
            loadUserPhotos(); // Refresh thumbnails
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            modal.hide();
        } else {
            showMessage(data.message || 'Failed to delete photo', 'error');
        }
    } catch (error) {
        console.error('Error deleting photo:', error);
        showMessage('Error deleting photo', 'error');
    }
}

function showMessage(message, type = 'info') {
    const existingMessage = document.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-weight: 500;
        max-width: 90vw;
        text-align: center;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    `;
    
    if (type === 'success') {
        messageDiv.style.backgroundColor = '#10b981';
        messageDiv.style.color = 'white';
    } else if (type === 'error') {
        messageDiv.style.backgroundColor = '#ef4444';
        messageDiv.style.color = 'white';
    } else if (type === 'info') {
        messageDiv.style.backgroundColor = '#3b82f6';
        messageDiv.style.color = 'white';
    }
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 300);
    }, 5000);
}