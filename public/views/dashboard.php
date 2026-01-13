<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Camagru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles-home.css">
    <link rel="stylesheet" href="../css/styles-dashboard.css">
</head>
<body>
    <?php include __DIR__ . '/shared/header.php'; ?>

    <main>
        <div class="container-fluid dashboard-container">
            <div class="row h-100">
                <div class="col-lg-3 col-md-4 sidebar">
                    <div class="sidebar-header">
                        <h5><i class="bi bi-images me-2"></i>My Photos</h5>
                        <small class="text-muted">Click to delete</small>
                    </div>

                    <div id="thumbnails-container" class="thumbnails-grid">
                        <!-- Las miniaturas se cargan aqui -->
                    </div>

                    <div id="no-photos" class="text-center py-4 d-none">
                        <i class="bi bi-camera display-4 text-muted"></i>
                        <p class="text-muted mt-2">No photos yet</p>
                        <p class="small text-muted">Take your first photo!</p>
                    </div>

                    <div class="col-lg-9 col-md-8 main-content">
                        <div class="content-wrapper">
                            <h2 class="text-center mb-4">Photo Studio</h2>

                            <div class="card camera-card mb-4">
                                <div class="card-body">
                                    <!-- Camera preview -->
                                    <div class="camera-section mb-4">
                                        <div class="camera-container">
                                            <video id="camera-preview" autoplay muted class="camera-preview d-none"></video>
                                            <canvas id="camera-canvas" class="camera-preview d-none"></canvas>
                                            <div id="camera-placeholder" class="camera-placeholder">
                                                <i class="bi bi-camera display-1"></i>
                                                <p>Click "Start Camera" to begin</p>
                                            </div>
                                        </div>
                                        
                                        <div class="camera-controls mt-3">
                                            <button id="start-camera-btn" class="btn btn-primary me-2">
                                                <i class="bi bi-camera-video"></i> Start Camera
                                            </button>
                                            <button id="stop-camera-btn" class="btn btn-secondary me-2 d-none">
                                                <i class="bi bi-camera-video-off"></i> Stop Camera
                                            </button>
                                            <button id="capture-btn" class="btn btn-success me-2 d-none" disabled>
                                                <i class="bi bi-camera"></i> Take Photo
                                            </button>
                                            <button id="reset-btn" class="btn btn-warning d-none">
                                                <i class="bi bi-arrow-clockwise"></i> Reset
                                            </button>
                                        </div>
                                    </div>

                                    <!-- File upload alternative -->
                                    <div class="upload-section">
                                        <hr class="my-4">
                                        <h6>Or upload an image:</h6>
                                        <div class="upload-area">
                                            <input type="file" id="image-upload" accept="image/*" class="form-control">
                                            <small class="text-muted">Supported formats: JPG, PNG, GIF (max 5MB)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        
                    </div>

                </div>
            </div>
        </div>
    </main>
</html>