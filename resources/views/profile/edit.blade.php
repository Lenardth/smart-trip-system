<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ── Color Tokens ── */
        :root {
            --deep: #3b1f2b;
            --deep-alt: #4d2a3a;
            --gold: #c9a96e;
            --gold-hover: #b8955a;
            --cream: #f5f0eb;
            --card-bg: #fff8f2;
            --border: #e2d5c7;
            --border-soft: #d4c4b0;
            --text-light: #f5e6d3;
            --text-muted: #6b5b4f;
            --text-sub: #d4c4b0;
            --success: #4caf50;
            --danger: #f44336;
            --warning: #ff9800;
            --info: #2196f3;
            --purple: #9c27b0;
            --sidebar-bg: #2a1721;
            --sidebar-hover: #3b1f2b;
            --iphone-bg: #000;
            --iphone-header: #1c1c1e;
            --iphone-toolbar: #2c2c2e;
        }

        /* ── Base & Layout ── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Sidebar Header with Logo */
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(135deg, var(--deep), var(--deep-alt));
        }
        .logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }


        .logo-text {
            font-size: 22px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--gold), #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 25px;
            color: var(--text-sub);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
            cursor: pointer;
            border-left: 4px solid transparent;
            margin: 5px 0;
        }

        .menu-item:hover,
        .menu-item.active {
            background: var(--sidebar-hover);
            color: var(--text-light);
            border-left: 4px solid var(--gold);
            box-shadow: inset 5px 0 10px rgba(0, 0, 0, 0.1);
        }

        .menu-item:hover::before,
        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(to bottom, var(--gold), transparent);
            opacity: 0.5;
        }

        .menu-item i {
            width: 24px;
            text-align: center;
            font-size: 18px;
            color: var(--gold);
            transition: all 0.3s ease;
        }

        .menu-item:hover i,
        .menu-item.active i {
            color: var(--text-light);
            transform: scale(1.1);
        }

        .menu-badge {
            background: var(--gold);
            color: var(--deep);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            margin-left: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .menu-item:hover .menu-badge {
            background: white;
            transform: translateY(-2px);
        }

        /* Enhanced Action Buttons */
        .action-btn {
            background: linear-gradient(135deg, var(--card-bg), white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(59, 31, 43, 0.08);
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .action-btn:hover {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            border-color: var(--gold);
            color: var(--deep);
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 20px rgba(201, 169, 110, 0.2);
        }

        .action-btn:hover::before {
            opacity: 1;
        }

        .action-btn:hover i {
            transform: scale(1.2);
            animation: pulse 1s infinite;
        }

        .action-btn i {
            font-size: 26px;
            transition: all 0.3s ease;
        }

        .action-btn span {
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        /* Icon-specific colors */
        .action-btn:nth-child(1) i {
            color: var(--info);
        }

        /* Upload */
        .action-btn:nth-child(2) i {
            color: var(--purple);
        }

        /* Plan Trip */
        .action-btn:nth-child(3) i {
            color: #FF6B6B;
        }

        /* My Bookings - Red */
        .action-btn:nth-child(4) i {
            color: var(--success);
        }

        /* Explore - Green */
        .action-btn:nth-child(5) i {
            color: var(--gold);
        }

        /* Profile - Gold */
        .action-btn:nth-child(6) i {
            color: var(--warning);
        }

        /* Settings - Orange */

        .action-btn:hover i {
            color: var(--deep) !important;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Enhanced Trip Icons */
        .trip-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--deep);
            font-size: 20px;
            box-shadow: 0 4px 8px rgba(201, 169, 110, 0.3);
            transition: all 0.3s ease;
        }

        .trip-item:hover .trip-icon {
            transform: rotate(5deg) scale(1.1);
            box-shadow: 0 6px 12px rgba(201, 169, 110, 0.4);
        }

        /* Enhanced Stat Cards */
        .stat-card {
            background: linear-gradient(135deg, white, var(--card-bg));
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(59, 31, 43, 0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(59, 31, 43, 0.15);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            background: rgba(33, 150, 243, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: rotate(10deg) scale(1.1);
        }

        .stat-icon.photos {
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.2), rgba(33, 150, 243, 0.1));
            color: var(--info);
        }

        .stat-icon.trips {
            background: linear-gradient(135deg, rgba(156, 39, 176, 0.2), rgba(156, 39, 176, 0.1));
            color: var(--purple);
        }

        .stat-icon.bookings {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.2), rgba(76, 175, 80, 0.1));
            color: var(--success);
        }

        .stat-icon.saved {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.2), rgba(255, 152, 0, 0.1));
            color: var(--warning);
        }

        /* Keep existing styles for the rest */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: absolute;
            bottom: 0;
            width: 100%;
            background: var(--deep);
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            border-color: white;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar .avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--gold), var(--deep-alt));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .user-info h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 3px;
        }

        .user-info p {
            font-size: 12px;
            color: var(--text-sub);
            background: rgba(255, 255, 255, 0.1);
            padding: 3px 8px;
            border-radius: 10px;
            display: inline-block;
        }

        /* ── Main Content ── */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 25px;
            transition: all 0.3s ease;
            max-width: calc(100% - 260px);
            background: var(--cream);
            min-height: 100vh;
        }

        /* ── Top Navigation ── */
        .top-nav {
            background: linear-gradient(135deg, white, var(--card-bg));
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(59, 31, 43, 0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-left h1 {
            font-size: 26px;
            font-weight: 700;
            color: var(--deep);
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--deep), var(--deep-alt));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-left p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--gold);
            cursor: pointer;
            flex-shrink: 0;
            box-shadow: 0 4px 8px rgba(201, 169, 110, 0.3);
            transition: all 0.3s ease;
        }

        .nav-profile-pic:hover {
            transform: scale(1.05);
            border-color: var(--deep);
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
            font-weight: 700;
            font-size: 18px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 14px 20px 14px 50px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            width: 320px;
            background: white;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
            transform: translateY(-2px);
        }

        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold);
            font-size: 16px;
        }

        .notification-btn {
            background: linear-gradient(135deg, white, var(--card-bg));
            border: 2px solid var(--border);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            font-size: 20px;
            color: var(--deep);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            border-color: var(--gold);
            background: var(--gold);
            color: var(--deep);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(201, 169, 110, 0.2);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            font-size: 11px;
            padding: 3px 7px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            font-weight: 700;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* ── Stats Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-info h3 {
            font-size: 32px;
            font-weight: 700;
            color: var(--deep);
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-change {
            font-size: 13px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
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
            background: linear-gradient(135deg, white, var(--card-bg));
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(59, 31, 43, 0.08);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .section-header {
            padding: 22px 30px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 248, 242, 0.9));
        }

        .section-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--deep);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header .btn {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            color: var(--deep);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(201, 169, 110, 0.3);
        }

        .section-header .btn:hover {
            background: linear-gradient(135deg, var(--gold-hover), var(--gold));
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(201, 169, 110, 0.4);
        }

        .section-header .btn i {
            margin-right: 8px;
        }

        .section-content {
            padding: 30px;
        }

        /* Enhanced Action Button Icons */
        .action-btn .fa-upload {
            color: var(--info) !important;
        }

        .action-btn .fa-plus-circle {
            color: var(--purple) !important;
        }

        .action-btn .fa-ticket-alt {
            color: #FF6B6B !important;
        }

        /* Red for bookings */
        .action-btn .fa-globe {
            color: var(--success) !important;
        }

        /* Green for explore */
        .action-btn .fa-user-circle {
            color: var(--gold) !important;
        }

        .action-btn .fa-cog {
            color: var(--warning) !important;
        }

        .action-btn:hover .fa-upload,
        .action-btn:hover .fa-plus-circle,
        .action-btn:hover .fa-ticket-alt,
        .action-btn:hover .fa-globe,
        .action-btn:hover .fa-user-circle,
        .action-btn:hover .fa-cog {
            color: var(--deep) !important;
        }

        /* Keep the rest of your existing styles below... */
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
        }

        .viewer-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 60px 20px 15px;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.7) 50%, transparent 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(20px);
        }

        .viewer-header-btn {
            background: rgba(255, 255, 255, 0.15);
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
            background: rgba(255, 255, 255, 0.25);
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
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.7) 50%, transparent 100%);
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
            background: rgba(255, 255, 255, 0.15);
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
            background: rgba(255, 255, 255, 0.25);
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
            background: rgba(255, 255, 255, 0.15);
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
            background: rgba(255, 255, 255, 0.25);
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
            background: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            backdrop-filter: blur(20px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .viewer-container:hover .video-controls {
            opacity: 1;
        }

        .video-play-btn {
            background: rgba(255, 255, 255, 0.2);
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
            background: rgba(255, 255, 255, 0.3);
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
            background: rgba(255, 255, 255, 0.15);
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
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.1);
        }

        /* EXIF Info */
        .exif-overlay {
            position: absolute;
            top: 120px;
            right: 30px;
            background: rgba(0, 0, 0, 0.7);
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
            padding: 18px;
            border: 2px solid var(--border);
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
            cursor: pointer;
        }

        .trip-item:hover {
            border-color: var(--gold);
            background: linear-gradient(135deg, rgba(201, 169, 110, 0.05), rgba(201, 169, 110, 0.1));
            transform: translateX(5px);
            box-shadow: 0 6px 12px rgba(201, 169, 110, 0.1);
        }

        .trip-info h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--deep);
            margin-bottom: 5px;
        }

        .trip-info p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .trip-status {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-confirmed {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(76, 175, 80, 0.1));
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 152, 0, 0.1));
            color: var(--warning);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .status-cancelled {
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.15), rgba(244, 67, 54, 0.1));
            color: var(--danger);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        /* ── Quick Actions ── */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        /* ── Modals ── */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 22px 30px;
            background: linear-gradient(135deg, var(--deep), var(--deep-alt));
            color: var(--text-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 22px;
            font-weight: 700;
        }

        .close-modal {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: var(--text-light);
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(90deg);
        }

        .modal-tabs {
            display: flex;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
        }

        .modal-tab {
            padding: 16px 30px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 15px;
            font-weight: 600;
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

        /* Upload Interface */
        .upload-interface {
            display: none;
        }

        .upload-interface.active {
            display: block;
        }

        .upload-area {
            border: 3px dashed var(--border);
            border-radius: 12px;
            padding: 50px 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--card-bg), white);
        }

        .upload-area:hover {
            border-color: var(--gold);
            background: linear-gradient(135deg, rgba(201, 169, 110, 0.05), rgba(201, 169, 110, 0.1));
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(201, 169, 110, 0.1);
        }

        .upload-icon {
            font-size: 52px;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .file-input {
            display: none;
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: linear-gradient(135deg, var(--deep), var(--deep-alt));
            color: var(--text-light);
            padding: 18px 28px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            font-weight: 600;
            border-left: 4px solid var(--gold);
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.success {
            background: linear-gradient(135deg, var(--success), #43a047);
        }

        .toast.error {
            background: linear-gradient(135deg, var(--danger), #e53935);
        }

        /* Upload Progress */
        .upload-progress {
            width: 100%;
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            margin: 15px 0;
            overflow: hidden;
        }

        .upload-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--gold), var(--gold-hover));
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 3px;
        }

        /* Media Storage */
        .storage-info {
            padding: 20px;
            background: linear-gradient(135deg, var(--card-bg), white);
            border-radius: 12px;
            border: 2px solid var(--border);
        }

        .storage-bar {
            width: 100%;
            height: 10px;
            background: var(--border);
            border-radius: 5px;
            margin: 15px 0;
            overflow: hidden;
        }

        .storage-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold), var(--deep-alt));
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 5px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .search-box input {
                width: 250px;
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
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
                font-size: 26px;
                color: var(--deep);
                cursor: pointer;
                padding: 10px;
            }

            .nav-left h1 {
                font-size: 22px;
            }

            .search-box input {
                width: 200px;
            }

            .iphone-gallery {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
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
                justify-content: space-between;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
            }

            .iphone-gallery {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
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

        @media (max-width: 768px) {
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

        /* Loading Spinner */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border);
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        /* Trip Form */
        .trip-form {
            display: grid;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--deep);
            font-size: 14px;
        }

        .form-control {
            padding: 12px 18px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 25px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
            <span class="logo-text">Smart Booking</span>
        </div>

        <div class="sidebar-menu">
            <a href="{{ route('dashboard') }}" class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" onclick="openPhotoManager()">
                <i class="fas fa-images"></i>
                <span>Media</span>
                <span class="menu-badge" id="totalMediaCount">0</span>
            </a>
            @if(auth()->user()->user_type === 'agency')
                <a href="{{ route('agency.flights') }}" class="menu-item">
                    <i class="fas fa-plane"></i>
                    <span>My Flights</span>
                </a>
                <a href="{{ route('agency.bookings') }}" class="menu-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
            @else
                @if (Route::has('trips.index'))
                    <a href="{{ route('trips.index') }}" class="menu-item">
                        <i class="fas fa-route"></i>
                        <span>My Trips</span>
                    </a>
                @endif
                <a href="{{ route('bookings.index') }}" class="menu-item">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Bookings</span>
                    <span class="menu-badge">{{ auth()->user()->bookings()->count() }}</span>
                </a>
            @endif
            <a href="{{ route('destinations') }}" class="menu-item">
                <i class="fas fa-globe"></i>
                <span>Explore</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="{{ route('settings') }}" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="nav-profile-pic">
                <div class="user-avatar">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->profile_picture_url }}" alt="Profile picture">
                    @else
                        <div class="avatar-placeholder">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="user-info">
                    <h4>{{ auth()->user()->name }}</h4>
                    <p>
                        @if(auth()->user()->user_type === 'agency')
                            {{ auth()->user()->agency_name }}
                        @else
                            Traveler
                        @endif
                    </p>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button class="btn" onclick="logout()"
                    style="margin-left: auto; padding: 5px 10px; background: rgba(255,255,255,0.1); border: 1px solid var(--gold); color: var(--text-light); border-radius: 6px; transition: all 0.3s ease;">
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
                <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="nav-profile-pic" onclick="openProfileModal()">
                    @if(auth()->user()->profile_picture)
                        <img src="{{ auth()->user()->profile_picture_url }}" alt="Profile picture">
                    @else
                        <div class="avatar-placeholder">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    @endif

                </div>
                <div>
                    <h1>Welcome back, {{ auth()->user()->name }}!</h1>
                    <p>Here's what's happening with your travel plans</p>
                </div>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search trips, photos..." id="globalSearch">
                </div>
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <button class="btn" onclick="openUploadModal()">
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
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12 this week</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon trips">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ auth()->user()->trips()->count() }}</h3>
                    <p>Active Trips</p>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+2 this month</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bookings">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ auth()->user()->bookings()->count() }}</h3>
                    <p>Bookings</p>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+3 upcoming</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon saved">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ auth()->user()->savedDestinations()->count() }}</h3>
                    <p>Saved Places</p>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5 new</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Recent Media Gallery -->
                <div class="dashboard-section mb-20">
                    <div class="section-header">
                        <h2><i class="fas fa-camera"></i> Recent Travel Media</h2>
                        <div>
                            <button class="btn" onclick="loadMoreMedia()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                            <button class="btn" onclick="openUploadModal()" style="margin-left: 10px;">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                    </div>
                    <div class="section-content" style="padding: 0;">
                        <div class="gallery-toolbar">
                            <div class="gallery-toolbar-left">
                                <button class="gallery-toolbar-btn" onclick="selectAllMedia()" id="selectAllBtn">
                                    <i class="far fa-square"></i>
                                </button>
                                <span class="gallery-title" id="galleryTitle">Loading media...</span>
                            </div>
                            <div class="gallery-toolbar-right">
                                <button class="gallery-toolbar-btn" onclick="deleteSelectedMedia()"
                                    id="deleteSelectedBtn" style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="gallery-toolbar-btn" onclick="shareSelectedMedia()" id="shareSelectedBtn"
                                    style="display: none;">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="iphone-gallery" id="mediaGallery">
                            <!-- Media will be loaded here -->
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Trips -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-plane"></i> Upcoming Trips</h2>
                        <button class="btn" onclick="createNewTrip()">
                            <i class="fas fa-plus"></i> New Trip
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
                            <div class="action-btn" onclick="openUploadModal()">
                                <i class="fas fa-upload"></i>
                                <span>Upload Media</span>
                            </div>
                            <div class="action-btn" onclick="createNewTrip()">
                                <i class="fas fa-plus-circle"></i>
                                <span>Plan Trip</span>
                            </div>
                            @if(auth()->user()->user_type === 'agency')
                                <a href="{{ route('flights.create') }}" class="action-btn">
                                    <i class="fas fa-plane"></i>
                                    <span>Add Flight</span>
                                </a>
                            @else
                                <a href="{{ route('bookings.index') }}" class="action-btn">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span>My Bookings</span>
                                </a>
                            @endif
                            <a href="{{ route('destinations') }}" class="action-btn">
                                <i class="fas fa-globe"></i>
                                <span>Explore</span>
                            </a>
                            <div class="action-btn" onclick="openProfileModal()">
                                <i class="fas fa-user-circle"></i>
                                <span>Profile</span>
                            </div>
                            <div class="action-btn" onclick="openSettings()">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Storage Info -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Storage</h2>
                    </div>
                    <div class="section-content">
                        <div class="storage-info">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span class="text-muted">Used Space</span>
                                <span id="usedSpace">0 MB / 500 MB</span>
                            </div>
                            <div class="storage-bar">
                                <div class="storage-fill" id="storageFill"></div>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; margin-top: 12px; font-size: 13px;">
                                <span class="text-muted">Photos: <span id="photoCountBadge">0</span></span>
                                <span class="text-muted">Videos: <span id="videoCountBadge">0</span></span>
                            </div>
                            <p class="text-muted" style="font-size: 12px; margin-top: 12px;">
                                <i class="fas fa-info-circle"></i> Upgrade for unlimited storage
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Viewer Modal -->
    <div class="iphone-viewer" id="mediaViewer">
        <div class="iphone-container">
            <div class="dynamic-island">
                <div class="dynamic-island-dot"></div>
                <div class="dynamic-island-dot"></div>
                <div class="dynamic-island-dot"></div>
            </div>

            <div class="viewer-header">
                <button class="viewer-header-btn" onclick="closeViewer()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="viewer-title" id="viewerTitle">Media Viewer</div>
                <button class="viewer-header-btn" onclick="toggleFavorite()" id="favoriteBtn">
                    <i class="far fa-heart"></i>
                </button>
            </div>

            <div class="viewer-container">
                <button class="viewer-nav-btn prev" onclick="prevMedia()">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="media-viewport" id="mediaViewport">
                    <img id="viewerImage" class="viewer-media" src="" alt="" style="display: none;">
                    <video id="viewerVideo" class="viewer-media" controls style="display: none;"></video>
                    <div class="loading-indicator" id="viewerLoading">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </div>
                </div>

                <button class="viewer-nav-btn next" onclick="nextMedia()">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Video Controls -->
                <div class="video-controls" id="videoControls" style="display: none;">
                    <button class="video-play-btn" onclick="togglePlay()">
                        <i class="fas fa-play" id="playIcon"></i>
                    </button>
                    <div class="video-progress" onclick="seekVideo(event)">
                        <div class="video-progress-bar" id="videoProgress"></div>
                    </div>
                    <div class="video-time" id="videoTime">0:00 / 0:00</div>
                </div>
            </div>

            <div class="viewer-footer">
                <div class="viewer-info">
                    <div class="viewer-info-title" id="mediaTitle"></div>
                    <div class="viewer-info-meta">
                        <span id="mediaDate"></span>
                        <span id="mediaLocation"></span>
                    </div>
                </div>
                <div class="viewer-controls">
                    <button class="viewer-control-btn" onclick="downloadMedia()">
                        <i class="fas fa-download"></i> Save
                    </button>
                    <button class="viewer-control-btn" onclick="shareMedia()">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <button class="viewer-control-btn" onclick="deleteMedia()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal" id="uploadModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Media</h2>
                <button class="close-modal" onclick="closeUploadModal()">&times;</button>
            </div>
            <div class="modal-tabs">
                <button class="modal-tab active" onclick="switchTab('upload')">
                    <i class="fas fa-upload"></i> Upload Files
                </button>
                <button class="modal-tab" onclick="switchTab('camera')">
                    <i class="fas fa-camera"></i> Camera
                </button>
            </div>
            <div class="modal-body">
                <!-- Upload Tab -->
                <div id="uploadTab" class="upload-interface active">
                    <div class="upload-area" onclick="document.getElementById('fileInput').click()" id="dropArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h3>Click to browse or drag & drop</h3>
                        <p class="text-muted">Support: JPG, PNG, MP4, MOV • Max 100MB per file</p>
                        <input type="file" id="fileInput" multiple style="display: none;" accept="image/*,video/*">
                    </div>

                    <div id="uploadPreview" style="display: none;">
                        <h4>Selected Files</h4>
                        <div id="previewGrid" class="iphone-gallery" style="margin: 15px 0; gap: 5px;"></div>

                        <div class="form-group">
                            <label for="tripSelect">Assign to Trip (Optional)</label>
                            <select id="tripSelect" class="form-control">
                                <option value="">No trip selected</option>
                                <!-- Trips will be loaded here -->
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="mediaLocation">Location</label>
                            <input type="text" id="mediaLocation" class="form-control"
                                placeholder="Where was this taken?">
                        </div>

                        <div class="upload-progress">
                            <div class="upload-progress-bar" id="uploadProgress"></div>
                        </div>

                        <div class="form-actions">
                            <button class="btn" onclick="clearSelection()">
                                <i class="fas fa-times"></i> Clear
                            </button>
                            <button class="btn" onclick="startUpload()" style="background: var(--gold);">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Camera Tab -->
                <div id="cameraTab" class="upload-interface">
                    <div style="text-align: center;">
                        <video id="cameraFeed" autoplay playsinline
                            style="width: 100%; max-height: 400px; border-radius: 8px; background: #000;"></video>
                        <canvas id="cameraCanvas" style="display: none;"></canvas>

                        <div class="form-actions" style="margin-top: 20px;">
                            <button class="btn" onclick="startCamera()" id="startCameraBtn">
                                <i class="fas fa-play"></i> Start Camera
                            </button>
                            <button class="btn" onclick="capturePhoto()" id="captureBtn"
                                style="display: none; background: var(--success);">
                                <i class="fas fa-camera"></i> Capture
                            </button>
                            <button class="btn" onclick="stopCamera()" id="stopCameraBtn"
                                style="display: none; background: var(--danger);">
                                <i class="fas fa-stop"></i> Stop
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal" id="profileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Profile Settings</h2>
                <button class="close-modal" onclick="closeProfileModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="profileForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Profile Picture</label>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <div class="user-avatar" style="width: 100px; height: 100px;">
                                @if(auth()->user()->avatar)
                                    <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Profile" id="profilePreview">
                                @else
                                    <div class="avatar-placeholder" id="profilePlaceholder">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                            <button type="button" class="btn" onclick="document.getElementById('avatarInput').click()">
                                <i class="fas fa-camera"></i> Change Photo
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" class="form-control" value="{{ auth()->user()->name }}">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" value="{{ auth()->user()->email }}">
                    </div>

                    @if(auth()->user()->user_type === 'agency')
                        <div class="form-group">
                            <label for="agency_name">Agency Name</label>
                            <input type="text" id="agency_name" class="form-control"
                                value="{{ auth()->user()->agency_name }}">
                        </div>
                    @endif

                    <div class="form-actions">
                        <button type="button" class="btn" onclick="closeProfileModal()">
                            Cancel
                        </button>
                        <button type="button" class="btn" onclick="updateProfile()" style="background: var(--gold);">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script>
        // Global variables
        let mediaItems = [];
        let currentMediaIndex = 0;
        let selectedMedia = new Set();
        let uploadFiles = [];
        let currentTripId = null;
        let cameraStream = null;
        let isUploading = false;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function () {
            loadStats();
            loadMedia();
            loadTrips();
            loadUpcomingTrips();
            setupEventListeners();
        });

        // Load statistics
        async function loadStats() {
            try {
                const response = await fetch('/api/dashboard/stats');
                const data = await response.json();

                document.getElementById('totalPhotos').textContent = data.total_photos || 0;
                document.getElementById('totalMediaCount').textContent = data.total_media || 0;
                document.getElementById('photoCountBadge').textContent = data.photos || 0;
                document.getElementById('videoCountBadge').textContent = data.videos || 0;

                // Update storage info
                const usedMB = data.used_storage || 0;
                const totalMB = 500;
                const percent = (usedMB / totalMB) * 100;

                document.getElementById('usedSpace').textContent = `${usedMB} MB / ${totalMB} MB`;
                document.getElementById('storageFill').style.width = `${percent}%`;

            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Load media from server
        async function loadMedia() {
            const gallery = document.getElementById('mediaGallery');
            gallery.innerHTML = '<div class="loading-spinner"></div>';

            try {
                const response = await fetch('/api/media', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(await response.text());
                }

                const data = await response.json();

                // ✅ FIX: backend may return array OR { media: [] }
                mediaItems = Array.isArray(data) ? data : (data.media || []);

                if (mediaItems.length === 0) {
                    gallery.innerHTML = `
                <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: white;">
                    <i class="fas fa-camera" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p style="color: var(--text-muted);">No media yet.</p>
                </div>
            `;
                } else {
                    renderMediaGallery();
                }

                document.getElementById('galleryTitle').textContent =
                    `${mediaItems.length} items`;

            } catch (error) {
                console.error('Error loading media:', error);
                gallery.innerHTML = `
            <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: red;">
                Failed to load media
            </div>
        `;
            }
        }



        // Render media gallery
        function renderMediaGallery() {
            const gallery = document.getElementById('mediaGallery');
            gallery.innerHTML = '';

            mediaItems.forEach((media, index) => {
                const isSelected = selectedMedia.has(media.id);
                const item = document.createElement('div');
                item.className = `gallery-item ${isSelected ? 'selected' : ''}`;
                item.dataset.id = media.id;
                item.dataset.index = index;

                item.innerHTML = `
                    ${media.type === 'image'
                        ? `<img src="${media.thumbnail || media.url}" alt="${media.title}" loading="lazy">`
                        : `<video src="${media.url}" poster="${media.thumbnail}" alt="${media.title}" loading="lazy"></video>`
                    }
                    <div class="media-badge ${media.type === 'video' ? 'video-badge' : ''}">
                        <i class="fas fa-${media.type === 'video' ? 'video' : 'image'}"></i>
                        ${media.duration || ''}
                    </div>
                    <div class="selected-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                `;

                item.addEventListener('click', (e) => {
                    if (e.shiftKey || e.ctrlKey || e.metaKey) {
                        toggleMediaSelection(media.id);
                    } else {
                        viewMedia(index);
                    }
                });

                gallery.appendChild(item);
            });
        }

        // Load trips for dropdown
        async function loadTrips() {
            try {
                const response = await fetch('/api/trips', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(await response.text());
                }

                const trips = await response.json();
                const select = document.getElementById('tripSelect');

                select.innerHTML = '<option value="">No trip selected</option>';

                trips.forEach(trip => {
                    const option = document.createElement('option');
                    option.value = trip.id;
                    option.textContent = trip.name;
                    select.appendChild(option);
                });

            } catch (error) {
                console.error('Error loading trips:', error);
            }
        }

        // Load upcoming trips
        async function loadUpcomingTrips() {
            const container = document.getElementById('upcomingTrips');

            try {
                const response = await fetch('/api/trips/upcoming', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(await response.text());
                }

                const trips = await response.json();

                if (trips.length === 0) {
                    container.innerHTML = `
                <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                    <i class="fas fa-plane"></i>
                    <p>No upcoming trips</p>
                </div>
            `;
                    return;
                }

                container.innerHTML = '';

                trips.forEach(trip => {
                    const item = document.createElement('div');
                    item.className = 'trip-item';
                    item.innerHTML = `
                <div class="trip-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="trip-info">
                    <h4>${trip.destination}</h4>
                    <p>${trip.start_date} - ${trip.end_date}</p>
                    <span class="trip-status status-${trip.status}">
                        ${trip.status}
                    </span>
                </div>
            `;
                    item.onclick = () => viewTrip(trip.id);
                    container.appendChild(item);
                });

            } catch (error) {
                console.error('Error loading upcoming trips:', error);
                container.innerHTML = `<p style="color:red">Failed to load trips</p>`;
            }
        }

        // Media selection
        function toggleMediaSelection(mediaId) {
            if (selectedMedia.has(mediaId)) {
                selectedMedia.delete(mediaId);
            } else {
                selectedMedia.add(mediaId);
            }

            renderMediaGallery();
            updateSelectionToolbar();
        }

        function selectAllMedia() {
            const allSelected = selectedMedia.size === mediaItems.length;

            if (allSelected) {
                selectedMedia.clear();
            } else {
                mediaItems.forEach(media => selectedMedia.add(media.id));
            }

            renderMediaGallery();
            updateSelectionToolbar();
        }

        function updateSelectionToolbar() {
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            const shareBtn = document.getElementById('shareSelectedBtn');
            const selectBtn = document.getElementById('selectAllBtn');

            if (selectedMedia.size > 0) {
                deleteBtn.style.display = 'block';
                shareBtn.style.display = 'block';
                selectBtn.innerHTML = '<i class="fas fa-check-square"></i>';
            } else {
                deleteBtn.style.display = 'none';
                shareBtn.style.display = 'none';
                selectBtn.innerHTML = '<i class="far fa-square"></i>';
            }
        }

        // Delete selected media
        async function deleteSelectedMedia() {
            if (selectedMedia.size === 0) return;

            const confirmed = await Swal.fire({
                title: 'Delete Media?',
                text: `Are you sure you want to delete ${selectedMedia.size} item${selectedMedia.size > 1 ? 's' : ''}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Delete'
            });

            if (!confirmed.isConfirmed) return;

            try {
                const response = await fetch('/api/media/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ids: Array.from(selectedMedia) })
                });

                if (response.ok) {
                    showToast(`Deleted ${selectedMedia.size} item${selectedMedia.size > 1 ? 's' : ''}`, 'success');
                    selectedMedia.clear();
                    await loadMedia();
                    await loadStats();
                } else {
                    throw new Error('Delete failed');
                }
            } catch (error) {
                showToast('Failed to delete media', 'error');
            }
        }

        // Share selected media
        async function shareSelectedMedia() {
            if (selectedMedia.size === 0) return;

            const selectedItems = mediaItems.filter(media => selectedMedia.has(media.id));

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'My Travel Photos',
                        text: `Check out my ${selectedItems.length} travel photo${selectedItems.length > 1 ? 's' : ''}!`,
                        url: window.location.href
                    });
                } catch (error) {
                    console.log('Share cancelled');
                }
            } else {
                // Fallback: copy first media URL to clipboard
                const firstMedia = selectedItems[0];
                await navigator.clipboard.writeText(firstMedia.url);
                showToast('Media URL copied to clipboard', 'success');
            }
        }

        // Media viewer functions
        function viewMedia(index) {
            currentMediaIndex = index;
            const media = mediaItems[index];

            if (!media) return;

            const viewer = document.getElementById('mediaViewer');
            const image = document.getElementById('viewerImage');
            const video = document.getElementById('viewerVideo');
            const loading = document.getElementById('viewerLoading');
            const videoControls = document.getElementById('videoControls');
            const favoriteBtn = document.getElementById('favoriteBtn');

            // Reset
            image.style.display = 'none';
            video.style.display = 'none';
            loading.style.display = 'flex';
            videoControls.style.display = 'none';

            // Set media info
            document.getElementById('viewerTitle').textContent = media.title || 'Media';
            document.getElementById('mediaTitle').textContent = media.title || 'Untitled';
            document.getElementById('mediaDate').textContent = new Date(media.created_at).toLocaleDateString();
            document.getElementById('mediaLocation').textContent = media.location || 'Unknown location';

            // Update favorite button
            favoriteBtn.innerHTML = media.is_favorite
                ? '<i class="fas fa-heart" style="color: #ff4757;"></i>'
                : '<i class="far fa-heart"></i>';

            // Load media
            if (media.type === 'image') {
                image.src = media.url;
                image.onload = () => {
                    loading.style.display = 'none';
                    image.style.display = 'block';
                };
            } else if (media.type === 'video') {
                video.src = media.url;
                video.onloadedmetadata = () => {
                    loading.style.display = 'none';
                    video.style.display = 'block';
                    videoControls.style.display = 'flex';
                };
            }

            viewer.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeViewer() {
            const viewer = document.getElementById('mediaViewer');
            const video = document.getElementById('viewerVideo');

            viewer.classList.remove('active');
            document.body.style.overflow = 'auto';

            if (video) {
                video.pause();
            }
        }

        function prevMedia() {
            if (currentMediaIndex > 0) {
                currentMediaIndex--;
                viewMedia(currentMediaIndex);
            }
        }

        function nextMedia() {
            if (currentMediaIndex < mediaItems.length - 1) {
                currentMediaIndex++;
                viewMedia(currentMediaIndex);
            }
        }

        async function toggleFavorite() {
            const media = mediaItems[currentMediaIndex];
            if (!media) return;

            try {
                const response = await fetch(`/api/media/${media.id}/favorite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    media.is_favorite = !media.is_favorite;
                    const favoriteBtn = document.getElementById('favoriteBtn');
                    favoriteBtn.innerHTML = media.is_favorite
                        ? '<i class="fas fa-heart" style="color: #ff4757;"></i>'
                        : '<i class="far fa-heart"></i>';

                    showToast(media.is_favorite ? 'Added to favorites' : 'Removed from favorites', 'success');
                }
            } catch (error) {
                showToast('Failed to update favorite', 'error');
            }
        }

        function togglePlay() {
            const video = document.getElementById('viewerVideo');
            const playIcon = document.getElementById('playIcon');

            if (video.paused) {
                video.play();
                playIcon.className = 'fas fa-pause';
            } else {
                video.pause();
                playIcon.className = 'fas fa-play';
            }
        }

        function seekVideo(event) {
            const video = document.getElementById('viewerVideo');
            const progress = document.getElementById('videoProgress');
            const rect = event.currentTarget.getBoundingClientRect();
            const percent = (event.clientX - rect.left) / rect.width;

            video.currentTime = percent * video.duration;
            progress.style.width = `${percent * 100}%`;
        }

        // Upload functions
        function openUploadModal() {
            document.getElementById('uploadModal').classList.add('active');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
            clearSelection();
            stopCamera();
        }

        function switchTab(tabName) {
            // Update tabs
            document.querySelectorAll('.modal-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.upload-interface').forEach(tab => {
                tab.classList.remove('active');
            });

            // Activate selected tab
            document.querySelector(`[onclick="switchTab('${tabName}')"]`).classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');

            if (tabName === 'camera') {
                startCamera();
            } else {
                stopCamera();
            }
        }

        // File upload handling
        document.getElementById('fileInput').addEventListener('change', handleFileSelect);

        function handleFileSelect(event) {
            uploadFiles = Array.from(event.target.files);
            displayPreview();
        }

        function displayPreview() {
            const previewGrid = document.getElementById('previewGrid');
            const uploadPreview = document.getElementById('uploadPreview');

            if (uploadFiles.length === 0) {
                uploadPreview.style.display = 'none';
                return;
            }

            previewGrid.innerHTML = '';
            uploadFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const isVideo = file.type.startsWith('video/');
                    const item = document.createElement('div');
                    item.className = 'gallery-item';

                    item.innerHTML = `
                        ${isVideo
                            ? `<video src="${e.target.result}" alt="${file.name}" loading="lazy"></video>`
                            : `<img src="${e.target.result}" alt="${file.name}" loading="lazy">`
                        }
                        <div class="media-badge ${isVideo ? 'video-badge' : ''}">
                            <i class="fas fa-${isVideo ? 'video' : 'image'}"></i>
                            ${(file.size / 1024 / 1024).toFixed(1)} MB
                        </div>
                    `;

                    previewGrid.appendChild(item);
                };
                reader.readAsDataURL(file);
            });

            uploadPreview.style.display = 'block';
        }

        // Drag and drop
        const dropArea = document.getElementById('dropArea');

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.style.borderColor = 'var(--gold)';
            dropArea.style.background = 'rgba(201,169,110,0.05)';
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.style.borderColor = '';
            dropArea.style.background = '';
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.style.borderColor = '';
            dropArea.style.background = '';

            const files = Array.from(e.dataTransfer.files).filter(file =>
                file.type.startsWith('image/') || file.type.startsWith('video/')
            );

            if (files.length > 0) {
                uploadFiles = files;
                displayPreview();
                showToast(`${files.length} file${files.length > 1 ? 's' : ''} ready for upload`, 'success');
            }
        });

        async function startUpload() {
            if (isUploading || uploadFiles.length === 0) return;

            isUploading = true;
            const progressBar = document.getElementById('uploadProgress');
            const formData = new FormData();

            uploadFiles.forEach(file => {
                formData.append('media[]', file);
            });

            const tripId = document.getElementById('tripSelect').value;
            const location = document.getElementById('mediaLocation').value;

            if (tripId) formData.append('trip_id', tripId);
            if (location) formData.append('location', location);

            try {
                const response = await fetch('/api/media/upload', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(await response.text());
                }

                showToast('Upload successful!', 'success');
                closeUploadModal();
                await loadMedia();
                await loadStats();

            } catch (error) {
                console.error('Upload error:', error);
                showToast('Upload failed', 'error');
            } finally {
                isUploading = false;
                progressBar.style.width = '0%';
            }
        }

        function clearSelection() {
            uploadFiles = [];
            document.getElementById('fileInput').value = '';
            document.getElementById('uploadPreview').style.display = 'none';
            document.getElementById('tripSelect').value = '';
            document.getElementById('mediaLocation').value = '';
        }

        // Camera functions
        async function startCamera() {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment',
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    }
                });

                const video = document.getElementById('cameraFeed');
                video.srcObject = cameraStream;

                document.getElementById('startCameraBtn').style.display = 'none';
                document.getElementById('captureBtn').style.display = 'block';
                document.getElementById('stopCameraBtn').style.display = 'block';

            } catch (error) {
                showToast('Camera access denied or not available', 'error');
            }
        }

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }

            const video = document.getElementById('cameraFeed');
            video.srcObject = null;

            document.getElementById('startCameraBtn').style.display = 'block';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('stopCameraBtn').style.display = 'none';
        }

        function capturePhoto() {
            const video = document.getElementById('cameraFeed');
            const canvas = document.getElementById('cameraCanvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(async (blob) => {
                const file = new File([blob], `photo_${Date.now()}.jpg`, { type: 'image/jpeg' });
                uploadFiles = [file];

                // Switch to upload tab
                switchTab('upload');
                displayPreview();

                showToast('Photo captured! Ready to upload.', 'success');
            }, 'image/jpeg', 0.9);
        }

        // Profile functions
        function openProfileModal() {
            document.getElementById('profileModal').classList.add('active');
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.remove('active');
        }

        async function updateProfile() {
            const formData = new FormData();
            formData.append('name', document.getElementById('name').value);
            formData.append('email', document.getElementById('email').value);

            if (document.getElementById('agency_name')) {
                formData.append('agency_name', document.getElementById('agency_name').value);
            }

            const avatarInput = document.getElementById('avatarInput');
            if (avatarInput.files.length > 0) {
                formData.append('avatar', avatarInput.files[0]);
            }

            try {
                const response = await fetch('/api/profile/update', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                if (response.ok) {
                    showToast('Profile updated successfully!', 'success');
                    closeProfileModal();
                    location.reload(); // Refresh to show updated profile
                } else {
                    throw new Error('Update failed');
                }
            } catch (error) {
                showToast('Failed to update profile', 'error');
            }
        }

        // Event listeners
        function setupEventListeners() {
            // Global search
            const searchInput = document.getElementById('globalSearch');
            let searchTimeout;

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterMedia(searchInput.value);
                }, 300);
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeUploadModal();
                    closeProfileModal();
                    if (document.getElementById('mediaViewer').classList.contains('active')) {
                        closeViewer();
                    }
                }

                if (e.key === 'ArrowLeft' && document.getElementById('mediaViewer').classList.contains('active')) {
                    prevMedia();
                }

                if (e.key === 'ArrowRight' && document.getElementById('mediaViewer').classList.contains('active')) {
                    nextMedia();
                }
            });

            // Profile picture preview
            document.getElementById('avatarInput').addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = document.getElementById('profilePreview');
                        const placeholder = document.getElementById('profilePlaceholder');

                        if (preview) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                            if (placeholder) placeholder.style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Filter media by search
        function filterMedia(query) {
            if (!query.trim()) {
                renderMediaGallery();
                return;
            }

            const filtered = mediaItems.filter(media =>
                (media.title && media.title.toLowerCase().includes(query.toLowerCase())) ||
                (media.description && media.description.toLowerCase().includes(query.toLowerCase())) ||
                (media.location && media.location.toLowerCase().includes(query.toLowerCase()))
            );

            const gallery = document.getElementById('mediaGallery');
            gallery.innerHTML = '';

            if (filtered.length === 0) {
                gallery.innerHTML = `
                    <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: white;">
                        <i class="fas fa-search" style="font-size: 48px; color: var(--deep); margin-bottom: 15px;"></i>
                        <p style="color: var(--text-muted);">No results for "${query}"</p>
                    </div>
                `;
            } else {
                filtered.forEach((media, index) => {
                    const item = document.createElement('div');
                    item.className = 'gallery-item';
                    item.dataset.id = media.id;

                    item.innerHTML = `
                        ${media.type === 'image'
                            ? `<img src="${media.thumbnail || media.url}" alt="${media.title}" loading="lazy">`
                            : `<video src="${media.url}" poster="${media.thumbnail}" alt="${media.title}" loading="lazy"></video>`
                        }
                        <div class="media-badge ${media.type === 'video' ? 'video-badge' : ''}">
                            <i class="fas fa-${media.type === 'video' ? 'video' : 'image'}"></i>
                            ${media.duration || ''}
                        </div>
                    `;

                    item.addEventListener('click', () => {
                        const originalIndex = mediaItems.findIndex(m => m.id === media.id);
                        if (originalIndex !== -1) {
                            viewMedia(originalIndex);
                        }
                    });

                    gallery.appendChild(item);
                });
            }
        }

        // Helper functions
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

        function logout() {
            document.getElementById('logout-form').submit();
        }

        function createNewTrip() {
            window.location.href = '/trips/create';
        }

        function viewTrip(tripId) {
            window.location.href = `/trips/${tripId}`;
        }

        function loadMoreMedia() {
            loadMedia();
            showToast('Media refreshed', 'success');
        }

        function openSettings() {
            window.location.href = '/settings';
        }

        // Initialize video progress updates
        setInterval(() => {
            const video = document.getElementById('viewerVideo');
            const progress = document.getElementById('videoProgress');
            const time = document.getElementById('videoTime');

            if (video && !video.paused) {
                const percent = (video.currentTime / video.duration) * 100;
                progress.style.width = `${percent}%`;

                const current = formatTime(video.currentTime);
                const total = formatTime(video.duration);
                time.textContent = `${current} / ${total}`;
            }
        }, 100);

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    </script>
</body>

</html>
