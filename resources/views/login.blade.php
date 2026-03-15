<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Real-Time Chat Libraries (Pusher + Laravel Echo) -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
            width: 50px;
            height: 50px;
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

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: absolute;
            bottom: 0;
            width: 100%;
            background: var(--deep);
        }

        /* User Profile Styles */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
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
            flex-shrink: 0;
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

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-info h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }

        .user-type-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-type-badge.agency {
            background: linear-gradient(135deg, rgba(156, 39, 176, 0.2), rgba(156, 39, 176, 0.1));
            color: var(--purple);
            border: 1px solid rgba(156, 39, 176, 0.3);
        }

        .user-type-badge.traveler {
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.2), rgba(33, 150, 243, 0.1));
            color: var(--info);
            border: 1px solid rgba(33, 150, 243, 0.3);
        }

        .verified-badge {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.2), rgba(76, 175, 80, 0.1));
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Logout Button */
        .logout-btn {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--gold);
            color: var(--text-light);
            border-radius: 8px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
        }

        .logout-btn:hover {
            background: rgba(201, 169, 110, 0.3);
            border-color: #fff;
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .logout-btn i {
            font-size: 16px;
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
            flex-direction: column;
            gap: 5px;
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
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* ── Notification Dropdown ── */
        .notification-dropdown {
            position: absolute;
            top: 70px;
            right: 20px;
            width: 400px;
            max-height: 600px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(59, 31, 43, 0.2);
            border: 1px solid var(--border);
            z-index: 1001;
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .notification-dropdown.active {
            display: flex;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--card-bg), white);
        }

        .notification-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--deep);
            margin: 0;
        }

        .mark-all-read {
            background: none;
            border: none;
            color: var(--gold);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mark-all-read:hover {
            color: var(--deep);
            text-decoration: underline;
        }

        .compose-message-btn {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(201, 169, 110, 0.3);
        }

        .compose-message-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(201, 169, 110, 0.5);
        }

        .notification-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            background: white;
        }

        .notification-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .notification-tab:hover {
            background: rgba(201, 169, 110, 0.05);
            color: var(--deep);
        }

        .notification-tab.active {
            color: var(--gold);
            border-bottom-color: var(--gold);
            background: rgba(201, 169, 110, 0.1);
        }

        .notification-list {
            flex: 1;
            overflow-y: auto;
            max-height: 450px;
        }

        .notification-item {
            padding: 18px 25px;
            border-bottom: 1px solid rgba(226, 213, 199, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            gap: 15px;
            position: relative;
        }

        .notification-item:hover {
            background: rgba(201, 169, 110, 0.05);
        }

        .notification-item.unread {
            background: rgba(201, 169, 110, 0.08);
            border-left: 4px solid var(--gold);
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: var(--gold);
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.2);
        }

        .notification-icon-wrapper {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .notification-item:hover .notification-icon-wrapper {
            transform: scale(1.1);
        }

        .notification-icon-wrapper.chat {
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.2), rgba(33, 150, 243, 0.1));
            color: var(--info);
        }

        .notification-icon-wrapper.booking {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.2), rgba(76, 175, 80, 0.1));
            color: var(--success);
        }

        .notification-icon-wrapper.trip {
            background: linear-gradient(135deg, rgba(156, 39, 176, 0.2), rgba(156, 39, 176, 0.1));
            color: var(--purple);
        }

        .notification-icon-wrapper.photo {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.2), rgba(255, 152, 0, 0.1));
            color: var(--warning);
        }

        .notification-icon-wrapper.system {
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.2), rgba(244, 67, 54, 0.1));
            color: var(--danger);
        }

        .notification-content {
            flex: 1;
        }

        .notification-content h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--deep);
            margin: 0 0 5px 0;
        }

        .notification-content p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.5;
        }

        .notification-time {
            font-size: 11px;
            color: var(--text-sub);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .notification-footer {
            padding: 15px 25px;
            border-top: 1px solid var(--border);
            text-align: center;
            background: linear-gradient(135deg, white, var(--card-bg));
        }

        .view-all-notifications {
            color: var(--gold);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .view-all-notifications:hover {
            color: var(--deep);
            text-decoration: underline;
        }

        .empty-notifications {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-notifications i {
            font-size: 48px;
            color: var(--border-soft);
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-notifications h4 {
            font-size: 16px;
            color: var(--deep);
            margin-bottom: 5px;
        }

        .empty-notifications p {
            font-size: 13px;
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

        /* ── Stats Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

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
            color: var(--text-muted);
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* ── Quick Actions Grid ── */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

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

        .action-btn:nth-child(2) i {
            color: var(--purple);
        }

        .action-btn:nth-child(3) i {
            color: #FF6B6B;
        }

        .action-btn:nth-child(4) i {
            color: var(--success);
        }

        .action-btn:nth-child(5) i {
            color: var(--gold);
        }

        .action-btn:nth-child(6) i {
            color: var(--warning);
        }

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

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 60px;
            color: var(--border-soft);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--deep);
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 25px;
        }

        .empty-state .btn {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            color: var(--deep);
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(201, 169, 110, 0.3);
        }

        .empty-state .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(201, 169, 110, 0.4);
        }

        /* ── iPhone-Style Photo Gallery ── */
        .gallery-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--iphone-bg);
            z-index: 2000;
            overflow: hidden;
        }

        .gallery-modal.active {
            display: flex;
            flex-direction: column;
        }

        .gallery-header {
            background: var(--iphone-header);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gallery-header h3 {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }

        .gallery-close {
            background: none;
            border: none;
            color: var(--gold);
            font-size: 16px;
            cursor: pointer;
            padding: 5px 10px;
            font-weight: 600;
        }

        .gallery-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 4px;
        }

        .gallery-item {
            aspect-ratio: 1;
            position: relative;
            cursor: pointer;
            overflow: hidden;
            background: #1c1c1e;
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

        .gallery-item .video-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .gallery-toolbar {
            background: var(--iphone-toolbar);
            padding: 15px 20px;
            display: flex;
            justify-content: space-around;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gallery-toolbar button {
            background: none;
            border: none;
            color: var(--gold);
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 10px 20px;
        }

        .gallery-toolbar button:hover {
            transform: scale(1.2);
            filter: brightness(1.3);
        }

        /* ── Media Viewer ── */
        .media-viewer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--iphone-bg);
            z-index: 2001;
        }

        .media-viewer.active {
            display: flex;
            flex-direction: column;
        }

        .viewer-header {
            background: var(--iphone-header);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .viewer-actions {
            display: flex;
            gap: 20px;
        }

        .viewer-actions button {
            background: none;
            border: none;
            color: var(--gold);
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .viewer-actions button:hover {
            transform: scale(1.2);
        }

        .viewer-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .viewer-content img,
        .viewer-content video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* ── File Upload Area ── */
        .upload-area {
            border: 3px dashed var(--border);
            border-radius: 15px;
            padding: 60px 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.5), rgba(255, 248, 242, 0.5));
        }

        .upload-area:hover {
            border-color: var(--gold);
            background: linear-gradient(135deg, rgba(201, 169, 110, 0.1), rgba(201, 169, 110, 0.05));
            transform: scale(1.02);
        }

        .upload-area.dragover {
            border-color: var(--gold);
            background: linear-gradient(135deg, rgba(201, 169, 110, 0.2), rgba(201, 169, 110, 0.1));
        }

        .upload-area i {
            font-size: 60px;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .upload-area h3 {
            font-size: 20px;
            color: var(--deep);
            margin-bottom: 10px;
        }

        .upload-area p {
            color: var(--text-muted);
            font-size: 14px;
        }

        input[type="file"] {
            display: none;
        }

        /* ── Responsive ── */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
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
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .search-box input {
                width: 100%;
            }

            .top-nav {
                flex-direction: column;
                gap: 15px;
            }

            .nav-right {
                width: 100%;
                justify-content: space-between;
            }
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            border-radius: 50%;
            border: none;
            color: var(--deep);
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(201, 169, 110, 0.4);
            z-index: 999;
            transition: all 0.3s ease;
        }

        .mobile-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(201, 169, 110, 0.5);
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <!-- Use actual logo image matching homepage -->
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo" id="appLogo">
            <div class="logo-text">Smart Booking</div>
        </div>

        <nav class="sidebar-menu">
            <a href="/" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="/dashboard" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" onclick="openGallery(); return false;">
                <i class="fas fa-images"></i>
                <span>My Photos</span>
                <span class="menu-badge" id="photosCount">0</span>
            </a>
            <a href="/plan-trip" class="menu-item">
                <i class="fas fa-route"></i>
                <span>Plan Trip</span>
            </a>
            <a href="/flights" class="menu-item">
                <i class="fas fa-plane"></i>
                <span>Book Flights</span>
            </a>
            <a href="/bookings" class="menu-item">
                <i class="fas fa-ticket-alt"></i>
                <span>My Bookings</span>
                <span class="menu-badge" id="bookingsCount">0</span>
            </a>
            <a href="/discover" class="menu-item">
                <i class="fas fa-compass"></i>
                <span>Discover</span>
            </a>
            <a href="/destinations" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Destinations</span>
            </a>
            <a href="/community" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Community</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-heart"></i>
                <span>Saved</span>
                <span class="menu-badge" id="savedCount">0</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar" onclick="viewProfile()">
                    <!-- Real user avatar -->
                    @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" id="userAvatarImg">
                    @else
                    <div class="avatar-placeholder" id="userInitials">
                        {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1) . (strpos(Auth::user()->name, ' ') !== false ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : '')) : 'U' }}
                    </div>
                    @endif
                </div>
                <div class="user-info">
                    <h4 id="userName">{{ Auth::user()->name ?? 'User' }}</h4>
                    <div class="user-badges">
                        <span class="user-type-badge {{ Auth::user()->type ?? 'traveler' }}" id="userTypeBadge">
                            <i class="fas fa-user"></i>
                            <span id="userTypeText">{{ ucfirst(Auth::user()->type ?? 'Traveler') }}</span>
                        </span>
                        @if(Auth::check() && Auth::user()->verified)
                        <span class="verified-badge">
                            <i class="fas fa-check-circle"></i> Verified
                        </span>
                        @endif
                    </div>
                </div>
                <button class="logout-btn" onclick="logout()">
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
                <h1 id="welcomeMessage">Welcome Back!</h1>
                <p>Here's what's happening with your trips today</p>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search destinations, hotels, flights...">
                </div>
                <button class="notification-btn" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                </button>

                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3><i class="fas fa-bell"></i> Notifications</h3>
                        <div style="display: flex; gap: 10px;">
                            <button class="compose-message-btn" onclick="openComposeMessage()" title="Send a message">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button class="mark-all-read" onclick="markAllRead()">Mark all as read</button>
                        </div>
                    </div>

                    <div class="notification-tabs">
                        <div class="notification-tab active" data-tab="all" onclick="switchNotificationTab('all')">
                            <i class="fas fa-th-large"></i> All
                        </div>
                        <div class="notification-tab" data-tab="chat" onclick="switchNotificationTab('chat')">
                            <i class="fas fa-comments"></i> Chat
                        </div>
                        <div class="notification-tab" data-tab="activity" onclick="switchNotificationTab('activity')">
                            <i class="fas fa-bell"></i> Activity
                        </div>
                    </div>

                    <div class="notification-list" id="notificationList">
                        <!-- Notifications will be loaded here -->
                    </div>

                    <div class="notification-footer">
                        <a href="/notifications" class="view-all-notifications">View All Notifications</a>
                    </div>
                </div>

                <div class="nav-profile-pic" onclick="viewProfile()">
                    @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                    @else
                    <div class="placeholder" id="navUserInitials">
                        {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1) . (strpos(Auth::user()->name, ' ') !== false ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : '')) : 'U' }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card" onclick="openGallery()">
                <div class="stat-icon photos">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statPhotosCount">0</h3>
                    <p>Total Photos</p>
                    <div class="stat-change">
                        <span>Upload to get started</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon trips">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statTripsCount">0</h3>
                    <p>Planned Trips</p>
                    <div class="stat-change">
                        <span>No trips yet</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bookings">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statBookingsCount">0</h3>
                    <p>Active Bookings</p>
                    <div class="stat-change">
                        <span>No bookings yet</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon saved">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statSavedCount">0</h3>
                    <p>Saved Places</p>
                    <div class="stat-change">
                        <span>Save your favorites</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-grid">
            <div class="action-btn" onclick="uploadPhotos()">
                <i class="fas fa-upload"></i>
                <span>Upload Photos</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/plan-trip'">
                <i class="fas fa-plus-circle"></i>
                <span>Plan Trip</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/flights'">
                <i class="fas fa-plane"></i>
                <span>Book Flights</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/bookings'">
                <i class="fas fa-ticket-alt"></i>
                <span>My Bookings</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/discover'">
                <i class="fas fa-compass"></i>
                <span>Discover</span>
            </div>
            <div class="action-btn" onclick="openSettings()">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Upcoming Trips -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-route"></i>
                        Upcoming Trips
                    </h2>
                    <button class="btn" onclick="window.location.href='/plan-trip'">
                        <i class="fas fa-plus"></i>
                        New Trip
                    </button>
                </div>
                <div class="section-content">
                    <div class="empty-state">
                        <i class="fas fa-route"></i>
                        <h3>No Trips Planned Yet</h3>
                        <p>Start planning your next adventure!</p>
                        <button class="btn" onclick="window.location.href='/plan-trip'">
                            <i class="fas fa-plus"></i> Create Your First Trip
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-clock"></i>
                        Recent Activity
                    </h2>
                </div>
                <div class="section-content">
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <h3>No Activity Yet</h3>
                        <p>Your recent actions will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- iPhone-Style Gallery Modal -->
    <div class="gallery-modal" id="galleryModal">
        <div class="gallery-header">
            <h3><i class="fas fa-images"></i> My Photos & Videos</h3>
            <button class="gallery-close" onclick="closeGallery()">Done</button>
        </div>
        <div class="gallery-content" id="galleryContent">
            <div class="upload-area" onclick="triggerFileInput()" id="uploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <h3>Upload Photos & Videos</h3>
                <p>Drag and drop files here or click to browse</p>
                <input type="file" id="mediaInput" multiple accept="image/*,video/*" onchange="handleFileSelect(event)">
            </div>
            <div class="gallery-grid" id="galleryGrid"></div>
        </div>
        <div class="gallery-toolbar">
            <button onclick="triggerFileInput()"><i class="fas fa-plus"></i></button>
            <button onclick="selectAll()"><i class="fas fa-check-double"></i></button>
            <button onclick="deleteSelected()"><i class="fas fa-trash"></i></button>
            <button onclick="shareSelected()"><i class="fas fa-share"></i></button>
        </div>
    </div>

    <!-- Media Viewer -->
    <div class="media-viewer" id="mediaViewer">
        <div class="viewer-header">
            <button class="gallery-close" onclick="closeViewer()"><i class="fas fa-arrow-left"></i> Back</button>
            <div class="viewer-actions">
                <button onclick="editMedia()"><i class="fas fa-edit"></i></button>
                <button onclick="downloadMedia()"><i class="fas fa-download"></i></button>
                <button onclick="shareMedia()"><i class="fas fa-share"></i></button>
                <button onclick="deleteMedia()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="viewer-content" id="viewerContent"></div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <script>
        // User data from backend (this would typically come from your server)
        const userData = {
            name: "{{ Auth::user()->name ?? 'User' }}",
            firstName: "{{ Auth::user()->first_name ?? (Auth::user() ? explode(' ', Auth::user()->name)[0] : 'User') }}",
            avatar: "{{ Auth::user()->avatar ?? '' }}",
            type: "{{ Auth::user()->type ?? 'traveler' }}",
            verified: {{ Auth::user()->verified ?? 'false' }},
            id: "{{ Auth::user()->id ?? '' }}"
        };

        // Initialize user interface
        function initializeUserData() {
            // Set welcome message
            const welcomeMsg = document.getElementById('welcomeMessage');
            welcomeMsg.textContent = `Welcome Back, ${userData.firstName}!`;

            // Set profile pictures
            if (userData.avatar && userData.avatar !== '') {
                // Use actual user avatar
                const avatarImages = document.querySelectorAll('.user-avatar img, .nav-profile-pic img');
                avatarImages.forEach(img => {
                    if (img) {
                        img.src = userData.avatar;
                        img.style.display = 'block';
                    }
                });

                // Hide placeholders
                document.querySelectorAll('.avatar-placeholder, .placeholder').forEach(el => {
                    el.style.display = 'none';
                });
            } else {
                // Use initials if no avatar
                const initials = userData.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                const initialsElements = document.querySelectorAll('.avatar-placeholder, .placeholder');
                initialsElements.forEach(el => {
                    el.textContent = initials;
                    el.style.display = 'flex';
                });

                // Hide avatar images
                document.querySelectorAll('.user-avatar img, .nav-profile-pic img').forEach(img => {
                    if (img) img.style.display = 'none';
                });
            }

            // Update user name
            document.getElementById('userName').textContent = userData.name;

            // Update user type badge
            const userTypeBadge = document.getElementById('userTypeBadge');
            if (userTypeBadge) {
                userTypeBadge.className = `user-type-badge ${userData.type}`;
                const userTypeText = document.getElementById('userTypeText');
                if (userTypeText) {
                    userTypeText.textContent = userData.type.charAt(0).toUpperCase() + userData.type.slice(1);
                }
            }

            // Load real user statistics
            loadUserStatistics();
        }

        // Load real statistics from backend
        function loadUserStatistics() {
            // In a real application, this would fetch from your API
            fetch('/api/user/statistics')
                .then(response => response.json())
                .then(data => {
                    updateCounts(data);
                })
                .catch(error => {
                    console.log('Using default counts');
                    // Use default counts if API fails
                });
        }

        // Update counts from real data
        function updateCounts(data = null) {
            const photoCount = data?.photos || mediaLibrary.length;
            const tripsCount = data?.trips || 0;
            const bookingsCount = data?.bookings || 0;
            const savedCount = data?.saved || 0;
            const notificationCount = data?.notifications || 0;

            // Update all count displays
            const photosCountEl = document.getElementById('photosCount');
            const statPhotosCountEl = document.getElementById('statPhotosCount');
            const bookingsCountEl = document.getElementById('bookingsCount');
            const statBookingsCountEl = document.getElementById('statBookingsCount');
            const savedCountEl = document.getElementById('savedCount');
            const statSavedCountEl = document.getElementById('statSavedCount');
            const statTripsCountEl = document.getElementById('statTripsCount');
            const notificationCountEl = document.getElementById('notificationCount');

            if (photosCountEl) photosCountEl.textContent = photoCount;
            if (statPhotosCountEl) statPhotosCountEl.textContent = photoCount;
            if (bookingsCountEl) bookingsCountEl.textContent = bookingsCount;
            if (statBookingsCountEl) statBookingsCountEl.textContent = bookingsCount;
            if (savedCountEl) savedCountEl.textContent = savedCount;
            if (statSavedCountEl) statSavedCountEl.textContent = savedCount;
            if (statTripsCountEl) statTripsCountEl.textContent = tripsCount;
            if (notificationCountEl) {
                notificationCountEl.textContent = notificationCount;
                notificationCountEl.style.display = notificationCount > 0 ? 'block' : 'none';
            }
        }

        // Media storage
        let mediaLibrary = [];
        let selectedMedia = new Set();
        let currentMediaIndex = 0;

        // Notification system
        let notifications = [];
        let currentTab = 'all';
        let unreadCount = 0;
        let pusherChannel = null;
        let chatPollingInterval = null;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            initializeUserData();
            loadMediaFromStorage();
            loadNotifications();
            initializeRealTimeChat();

            // Poll for new notifications every 5 seconds (fallback if WebSocket fails)
            setInterval(loadNotifications, 5000);
        });

        // Real-Time Chat with Pusher/WebSocket
        function initializeRealTimeChat() {
            // Try to initialize Pusher if available
            if (typeof Pusher !== 'undefined') {
                try {
                    const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
                        cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
                        encrypted: true
                    });

                    // Subscribe to user's private channel
                    pusherChannel = pusher.subscribe('private-user.{{ Auth::id() }}');

                    // Listen for new chat messages
                    pusherChannel.bind('new-chat-message', function(data) {
                        handleRealTimeChatMessage(data);
                    });

                    // Listen for notification updates
                    pusherChannel.bind('notification', function(data) {
                        handleRealTimeNotification(data);
                    });

                    console.log('✅ Real-time chat initialized with Pusher');
                } catch (error) {
                    console.log('Pusher not available, using polling fallback');
                    startChatPolling();
                }
            } else {
                console.log('Pusher library not loaded, using polling fallback');
                startChatPolling();
            }
        }

        // Fallback: Fast polling for real-time feel
        function startChatPolling() {
            // Poll every 2 seconds for chat messages
            chatPollingInterval = setInterval(() => {
                loadNotifications(true); // silent mode
            }, 2000);
        }

        // Handle real-time chat message
        function handleRealTimeChatMessage(data) {
            const newNotification = {
                id: data.message_id || Date.now(),
                type: 'chat',
                title: `New chat from ${data.sender_name}`,
                message: data.content,
                time: 'Just now',
                read: false,
                user: {
                    name: data.sender_name,
                    avatar: data.sender_avatar,
                    initials: data.sender_initials
                }
            };

            // Add to notifications array
            notifications.unshift(newNotification);
            unreadCount++;

            // Update UI
            updateNotificationBadge();
            renderNotifications();

            // Play notification sound
            playNotificationSound();

            // Show toast notification
            showChatToast(data.sender_name, data.content);
        }

        // Handle real-time notification
        function handleRealTimeNotification(data) {
            notifications.unshift(data);
            if (!data.read) unreadCount++;
            updateNotificationBadge();
            renderNotifications();
        }

        // Show toast notification for new chat
        function showChatToast(sender, message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: white;
                border: 2px solid var(--gold);
                border-radius: 12px;
                padding: 15px 20px;
                box-shadow: 0 8px 24px rgba(59, 31, 43, 0.2);
                z-index: 10000;
                min-width: 300px;
                max-width: 400px;
                animation: slideInRight 0.4s ease;
                cursor: pointer;
            `;

            const preview = message.length > 60 ? message.substring(0, 60) + '...' : message;

            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--gold), var(--deep)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px;">
                        ${sender.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: var(--deep); margin-bottom: 3px;">
                            <i class="fas fa-comments" style="color: var(--gold);"></i>
                            ${sender}
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted);">
                            ${preview}
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 16px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            toast.onclick = function() {
                window.location.href = '/chat';
                this.remove();
            };

            document.body.appendChild(toast);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.4s ease';
                setTimeout(() => toast.remove(), 400);
            }, 5000);
        }

        // Play notification sound
        function playNotificationSound() {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBDGH0fPTgjMGHm7A7+OZUA0PVqzn77BdGgc+ltryxnYpBSh+zPLaizsIGGS57OihUxELTKXh8bllHAU2kNXzzn0vBSh6yfDajDwIFmq+7eibUg4OVKzl8LRfGgc8ldjywngqBCh9y/HajjwIFmm97OmgURALTqPi8bllHAU3kdXzzoAuBSh6yfDajjsJFWq97OmgUg0PVanl8LVfGgc8ldryw3kpBCd9y/DajjsJFWq+7OmfUhAMTqPh8bhnHgU3kdXzzn4vBCh6yfDajjsJFWq+7OidUREMTqPh8bhmHQU3kdXzzn4vBCd7yfDajjsJFmq97OmdUREMTqTg8bhmHQU3kdTzz34uBSd7yfDajjsJFmq97OmdUREMT6Th8bhpHgU2kNTzzoAuBSd7yfDbjTsIFmq97OicUhAMT6Tg8bppHgU2kNTzz4AuBSZ7yfDbkToJFWq97Omc');
                audio.volume = 0.3;
                audio.play().catch(() => {}); // Ignore if autoplay blocked
            } catch (e) {
                console.log('Could not play notification sound');
            }
        }

        // Add CSS animation for toast
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Notification Functions
        function loadNotifications(silent = false) {
            // Load from API
            fetch('/api/notifications')
                .then(response => response.json())
                .then(data => {
                    notifications = data.notifications || getSampleNotifications();
                    unreadCount = notifications.filter(n => !n.read).length;
                    updateNotificationBadge();
                    renderNotifications();
                })
                .catch(error => {
                    if (!silent) {
                        console.log('Using sample notifications');
                    }
                    notifications = getSampleNotifications();
                    unreadCount = notifications.filter(n => !n.read).length;
                    updateNotificationBadge();
                    renderNotifications();
                });
        }

        function getSampleNotifications() {
            return [
                {
                    id: 1,
                    type: 'chat',
                    title: 'New chat from Sarah Johnson',
                    message: 'Hey! I saw you\'re planning a trip to Bali. I have some great recommendations!',
                    time: '5 minutes ago',
                    read: false,
                    user: {
                        name: 'Sarah Johnson',
                        avatar: null,
                        initials: 'SJ'
                    }
                },
                {
                    id: 2,
                    type: 'booking',
                    title: 'Booking Confirmed',
                    message: 'Your flight to Tokyo has been confirmed. Check-in opens 24 hours before departure.',
                    time: '2 hours ago',
                    read: false
                },
                {
                    id: 3,
                    type: 'chat',
                    title: 'Michael Roberts sent you a chat',
                    message: 'Thanks for the travel tips! The restaurant you recommended was amazing.',
                    time: '5 hours ago',
                    read: true,
                    user: {
                        name: 'Michael Roberts',
                        avatar: null,
                        initials: 'MR'
                    }
                },
                {
                    id: 4,
                    type: 'trip',
                    title: 'Trip Reminder',
                    message: 'Your trip to Paris starts in 5 days. Don\'t forget to pack!',
                    time: '1 day ago',
                    read: false
                },
                {
                    id: 5,
                    type: 'photo',
                    title: 'Photos Uploaded',
                    message: 'Successfully uploaded 24 photos to your Bali album.',
                    time: '2 days ago',
                    read: true
                },
                {
                    id: 6,
                    type: 'chat',
                    title: 'Anna Chen mentioned you',
                    message: 'Anna Chen mentioned you in a chat: "You should check out this place!"',
                    time: '2 days ago',
                    read: true,
                    user: {
                        name: 'Anna Chen',
                        avatar: null,
                        initials: 'AC'
                    }
                },
                {
                    id: 7,
                    type: 'booking',
                    title: 'Price Drop Alert',
                    message: 'Good news! The hotel you saved in Santorini dropped by 25%.',
                    time: '3 days ago',
                    read: true
                },
                {
                    id: 8,
                    type: 'system',
                    title: 'Account Verified',
                    message: 'Congratulations! Your account has been successfully verified.',
                    time: '1 week ago',
                    read: true
                }
            ];
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');

            // Mark as read when opened
            if (dropdown.classList.contains('active')) {
                setTimeout(() => {
                    markVisibleAsRead();
                }, 1000);
            }
        }

        function switchNotificationTab(tab) {
            currentTab = tab;

            // Update tab styling
            document.querySelectorAll('.notification-tab').forEach(t => {
                t.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');

            // Render filtered notifications
            renderNotifications();
        }

        function renderNotifications() {
            const listEl = document.getElementById('notificationList');
            let filteredNotifications = notifications;

            // Filter by tab
            if (currentTab === 'chat') {
                filteredNotifications = notifications.filter(n => n.type === 'chat');
            } else if (currentTab === 'activity') {
                filteredNotifications = notifications.filter(n => n.type !== 'chat');
            }

            if (filteredNotifications.length === 0) {
                listEl.innerHTML = `
                    <div class="empty-notifications">
                        <i class="fas fa-bell-slash"></i>
                        <h4>No notifications</h4>
                        <p>You're all caught up!</p>
                    </div>
                `;
                return;
            }

            listEl.innerHTML = filteredNotifications.map(notif => {
                const iconClass = getNotificationIcon(notif.type);
                const userAvatar = notif.user ?
                    (notif.user.avatar ?
                        `<img src="${notif.user.avatar}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">` :
                        `<div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">${notif.user.initials}</div>`
                    ) :
                    `<i class="${iconClass}"></i>`;

                return `
                    <div class="notification-item ${notif.read ? '' : 'unread'}" onclick="handleNotificationClick(${notif.id})">
                        <div class="notification-icon-wrapper ${notif.type}">
                            ${userAvatar}
                        </div>
                        <div class="notification-content">
                            <h4>${notif.title}</h4>
                            <p>${notif.message}</p>
                            <div class="notification-time">
                                <i class="fas fa-clock"></i>
                                ${notif.time}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getNotificationIcon(type) {
            const icons = {
                'chat': 'fas fa-comments',
                'booking': 'fas fa-ticket-alt',
                'trip': 'fas fa-route',
                'photo': 'fas fa-images',
                'system': 'fas fa-info-circle'
            };
            return icons[type] || 'fas fa-bell';
        }

        function updateNotificationBadge() {
            const badge = document.getElementById('notificationCount');
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }

        function markAllRead() {
            notifications = notifications.map(n => ({...n, read: true}));
            unreadCount = 0;
            updateNotificationBadge();
            renderNotifications();

            // Send to backend
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).catch(console.error);

            Swal.fire({
                title: 'All notifications marked as read',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }

        function markVisibleAsRead() {
            const unreadNotifs = notifications.filter(n => !n.read);
            if (unreadNotifs.length === 0) return;

            const unreadIds = unreadNotifs.map(n => n.id);

            notifications = notifications.map(n =>
                unreadIds.includes(n.id) ? {...n, read: true} : n
            );

            unreadCount = notifications.filter(n => !n.read).length;
            updateNotificationBadge();
            renderNotifications();

            // Send to backend
            fetch('/api/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: unreadIds })
            }).catch(console.error);
        }

        function handleNotificationClick(notificationId) {
            const notification = notifications.find(n => n.id === notificationId);
            if (!notification) return;

            // Mark as read
            notifications = notifications.map(n =>
                n.id === notificationId ? {...n, read: true} : n
            );
            unreadCount = notifications.filter(n => !n.read).length;
            updateNotificationBadge();
            renderNotifications();

            // Navigate based on type
            if (notification.type === 'chat') {
                window.location.href = '/chat';
            } else if (notification.type === 'booking') {
                window.location.href = '/bookings';
            } else if (notification.type === 'trip') {
                window.location.href = '/plan-trip';
            } else if (notification.type === 'photo') {
                openGallery();
                toggleNotifications();
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const button = document.querySelector('.notification-btn');

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Compose Chat Functions
        function openComposeMessage() {
            Swal.fire({
                title: '<i class="fas fa-comments"></i> Send API Chat Message',
                html: `
                    <div style="text-align: left; padding: 10px 20px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--deep);">
                                <i class="fas fa-user"></i> To:
                            </label>
                            <input type="text" id="userSearch" placeholder="Search users..."
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px;"
                                oninput="searchUsers(this.value)">
                            <div id="userSearchResults" style="max-height: 150px; overflow-y: auto; margin-top: 10px; border: 1px solid var(--border); border-radius: 8px; display: none;">
                            </div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <div id="selectedUser" style="display: none; padding: 12px; background: rgba(201, 169, 110, 0.1); border-radius: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div id="selectedUserAvatar"></div>
                                    <div>
                                        <div id="selectedUserName" style="font-weight: 600; color: var(--deep);"></div>
                                        <div id="selectedUserType" style="font-size: 12px; color: var(--text-muted);"></div>
                                    </div>
                                    <button onclick="clearSelectedUser()" style="margin-left: auto; background: none; border: none; color: var(--danger); cursor: pointer; font-size: 18px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--deep);">
                                <i class="fas fa-comments"></i> Chat Message:
                            </label>
                            <textarea id="messageContent" placeholder="Type your chat message here..."
                                style="width: 100%; min-height: 120px; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Georgia', serif; resize: vertical;"
                                maxlength="1000"></textarea>
                            <div style="text-align: right; font-size: 12px; color: var(--text-muted); margin-top: 5px;">
                                <span id="charCount">0</span>/1000 characters
                            </div>
                        </div>
                        <input type="hidden" id="selectedUserId" value="">
                    </div>
                `,
                width: 600,
                showCancelButton: true,
                confirmButtonColor: '#c9a96e',
                cancelButtonColor: '#f44336',
                confirmButtonText: '<i class="fas fa-paper-plane"></i> Send Chat',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                didOpen: () => {
                    // Add character counter
                    const textarea = document.getElementById('messageContent');
                    textarea.addEventListener('input', function() {
                        document.getElementById('charCount').textContent = this.value.length;
                    });
                },
                preConfirm: () => {
                    const userId = document.getElementById('selectedUserId').value;
                    const message = document.getElementById('messageContent').value.trim();

                    if (!userId) {
                        Swal.showValidationMessage('Please select a user to chat with');
                        return false;
                    }

                    if (!message) {
                        Swal.showValidationMessage('Please enter a chat message');
                        return false;
                    }

                    if (message.length > 1000) {
                        Swal.showValidationMessage('Message is too long (max 1000 characters)');
                        return false;
                    }

                    return sendMessage(userId, message);
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Chat Message Sent!',
                        text: 'Your message has been delivered in real-time.',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000
                    });

                    // Refresh notifications
                    loadNotifications();
                }
            });
        }

        let searchTimeout;
        let availableUsers = [];

        function searchUsers(query) {
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                document.getElementById('userSearchResults').style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                // Fetch users from API
                fetch(`/api/users/search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        availableUsers = data.users || getSampleUsers().filter(u =>
                            u.name.toLowerCase().includes(query.toLowerCase())
                        );
                        displaySearchResults(availableUsers);
                    })
                    .catch(error => {
                        console.log('Using sample users');
                        availableUsers = getSampleUsers().filter(u =>
                            u.name.toLowerCase().includes(query.toLowerCase())
                        );
                        displaySearchResults(availableUsers);
                    });
            }, 300);
        }

        function getSampleUsers() {
            return [
                { id: 2, name: 'Sarah Johnson', type: 'traveler', avatar: null, verified: true },
                { id: 3, name: 'Michael Roberts', type: 'traveler', avatar: null, verified: false },
                { id: 4, name: 'Anna Chen', type: 'agency', avatar: null, verified: true },
                { id: 5, name: 'David Martinez', type: 'traveler', avatar: null, verified: true },
                { id: 6, name: 'Emily Wilson', type: 'agency', avatar: null, verified: true },
                { id: 7, name: 'James Brown', type: 'traveler', avatar: null, verified: false },
                { id: 8, name: 'Lisa Anderson', type: 'traveler', avatar: null, verified: true },
                { id: 9, name: 'Tom Smith', type: 'agency', avatar: null, verified: true }
            ];
        }

        function displaySearchResults(users) {
            const resultsDiv = document.getElementById('userSearchResults');

            if (users.length === 0) {
                resultsDiv.innerHTML = '<div style="padding: 15px; text-align: center; color: var(--text-muted);">No users found</div>';
                resultsDiv.style.display = 'block';
                return;
            }

            resultsDiv.innerHTML = users.map(user => {
                const initials = user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                const avatarHtml = user.avatar ?
                    `<img src="${user.avatar}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">` :
                    `<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">${initials}</div>`;

                const badge = user.type === 'agency' ?
                    '<span style="background: rgba(156, 39, 176, 0.1); color: var(--purple); padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;"><i class="fas fa-building"></i> Agency</span>' :
                    '';

                const verifiedBadge = user.verified ?
                    '<i class="fas fa-check-circle" style="color: var(--success); font-size: 12px; margin-left: 5px;"></i>' : '';

                return `
                    <div onclick="selectUser(${user.id})" style="padding: 12px; cursor: pointer; border-bottom: 1px solid var(--border); transition: all 0.3s ease; display: flex; align-items: center; gap: 12px;">
                        ${avatarHtml}
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: var(--deep); font-size: 14px;">
                                ${user.name}
                                ${verifiedBadge}
                                ${badge}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            resultsDiv.style.display = 'block';

            // Add hover effect
            resultsDiv.querySelectorAll('div[onclick^="selectUser"]').forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.background = 'rgba(201, 169, 110, 0.1)';
                });
                el.addEventListener('mouseleave', function() {
                    this.style.background = 'transparent';
                });
            });
        }

        function selectUser(userId) {
            const user = availableUsers.find(u => u.id === userId);
            if (!user) return;

            const initials = user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
            const avatarHtml = user.avatar ?
                `<img src="${user.avatar}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">` :
                `<div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">${initials}</div>`;

            document.getElementById('selectedUserId').value = userId;
            document.getElementById('selectedUser').style.display = 'block';
            document.getElementById('selectedUserAvatar').innerHTML = avatarHtml;
            document.getElementById('selectedUserName').textContent = user.name;
            document.getElementById('selectedUserType').textContent = user.type === 'agency' ? 'Travel Agency' : 'Traveler';
            document.getElementById('userSearch').value = '';
            document.getElementById('userSearchResults').style.display = 'none';

            // Focus on message textarea
            document.getElementById('messageContent').focus();
        }

        function clearSelectedUser() {
            document.getElementById('selectedUserId').value = '';
            document.getElementById('selectedUser').style.display = 'none';
            document.getElementById('userSearch').focus();
        }

        async function sendMessage(userId, message) {
            try {
                const response = await fetch('/api/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        receiver_id: userId,
                        content: message
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to send chat message');
                }

                const data = await response.json();

                // Show success toast
                const toast = document.createElement('div');
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
                    color: white;
                    padding: 15px 25px;
                    border-radius: 12px;
                    box-shadow: 0 8px 24px rgba(201, 169, 110, 0.4);
                    z-index: 10000;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    animation: slideInUp 0.4s ease;
                `;
                toast.innerHTML = `
                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    Chat message sent in real-time!
                `;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.animation = 'slideOutDown 0.4s ease';
                    setTimeout(() => toast.remove(), 400);
                }, 3000);

                return data;
            } catch (error) {
                console.error('Error sending chat message:', error);
                Swal.showValidationMessage('Failed to send chat. Please try again.');
                return false;
            }
        }

        // Add slide animations
        const slideStyle = document.createElement('style');
        slideStyle.textContent = `
            @keyframes slideInUp {
                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes slideOutDown {
                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(100px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(slideStyle);

        // Toggle sidebar for mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Gallery functions
        function openGallery() {
            document.getElementById('galleryModal').classList.add('active');
            renderGallery();
        }

        function closeGallery() {
            document.getElementById('galleryModal').classList.remove('active');
        }

        function triggerFileInput() {
            document.getElementById('mediaInput').click();
        }

        // File handling
        function handleFileSelect(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const mediaItem = {
                        id: Date.now() + Math.random(),
                        type: file.type.startsWith('image/') ? 'image' : 'video',
                        src: e.target.result,
                        name: file.name,
                        date: new Date().toISOString()
                    };
                    mediaLibrary.push(mediaItem);
                    saveMediaToStorage();
                    renderGallery();
                    updateMediaCounts();
                };
                reader.readAsDataURL(file);
            });
            event.target.value = '';
        }

        // Drag and drop
        const uploadArea = document.getElementById('uploadArea');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            });
        });

        uploadArea.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('mediaInput').files = files;
            handleFileSelect({ target: { files: files } });
        });

        // Render gallery
        function renderGallery() {
            const galleryGrid = document.getElementById('galleryGrid');
            if (mediaLibrary.length === 0) {
                galleryGrid.innerHTML = '';
                return;
            }

            galleryGrid.innerHTML = mediaLibrary.map((item, index) => `
                <div class="gallery-item" onclick="viewMedia(${index})">
                    ${item.type === 'image' ?
                    `<img src="${item.src}" alt="${item.name}">` :
                    `<video src="${item.src}"></video>
                        <div class="video-badge">
                            <i class="fas fa-play"></i>
                            Video
                        </div>`
                }
                </div>
            `).join('');
        }

        // View media
        function viewMedia(index) {
            currentMediaIndex = index;
            const item = mediaLibrary[index];
            const viewerContent = document.getElementById('viewerContent');

            if (item.type === 'image') {
                viewerContent.innerHTML = `<img src="${item.src}" alt="${item.name}">`;
            } else {
                viewerContent.innerHTML = `<video src="${item.src}" controls autoplay></video>`;
            }

            document.getElementById('mediaViewer').classList.add('active');
        }

        function closeViewer() {
            document.getElementById('mediaViewer').classList.remove('active');
            const viewerContent = document.getElementById('viewerContent');
            viewerContent.innerHTML = '';
        }

        // Media actions
        function editMedia() {
            Swal.fire({
                title: 'Edit Media',
                html: `
                    <div style="text-align: left;">
                        <p><strong>Editing Features:</strong></p>
                        <ul style="margin-left: 20px;">
                            <li>Crop & Rotate</li>
                            <li>Filters & Adjustments</li>
                            <li>Add Text & Stickers</li>
                            <li>Drawing Tools</li>
                        </ul>
                    </div>
                `,
                icon: 'info',
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Open Editor'
            });
        }

        function downloadMedia() {
            const item = mediaLibrary[currentMediaIndex];
            const link = document.createElement('a');
            link.href = item.src;
            link.download = item.name;
            link.click();
            Swal.fire({
                title: 'Downloaded!',
                text: 'Media has been saved to your device',
                icon: 'success',
                confirmButtonColor: '#c9a96e',
                timer: 2000
            });
        }

        function shareMedia() {
            Swal.fire({
                title: 'Share Media',
                text: 'Choose how you want to share this media',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Copy Link'
            });
        }

        function deleteMedia() {
            Swal.fire({
                title: 'Delete Media?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    mediaLibrary.splice(currentMediaIndex, 1);
                    saveMediaToStorage();
                    updateMediaCounts();
                    closeViewer();
                    renderGallery();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Media has been removed',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000
                    });
                }
            });
        }

        function selectAll() {
            selectedMedia = new Set(mediaLibrary.map((_, i) => i));
            Swal.fire({
                title: 'All Selected',
                text: `${mediaLibrary.length} items selected`,
                icon: 'success',
                confirmButtonColor: '#c9a96e',
                timer: 1500
            });
        }

        function deleteSelected() {
            if (selectedMedia.size === 0) {
                Swal.fire({
                    title: 'No Selection',
                    text: 'Please select items first',
                    icon: 'warning',
                    confirmButtonColor: '#c9a96e'
                });
                return;
            }

            Swal.fire({
                title: `Delete ${selectedMedia.size} items?`,
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: 'Yes, delete them'
            }).then((result) => {
                if (result.isConfirmed) {
                    mediaLibrary = mediaLibrary.filter((_, i) => !selectedMedia.has(i));
                    selectedMedia.clear();
                    saveMediaToStorage();
                    updateMediaCounts();
                    renderGallery();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Selected items have been removed',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000
                    });
                }
            });
        }

        function shareSelected() {
            Swal.fire({
                title: 'Share Selected',
                text: `Share ${selectedMedia.size} selected items`,
                icon: 'info',
                confirmButtonColor: '#c9a96e'
            });
        }

        // Storage functions
        function saveMediaToStorage() {
            localStorage.setItem('smartBookingMedia', JSON.stringify(mediaLibrary));
        }

        function loadMediaFromStorage() {
            const stored = localStorage.getItem('smartBookingMedia');
            if (stored) {
                mediaLibrary = JSON.parse(stored);
                updateMediaCounts();
            }
        }

        // Update counts
        function updateMediaCounts() {
            const photoCount = mediaLibrary.length;

            // Update display counts
            const counts = {
                photos: photoCount,
                trips: 0,
                bookings: 0,
                saved: 0,
                notifications: photoCount > 0 ? 1 : 0
            };

            updateCounts(counts);
        }

        // Action functions
        function uploadPhotos() {
            openGallery();
        }

        function viewProfile() {
            Swal.fire({
                title: 'Your Profile',
                html: `
                    <div style="text-align: center; margin-bottom: 20px;">
                        ${userData.avatar ?
                            `<img src="${userData.avatar}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--gold);">` :
                            `<div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">${userData.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}</div>`
                        }
                    </div>
                    <div style="text-align: left; padding: 0 20px;">
                        <p style="margin: 10px 0;"><strong>Name:</strong> ${userData.name}</p>
                        <p style="margin: 10px 0;"><strong>User Type:</strong> ${userData.type.charAt(0).toUpperCase() + userData.type.slice(1)}</p>
                        <p style="margin: 10px 0;"><strong>Verified:</strong> ${userData.verified ? '✅ Yes' : '❌ No'}</p>
                        <p style="margin: 10px 0;"><strong>User ID:</strong> ${userData.id || 'N/A'}</p>
                    </div>
                `,
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Edit Profile',
                showCancelButton: true,
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/profile/edit';
                }
            });
        }

        function openSettings() {
            Swal.fire({
                title: 'Settings',
                html: `
                    <div style="text-align: left; padding: 0 20px;">
                        <h4 style="margin-top: 20px;">Account Settings</h4>
                        <p>• Update profile information</p>
                        <p>• Change password</p>
                        <p>• Privacy settings</p>
                        <h4 style="margin-top: 20px;">Notification Preferences</h4>
                        <p>• Email notifications</p>
                        <p>• Push notifications</p>
                        <h4 style="margin-top: 20px;">Travel Preferences</h4>
                        <p>• Default budget range</p>
                        <p>• Preferred destinations</p>
                    </div>
                `,
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Go to Settings',
                showCancelButton: true,
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/settings';
                }
            });
        }

        function logout() {
            Swal.fire({
                title: 'Logout',
                text: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c9a96e',
                cancelButtonColor: '#f44336',
                confirmButtonText: 'Yes, logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit it with POST method
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/logout';

                    // Add CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken.getAttribute('content');
                        form.appendChild(csrfInput);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Add active state to menu items
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function (e) {
                // Only prevent default and toggle active for items without real hrefs
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                // Don't change active state if this item handles its own navigation
                if (!this.onclick || this.getAttribute('href') === '#') {
                    document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>

</html>
