<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ── Color Tokens ── */
        :root {
            --deep:     #3b1f2b;
            --deep-alt: #4d2a3a;
            --gold:     #c9a96e;
            --gold-hover: #b8955a;
            --cream:    #f5f0eb;
            --card-bg:  #fff8f2;
            --border:   #e2d5c7;
            --border-soft: #d4c4b0;
            --text-light: #f5e6d3;
            --text-muted: #6b5b4f;
            --text-sub:  #d4c4b0;
            --success:  #4caf50;
            --danger:   #f44336;
            --warning:  #ff9800;
            --info:     #2196f3;
            --purple:   #9c27b0;
            --sidebar-bg: #2a1721;
            --sidebar-hover: #3b1f2b;
            --iphone-bg: #000;
            --iphone-header: #1c1c1e;
            --iphone-toolbar: #2c2c2e;
        }

        /* ── Base & Layout ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Georgia', serif;
            background: var(--cream);
            color: #2c2c2c;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: var(--text-light);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar-logo {
            height: 40px;
            filter: brightness(0) invert(1);
        }

        .sidebar-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-light);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            color: var(--text-sub);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
            cursor: pointer;
        }

        .menu-item:hover,
        .menu-item.active {
            background: var(--sidebar-hover);
            color: var(--text-light);
            border-left: 4px solid var(--gold);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .menu-badge {
            background: var(--gold);
            color: var(--deep);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: auto;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            border: 2px solid var(--gold);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar .avatar-placeholder {
            width: 100%;
            height: 100%;
            background: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--deep);
            font-weight: 600;
            font-size: 18px;
        }

        .user-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 2px;
        }

        .user-info p {
            font-size: 12px;
            color: var(--text-sub);
        }

        /* ── Main Content ── */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
            transition: all 0.3s ease;
            max-width: calc(100% - 260px);
        }

        /* ── Top Navigation ── */
        .top-nav {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(59,31,43,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-left h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--deep);
            margin-bottom: 5px;
        }

        .nav-left p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-profile-pic {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--gold);
            cursor: pointer;
            flex-shrink: 0;
        }

        .nav-profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nav-profile-pic .placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--gold), var(--deep-alt));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 12px 20px 12px 45px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            width: 300px;
            background: var(--card-bg);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(201,169,110,0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .notification-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--deep);
            cursor: pointer;
            position: relative;
            padding: 8px;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--danger);
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 15px;
            text-align: center;
        }

        /* ── Stats Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(59,31,43,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.photos {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }
        .stat-icon.trips {
            background: rgba(156, 39, 176, 0.1);
            color: var(--purple);
        }
        .stat-icon.bookings {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }
        .stat-icon.saved {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 600;
            color: var(--deep);
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .stat-change {
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-change.positive {
            color: var(--success);
        }
        .stat-change.negative {
            color: var(--danger);
        }

        /* ── Dashboard Sections ── */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .dashboard-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(59,31,43,0.05);
            overflow: hidden;
        }

        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--deep);
        }

        .section-header .btn {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .section-header .btn:hover {
            background: var(--gold-hover);
        }
        .section-content {
            padding: 25px;
        }

        /* ── iPhone Gallery ── */
        .iphone-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 2px;
            background: var(--iphone-bg);
            padding: 2px;
            border-radius: 12px;
            overflow: hidden;
        }

        .gallery-item {
            position: relative;
            cursor: pointer;
            aspect-ratio: 3/4;
            overflow: hidden;
            background: var(--iphone-bg);
        }

        .gallery-item img,
        .gallery-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img,
        .gallery-item:hover video {
            transform: scale(1.05);
        }

        .media-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .video-badge {
            background: rgba(219, 68, 55, 0.9);
        }

        .selected-indicator {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--info);
            border: 2px solid white;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
        }

        .gallery-item.selected .selected-indicator {
            display: flex;
        }

        .gallery-toolbar {
            background: var(--iphone-toolbar);
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .gallery-toolbar-left,
        .gallery-toolbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .gallery-toolbar-btn {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.3s ease;
        }

        .gallery-toolbar-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        .gallery-title {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }

        /* ── iPhone Viewer ── */
        .iphone-viewer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: var(--iphone-bg);
            z-index: 2000;
            display: none;
            overflow: hidden;
        }

        .iphone-viewer.active {
            display: block;
        }

        .iphone-container {
            width: 100vw;
            height: 100vh;
            position: relative;
            background: var(--iphone-bg);
            overflow: hidden;
        }

        .dynamic-island {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 126px;
            height: 37px;
            background: black;
            border-radius: 37px;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 15px;
        }

        .dynamic-island-dot {
            width: 6px;
            height: 6px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
        }

        .viewer-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 60px 20px 15px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.7) 50%, transparent 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(20px);
        }

        .viewer-header-btn {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .viewer-header-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.1);
        }

        .viewer-title {
            color: white;
            font-size: 17px;
            font-weight: 600;
            opacity: 0.9;
        }

        .viewer-container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .media-viewport {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .viewer-media {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.2s ease;
            user-select: none;
        }

        .viewer-media.zoomable {
            cursor: zoom-in;
        }

        .viewer-media.zoomed {
            cursor: grab;
        }

        .viewer-media.zoomed:active {
            cursor: grabbing;
        }

        .viewer-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.7) 50%, transparent 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(20px);
        }

        .viewer-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .viewer-control-btn {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .viewer-control-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }

        .viewer-info {
            color: white;
            max-width: 50%;
        }

        .viewer-info-title {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 4px;
            opacity: 0.95;
        }

        .viewer-info-meta {
            font-size: 13px;
            opacity: 0.7;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .viewer-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            z-index: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .viewer-nav-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-50%) scale(1.1);
        }

        .viewer-nav-btn.prev {
            left: 30px;
        }

        .viewer-nav-btn.next {
            right: 30px;
        }

        /* Video Controls */
        .video-controls {
            position: absolute;
            bottom: 120px;
            left: 20px;
            right: 20px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(0,0,0,0.7);
            border-radius: 12px;
            backdrop-filter: blur(20px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .viewer-container:hover .video-controls {
            opacity: 1;
        }

        .video-play-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .video-progress {
            flex: 1;
            height: 4px;
            background: rgba(255,255,255,0.3);
            border-radius: 2px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .video-progress-bar {
            height: 100%;
            background: var(--info);
            width: 0%;
            transition: width 0.1s linear;
            position: relative;
        }

        .video-time {
            color: white;
            font-size: 13px;
            min-width: 70px;
            text-align: center;
            opacity: 0.8;
        }

        /* Zoom Controls */
        .zoom-controls {
            position: absolute;
            right: 30px;
            bottom: 120px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 500;
        }

        .zoom-btn {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .zoom-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.1);
        }

        /* EXIF Info */
        .exif-overlay {
            position: absolute;
            top: 120px;
            right: 30px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 16px;
            border-radius: 12px;
            backdrop-filter: blur(20px);
            font-size: 13px;
            line-height: 1.5;
            max-width: 300px;
            display: none;
            z-index: 500;
        }

        .exif-overlay.active {
            display: block;
        }

        .exif-title {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            opacity: 0.9;
        }

        .exif-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 4px;
            opacity: 0.8;
        }

        /* Loading */
        .loading-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 48px;
            z-index: 100;
        }

        /* ── Upcoming Trips ── */
        .trips-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .trip-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .trip-item:hover {
            border-color: var(--gold);
            background: rgba(201,169,110,0.05);
        }

        .trip-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--deep);
            font-size: 18px;
        }

        .trip-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--deep);
            margin-bottom: 5px;
        }

        .trip-info p {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .trip-status {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .status-confirmed {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }
        .status-pending {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }
        .status-cancelled {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        /* ── Quick Actions ── */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .action-btn {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .action-btn:hover {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--deep);
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 24px;
        }
        .action-btn span {
            font-size: 13px;
            font-weight: 500;
        }

        /* ── Modals ── */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow: hidden;
            position: relative;
        }

        .modal-header {
            padding: 20px 30px;
            background: var(--deep);
            color: var(--text-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-tabs {
            display: flex;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
        }

        .modal-tab {
            padding: 15px 30px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-tab.active {
            color: var(--deep);
            border-bottom-color: var(--gold);
            background: white;
        }

        .modal-body {
            padding: 30px;
            max-height: 60vh;
            overflow-y: auto;
        }

        /* Profile Picture Modal */
        .profile-picture-modal .modal-content {
            max-width: 500px;
        }

        .profile-picture-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .profile-picture-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--gold);
        }

        .profile-picture-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-picture-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            width: 100%;
        }

        .profile-picture-option {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .profile-picture-option:hover,
        .profile-picture-option.active {
            border-color: var(--gold);
            transform: scale(1.05);
        }

        .profile-picture-option img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-picture-upload {
            text-align: center;
            padding: 20px;
            border: 2px dashed var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .profile-picture-upload:hover {
            border-color: var(--gold);
            background: rgba(201,169,110,0.05);
        }

        /* Camera Interface */
        .camera-interface {
            display: none;
        }
        .camera-interface.active {
            display: block;
        }

        .camera-preview {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            background: #000;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .camera-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .camera-btn {
            background: var(--info);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .camera-btn:hover {
            opacity: 0.9;
        }
        .camera-btn.capture {
            background: var(--success);
        }
        .camera-btn.retake {
            background: var(--warning);
        }

        /* Upload Interface */
        .upload-interface {
            display: none;
        }
        .upload-interface.active {
            display: block;
        }

        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: var(--gold);
            background: rgba(201,169,110,0.05);
        }

        .upload-icon {
            font-size: 48px;
            color: var(--deep);
            margin-bottom: 15px;
        }
        .file-input {
            display: none;
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--deep);
            color: var(--text-light);
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast.success {
            background: var(--success);
        }
        .toast.error {
            background: var(--danger);
        }

        /* ── Responsive Design ── */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .search-box input {
                width: 200px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                max-width: 100%;
                padding: 15px;
            }
            .mobile-menu-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 24px;
                color: var(--deep);
                cursor: pointer;
            }
            .nav-left h1 {
                font-size: 20px;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .iphone-gallery {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            .actions-grid {
                grid-template-columns: 1fr;
            }
            .nav-right {
                flex-wrap: wrap;
            }
            .search-box input {
                width: 100%;
            }
            .viewer-footer {
                flex-direction: column;
                gap: 15px;
            }
            .viewer-info {
                max-width: 100%;
            }
            .viewer-controls {
                justify-content: center;
            }
            .viewer-nav-btn {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }
            .viewer-nav-btn.prev {
                left: 15px;
            }
            .viewer-nav-btn.next {
                right: 15px;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .top-nav {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
                padding: 15px;
            }
            .nav-right {
                width: 100%;
            }
            .profile-picture-options {
                grid-template-columns: repeat(2, 1fr);
            }
            .iphone-gallery {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }
            .viewer-header-btn {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }
            .viewer-control-btn {
                padding: 10px 16px;
                font-size: 14px;
            }
            .zoom-controls {
                right: 15px;
                bottom: 100px;
            }
            .zoom-btn {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }
        }

        /* Utility Classes */
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--gold);
            color: var(--deep);
        }
        .btn-primary:hover {
            background: var(--gold-hover);
        }
        .btn-secondary {
            background: var(--card-bg);
            color: var(--deep);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover {
            background: var(--border);
        }
        .text-muted {
            color: var(--text-muted);
        }
        .text-center {
            text-align: center;
        }
        .mb-20 {
            margin-bottom: 20px;
        }
        .mt-20 {
            margin-top: 20px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--deep);
            cursor: pointer;
        }

        @media (max-width: 992px) {
            .mobile-menu-toggle {
                display: block;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .fa-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/travel-icon.png') }}" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <span class="sidebar-title">Smart Booking</span>
        </div>

        <div class="sidebar-menu">
            <a href="{{ route('dashboard') }}" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" onclick="openPhotoManager(); return false;">
                <i class="fas fa-camera"></i>
                <span>Photo Manager</span>
                <span class="menu-badge" id="photoCount">0</span>
            </a>
            @if(Auth::user()->isAgency())
                <a href="{{ route('flights.my') }}" class="menu-item">
                    <i class="fas fa-plane"></i>
                    <span>My Flights</span>
                    <span class="menu-badge" id="flightCount">{{ Auth::user()->flights()->count() }}</span>
                </a>
                <a href="{{ route('bookings.agency') }}" class="menu-item">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Bookings</span>
                </a>
            @else
                <a href="{{ route('plan-trip') }}" class="menu-item">
                    <i class="fas fa-route"></i>
                    <span>Plan Trips</span>
                </a>
                <a href="{{ route('bookings.index') }}" class="menu-item">
                    <i class="fas fa-suitcase"></i>
                    <span>My Bookings</span>
                    <span class="menu-badge" id="bookingCount">{{ Auth::user()->bookings()->count() }}</span>
                </a>
            @endif
            <a href="{{ route('discover') }}" class="menu-item">
                <i class="fas fa-compass"></i>
                <span>Discover</span>
            </a>
            <a href="{{ route('destinations') }}" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Destinations</span>
                <span class="menu-badge">12</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="{{ route('community') }}" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Community</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar" onclick="openProfilePictureModal()">
                    <img id="sidebarProfilePicture" src="" alt="Profile Picture" onerror="this.style.display='none'">
                    <div class="avatar-placeholder" id="sidebarAvatarPlaceholder">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                </div>
                <div class="user-info">
                    <h4>{{ Auth::user()->name }}</h4>
                    <p>
                        @if(Auth::user()->isAgency())
                            {{ Auth::user()->agency_name ?? 'Agency' }}
                        @else
                            Premium Member
                        @endif
                    </p>
                </div>
                <button class="btn btn-secondary" onclick="logout()" style="margin-left: auto; padding: 5px 10px; font-size: 12px;">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="nav-left">
                <div class="nav-profile-pic" onclick="openProfilePictureModal()">
                    <img id="navProfilePicture" src="" alt="Profile Picture" onerror="this.style.display='none'">
                    <div class="placeholder" id="navProfilePlaceholder">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
                    <p id="welcomeMessage">
                        @if(Auth::user()->isAgency())
                            Manage your flights and bookings with ease
                        @else
                            Here's what's happening with your travel plans today
                        @endif
                    </p>
                </div>
            </div>
            <div class="nav-right">
                <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search destinations, bookings, photos..." oninput="searchGallery(this.value)">
                </div>
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <button class="btn btn-primary" onclick="openPhotoManager()">
                    <i class="fas fa-plus"></i> Add Media
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon photos">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalPhotos">0</h3>
                    <p>Travel Photos</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        12% from last month
                    </span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon trips">
                    <i class="fas fa-suitcase"></i>
                </div>
                <div class="stat-info">
                    <h3 id="tripCount">
                        @if(Auth::user()->isAgency())
                            {{ Auth::user()->flights()->count() }}
                        @else
                            {{ Auth::user()->bookings()->count() }}
                        @endif
                    </h3>
                    <p id="tripLabel">
                        @if(Auth::user()->isAgency())
                            Active Flights
                        @else
                            Active Trips
                        @endif
                    </p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        1 new trip
                    </span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bookings">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3>7</h3>
                    <p>Total Bookings</p>
                    <span class="stat-change negative">
                        <i class="fas fa-arrow-down"></i>
                        2 upcoming
                    </span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon saved">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div class="stat-info">
                    <h3>12</h3>
                    <p>Saved Destinations</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        3 new saves
                    </span>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- iPhone Style Gallery Section -->
                <div class="dashboard-section mb-20">
                    <div class="section-header">
                        <h2>Recent Media</h2>
                        <div>
                            <button class="btn" onclick="openPhotoManager()">
                                <i class="fas fa-th"></i> All Media
                            </button>
                            <button class="btn btn-secondary" onclick="openCameraModal()" style="margin-left: 10px;">
                                <i class="fas fa-camera"></i> Camera
                            </button>
                        </div>
                    </div>
                    <div class="section-content" style="padding: 0;">
                        <div class="gallery-toolbar">
                            <div class="gallery-toolbar-left">
                                <button class="gallery-toolbar-btn" onclick="selectAllMedia()" id="selectAllBtn">
                                    <i class="far fa-square"></i>
                                </button>
                                <span class="gallery-title">Photos & Videos</span>
                            </div>
                            <div class="gallery-toolbar-right">
                                <button class="gallery-toolbar-btn" onclick="deleteSelectedMedia()" id="deleteSelectedBtn" style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="gallery-toolbar-btn" onclick="shareSelectedMedia()" id="shareSelectedBtn" style="display: none;">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="iphone-gallery" id="iphoneGallery">
                            <!-- Media items will be loaded here -->
                            <div class="text-center text-muted" style="grid-column: 1 / -1; padding: 40px; background: white;">
                                <i class="fas fa-camera" style="font-size: 48px; margin-bottom: 20px; color: var(--deep);"></i>
                                <p>No media yet. Start capturing your travel memories!</p>
                                <button class="btn btn-primary mt-20" onclick="openPhotoManager()">
                                    <i class="fas fa-camera"></i> Add Your First Photo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Trips -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Upcoming Trips</h2>
                        <button class="btn" onclick="planNewTrip()">
                            @if(Auth::user()->isAgency())
                                Add Flight
                            @else
                                Plan New Trip
                            @endif
                        </button>
                    </div>
                    <div class="section-content">
                        <div class="trips-list" id="upcomingTrips">
                            <!-- Trips will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Quick Actions -->
                <div class="dashboard-section mb-20">
                    <div class="section-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="section-content">
                        <div class="actions-grid">
                            <div class="action-btn" onclick="openCameraModal()">
                                <i class="fas fa-camera"></i>
                                <span>Take Photo</span>
                            </div>
                            <div class="action-btn" onclick="openUploadModal()">
                                <i class="fas fa-upload"></i>
                                <span>Upload Media</span>
                            </div>
                            @if(Auth::user()->isAgency())
                                <a href="{{ route('flights.create') }}" class="action-btn" style="text-decoration:none;color:inherit;">
                                    <i class="fas fa-plane"></i>
                                    <span>Add Flight</span>
                                </a>
                                <a href="{{ route('bookings.agency') }}" class="action-btn" style="text-decoration:none;color:inherit;">
                                    <i class="fas fa-list"></i>
                                    <span>View Bookings</span>
                                </a>
                            @else
                                <div class="action-btn" onclick="planNewTrip()">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Plan Trip</span>
                                </div>
                                <div class="action-btn" onclick="viewBookings()">
                                    <i class="fas fa-receipt"></i>
                                    <span>View Bookings</span>
                                </div>
                            @endif
                            <div class="action-btn" onclick="exploreDestinations()">
                                <i class="fas fa-compass"></i>
                                <span>Explore</span>
                            </div>
                            <div class="action-btn" onclick="openProfilePictureModal()">
                                <i class="fas fa-user-circle"></i>
                                <span>Profile Picture</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Storage -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Media Storage</h2>
                    </div>
                    <div class="section-content">
                        <div class="storage-info">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span class="text-muted">Used Space</span>
                                <span id="usedSpace">0 MB / 500 MB</span>
                            </div>
                            <div style="background: var(--border); height: 8px; border-radius: 4px; overflow: hidden;">
                                <div id="storageBar" style="background: var(--gold); height: 100%; width: 0%;"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                                <small class="text-muted">Photos: <span id="photoCountBadge">0</span></small>
                                <small class="text-muted">Videos: <span id="videoCountBadge">0</span></small>
                            </div>
                            <p class="text-muted" style="font-size: 12px; margin-top: 10px;">
                                <i class="fas fa-info-circle"></i>
                                Upgrade to premium for unlimited storage
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- iPhone Viewer -->
    <div class="iphone-viewer" id="iphoneViewer">
        <div class="iphone-container">
            <!-- Dynamic Island -->
            <div class="dynamic-island">
                <div class="dynamic-island-dot"></div>
                <div class="dynamic-island-dot"></div>
                <div class="dynamic-island-dot"></div>
            </div>

            <div class="viewer-header">
                <button class="viewer-header-btn" onclick="closeIphoneViewer()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="viewer-title" id="viewerTitle">Photo</div>
                <button class="viewer-header-btn" onclick="toggleExifInfo()">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>

            <div class="viewer-container" id="viewerContainer">
                <button class="viewer-nav-btn prev" onclick="navigateMedia(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="media-viewport" id="mediaViewport">
                    <div id="mediaContainer">
                        <!-- Media will be inserted here -->
                    </div>
                </div>

                <button class="viewer-nav-btn next" onclick="navigateMedia(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Zoom Controls -->
                <div class="zoom-controls">
                    <button class="zoom-btn" onclick="zoomIn()">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button class="zoom-btn" onclick="zoomOut()">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button class="zoom-btn" onclick="resetZoom()">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>

                <!-- EXIF Info Overlay -->
                <div class="exif-overlay" id="exifOverlay">
                    <div class="exif-title">Photo Information</div>
                    <div class="exif-row">
                        <span class="exif-label">Date:</span>
                        <span id="exifDate">Unknown</span>
                    </div>
                    <div class="exif-row">
                        <span class="exif-label">Size:</span>
                        <span id="exifSize">Unknown</span>
                    </div>
                    <div class="exif-row">
                        <span class="exif-label">Resolution:</span>
                        <span id="exifResolution">1290 × 2796</span>
                    </div>
                    <div class="exif-row">
                        <span class="exif-label">Location:</span>
                        <span id="exifLocation">Unknown</span>
                    </div>
                    <div class="exif-row">
                        <span class="exif-label">Camera:</span>
                        <span id="exifCamera">iPhone 14 Pro Max</span>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div class="loading-indicator" id="loadingIndicator" style="display: none;">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>

                <!-- Video Controls -->
                <div class="video-controls" id="videoControls" style="display: none;">
                    <button class="video-play-btn" id="videoPlayBtn" onclick="toggleVideoPlay()">
                        <i class="fas fa-play"></i>
                    </button>
                    <div class="video-progress" onclick="seekVideo(event)">
                        <div class="video-progress-bar" id="videoProgressBar"></div>
                    </div>
                    <div class="video-time">
                        <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
                    </div>
                </div>
            </div>

            <div class="viewer-footer">
                <div class="viewer-info">
                    <div class="viewer-info-title" id="viewerMediaTitle"></div>
                    <div class="viewer-info-meta">
                        <span id="viewerMediaDate"></span>
                        <span id="viewerMediaType"></span>
                    </div>
                </div>
                <div class="viewer-controls">
                    <button class="viewer-control-btn" onclick="downloadCurrentMedia()">
                        <i class="fas fa-download"></i> Save
                    </button>
                    <button class="viewer-control-btn" onclick="shareCurrentMedia()">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <button class="viewer-control-btn" onclick="favoriteCurrentMedia()">
                        <i class="far fa-heart"></i> Favorite
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Manager Modal -->
    <div class="modal" id="photoManagerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Media Manager</h2>
                <button class="close-modal" onclick="closePhotoManager()">&times;</button>
            </div>

            <div class="modal-tabs">
                <button class="modal-tab active" onclick="switchTab('camera')">
                    <i class="fas fa-camera"></i> Camera
                </button>
                <button class="modal-tab" onclick="switchTab('upload')">
                    <i class="fas fa-upload"></i> Upload
                </button>
                <button class="modal-tab" onclick="switchTab('manage')">
                    <i class="fas fa-folder"></i> Manage
                </button>
            </div>

            <div class="modal-body">
                <!-- Camera Tab -->
                <div class="camera-interface active" id="cameraTab">
                    <div class="camera-container">
                        <div style="display: flex; gap: 10px; margin-bottom: 15px; justify-content: center;">
                            <button class="btn btn-primary" onclick="startCamera('photo')">
                                <i class="fas fa-camera"></i> Photo Mode
                            </button>
                            <button class="btn btn-secondary" onclick="startCamera('video')">
                                <i class="fas fa-video"></i> Video Mode
                            </button>
                        </div>

                        <div style="text-align: center; margin-bottom: 20px;">
                            <small class="text-muted">Optimized for iPhone 14 Pro Max (1290 × 2796)</small>
                        </div>

                        <video id="cameraFeed" class="camera-preview" autoplay playsinline></video>
                        <canvas id="cameraCanvas" style="display: none;"></canvas>

                        <div id="capturedMediaContainer" style="display: none;">
                            <img id="capturedImage" class="camera-preview" alt="Captured Image">
                            <video id="capturedVideo" class="camera-preview" controls style="display: none;"></video>
                        </div>

                        <div class="camera-controls">
                            <button class="camera-btn" id="startCameraBtn" onclick="startCamera('photo')">
                                <i class="fas fa-play"></i> Start Camera
                            </button>
                            <button class="camera-btn capture" id="captureBtn" onclick="captureMedia()" style="display: none;">
                                <i class="fas fa-camera"></i> Capture
                            </button>
                            <button class="camera-btn" id="recordBtn" onclick="toggleRecording()" style="display: none; background: var(--danger);">
                                <i class="fas fa-circle"></i> Record
                            </button>
                            <button class="camera-btn retake" id="retakeBtn" onclick="retakeMedia()" style="display: none;">
                                <i class="fas fa-redo"></i> Retake
                            </button>
                            <button class="camera-btn" id="saveMediaBtn" onclick="saveMedia()" style="display: none; background: var(--success);">
                                <i class="fas fa-save"></i> Save Media
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upload Tab -->
                <div class="upload-interface" id="uploadTab">
                    <div class="upload-area" onclick="document.getElementById('mediaInput').click()">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h3>Click to browse or drag & drop media</h3>
                        <p class="text-muted">Photos: JPG, PNG, GIF • Videos: MP4, MOV, WEBM • Max 100MB per file</p>
                        <p class="text-muted" style="margin-top: 10px;">
                            <i class="fas fa-mobile-alt"></i>
                            Optimized for iPhone 14 Pro Max display (1290 × 2796)
                        </p>
                    </div>

                    <input type="file" id="mediaInput" class="file-input" accept="image/*,video/*" multiple onchange="handleMediaSelect(event)">

                    <div id="uploadPreview" style="display: none;">
                        <h4>Selected Media</h4>
                        <div id="uploadGrid" class="iphone-gallery" style="margin: 20px 0; gap: 5px;"></div>
                        <button class="btn btn-primary" onclick="uploadSelectedMedia()" style="width: 100%;">
                            <i class="fas fa-upload"></i> Upload All Media
                        </button>
                    </div>
                </div>

                <!-- Manage Tab -->
                <div class="edit-interface" id="manageTab">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div class="search-box" style="flex: 1; margin-right: 20px;">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search media..." oninput="searchAllMedia(this.value)">
                        </div>
                        <button class="btn btn-secondary" onclick="exportMedia()">
                            <i class="fas fa-download"></i> Export All
                        </button>
                    </div>

                    <div id="manageMediaGrid" class="iphone-gallery">
                        <!-- Media will be loaded here -->
                    </div>

                    <div id="noMediaMessage" class="text-center text-muted" style="padding: 40px; display: none;">
                        <i class="fas fa-images" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <p>No media found. Start by taking or uploading some photos/videos!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Picture Modal -->
    <div class="modal profile-picture-modal" id="profilePictureModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Profile Picture</h2>
                <button class="close-modal" onclick="closeProfilePictureModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="profile-picture-container">
                    <div class="profile-picture-preview">
                        <img id="profilePicturePreview" src="" alt="Preview" style="display: none;">
                        <div class="avatar-placeholder" id="profilePicturePlaceholder">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    </div>

                    <div class="profile-picture-options">
                        <div class="profile-picture-option" onclick="selectProfilePicture('avatar1')">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ Auth::user()->name }}&backgroundColor=ffdfbf" alt="Avatar 1">
                        </div>
                        <div class="profile-picture-option" onclick="selectProfilePicture('avatar2')">
                            <img src="https://api.dicebear.com/7.x/adventurer/svg?seed={{ Auth::user()->name }}&backgroundColor=ffdfbf" alt="Avatar 2">
                        </div>
                        <div class="profile-picture-option" onclick="selectProfilePicture('avatar3')">
                            <img src="https://api.dicebear.com/7.x/thumbs/svg?seed={{ Auth::user()->name }}&backgroundColor=ffdfbf" alt="Avatar 3">
                        </div>
                    </div>

                    <div class="profile-picture-upload" onclick="document.getElementById('profilePictureUpload').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 48px; margin-bottom: 15px; color: var(--deep);"></i>
                        <h3>Upload Custom Photo</h3>
                        <p class="text-muted">JPG, PNG, or GIF • Max 5MB</p>
                        <input type="file" id="profilePictureUpload" class="file-input" accept="image/*" onchange="handleProfilePictureUpload(event)" style="display: none;">
                    </div>

                    <div class="camera-controls">
                        <button class="camera-btn" onclick="takeProfilePicture()">
                            <i class="fas fa-camera"></i> Take Photo
                        </button>
                        <button class="camera-btn" onclick="saveProfilePicture()" style="background: var(--success);">
                            <i class="fas fa-save"></i> Save Profile Picture
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script>
        // Global variables
        let currentStream = null;
        let capturedMedia = null;
        let selectedFiles = [];
        let userMedia = JSON.parse(localStorage.getItem('smartBookingMedia_{{ Auth::id() }}')) || [];
        let currentMediaIndex = 0;
        let currentViewingMedia = null;
        let userProfilePicture = localStorage.getItem('smartBookingProfilePicture_{{ Auth::id() }}');
        let selectedProfilePicture = null;
        let selectedMedia = new Set();
        let isRecording = false;
        let mediaRecorder = null;
        let recordedChunks = [];
        let cameraMode = 'photo';
        let currentVideo = null;
        let zoomLevel = 1;
        let isZoomed = false;
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let translateX = 0;
        let translateY = 0;
        let scale = 1;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            updateProfilePictureDisplay();
            loadUpcomingTrips();
            updateStats();
            updateIphoneGallery();
            updateStorageInfo();
            setupDragAndDrop();
        });

        function updateProfilePictureDisplay() {
            const sidebarImg = document.getElementById('sidebarProfilePicture');
            const sidebarPlaceholder = document.getElementById('sidebarAvatarPlaceholder');
            const navImg = document.getElementById('navProfilePicture');
            const navPlaceholder = document.getElementById('navProfilePlaceholder');
            const previewImg = document.getElementById('profilePicturePreview');
            const previewPlaceholder = document.getElementById('profilePicturePlaceholder');

            if (userProfilePicture) {
                [sidebarImg, navImg, previewImg].forEach(img => {
                    img.src = userProfilePicture;
                    img.style.display = 'block';
                });
                [sidebarPlaceholder, navPlaceholder, previewPlaceholder].forEach(placeholder => {
                    placeholder.style.display = 'none';
                });
            } else {
                [sidebarImg, navImg, previewImg].forEach(img => {
                    img.style.display = 'none';
                });
                sidebarPlaceholder.style.display = 'flex';
                sidebarPlaceholder.textContent = '{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}';
                navPlaceholder.style.display = 'flex';
                navPlaceholder.textContent = '{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}';
                previewPlaceholder.style.display = 'flex';
                previewPlaceholder.textContent = '{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}';
            }
        }

        function saveProfilePictureToStorage() {
            if (selectedProfilePicture) {
                localStorage.setItem('smartBookingProfilePicture_{{ Auth::id() }}', selectedProfilePicture);
                userProfilePicture = selectedProfilePicture;
                updateProfilePictureDisplay();
                showToast('Profile picture updated!', 'success');
            }
        }

        function saveMediaToStorage() {
            localStorage.setItem('smartBookingMedia_{{ Auth::id() }}', JSON.stringify(userMedia));
            updateMediaCount();
            updateStats();
            updateIphoneGallery();
            updateStorageInfo();
        }

        function updateMediaCount() {
            const photos = userMedia.filter(m => m.type === 'photo').length;
            const videos = userMedia.filter(m => m.type === 'video').length;
            const total = photos + videos;

            document.getElementById('photoCount').textContent = total;
            document.getElementById('totalPhotos').textContent = total;
            document.getElementById('photoCountBadge').textContent = photos;
            document.getElementById('videoCountBadge').textContent = videos;
        }

        function updateStorageInfo() {
            const totalPhotos = userMedia.filter(m => m.type === 'photo').length;
            const totalVideos = userMedia.filter(m => m.type === 'video').length;
            const estimatedSize = (totalPhotos * 2) + (totalVideos * 10);
            const maxSize = 500;

            document.getElementById('usedSpace').textContent = `${estimatedSize} MB / ${maxSize} MB`;
            document.getElementById('storageBar').style.width = `${(estimatedSize / maxSize) * 100}%`;
        }

        function updateIphoneGallery() {
            const gallery = document.getElementById('iphoneGallery');
            if (userMedia.length === 0) {
                gallery.innerHTML = `
                    <div class="text-center text-muted" style="grid-column: 1 / -1; padding: 40px; background: white;">
                        <i class="fas fa-camera" style="font-size: 48px; margin-bottom: 20px; color: var(--deep);"></i>
                        <p>No media yet. Start capturing your travel memories!</p>
                        <button class="btn btn-primary mt-20" onclick="openPhotoManager()">
                            <i class="fas fa-camera"></i> Add Your First Photo
                        </button>
                    </div>
                `;
                return;
            }

            gallery.innerHTML = '';
            const recentMedia = [...userMedia].reverse().slice(0, 24);

            recentMedia.forEach((media, index) => {
                const item = document.createElement('div');
                item.className = 'gallery-item' + (selectedMedia.has(media.id) ? ' selected' : '');
                item.onclick = (e) => {
                    if (e.shiftKey || e.metaKey || e.ctrlKey) {
                        toggleMediaSelection(media.id);
                    } else {
                        viewMedia(media.id);
                    }
                };

                item.innerHTML = `
                    ${media.type === 'photo'
                        ? `<img src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;">`
                        : `<video src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;"></video>`
                    }
                    <div class="media-badge ${media.type === 'video' ? 'video-badge' : ''}">
                        <i class="fas fa-${media.type === 'video' ? 'video' : 'image'}"></i>
                        ${media.type === 'video' ? media.duration || '' : ''}
                    </div>
                    <div class="selected-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                `;

                gallery.appendChild(item);
            });
        }

        function updateStats() {
            const total = userMedia.length;
            document.getElementById('totalPhotos').textContent = total;
        }

        function loadUpcomingTrips() {
            const trips = [
                { id: 1, destination: 'Paris, France', date: 'Jun 15-22, 2024', status: 'confirmed', icon: 'fas fa-eiffel-tower' },
                { id: 2, destination: 'Bali, Indonesia', date: 'Aug 3-10, 2024', status: 'pending', icon: 'fas fa-umbrella-beach' },
                { id: 3, destination: 'Tokyo, Japan', date: 'Nov 20-27, 2024', status: 'confirmed', icon: 'fas fa-torii-gate' }
            ];
            const container = document.getElementById('upcomingTrips');
            container.innerHTML = '';
            trips.forEach(trip => {
                const item = document.createElement('div');
                item.className = 'trip-item';
                item.innerHTML = `
                    <div class="trip-icon"><i class="${trip.icon}"></i></div>
                    <div class="trip-info">
                        <h4>${trip.destination}</h4>
                        <p>${trip.date}</p>
                        <span class="trip-status status-${trip.status}">${trip.status}</span>
                    </div>
                `;
                container.appendChild(item);
            });
        }

        // iPhone Gallery Functions
        function toggleMediaSelection(mediaId) {
            if (selectedMedia.has(mediaId)) {
                selectedMedia.delete(mediaId);
            } else {
                selectedMedia.add(mediaId);
            }

            updateIphoneGallery();
            updateSelectionToolbar();
        }

        function selectAllMedia() {
            if (selectedMedia.size === userMedia.length) {
                selectedMedia.clear();
            } else {
                userMedia.forEach(media => selectedMedia.add(media.id));
            }

            updateIphoneGallery();
            updateSelectionToolbar();
        }

        function updateSelectionToolbar() {
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            const shareBtn = document.getElementById('shareSelectedBtn');
            const selectAllBtn = document.getElementById('selectAllBtn');

            if (selectedMedia.size > 0) {
                deleteBtn.style.display = 'block';
                shareBtn.style.display = 'block';
                selectAllBtn.innerHTML = '<i class="fas fa-check-square"></i>';
            } else {
                deleteBtn.style.display = 'none';
                shareBtn.style.display = 'none';
                selectAllBtn.innerHTML = '<i class="far fa-square"></i>';
            }
        }

        function deleteSelectedMedia() {
            if (selectedMedia.size === 0) return;

            if (confirm(`Delete ${selectedMedia.size} selected item${selectedMedia.size > 1 ? 's' : ''}?`)) {
                userMedia = userMedia.filter(media => !selectedMedia.has(media.id));
                selectedMedia.clear();
                saveMediaToStorage();
                showToast(`Deleted ${selectedMedia.size} item${selectedMedia.size > 1 ? 's' : ''}`, 'success');
                updateSelectionToolbar();
            }
        }

        function shareSelectedMedia() {
            if (selectedMedia.size === 0) return;

            const selectedItems = userMedia.filter(media => selectedMedia.has(media.id));
            const urls = selectedItems.map(item => item.data);

            if (navigator.share) {
                navigator.share({
                    title: 'My Travel Memories',
                    text: `Check out my ${selectedItems.length} travel ${selectedItems.length > 1 ? 'photos/videos' : 'photo/video'}!`,
                    url: window.location.href
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(urls[0]);
                showToast('Media URL copied to clipboard', 'success');
            }
        }

        // iPhone Viewer Functions
        function viewMedia(mediaId) {
            const mediaIndex = userMedia.findIndex(m => m.id === mediaId);
            if (mediaIndex === -1) return;

            currentMediaIndex = mediaIndex;
            currentViewingMedia = userMedia[mediaIndex];
            openIphoneViewer();
        }

        function openIphoneViewer() {
            if (!currentViewingMedia) return;

            const viewer = document.getElementById('iphoneViewer');
            const mediaContainer = document.getElementById('mediaContainer');
            const title = document.getElementById('viewerMediaTitle');
            const date = document.getElementById('viewerMediaDate');
            const type = document.getElementById('viewerMediaType');
            const videoControls = document.getElementById('videoControls');
            const loadingIndicator = document.getElementById('loadingIndicator');

            loadingIndicator.style.display = 'block';
            mediaContainer.innerHTML = '';

            resetZoom();
            zoomLevel = 1;
            isZoomed = false;
            translateX = 0;
            translateY = 0;

            let mediaElement;
            if (currentViewingMedia.type === 'photo') {
                mediaElement = document.createElement('img');
                mediaElement.src = currentViewingMedia.data;
                mediaElement.alt = currentViewingMedia.title;
                mediaElement.className = 'viewer-media zoomable';
                mediaElement.onload = () => {
                    loadingIndicator.style.display = 'none';
                    updateExifInfo();
                };
                videoControls.style.display = 'none';
            } else {
                mediaElement = document.createElement('video');
                mediaElement.src = currentViewingMedia.data;
                mediaElement.controls = false;
                mediaElement.className = 'viewer-media';
                mediaElement.addEventListener('loadedmetadata', function() {
                    document.getElementById('duration').textContent = formatTime(mediaElement.duration);
                    loadingIndicator.style.display = 'none';
                    updateExifInfo();
                });
                mediaElement.addEventListener('timeupdate', updateVideoProgress);
                videoControls.style.display = 'flex';
                currentVideo = mediaElement;
            }

            mediaContainer.appendChild(mediaElement);

            title.textContent = currentViewingMedia.title;

            const mediaDate = new Date(currentViewingMedia.date);
            date.textContent = mediaDate.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            type.textContent = currentViewingMedia.type === 'photo' ? 'PHOTO' : 'VIDEO';

            setupZoomAndDrag(mediaElement);

            viewer.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeIphoneViewer() {
            document.getElementById('iphoneViewer').classList.remove('active');
            document.body.style.overflow = 'auto';

            if (currentVideo) {
                currentVideo.pause();
                currentVideo = null;
            }

            resetZoom();
        }

        function setupZoomAndDrag(mediaElement) {
            const mediaViewport = document.getElementById('mediaViewport');

            mediaElement.addEventListener('dblclick', function(e) {
                if (!isZoomed) {
                    zoomIn();
                } else {
                    resetZoom();
                }
            });

            mediaViewport.addEventListener('wheel', function(e) {
                e.preventDefault();
                if (e.deltaY < 0) {
                    zoomIn();
                } else {
                    zoomOut();
                }
            }, { passive: false });

            mediaViewport.addEventListener('touchstart', function(e) {
                if (e.touches.length === 1 && isZoomed) {
                    isDragging = true;
                    startX = e.touches[0].clientX - translateX;
                    startY = e.touches[0].clientY - translateY;
                }
            });

            mediaViewport.addEventListener('touchmove', function(e) {
                e.preventDefault();
                if (e.touches.length === 1 && isDragging && isZoomed) {
                    translateX = e.touches[0].clientX - startX;
                    translateY = e.touches[0].clientY - startY;
                    updateMediaTransform();
                }
            }, { passive: false });

            mediaViewport.addEventListener('touchend', function() {
                isDragging = false;
            });

            mediaViewport.addEventListener('mousedown', function(e) {
                if (isZoomed) {
                    isDragging = true;
                    startX = e.clientX - translateX;
                    startY = e.clientY - translateY;
                    mediaElement.style.cursor = 'grabbing';
                }
            });

            document.addEventListener('mousemove', function(e) {
                if (isDragging && isZoomed) {
                    translateX = e.clientX - startX;
                    translateY = e.clientY - startY;
                    updateMediaTransform();
                }
            });

            document.addEventListener('mouseup', function() {
                isDragging = false;
                if (mediaElement) {
                    mediaElement.style.cursor = isZoomed ? 'grab' : 'zoom-in';
                }
            });
        }

        function zoomIn() {
            if (zoomLevel >= 3) return;

            const mediaElement = document.querySelector('.viewer-media');
            if (!mediaElement) return;

            zoomLevel = Math.min(zoomLevel * 1.5, 3);
            isZoomed = true;
            mediaElement.classList.remove('zoomable');
            mediaElement.classList.add('zoomed');
            mediaElement.style.cursor = 'grab';

            updateMediaTransform();
        }

        function zoomOut() {
            if (zoomLevel <= 1) return;

            const mediaElement = document.querySelector('.viewer-media');
            if (!mediaElement) return;

            zoomLevel = Math.max(zoomLevel / 1.5, 1);
            if (zoomLevel === 1) {
                isZoomed = false;
                mediaElement.classList.add('zoomable');
                mediaElement.classList.remove('zoomed');
                mediaElement.style.cursor = 'zoom-in';
                translateX = 0;
                translateY = 0;
            }

            updateMediaTransform();
        }

        function resetZoom() {
            zoomLevel = 1;
            isZoomed = false;
            translateX = 0;
            translateY = 0;

            const mediaElement = document.querySelector('.viewer-media');
            if (mediaElement) {
                mediaElement.classList.add('zoomable');
                mediaElement.classList.remove('zoomed');
                mediaElement.style.cursor = 'zoom-in';
                updateMediaTransform();
            }
        }

        function updateMediaTransform() {
            const mediaElement = document.querySelector('.viewer-media');
            if (mediaElement) {
                mediaElement.style.transform = `translate(${translateX}px, ${translateY}px) scale(${zoomLevel})`;
            }
        }

        function navigateMedia(direction) {
            const newIndex = currentMediaIndex + direction;
            if (newIndex >= 0 && newIndex < userMedia.length) {
                currentMediaIndex = newIndex;
                currentViewingMedia = userMedia[newIndex];
                openIphoneViewer();
            }
        }

        function updateExifInfo() {
            if (!currentViewingMedia) return;

            const exifDate = document.getElementById('exifDate');
            const exifSize = document.getElementById('exifSize');
            const exifLocation = document.getElementById('exifLocation');

            const mediaDate = new Date(currentViewingMedia.date);
            exifDate.textContent = mediaDate.toLocaleDateString();

            const estimatedSize = currentViewingMedia.type === 'photo' ? '~2.5 MB' : '~15 MB';
            exifSize.textContent = estimatedSize;

            exifLocation.textContent = currentViewingMedia.location || 'Unknown Location';
        }

        function toggleExifInfo() {
            document.getElementById('exifOverlay').classList.toggle('active');
        }

        function downloadCurrentMedia() {
            if (!currentViewingMedia) return;
            const link = document.createElement('a');
            link.href = currentViewingMedia.data;
            link.download = `${currentViewingMedia.title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.${currentViewingMedia.type === 'photo' ? 'jpg' : 'mp4'}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('Download started!', 'success');
        }

        function shareCurrentMedia() {
            if (!currentViewingMedia) return;

            if (navigator.share) {
                navigator.share({
                    title: currentViewingMedia.title,
                    text: currentViewingMedia.description,
                    url: currentViewingMedia.data
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(currentViewingMedia.data);
                showToast('Media URL copied to clipboard!', 'success');
            }
        }

        function favoriteCurrentMedia() {
            if (!currentViewingMedia) return;

            currentViewingMedia.favorite = !currentViewingMedia.favorite;

            const mediaIndex = userMedia.findIndex(m => m.id === currentViewingMedia.id);
            if (mediaIndex !== -1) {
                userMedia[mediaIndex] = currentViewingMedia;
                saveMediaToStorage();
            }

            const btn = event.currentTarget;
            const icon = btn.querySelector('i');

            if (currentViewingMedia.favorite) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showToast('Added to favorites!', 'success');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showToast('Removed from favorites', 'info');
            }
        }

        // Video Controls
        function toggleVideoPlay() {
            if (!currentVideo) return;

            const playBtn = document.getElementById('videoPlayBtn');
            if (currentVideo.paused) {
                currentVideo.play();
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                currentVideo.pause();
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        }

        function updateVideoProgress() {
            if (!currentVideo) return;

            const progress = document.getElementById('videoProgressBar');
            const currentTime = document.getElementById('currentTime');
            const duration = document.getElementById('duration');

            const percent = (currentVideo.currentTime / currentVideo.duration) * 100;
            progress.style.width = percent + '%';

            currentTime.textContent = formatTime(currentVideo.currentTime);
            duration.textContent = formatTime(currentVideo.duration);
        }

        function seekVideo(event) {
            if (!currentVideo) return;

            const progressBar = event.currentTarget;
            const rect = progressBar.getBoundingClientRect();
            const percent = (event.clientX - rect.left) / rect.width;
            currentVideo.currentTime = percent * currentVideo.duration;
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        // Profile Picture Functions
        function openProfilePictureModal() {
            selectedProfilePicture = userProfilePicture;
            updateProfilePicturePreview();
            document.getElementById('profilePictureModal').classList.add('active');
        }

        function closeProfilePictureModal() {
            document.getElementById('profilePictureModal').classList.remove('active');
        }

        function selectProfilePicture(type) {
            let avatarUrl = '';
            switch(type) {
                case 'avatar1':
                    avatarUrl = `https://api.dicebear.com/7.x/avataaars/svg?seed={{ Auth::user()->name }}&backgroundColor=ffdfbf`;
                    break;
                case 'avatar2':
                    avatarUrl = `https://api.dicebear.com/7.x/adventurer/svg?seed={{ Auth::user()->name }}&backgroundColor=ffdfbf`;
                    break;
                case 'avatar3':
                    avatarUrl = `https://api.dicebear.com/7.x/thumbs/svg?seed={{ Auth::user()->name }}&backgroundColor=ffdfbf`;
                    break;
            }
            selectedProfilePicture = avatarUrl;
            updateProfilePicturePreview();
        }

        function updateProfilePicturePreview() {
            const previewImg = document.getElementById('profilePicturePreview');
            const previewPlaceholder = document.getElementById('profilePicturePlaceholder');

            if (selectedProfilePicture) {
                previewImg.src = selectedProfilePicture;
                previewImg.style.display = 'block';
                previewPlaceholder.style.display = 'none';
            } else {
                previewImg.style.display = 'none';
                previewPlaceholder.style.display = 'flex';
                previewPlaceholder.textContent = '{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}';
            }
        }

        function handleProfilePictureUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                showToast('File size must be less than 5MB', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                selectedProfilePicture = e.target.result;
                updateProfilePicturePreview();
            };
            reader.readAsDataURL(file);
        }

        function takeProfilePicture() {
            closeProfilePictureModal();
            setTimeout(() => {
                openCameraModal();
                showToast('After taking photo, you can set it as profile picture', 'info');
            }, 300);
        }

        function saveProfilePicture() {
            if (!selectedProfilePicture) {
                showToast('Please select or upload a profile picture', 'error');
                return;
            }
            saveProfilePictureToStorage();
            closeProfilePictureModal();
        }

        // Media Manager Functions
        function openPhotoManager() {
            document.getElementById('photoManagerModal').classList.add('active');
            switchTab('camera');
        }

        function closePhotoManager() {
            document.getElementById('photoManagerModal').classList.remove('active');
            stopCamera();
        }

        function switchTab(tabName) {
            document.querySelectorAll('.modal-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.camera-interface, .upload-interface, .edit-interface').forEach(tab => tab.classList.remove('active'));

            const tabButtons = document.querySelectorAll('.modal-tab');
            const tabIndex = Array.from(tabButtons).findIndex(btn => btn.textContent.toLowerCase().includes(tabName));
            if (tabIndex !== -1) {
                tabButtons[tabIndex].classList.add('active');
            }

            document.getElementById(tabName + 'Tab').classList.add('active');

            if (tabName === 'manage') {
                loadManageMedia();
            } else if (tabName === 'camera') {
                stopCamera();
            }
        }

        // Camera Functions
        function startCamera(mode = 'photo') {
            cameraMode = mode;

            const constraints = {
                video: {
                    facingMode: 'environment'
                },
                audio: mode === 'video'
            };

            navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => {
                currentStream = stream;
                const video = document.getElementById('cameraFeed');
                video.srcObject = stream;

                document.getElementById('startCameraBtn').style.display = 'none';
                document.getElementById('captureBtn').style.display = mode === 'photo' ? 'block' : 'none';
                document.getElementById('recordBtn').style.display = mode === 'video' ? 'block' : 'none';

                if (mode === 'video') {
                    mediaRecorder = new MediaRecorder(stream);
                    recordedChunks = [];

                    mediaRecorder.ondataavailable = (event) => {
                        if (event.data.size > 0) {
                            recordedChunks.push(event.data);
                        }
                    };

                    mediaRecorder.onstop = () => {
                        const blob = new Blob(recordedChunks, { type: 'video/webm' });
                        capturedMedia = URL.createObjectURL(blob);

                        const capturedVideo = document.getElementById('capturedVideo');
                        capturedVideo.src = capturedMedia;
                        capturedVideo.style.display = 'block';
                        document.getElementById('cameraFeed').style.display = 'none';

                        document.getElementById('recordBtn').style.display = 'none';
                        document.getElementById('retakeBtn').style.display = 'block';
                        document.getElementById('saveMediaBtn').style.display = 'block';
                    };
                }
            })
            .catch(err => {
                console.error('Camera error:', err);
                showToast('Camera access denied or not available', 'error');
            });
        }

        function stopCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
        }

        function captureMedia() {
            if (cameraMode === 'photo') {
                const video = document.getElementById('cameraFeed');
                const canvas = document.getElementById('cameraCanvas');
                const capturedImg = document.getElementById('capturedImage');
                const capturedContainer = document.getElementById('capturedMediaContainer');

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                capturedMedia = canvas.toDataURL('image/jpeg');
                capturedImg.src = capturedMedia;
                capturedContainer.style.display = 'block';
                document.getElementById('cameraFeed').style.display = 'none';

                document.getElementById('captureBtn').style.display = 'none';
                document.getElementById('retakeBtn').style.display = 'block';
                document.getElementById('saveMediaBtn').style.display = 'block';

                stopCamera();
            }
        }

        function toggleRecording() {
            if (!mediaRecorder) return;

            if (!isRecording) {
                recordedChunks = [];
                mediaRecorder.start();
                isRecording = true;
                document.getElementById('recordBtn').innerHTML = '<i class="fas fa-stop"></i> Stop';
                document.getElementById('recordBtn').style.background = 'var(--warning)';
            } else {
                mediaRecorder.stop();
                isRecording = false;
                document.getElementById('recordBtn').innerHTML = '<i class="fas fa-circle"></i> Record';
                document.getElementById('recordBtn').style.background = 'var(--danger)';
                stopCamera();
            }
        }

        function retakeMedia() {
            const video = document.getElementById('cameraFeed');
            const capturedContainer = document.getElementById('capturedMediaContainer');

            capturedContainer.style.display = 'none';
            video.style.display = 'block';

            document.getElementById('captureBtn').style.display = cameraMode === 'photo' ? 'block' : 'none';
            document.getElementById('recordBtn').style.display = cameraMode === 'video' ? 'block' : 'none';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('saveMediaBtn').style.display = 'none';

            startCamera(cameraMode);
        }

        function saveMedia() {
            if (!capturedMedia) return;

            const mediaData = {
                id: Date.now().toString(),
                title: `${cameraMode === 'photo' ? 'Travel Photo' : 'Travel Video'} ${userMedia.length + 1}`,
                description: `Captured on ${new Date().toLocaleDateString()}`,
                data: capturedMedia,
                type: cameraMode,
                date: new Date().toISOString(),
                location: 'Unknown Location',
                favorite: false
            };

            userMedia.push(mediaData);
            saveMediaToStorage();

            showToast(`${cameraMode === 'photo' ? 'Photo' : 'Video'} saved successfully!`, 'success');
            switchTab('manage');

            capturedMedia = null;
            document.getElementById('capturedMediaContainer').style.display = 'none';
            document.getElementById('cameraFeed').style.display = 'block';
            document.getElementById('startCameraBtn').style.display = 'block';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('recordBtn').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('saveMediaBtn').style.display = 'none';
        }

        // Upload Functions
        function handleMediaSelect(event) {
            selectedFiles = Array.from(event.target.files);
            displayUploadPreview();
        }

        function displayUploadPreview() {
            const container = document.getElementById('uploadGrid');
            const preview = document.getElementById('uploadPreview');

            if (selectedFiles.length === 0) {
                preview.style.display = 'none';
                return;
            }

            container.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const item = document.createElement('div');
                    item.className = 'gallery-item';

                    const isVideo = file.type.startsWith('video/');
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);

                    item.innerHTML = `
                        ${isVideo
                            ? `<video src="${e.target.result}" alt="${file.name}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;"></video>`
                            : `<img src="${e.target.result}" alt="${file.name}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;">`
                        }
                        <div class="media-badge ${isVideo ? 'video-badge' : ''}">
                            <i class="fas fa-${isVideo ? 'video' : 'image'}"></i>
                            ${fileSize} MB
                        </div>
                    `;
                    container.appendChild(item);
                };
                reader.readAsDataURL(file);
            });

            preview.style.display = 'block';
        }

        function uploadSelectedMedia() {
            if (selectedFiles.length === 0) {
                showToast('No files selected', 'error');
                return;
            }

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const isVideo = file.type.startsWith('video/');
                    const mediaData = {
                        id: (Date.now() + index).toString(),
                        title: file.name.replace(/\.[^/.]+$/, ""),
                        description: 'Uploaded on ' + new Date().toLocaleDateString(),
                        data: e.target.result,
                        type: isVideo ? 'video' : 'photo',
                        date: new Date().toISOString(),
                        location: 'Unknown Location',
                        favorite: false,
                        filename: file.name,
                        size: file.size
                    };

                    userMedia.push(mediaData);

                    if (index === selectedFiles.length - 1) {
                        saveMediaToStorage();
                        showToast(`${selectedFiles.length} files uploaded successfully!`, 'success');
                        switchTab('manage');
                        selectedFiles = [];
                        document.getElementById('uploadPreview').style.display = 'none';
                        document.getElementById('mediaInput').value = '';
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        // Manage Tab Functions
        function loadManageMedia() {
            const grid = document.getElementById('manageMediaGrid');
            const noMedia = document.getElementById('noMediaMessage');

            if (userMedia.length === 0) {
                grid.style.display = 'none';
                noMedia.style.display = 'block';
                return;
            }

            grid.style.display = 'grid';
            noMedia.style.display = 'none';
            grid.innerHTML = '';

            [...userMedia].reverse().forEach(media => {
                const item = document.createElement('div');
                item.className = 'gallery-item';
                item.onclick = () => viewMedia(media.id);

                item.innerHTML = `
                    ${media.type === 'photo'
                        ? `<img src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;">`
                        : `<video src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;"></video>`
                    }
                    <div class="media-badge ${media.type === 'video' ? 'video-badge' : ''}">
                        <i class="fas fa-${media.type === 'video' ? 'video' : 'image'}"></i>
                        ${media.type === 'video' ? media.duration || '' : ''}
                        ${media.favorite ? ' <i class="fas fa-heart" style="color: #ff4757;"></i>' : ''}
                    </div>
                `;
                grid.appendChild(item);
            });
        }

        function searchAllMedia(query) {
            const filtered = userMedia.filter(media =>
                media.title.toLowerCase().includes(query.toLowerCase()) ||
                media.description.toLowerCase().includes(query.toLowerCase()) ||
                (media.location && media.location.toLowerCase().includes(query.toLowerCase()))
            );

            const grid = document.getElementById('manageMediaGrid');
            const noMedia = document.getElementById('noMediaMessage');

            if (filtered.length === 0) {
                grid.style.display = 'none';
                noMedia.style.display = 'block';
                noMedia.innerHTML = `
                    <i class="fas fa-search" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <p>No media found for "${query}"</p>
                `;
                return;
            }

            grid.style.display = 'grid';
            noMedia.style.display = 'none';
            grid.innerHTML = '';

            filtered.reverse().forEach(media => {
                const item = document.createElement('div');
                item.className = 'gallery-item';
                item.onclick = () => viewMedia(media.id);

                item.innerHTML = `
                    ${media.type === 'photo'
                        ? `<img src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;">`
                        : `<video src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;"></video>`
                    }
                    <div class="media-badge ${media.type === 'video' ? 'video-badge' : ''}">
                        <i class="fas fa-${media.type === 'video' ? 'video' : 'image'}"></i>
                        ${media.type === 'video' ? media.duration || '' : ''}
                    </div>
                `;
                grid.appendChild(item);
            });
        }

        function searchGallery(query) {
            const filtered = userMedia.filter(media =>
                media.title.toLowerCase().includes(query.toLowerCase()) ||
                media.description.toLowerCase().includes(query.toLowerCase())
            );

            const gallery = document.getElementById('iphoneGallery');

            if (filtered.length === 0) {
                gallery.innerHTML = `
                    <div class="text-center text-muted" style="grid-column: 1 / -1; padding: 40px; background: white;">
                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 20px; color: var(--deep);"></i>
                        <p>No media found for "${query}"</p>
                    </div>
                `;
                return;
            }

            gallery.innerHTML = '';
            const recentFiltered = filtered.slice(0, 24);

            recentFiltered.forEach((media, index) => {
                const item = document.createElement('div');
                item.className = 'gallery-item';

                item.innerHTML = `
                    ${media.type === 'photo'
                        ? `<img src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;">`
                        : `<video src="${media.data}" alt="${media.title}" loading="lazy" style="aspect-ratio: 3/4; object-fit: cover;"></video>`
                    }
                    <div class="media-badge ${media.type === 'video' ? 'video-badge' : ''}">
                        <i class="fas fa-${media.type === 'video' ? 'video' : 'image'}"></i>
                        ${media.type === 'video' ? media.duration || '' : ''}
                    </div>
                `;

                item.onclick = () => viewMedia(media.id);
                gallery.appendChild(item);
            });
        }

        function exportMedia() {
            const exportData = {
                version: '2.0',
                exportDate: new Date().toISOString(),
                mediaCount: userMedia.length,
                photos: userMedia.filter(m => m.type === 'photo').length,
                videos: userMedia.filter(m => m.type === 'video').length,
                media: userMedia
            };

            const dataStr = JSON.stringify(exportData, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
            const link = document.createElement('a');
            link.setAttribute('href', dataUri);
            link.setAttribute('download', `smartbooking_media_${new Date().toISOString().split('T')[0]}.json`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast(`Exported ${userMedia.length} media items`, 'success');
        }

        // Helper Functions
        function setupDragAndDrop() {
            const uploadArea = document.querySelector('.upload-area');
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = 'var(--gold)';
                uploadArea.style.background = 'rgba(201,169,110,0.05)';
            });
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.borderColor = '';
                uploadArea.style.background = '';
            });
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '';
                uploadArea.style.background = '';
                const files = Array.from(e.dataTransfer.files).filter(file =>
                    file.type.startsWith('image/') || file.type.startsWith('video/')
                );
                if (files.length > 0) {
                    selectedFiles = files;
                    displayUploadPreview();
                    showToast(`${files.length} files ready for upload`, 'success');
                }
            });
        }

        function openCameraModal() {
            openPhotoManager();
            switchTab('camera');
        }

        function openUploadModal() {
            openPhotoManager();
            switchTab('upload');
        }

        function planNewTrip() {
            @if(Auth::user()->isAgency())
                window.location.href = '{{ route("flights.create") }}';
            @else
                window.location.href = '{{ route("plan-trip") }}';
            @endif
        }

        function viewBookings() {
            @if(Auth::user()->isAgency())
                window.location.href = '{{ route("bookings.agency") }}';
            @else
                window.location.href = '{{ route("bookings.index") }}';
            @endif
        }

        function exploreDestinations() {
            window.location.href = '{{ route("discover") }}';
        }

        function logout() {
            document.getElementById('logout-form').submit();
        }

        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closePhotoManager();
                closeProfilePictureModal();
                if (document.getElementById('iphoneViewer').classList.contains('active')) {
                    closeIphoneViewer();
                }
            }
        });

        // Click outside modals to close
        document.addEventListener('click', (e) => {
            const photoModal = document.getElementById('photoManagerModal');
            const profileModal = document.getElementById('profilePictureModal');

            if (e.target === photoModal) {
                closePhotoManager();
            }
            if (e.target === profileModal) {
                closeProfilePictureModal();
            }
        });
    </script>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>
</body>
</html>
