document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    
    loadGallery(currentPage);

    // Event listeners
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', handleCommentSubmit);
    }
});

async function loadGallery(page = 1) {
    const loadingSpinner = document.getElementById('loading-spinner');
    const galleryGrid = document.getElementById('gallery-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const noImagesDiv = document.getElementById('no-images');

    loadingSpinner.classList.remove('d-none');
    galleryGrid.innerHTML = '';
    paginationContainer.innerHTML = '';
    noImagesDiv.classList.add('d-none');

    try {
        const response = await fetch(`/api/gallery?page=${page}`);
        const data = await response.json();

        if (data.success) {
            if (data.images.length === 0) {
                noImagesDiv.classList.remove('d-none');
            } else {
                renderImages(data.images);
                renderPagination(data.pagination);
            }
        } else {
            showMessage(data.message || 'Failed to load gallery', 'error');
        }
    } catch (error) {
        console.error('Error loading gallery:', error);
        showMessage('Error loading gallery', 'error');
    } finally {
        loadingSpinner.classList.add('d-none');
    }
}

function renderImages(images) {
    const galleryGrid = document.getElementById('gallery-grid');
    
    images.forEach(image => {
        const imageCard = createImageCard(image);
        galleryGrid.appendChild(imageCard);
    });
}

function createImageCard(image) {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-4 col-xl-3';
    
    const formattedDate = new Date(image.created_at).toLocaleDateString();
    
    col.innerHTML = `
        <div class="card gallery-card h-100" onclick="openImageModal(${image.id})">
            <div class="card-img-container">
                <img src="${image.file_path}" class="card-img-top" alt="${image.filename}" loading="lazy">
                <div class="card-overlay">
                    <div class="overlay-content">
                        <div class="d-flex justify-content-between text-white">
                            <span><i class="bi bi-heart-fill"></i> ${image.likes_count}</span>
                            <span><i class="bi bi-chat-fill"></i> ${image.comments_count}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">@${image.username}</small>
                    <small class="text-muted">${formattedDate}</small>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination-container');
    
    if (pagination.total_pages <= 1) return;
    
    const nav = document.createElement('nav');
    nav.innerHTML = `
        <ul class="pagination">
            <li class="page-item ${!pagination.has_prev ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1}); return false;">Previous</a>
            </li>
            ${generatePageNumbers(pagination)}
            <li class="page-item ${!pagination.has_next ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1}); return false;">Next</a>
            </li>
        </ul>
    `;
    
    container.appendChild(nav);
}

function generatePageNumbers(pagination) {
    let html = '';
    const current = pagination.current_page;
    const total = pagination.total_pages;
    
    // Show first page
    if (current > 3) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1); return false;">1</a></li>`;
        if (current > 4) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Show pages around current
    for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
        html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>`;
    }
    
    // Show last page
    if (current < total - 2) {
        if (current < total - 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${total}); return false;">${total}</a></li>`;
    }
    
    return html;
}

function changePage(page) {
    currentPage = page;
    loadGallery(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function openImageModal(imageId) {
    try {
        const response = await fetch(`/api/gallery?page=${currentPage}`);
        const data = await response.json();
        
        const image = data.images.find(img => img.id == imageId);
        if (!image) return;
        
        // Populate modal
        document.getElementById('modalImage').src = image.file_path;
        document.getElementById('modalImage').alt = image.filename;
        document.getElementById('modalUsername').textContent = `@${image.username}`;
        document.getElementById('modalDate').textContent = new Date(image.created_at).toLocaleDateString();
        
        if (window.isLoggedIn) {
            const likeBtn = document.getElementById('modalLikeBtn');
            const likeCount = document.getElementById('modalLikeCount');
            
            likeBtn.innerHTML = `<i class="bi bi-heart${image.is_liked ? '-fill text-danger' : ''}"></i>`;
            likeBtn.onclick = () => toggleLike(imageId);
            likeCount.textContent = image.likes_count;
            
            document.getElementById('commentImageId').value = imageId;
        }
        
        renderComments(image.comments);
        
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
        
    } catch (error) {
        console.error('Error opening modal:', error);
        showMessage('Error loading image details', 'error');
    }
}

async function toggleLike(imageId) {
    if (!window.isLoggedIn) {
        showMessage('Please log in to like images', 'error');
        return;
    }

    try {
        const response = await fetch('/api/gallery/like', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image_id: imageId })
        });

        const data = await response.json();
        
        if (data.success) {
            const likeBtn = document.getElementById('modalLikeBtn');
            const likeCount = document.getElementById('modalLikeCount');
            
            likeBtn.innerHTML = `<i class="bi bi-heart${data.is_liked ? '-fill text-danger' : ''}"></i>`;
            likeCount.textContent = data.like_count;
            
            // Refresh gallery to show updated counts
            loadGallery(currentPage);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Error toggling like:', error);
        showMessage('Error updating like', 'error');
    }
}

async function handleCommentSubmit(e) {
    e.preventDefault();
    
    const imageId = document.getElementById('commentImageId').value;
    const content = document.getElementById('commentInput').value.trim();
    
    if (!content) return;
    
    try {
        const response = await fetch('/api/gallery/comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image_id: imageId, content: content })
        });

        const data = await response.json();
        
        if (data.success) {
            renderComments(data.comments);
            document.getElementById('commentInput').value = '';
            loadGallery(currentPage); // Refresh to update comment count
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        showMessage('Error adding comment', 'error');
    }
}

function renderComments(comments) {
    const container = document.getElementById('modalComments');
    
    if (!comments || comments.length === 0) {
        container.innerHTML = '<p class="text-muted small">No comments yet</p>';
        return;
    }
    
    container.innerHTML = comments.map(comment => `
        <div class="comment mb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong class="small">@${comment.username}</strong>
                    <p class="mb-1 small">${comment.content}</p>
                    <small class="text-muted">${new Date(comment.created_at).toLocaleDateString()}</small>
                </div>
                ${comment.user_id == window.currentUserId ? `
                    <button class="btn btn-link btn-sm p-0 text-danger" onclick="deleteComment(${comment.id}, ${comment.image_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                ` : ''}
            </div>
        </div>
    `).join('');
}

async function deleteComment(commentId, imageId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    try {
        const response = await fetch('/api/gallery/comment/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        });

        const data = await response.json();
        
        if (data.success) {
            // Refresh comments by reopening modal
            openImageModal(imageId);
            loadGallery(currentPage); // Refresh gallery
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting comment:', error);
        showMessage('Error deleting comment', 'error');
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