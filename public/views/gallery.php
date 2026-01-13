<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery | Camagru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles-home.css">
    <link rel="stylesheet" href="../css/styles-gallery.css">
</head>
<body>
    <?php include __DIR__ . '/shared/header.php'; ?>

    <main>
        <div class="container-fluid gallery-container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-4">Photo Gallery</h2>
                    
                    <div id="loading-spinner" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div id="gallery-grid" class="row g-3">
                        <!-- Las fotos se cargan aqui con gallery.js -->
                    </div>

                    <div id="pagination-container" class="d-flex justify-content-center mt-4">
                        <!-- La paginación se carga aqui con gallery.js -->
                    </div>

                    <div id="no-images" class="text-center py-5 d-none">
                        <i class="bi bi-images display-1 text-muted"></i>
                        <h4 class="text-muted">No images found</h4>
                        <p class="text-muted">Be the first to share a photo!</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageModalTitle">Photo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="row g-0">
                            <div class="col-lg-8">
                                <img id="modalImage" src="" alt="" class="img-fluid w-100" style="max-height: 70vh; object-fit: contain;">
                            </div>
                            <div class="col-lg-4 p-3">
                                <div class="d-flex align-items-center mb-3">
                                    <strong id="modalUsername" class="me-2"></strong>
                                    <small id="modalDate" class="text-muted"></small>
                                </div>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <button id="modalLikeBtn" class="btn btn-link p-0 me-2">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <span id="modalLikeCount">0</span>
                                    <span class="ms-1">likes</span>
                                </div>
                                <?php endif; ?>

                                <div class="comments-section">
                                    <h6>Comments</h6>
                                    <div id="modalComments" class="mb-3" style="max-height: 200px; overflow-y: auto;">
                                        <!-- Comments will be loaded here -->
                                    </div>

                                    <?php if (isset($_SESSION['user_id'])): ?>
                                    <form id="commentForm" class="d-flex">
                                        <input type="hidden" id="commentImageId">
                                        <input type="text" id="commentInput" class="form-control me-2" placeholder="Add a comment..." maxlength="500">
                                        <button type="submit" class="btn btn-primary">Post</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/shared/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass PHP session data to JavaScript
        window.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    </script>
    <script src="/js/gallery.js"></script>
    <script src="/js/mobile-nav.js"></script>
    <script src="/js/logout.js"></script>
</body>
</html>